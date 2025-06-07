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
use Laudis\Neo4j\Contracts\TransactionInterface;

class TreeController extends Controller
{
    protected Neo4jIndividualService $neo4jService;

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
            $search = $request->input('search', '');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Whitelist allowed sort columns and directions
        $allowedSort = ['created_at', 'name', 'individuals_count'];
        $allowedDir = ['asc', 'desc'];

        $sort = in_array($request->input('sort'), $allowedSort, true) ? $request->input('sort') : 'created_at';
        $direction = in_array($request->input('direction'), $allowedDir, true) ? $request->input('direction') : 'desc';

        $query->orderBy($sort, $direction);

        $trees = $query->paginate(10)->withQueryString();

        return view('trees.index', compact('trees'));
    }

    public function import(): View
    {
        return view('trees.import');
    }

    public function handleImport(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'gedcom' => ['required', 'file', 'mimes:ged,gedcom', 'max:10240'],
        ]);

        $file = $request->file('gedcom');
        if (!$file) {
            return redirect()->back()->withErrors(['gedcom' => 'No file was uploaded.']);
        }

        $path = $file->store('gedcoms', 'private');
        $content = file_get_contents(storage_path('app/private/'.$path));
        if ($content === false) {
            return redirect()->back()->withErrors(['gedcom' => 'Failed to read the uploaded file.']);
        }

        // Parse and import GEDCOM
        $gedcomService = new GedcomService;
        $parsed = $gedcomService->parse($content);
        
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // TODO: Choose/create tree for import. For now, create a new tree per import.
        $tree = Tree::create([
            'name' => 'Imported Tree '.now()->format('Y-m-d H:i:s'),
            'user_id' => $user->id,
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
    public function exportGedcom(int $id): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $tree = Tree::findOrFail($id);
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

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array{name: string, description: string|null, user_id: int} $validated */
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        $validated['user_id'] = $user->id;
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
                'user_id' => $user->id,
            ], $neo4jTransaction);

            $neo4jTransaction->run('COMMIT');
            DB::commit();

            return redirect()->route('trees.show', $tree)
                ->with('success', 'Tree created successfully.');
        } catch (\Exception $e) {
            if ($neo4jTransaction) {
                try {
                    $neo4jTransaction->run('ROLLBACK');
                } catch (\Exception $neo4jError) {
                    Log::error('Failed to rollback Neo4j transaction', [
                        'exception' => $neo4jError,
                    ]);
                }
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

    public function show(int $id): View
    {
        $tree = Tree::findOrFail($id);

        // Get tree data from Neo4j
        $treeData = $this->neo4jService->getTreeIndividuals($id);

        // Convert Neo4j results to array format for D3.js
        $treeDataArray = [
            'name' => $tree->name,
            'children' => [],
        ];

        /** @var array<int, mixed> $treeData */
        foreach ($treeData as $record) {
            $individual = $record->get('i');
            if ($individual) {
                $treeDataArray['children'][] = [
                    'id' => $individual->getProperty('id'),
                    'name' => $individual->getProperty('first_name').' '.$individual->getProperty('last_name'),
                    'birth_date' => $individual->getProperty('birth_date'),
                    'death_date' => $individual->getProperty('death_date'),
                ];
            }
        }

        return view('trees.show', [
            'tree' => $tree,
            'treeDataJson' => json_encode($treeDataArray),
        ]);
    }

    public function edit(int $id): View
    {
        $tree = Tree::findOrFail($id);
        return view('trees.edit', compact('tree'));
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        /** @var array{name: string, description: string|null} $validated */
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
                'user_id' => $request->user()->id,
            ], $neo4jTransaction);

            $neo4jTransaction->run('COMMIT');
            DB::commit();

            return redirect()->route('trees.show', $tree)
                ->with('success', 'Tree updated successfully.');
        } catch (\Exception $e) {
            if ($neo4jTransaction) {
                $neo4jTransaction->run('ROLLBACK');
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

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
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

            $neo4jTransaction->run('COMMIT');
            DB::commit();

            return redirect()->route('trees.index')
                ->with('success', 'Tree deleted successfully.');
        } catch (\Exception $e) {
            if ($neo4jTransaction) {
                $neo4jTransaction->run('ROLLBACK');
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
