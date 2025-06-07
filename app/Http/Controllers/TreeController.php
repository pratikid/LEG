<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tree;
use App\Services\GedcomService;
use App\Services\Neo4jIndividualService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class TreeController extends Controller
{
    protected $neo4jService;

    public function __construct(Neo4jIndividualService $neo4jService)
    {
        $this->neo4jService = $neo4jService;
    }

    public function index(Request $request): View
    {
        $query = Tree::withCount(['individuals', 'groups'])
            ->with(['individuals' => function ($q) {
                $q->latest()->limit(3);
            }]);

        // Search functionality
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Whitelist allowed sort columns and directions
        $allowedSort = ['created_at', 'name', 'individuals_count'];
        $allowedDir = ['asc', 'desc'];

        $sort = in_array($request->get('sort'), $allowedSort, true) ? $request->get('sort') : 'created_at';
        $direction = in_array($request->get('direction'), $allowedDir, true) ? $request->get('direction') : 'desc';

        $query->orderBy($sort, $direction);

        $trees = $query->paginate(10)->withQueryString();

        return view('trees.index', compact('trees'));
    }

    public function import(): \Illuminate\View\View
    {
        return view('trees.import');
    }

    public function handleImport(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'gedcom' => ['required', 'file', 'mimes:ged,gedcom', 'max:10240'],
        ]);
        $path = $request->file('gedcom')->store('gedcoms', 'private');
        $content = file_get_contents(storage_path('app/private/'.$path));

        // Parse and import GEDCOM
        $gedcomService = new GedcomService;
        $parsed = $gedcomService->parse($content);
        // TODO: Choose/create tree for import. For now, create a new tree per import.
        $tree = \App\Models\Tree::create([
            'name' => 'Imported Tree '.now()->format('Y-m-d H:i:s'),
            'user_id' => $request->user()->id,
            'description' => 'Imported from GEDCOM',
        ]);
        $gedcomService->importToDatabase($parsed, $tree->id);

        return redirect()->route('trees.index')->with('success', 'GEDCOM file imported successfully.');
    }

    /**
     * Export a tree as a GEDCOM file.
     *
     * @param  int  $id  Tree ID
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportGedcom($id)
    {
        $tree = \App\Models\Tree::findOrFail((int) $id);
        $gedcomService = new GedcomService;
        $gedcomContent = $gedcomService->exportFromDatabase($tree->id);
        $filename = 'tree_'.$tree->id.'_'.now()->format('Ymd_His').'.ged';

        return response()->streamDownload(function () use ($gedcomContent) {
            echo $gedcomContent;
        }, $filename, [
            'Content-Type' => 'text/plain',
        ]);
    }

    public function create(): View
    {
        return view('trees.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $neo4jTransaction = null;

        try {
            DB::beginTransaction();
            $neo4jTransaction = $this->neo4jService->beginTransaction();

            $tree = Tree::create($validated);

            // Create tree node in Neo4j
            $this->neo4jService->createTreeNode([
                'id' => $tree->id,
                'name' => $tree->name,
                'description' => $tree->description,
            ], $neo4jTransaction);

            $neo4jTransaction->commit();
            DB::commit();

            return redirect()->route('trees.show', $tree)
                ->with('success', 'Tree created successfully.');
        } catch (\Exception $e) {
            if ($neo4jTransaction) {
                $neo4jTransaction->rollback();
            }
            DB::rollBack();

            Log::error('Failed to create tree', [
                'input' => $validated,
                'exception' => $e,
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    public function show($id)
    {
        $tree = Tree::findOrFail($id);

        // Get tree data from Neo4j
        $treeData = $this->neo4jService->getTreeIndividuals($id);

        // Convert Neo4j results to array format for D3.js
        $treeDataArray = [
            'name' => $tree->name,
            'children' => [],
        ];

        foreach ($treeData as $record) {
            $individual = $record->get('i');
            $treeDataArray['children'][] = [
                'id' => $individual->getProperty('id'),
                'name' => $individual->getProperty('first_name').' '.$individual->getProperty('last_name'),
                'birth_date' => $individual->getProperty('birth_date'),
                'death_date' => $individual->getProperty('death_date'),
            ];
        }

        return view('trees.show', [
            'tree' => $tree,
            'treeDataJson' => json_encode($treeDataArray),
        ]);
    }

    public function edit($id): View
    {
        $tree = Tree::findOrFail((int) $id);

        return view('trees.edit', compact('tree'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $neo4jTransaction = null;

        try {
            DB::beginTransaction();
            $neo4jTransaction = $this->neo4jService->beginTransaction();

            $tree = Tree::findOrFail($id);
            $tree->update($validated);

            // Update tree node in Neo4j
            $this->neo4jService->updateTreeNode([
                'id' => $tree->id,
                'name' => $tree->name,
                'description' => $tree->description,
            ], $neo4jTransaction);

            $neo4jTransaction->commit();
            DB::commit();

            return redirect()->route('trees.show', $tree)
                ->with('success', 'Tree updated successfully.');
        } catch (\Exception $e) {
            if ($neo4jTransaction) {
                $neo4jTransaction->rollback();
            }
            DB::rollBack();

            Log::error('Failed to update tree', [
                'id' => $id,
                'input' => $validated,
                'exception' => $e,
            ]);

            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    public function destroy($id)
    {
        $neo4jTransaction = null;

        try {
            DB::beginTransaction();
            $neo4jTransaction = $this->neo4jService->beginTransaction();

            $tree = Tree::findOrFail($id);

            // Delete SQL record first
            $tree->delete();

            // Then delete Neo4j node
            $this->neo4jService->deleteTreeNode($id, $neo4jTransaction);

            $neo4jTransaction->commit();
            DB::commit();

            return redirect()->route('trees.index')
                ->with('success', 'Tree deleted successfully.');
        } catch (\Exception $e) {
            if ($neo4jTransaction) {
                $neo4jTransaction->rollback();
            }
            DB::rollBack();

            Log::error('Failed to delete tree', [
                'id' => $id,
                'exception' => $e,
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'An unexpected error occurred. Please try again later.']);
        }
    }
}
