<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Individual;
use App\Services\Neo4jIndividualService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class IndividualController extends Controller
{
    protected Neo4jIndividualService $neo4jService;

    public function __construct(Neo4jIndividualService $neo4jService)
    {
        $this->neo4jService = $neo4jService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $individuals = Individual::latest()->paginate(10);

        return view('individuals.index', compact('individuals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $trees = \App\Models\Tree::all();
        $error = null;
        if ($trees->isEmpty()) {
            $error = 'No trees available. Please create a tree first.';
        }

        return view('individuals.create', compact('trees', 'error'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        /** @var array{
            first_name: string,
            last_name: string,
            birth_date: string|null,
            death_date: string|null,
            tree_id: int,
            parent_ids: array<int>|null,
            spouse_ids: array<int>|null,
            sibling_ids: array<int>|null
        } $validated */
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
            'tree_id' => 'required|exists:trees,id',
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => 'exists:individuals,id',
            'spouse_ids' => 'nullable|array',
            'spouse_ids.*' => 'exists:individuals,id',
            'sibling_ids' => 'nullable|array',
            'sibling_ids.*' => 'exists:individuals,id',
        ]);

        DB::beginTransaction();
        $neo4jTransaction = $this->neo4jService->beginTransaction();
        $individual = null;
        $neo4jCommitted = false;

        try {
            // Create SQL record
            $individual = Individual::create($validated);

            // Create Neo4j node
            /** @var array{id: int, first_name: string, last_name: string, birth_date: string|null, death_date: string|null, tree_id: int} $individualData */
            $individualData = $individual->toArray();
            $this->neo4jService->createIndividualNode($individualData, $neo4jTransaction);

            // Create relationships in Neo4j
            if (isset($validated['parent_ids']) && is_array($validated['parent_ids'])) {
                foreach ($validated['parent_ids'] as $parentId) {
                    $this->neo4jService->createParentChildRelationship((int) $parentId, $individual->id, $neo4jTransaction);
                }
            }

            if (isset($validated['spouse_ids']) && is_array($validated['spouse_ids'])) {
                foreach ($validated['spouse_ids'] as $spouseId) {
                    $this->neo4jService->createSpouseRelationship($individual->id, (int) $spouseId, $neo4jTransaction);
                }
            }

            if (isset($validated['sibling_ids']) && is_array($validated['sibling_ids'])) {
                foreach ($validated['sibling_ids'] as $siblingId) {
                    $this->neo4jService->createSiblingRelationship($individual->id, (int) $siblingId, $neo4jTransaction);
                }
            }

            // Commit Neo4j transaction first
            $neo4jTransaction->run('COMMIT');
            $neo4jCommitted = true;

            // Then commit SQL transaction
            DB::commit();

            return redirect()->route('individuals.show', $individual)
                ->with('success', 'Individual created successfully.');
        } catch (\Exception $e) {
            // Rollback Neo4j transaction if not committed
            if ($neo4jTransaction && ! $neo4jCommitted) {
                try {
                    $neo4jTransaction->run('ROLLBACK');
                } catch (\Exception $rollbackError) {
                    Log::error('Failed to rollback Neo4j transaction', [
                        'individual_id' => $individual?->id,
                        'exception' => $rollbackError,
                    ]);
                }
            }

            // Rollback SQL transaction
            DB::rollBack();

            // If Neo4j committed but SQL failed, we need to clean up the Neo4j node
            if ($neo4jCommitted && $individual) {
                $this->cleanupNeo4jNode($individual->id);
            }

            Log::error('Failed to create individual', [
                'input' => $validated,
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        $individual = Individual::findOrFail($id);
        $allIndividuals = Individual::all();
        $error = null;
        if ($allIndividuals->isEmpty() || ($allIndividuals->count() === 1 && $allIndividuals->first()->id === $individual->id)) {
            $error = 'No other individuals available for relationships.';
        }

        return view('individuals.show', compact('individual', 'allIndividuals', 'error'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $individual = Individual::findOrFail($id);

        return view('individuals.edit', compact('individual'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Individual $individual): RedirectResponse
    {
        /** @var array{
            first_name: string,
            last_name: string,
            birth_date: string|null,
            death_date: string|null,
            tree_id: int,
            parent_ids: array<int>|null,
            spouse_ids: array<int>|null,
            sibling_ids: array<int>|null
        } $validated */
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'birth_date' => 'nullable|date',
            'death_date' => 'nullable|date|after_or_equal:birth_date',
            'tree_id' => 'required|exists:trees,id',
            'parent_ids' => 'nullable|array',
            'parent_ids.*' => 'exists:individuals,id',
            'spouse_ids' => 'nullable|array',
            'spouse_ids.*' => 'exists:individuals,id',
            'sibling_ids' => 'nullable|array',
            'sibling_ids.*' => 'exists:individuals,id',
        ]);

        DB::beginTransaction();
        $neo4jTransaction = $this->neo4jService->beginTransaction();
        $neo4jCommitted = false;

        try {
            // Update SQL record
            $individual->update($validated);

            // Update Neo4j node
            /** @var array{id: int, first_name: string, last_name: string, birth_date: string|null, death_date: string|null, tree_id: int} $individualData */
            $individualData = $individual->toArray();
            $this->neo4jService->updateIndividualNode($individualData, $neo4jTransaction);

            // Update relationships in Neo4j
            // First, delete existing relationships
            $this->neo4jService->deleteIndividualNode($individual->id, $neo4jTransaction);
            $this->neo4jService->createIndividualNode($individualData, $neo4jTransaction);

            // Then create new relationships
            if (isset($validated['parent_ids']) && is_array($validated['parent_ids'])) {
                foreach ($validated['parent_ids'] as $parentId) {
                    $this->neo4jService->createParentChildRelationship((int) $parentId, $individual->id, $neo4jTransaction);
                }
            }

            if (isset($validated['spouse_ids']) && is_array($validated['spouse_ids'])) {
                foreach ($validated['spouse_ids'] as $spouseId) {
                    $this->neo4jService->createSpouseRelationship($individual->id, (int) $spouseId, $neo4jTransaction);
                }
            }

            if (isset($validated['sibling_ids']) && is_array($validated['sibling_ids'])) {
                foreach ($validated['sibling_ids'] as $siblingId) {
                    $this->neo4jService->createSiblingRelationship($individual->id, (int) $siblingId, $neo4jTransaction);
                }
            }

            // Commit Neo4j transaction first
            $neo4jTransaction->run('COMMIT');
            $neo4jCommitted = true;

            // Then commit SQL transaction
            DB::commit();

            return redirect()->route('individuals.show', $individual)
                ->with('success', 'Individual updated successfully.');
        } catch (\Exception $e) {
            // Rollback Neo4j transaction if not committed
            if ($neo4jTransaction && ! $neo4jCommitted) {
                try {
                    $neo4jTransaction->run('ROLLBACK');
                } catch (\Exception $rollbackError) {
                    Log::error('Failed to rollback Neo4j transaction', [
                        'individual_id' => $individual->id,
                        'exception' => $rollbackError,
                    ]);
                }
            }

            // Rollback SQL transaction
            DB::rollBack();

            Log::error('Failed to update individual', [
                'individual_id' => $individual->id,
                'input' => $validated,
                'exception' => $e,
            ]);

            return back()
                ->withInput()
                ->withErrors(['error' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        $individual = Individual::findOrFail($id);

        DB::beginTransaction();
        $neo4jTransaction = $this->neo4jService->beginTransaction();
        $neo4jCommitted = false;

        try {
            // Delete SQL record
            $individual->delete();

            // Delete Neo4j node
            $this->neo4jService->deleteIndividualNode($individual->id, $neo4jTransaction);

            // Commit Neo4j transaction first
            $neo4jTransaction->run('COMMIT');
            $neo4jCommitted = true;

            // Then commit SQL transaction
            DB::commit();

            return redirect()->route('individuals.index')
                ->with('success', 'Individual deleted successfully.');
        } catch (\Exception $e) {
            // Rollback Neo4j transaction if not committed
            if ($neo4jTransaction && ! $neo4jCommitted) {
                try {
                    $neo4jTransaction->run('ROLLBACK');
                } catch (\Exception $rollbackError) {
                    Log::error('Failed to rollback Neo4j transaction', [
                        'individual_id' => $individual->id,
                        'exception' => $rollbackError,
                    ]);
                }
            }

            // Rollback SQL transaction
            DB::rollBack();

            Log::error('Failed to delete individual', [
                'individual_id' => $individual->id,
                'exception' => $e,
            ]);

            return back()->withErrors(['error' => 'An unexpected error occurred. Please try again later.']);
        }
    }

    /**
     * Show a timeline view of individuals (future feature).
     */
    public function timeline(): View
    {
        // Placeholder implementation
        return view('individuals.timeline');
    }

    /**
     * Clean up a Neo4j node that was created but the SQL transaction failed.
     */
    private function cleanupNeo4jNode(int $individualId, int $attempt = 1, int $maxAttempts = 3): void
    {
        if ($attempt > $maxAttempts) {
            Log::error('Failed to clean up Neo4j node after maximum attempts', [
                'individual_id' => $individualId,
                'attempts' => $attempt,
            ]);

            return;
        }

        try {
            $this->neo4jService->deleteIndividualNode($individualId);
            Log::info('Successfully cleaned up Neo4j node', [
                'individual_id' => $individualId,
                'attempt' => $attempt,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clean up Neo4j node', [
                'individual_id' => $individualId,
                'attempt' => $attempt,
                'exception' => $e,
            ]);

            // Retry after a short delay
            sleep(1);
            $this->cleanupNeo4jNode($individualId, $attempt + 1, $maxAttempts);
        }
    }
}
