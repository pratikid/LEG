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
        // Only show trees with no individuals for selection
        $emptyTrees = Tree::doesntHave('individuals')->get();
        return view('trees.import', compact('emptyTrees'));
    }

    public function handleImport(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'gedcom' => ['required', 'file', 'mimes:ged,gedcom', 'max:10240'],
            'tree_id' => ['nullable', 'integer', 'exists:trees,id'],
        ]);

        $file = $request->file('gedcom');
        if (! $file) {
            return redirect()->back()->withErrors(['gedcom' => 'No file was uploaded.']);
        }

        $path = $file->store('gedcoms', 'local');
        $content = file_get_contents(storage_path('app/private/'.$path));
        if ($content === false) {
            return redirect()->back()->withErrors(['gedcom' => 'Failed to read the uploaded file.']);
        }

        // Detect GEDCOM version
        $version = null;
        if (preg_match('/^1 VERS (.+)$/m', $content, $m)) {
            $version = trim($m[1]);
        }

        $gedcomService = new GedcomService;
        $parsed = $gedcomService->parse($content);

        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        // Use selected tree or create a new one
        $treeId = $request->input('tree_id');
        if ($treeId) {
            $tree = Tree::findOrFail($treeId);
            $tree->update([
                'user_id' => $user->id,
                'description' => 'Imported from GEDCOM',
            ]);
        } else {
            $tree = Tree::create([
                'name' => 'Imported Tree '.now()->format('Y-m-d H:i:s'),
                'user_id' => $user->id,
                'description' => 'Imported from GEDCOM',
            ]);
        }

        // Branch logic based on GEDCOM version if needed
        // Example: if ($version === '7.0') { ... } else { ... }
        $gedcomService->importToDatabase($parsed, $tree->id);

        return redirect()->route('trees.index')->with('success', 'GEDCOM file imported successfully.');
    }

    /**
     * Export a tree as a GEDCOM file.
     *
     * @param  int  $id  Tree ID
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
        /** @var array{name: string, description: string|null} $validated */
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();
            $neo4jTransaction = $this->neo4jService->beginTransaction();

            $user = $request->user();
            if (! $user) {
                throw new \Exception('User not authenticated');
            }

            $validated['user_id'] = $user->id;

            $tree = Tree::create($validated);

            // Create tree node in Neo4j
            $this->neo4jService->createTreeNode([
                'id' => $tree->id,
                'name' => $tree->name,
                'description' => $tree->description,
                'user_id' => $user->id,
            ], $neo4jTransaction);

            // Verify tree was created in Neo4j
            if (! $this->neo4jService->validateTreeExists($tree->id, $neo4jTransaction)) {
                throw new \Exception('Failed to create tree in Neo4j');
            }

            // Neo4j transaction is automatically committed when the transaction object is destroyed
            unset($neo4jTransaction);
            DB::commit();

            return redirect()->route('trees.show', $tree)
                ->with('success', 'Tree created successfully.');
        } catch (\Exception $e) {
            if (isset($neo4jTransaction)) {
                try {
                    // Neo4j transaction is automatically rolled back when the transaction object is destroyed
                    unset($neo4jTransaction);
                } catch (\Exception $neo4jError) {
                    Log::error('Failed to handle Neo4j transaction', [
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

    public function show(Tree $tree): View
    {
        // Get tree data from Neo4j
        $treeData = $this->neo4jService->getTreeIndividuals($tree->id);
        $treeStats = $this->neo4jService->getTreeStats($tree->id);

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

        // Convert stats to array
        $stats = [];
        if ($treeStats->first()) {
            $stats = [
                'total_individuals' => $treeStats->first()->get('total_individuals'),
                'parent_relationships' => $treeStats->first()->get('parent_relationships'),
                'spouse_relationships' => $treeStats->first()->get('spouse_relationships'),
                'sibling_relationships' => $treeStats->first()->get('sibling_relationships'),
            ];
        }

        return view('trees.show', [
            'tree' => $tree,
            'treeDataJson' => json_encode($treeDataArray),
            'stats' => $stats,
        ]);
    }

    public function visualization(Tree $tree)
    {
        try {
            $neo4jTransaction = $this->neo4jService->beginTransaction();

            // Query to get all individuals and their relationships (directed, no duplicates)
            $query = 'MATCH (i:Individual {tree_id: "'.$tree->id.'"})
                     OPTIONAL MATCH (i)-[r]->(j:Individual {tree_id: "'.$tree->id.'"})
                     RETURN i, r, j';

            $result = $neo4jTransaction->run($query);
            $nodes = [];
            $edges = [];
            $processedNodes = [];
            $edgeSet = [];

            foreach ($result as $record) {
                $individual = $record->get('i');
                $relationship = $record->get('r');
                $relatedIndividual = $record->get('j');

                // Add node if not already added
                $iId = $individual->getProperty('id');
                if (! isset($processedNodes[$iId])) {
                    $nodes[] = [
                        'id' => $iId,
                        'name' => $individual->getProperty('first_name').' '.$individual->getProperty('last_name'),
                        'first_name' => $individual->getProperty('first_name'),
                        'last_name' => $individual->getProperty('last_name'),
                        // 'birth_date' => $individual->getProperty('birth_date'),
                        // 'sex' => $individual->getProperty('sex'),
                        // 'death_date' => $individual->getProperty('death_date'),
                    ];
                    $processedNodes[$iId] = true;
                }

                if ($relatedIndividual) {
                    $jId = $relatedIndividual->getProperty('id');
                    if (! isset($processedNodes[$jId])) {
                        $nodes[] = [
                            'id' => $jId,
                            'name' => $relatedIndividual->getProperty('first_name').' '.$relatedIndividual->getProperty('last_name'),
                            'first_name' => $relatedIndividual->getProperty('first_name'),
                            'last_name' => $relatedIndividual->getProperty('last_name'),
                            // 'birth_date' => $relatedIndividual->getProperty('birth_date'),
                            // 'sex' => $relatedIndividual->getProperty('sex'),
                            // 'death_date' => $relatedIndividual->getProperty('death_date'),
                        ];
                        $processedNodes[$jId] = true;
                    }
                }

                // Only add edge if relationship exists and is not a duplicate
                if ($relationship && $relatedIndividual) {
                    $type = $relationship->getType();
                    $from = $iId;
                    $to = $relatedIndividual->getProperty('id');

                    // For undirected relationships, only add one direction (lowest id first)
                    if (in_array($type, ['SPOUSE_OF', 'SIBLING_OF'])) {
                        if ($from > $to) {
                            Log::info('Skipping edge from '.$from.' to '.$to.' because it is a duplicate');

                            // Only add edge from lower id to higher id
                            continue;
                        }
                    }

                    $edgeKey = $from.'-'.$to.'-'.$type;
                    if (! isset($edgeSet[$edgeKey])) {
                        $edges[] = [
                            'from' => $from,
                            'to' => $to,
                            'type' => $type,
                        ];
                        $edgeSet[$edgeKey] = true;
                    }
                }
            }

            $treeData = [
                'node_count' => count($nodes),
                'edge_count' => count($edges),
                'nodes' => $nodes,
                'edges' => $edges,
            ];

            Log::info('Tree Data:', [
                'treeData' => $treeData,
            ]);

            return view('trees.visualization', [
                'tree' => $tree,
                'treeData' => $treeData,
            ]);

        } catch (\Exception $e) {
            Log::error('Error in tree visualization:', [
                'tree_id' => $tree->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to generate tree visualization',
            ], 500);
        }
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

            // Neo4j transaction is automatically committed when the transaction object is destroyed
            unset($neo4jTransaction);
            DB::commit();

            return redirect()->route('trees.show', $tree)
                ->with('success', 'Tree updated successfully.');
        } catch (\Exception $e) {
            if ($neo4jTransaction) {
                try {
                    // Neo4j transaction is automatically rolled back when the transaction object is destroyed
                    unset($neo4jTransaction);
                } catch (\Exception $neo4jError) {
                    Log::error('Failed to handle Neo4j transaction', [
                        'exception' => $neo4jError,
                    ]);
                }
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

            // Delete all relationships in Neo4j first
            $this->neo4jService->deleteTreeRelationships($id, $neo4jTransaction);

            // Delete SQL record
            $tree->delete();

            // Then delete Neo4j node
            $this->neo4jService->deleteTreeNode($id, $neo4jTransaction);

            // Verify tree was deleted from Neo4j
            if ($this->neo4jService->validateTreeExists($id, $neo4jTransaction)) {
                throw new \Exception('Failed to delete tree from Neo4j');
            }

            // Neo4j transaction is automatically committed when the transaction object is destroyed
            unset($neo4jTransaction);
            DB::commit();

            return redirect()->route('trees.index')
                ->with('success', 'Tree deleted successfully.');
        } catch (\Exception $e) {
            if ($neo4jTransaction) {
                try {
                    // Neo4j transaction is automatically rolled back when the transaction object is destroyed
                    unset($neo4jTransaction);
                } catch (\Exception $neo4jError) {
                    Log::error('Failed to handle Neo4j transaction', [
                        'exception' => $neo4jError,
                    ]);
                }
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
