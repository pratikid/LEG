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
        return view('trees.import');
    }

    public function handleImport(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'gedcom' => ['required', 'file', 'mimes:ged,gedcom', 'max:10240'],
        ]);

        $file = $request->file('gedcom');
        if (! $file) {
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
        if (! $user) {
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

    public function show(int $id): View
    {
        $tree = Tree::findOrFail($id);

        // Get tree data from Neo4j
        $treeData = $this->neo4jService->getTreeIndividuals($id);
        $treeStats = $this->neo4jService->getTreeStats($id);

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

    public function visualization(int $id): \Illuminate\View\View|\Illuminate\Http\RedirectResponse
    {
        $tree = Tree::findOrFail($id);
        $neo4jTransaction = null;

        try {
            $neo4jTransaction = $this->neo4jService->beginTransaction();
            
            // Get all individuals and their relationships in the tree
            $query = '
                MATCH (i:Individual)-[:BELONGS_TO]->(t:Tree {id: $treeId})
                WITH i
                OPTIONAL MATCH (i)-[r:PARENT_OF]->(child:Individual)
                OPTIONAL MATCH (i)-[s:SPOUSE_OF]-(spouse:Individual)
                OPTIONAL MATCH (i)-[sib:SIBLING_OF]-(sibling:Individual)
                RETURN i,
                    collect(DISTINCT {type: "PARENT_OF", related: child}) as children,
                    collect(DISTINCT {type: "SPOUSE_OF", related: spouse}) as spouses,
                    collect(DISTINCT {type: "SIBLING_OF", related: sibling}) as siblings
            ';
            
            try {
                // Log the query and parameters
                Log::info('Executing Neo4j Query:', [
                    'query' => $query,
                    'parameters' => ['treeId' => $id]
                ]);

                $result = $neo4jTransaction->run($query, ['treeId' => $id]);
                
                // Log the query result count
                Log::info('Neo4j Query Result:', [
                    'result_count' => $result->count(),
                    'has_results' => $result->count() > 0
                ]);

                // Debug log the raw Neo4j results
                Log::info('Raw Neo4j Results:', [
                    'count' => $result->count(),
                    'first_record' => $result->first() ? [
                        'individual' => $result->first()->get('i')->toArray(),
                        'children' => $result->first()->get('children'),
                        'spouses' => $result->first()->get('spouses'),
                        'siblings' => $result->first()->get('siblings')
                    ] : null
                ]);
                
                // Convert Neo4j results to array format for D3.js
                $treeDataArray = [
                    'name' => $tree->name,
                    'children' => [],
                ];
                
                $individuals = [];
                $relationships = [];
                
                // First pass: collect all individuals
                foreach ($result as $record) {
                    $individual = $record->get('i');
                    if ($individual) {
                        $individualId = $individual->getProperty('id');
                        $individuals[$individualId] = [
                            'id' => $individualId,
                            'name' => $individual->getProperty('first_name').' '.$individual->getProperty('last_name'),
                            'birth_date' => $individual->getProperty('birth_date'),
                            'death_date' => $individual->getProperty('death_date'),
                            'children' => [],
                            'spouses' => [],
                            'siblings' => []
                        ];
                        
                        // Process children
                        $children = $record->get('children');
                        Log::info('Processing Children:', [
                            'individual_id' => $individualId,
                            'children_count' => count($children),
                            'children_data' => $children
                        ]);
                        
                        foreach ($children as $child) {
                            if ($child['related']) {
                                $childId = $child['related']->getProperty('id');
                                $relationships[] = [
                                    'type' => 'PARENT_OF',
                                    'from' => $individualId,
                                    'to' => $childId
                                ];
                            }
                        }
                        
                        // Process spouses
                        $spouses = $record->get('spouses');
                        Log::info('Processing Spouses:', [
                            'individual_id' => $individualId,
                            'spouses_count' => count($spouses),
                            'spouses_data' => $spouses
                        ]);
                        
                        foreach ($spouses as $spouse) {
                            if ($spouse['related']) {
                                $spouseId = $spouse['related']->getProperty('id');
                                $relationships[] = [
                                    'type' => 'SPOUSE_OF',
                                    'from' => $individualId,
                                    'to' => $spouseId
                                ];
                            }
                        }
                        
                        // Process siblings
                        $siblings = $record->get('siblings');
                        Log::info('Processing Siblings:', [
                            'individual_id' => $individualId,
                            'siblings_count' => count($siblings),
                            'siblings_data' => $siblings
                        ]);
                        
                        foreach ($siblings as $sibling) {
                            if ($sibling['related']) {
                                $siblingId = $sibling['related']->getProperty('id');
                                $relationships[] = [
                                    'type' => 'SIBLING_OF',
                                    'from' => $individualId,
                                    'to' => $siblingId
                                ];
                            }
                        }
                    }
                }

                // Log the collected relationships
                Log::info('Collected Relationships:', [
                    'total_relationships' => count($relationships),
                    'relationships_by_type' => array_count_values(array_column($relationships, 'type'))
                ]);
                
                // Second pass: organize relationships into hierarchical structure
                foreach ($relationships as $rel) {
                    $from = $rel['from'];
                    $to = $rel['to'];
                    
                    switch ($rel['type']) {
                        case 'PARENT_OF':
                            if (isset($individuals[$from]) && isset($individuals[$to])) {
                                $individuals[$from]['children'][] = $individuals[$to];
                            }
                            break;
                        case 'SPOUSE_OF':
                            if (isset($individuals[$from]) && isset($individuals[$to])) {
                                $individuals[$from]['spouses'][] = $individuals[$to];
                            }
                            break;
                        case 'SIBLING_OF':
                            if (isset($individuals[$from]) && isset($individuals[$to])) {
                                $individuals[$from]['siblings'][] = $individuals[$to];
                            }
                            break;
                    }
                }
                
                // Find root nodes (individuals without parents)
                foreach ($individuals as $id => $individual) {
                    $hasParent = false;
                    foreach ($relationships as $rel) {
                        if ($rel['type'] === 'PARENT_OF' && $rel['to'] === $id) {
                            $hasParent = true;
                            break;
                        }
                    }
                    if (!$hasParent) {
                        $treeDataArray['children'][] = $individual;
                    }
                }

                // Log the final tree structure
                Log::info('Final Tree Structure:', [
                    'tree_name' => $treeDataArray['name'],
                    'root_nodes_count' => count($treeDataArray['children']),
                    'tree_data' => $treeDataArray
                ]);

                // Neo4j transaction is automatically committed when the transaction object is destroyed
                unset($neo4jTransaction);

                return view('trees.visualization', [
                    'tree' => $tree,
                    'treeDataJson' => json_encode($treeDataArray),
                ]);
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

                Log::error('Failed to load tree visualization', [
                    'tree_id' => $id,
                    'exception' => $e,
                ]);

                return redirect()->route('trees.show', $id)
                    ->withErrors(['error' => 'Failed to load tree visualization. Please try again later.']);
            }
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

            Log::error('Failed to load tree visualization', [
                'tree_id' => $id,
                'exception' => $e,
            ]);

            return redirect()->route('trees.show', $id)
                ->withErrors(['error' => 'Failed to load tree visualization. Please try again later.']);
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
