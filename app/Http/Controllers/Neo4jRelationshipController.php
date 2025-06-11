<?php

namespace App\Http\Controllers;

use App\Models\Individual;
use App\Services\Neo4jIndividualService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class Neo4jRelationshipController extends Controller
{
    protected Neo4jIndividualService $neo4j;

    public function __construct(Neo4jIndividualService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    // Add a parent-child relationship
    public function addParentChild(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array{parent_id: int, child_id: int} $validated */
        $validated = $request->validate([
            'parent_id' => 'required|integer|exists:individuals,id|not_in:0',
            'child_id' => 'required|integer|exists:individuals,id|not_in:0',
        ]);

        try {
            $transaction = $this->neo4j->beginTransaction();

            // Validate no cycles in parent-child relationship
            if (! $this->neo4j->validateNoCycles($validated['child_id'], $validated['parent_id'], 'PARENT_OF', $transaction)) {
                throw new \Exception('Cannot create parent-child relationship: would create a cycle in the family tree');
            }

            // Validate relationship doesn't already exist
            if ($this->neo4j->validateRelationship($validated['parent_id'], $validated['child_id'], 'PARENT_OF', $transaction)) {
                throw new \Exception('Parent-child relationship already exists');
            }

            $this->neo4j->createParentChildRelationship($validated['parent_id'], $validated['child_id'], $transaction);
            unset($transaction);

            return back()->with('success', 'Parent-child relationship added successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to add parent-child relationship', [
                'parent_id' => $validated['parent_id'],
                'child_id' => $validated['child_id'],
                'exception' => $e,
            ]);

            return back()->withErrors(['error' => $e->getMessage() ?: 'Failed to add parent-child relationship. Please try again.']);
        }
    }

    // Add a spouse relationship
    public function addSpouse(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array{spouse_a_id: int, spouse_b_id: int} $validated */
        $validated = $request->validate([
            'spouse_a_id' => 'required|integer|exists:individuals,id|not_in:0',
            'spouse_b_id' => 'required|integer|exists:individuals,id|not_in:0',
        ]);

        try {
            $transaction = $this->neo4j->beginTransaction();

            // Get individual names for better error messages
            $spouseA = Individual::findOrFail($validated['spouse_a_id']);
            $spouseB = Individual::findOrFail($validated['spouse_b_id']);

            // Validate relationship doesn't already exist
            if ($this->neo4j->validateRelationship($validated['spouse_a_id'], $validated['spouse_b_id'], 'SPOUSE_OF', $transaction)) {
                throw new \Exception(sprintf(
                    'A spouse relationship already exists between %s %s and %s %s',
                    $spouseA->first_name,
                    $spouseA->last_name,
                    $spouseB->first_name,
                    $spouseB->last_name
                ));
            }

            $this->neo4j->createSpouseRelationship($validated['spouse_a_id'], $validated['spouse_b_id'], $transaction);
            unset($transaction);

            return back()->with('success', sprintf(
                'Spouse relationship added successfully between %s %s and %s %s!',
                $spouseA->first_name,
                $spouseA->last_name,
                $spouseB->first_name,
                $spouseB->last_name
            ));
        } catch (\Exception $e) {
            Log::error('Failed to add spouse relationship', [
                'spouse_a_id' => $validated['spouse_a_id'],
                'spouse_b_id' => $validated['spouse_b_id'],
                'exception' => $e,
            ]);

            return back()->withErrors(['error' => $e->getMessage() ?: 'Failed to add spouse relationship. Please try again.']);
        }
    }

    // Get all children of a parent
    public function getChildren(int $parentId): \Illuminate\Http\JsonResponse
    {
        try {
            $result = $this->neo4j->getChildren($parentId);
            /** @var array<int, array<string, mixed>> $children */
            $children = collect($result->toArray())->map(fn ($r) => $r->get('c')->toArray())->toArray();

            return response()->json($children);
        } catch (\Exception $e) {
            Log::error('Failed to get children', [
                'parent_id' => $parentId,
                'exception' => $e,
            ]);

            return response()->json(['error' => 'Failed to get children'], 500);
        }
    }

    // Get all parents of a child
    public function getParents(int $childId): \Illuminate\Http\JsonResponse
    {
        try {
            $result = $this->neo4j->getParents($childId);
            /** @var array<int, array<string, mixed>> $parents */
            $parents = collect($result->toArray())->map(fn ($r) => $r->get('p')->toArray())->toArray();

            return response()->json($parents);
        } catch (\Exception $e) {
            Log::error('Failed to get parents', [
                'child_id' => $childId,
                'exception' => $e,
            ]);

            return response()->json(['error' => 'Failed to get parents'], 500);
        }
    }

    // Get all spouses of an individual
    public function getSpouses(int $individualId): \Illuminate\Http\JsonResponse
    {
        try {
            $result = $this->neo4j->getSpouses($individualId);
            /** @var array<int, array<string, mixed>> $spouses */
            $spouses = collect($result->toArray())->map(fn ($r) => $r->get('b')->toArray())->toArray();

            return response()->json($spouses);
        } catch (\Exception $e) {
            Log::error('Failed to get spouses', [
                'individual_id' => $individualId,
                'exception' => $e,
            ]);

            return response()->json(['error' => 'Failed to get spouses'], 500);
        }
    }

    // Get all ancestors of an individual
    public function getAncestors(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $maxDepth = (int) $request->query('maxDepth', '5');
        $limit = (int) $request->query('limit', '20');
        $result = $this->neo4j->getAncestors($id, $maxDepth, $limit);
        /** @var array<int, array<string, mixed>> $ancestors */
        $ancestors = collect($result->toArray())->map(fn ($r) => $r->get('ancestor')->toArray())->toArray();

        return response()->json($ancestors);
    }

    // Get all descendants of an individual
    public function getDescendants(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $maxDepth = (int) $request->query('maxDepth', '5');
        $limit = (int) $request->query('limit', '20');
        $result = $this->neo4j->getDescendants($id, $maxDepth, $limit);
        /** @var array<int, array<string, mixed>> $descendants */
        $descendants = collect($result->toArray())->map(fn ($r) => $r->get('descendant')->toArray())->toArray();

        return response()->json($descendants);
    }

    // Get all siblings of an individual
    public function getSiblings(Request $request, int $id): \Illuminate\Http\JsonResponse
    {
        $limit = (int) $request->query('limit', '20');
        $result = $this->neo4j->getSiblings($id, $limit);
        /** @var array<int, array<string, mixed>> $siblings */
        $siblings = collect($result->toArray())->map(fn ($r) => $r->get('sibling')->toArray())->toArray();

        return response()->json($siblings);
    }

    // Get shortest path between two individuals
    public function getShortestPath(Request $request, int $fromId, int $toId): \Illuminate\Http\JsonResponse
    {
        $maxDepth = (int) $request->query('maxDepth', '10');
        $result = $this->neo4j->getShortestPath($fromId, $toId, $maxDepth);
        /** @var array<int, array<string, mixed>> $paths */
        $paths = collect($result->toArray())->map(fn ($r) => $r->get('path')->toArray())->toArray();

        return response()->json($paths);
    }

    // Add a sibling relationship
    public function addSibling(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array{sibling_a_id: int, sibling_b_id: int} $validated */
        $validated = $request->validate([
            'sibling_a_id' => 'required|integer|exists:individuals,id|not_in:0',
            'sibling_b_id' => 'required|integer|exists:individuals,id|not_in:0',
        ]);

        try {
            $transaction = $this->neo4j->beginTransaction();

            // Validate relationship doesn't already exist
            if ($this->neo4j->validateRelationship($validated['sibling_a_id'], $validated['sibling_b_id'], 'SIBLING_OF', $transaction)) {
                throw new \Exception('Sibling relationship already exists');
            }

            $this->neo4j->createSiblingRelationship($validated['sibling_a_id'], $validated['sibling_b_id'], $transaction);
            unset($transaction);

            return back()->with('success', 'Sibling relationship added successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to add sibling relationship', [
                'sibling_a_id' => $validated['sibling_a_id'],
                'sibling_b_id' => $validated['sibling_b_id'],
                'exception' => $e,
            ]);

            return back()->withErrors(['error' => $e->getMessage() ?: 'Failed to add sibling relationship. Please try again.']);
        }
    }

    // Remove a parent-child relationship
    public function removeParentChild(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array{parent_id: int, child_id: int} $validated */
        $validated = $request->validate([
            'parent_id' => 'required|integer|exists:individuals,id',
            'child_id' => 'required|integer|exists:individuals,id',
        ]);

        try {
            $transaction = $this->neo4j->beginTransaction();
            $this->neo4j->deleteParentChildRelationship($validated['parent_id'], $validated['child_id'], $transaction);
            unset($transaction);

            return back()->with('success', 'Parent-child relationship removed successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to remove parent-child relationship', [
                'parent_id' => $validated['parent_id'],
                'child_id' => $validated['child_id'],
                'exception' => $e,
            ]);

            return back()->withErrors(['error' => 'Failed to remove parent-child relationship. Please try again.']);
        }
    }

    // Remove a spouse relationship
    public function removeSpouse(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array{spouse_a_id: int, spouse_b_id: int} $validated */
        $validated = $request->validate([
            'spouse_a_id' => 'required|integer|exists:individuals,id',
            'spouse_b_id' => 'required|integer|exists:individuals,id',
        ]);

        try {
            $transaction = $this->neo4j->beginTransaction();
            $this->neo4j->deleteSpouseRelationship($validated['spouse_a_id'], $validated['spouse_b_id'], $transaction);
            unset($transaction);

            return back()->with('success', 'Spouse relationship removed successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to remove spouse relationship', [
                'spouse_a_id' => $validated['spouse_a_id'],
                'spouse_b_id' => $validated['spouse_b_id'],
                'exception' => $e,
            ]);

            return back()->withErrors(['error' => 'Failed to remove spouse relationship. Please try again.']);
        }
    }

    // Remove a sibling relationship
    public function removeSibling(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array{sibling_a_id: int, sibling_b_id: int} $validated */
        $validated = $request->validate([
            'sibling_a_id' => 'required|integer|exists:individuals,id',
            'sibling_b_id' => 'required|integer|exists:individuals,id',
        ]);

        try {
            $transaction = $this->neo4j->beginTransaction();
            $this->neo4j->deleteSiblingRelationship($validated['sibling_a_id'], $validated['sibling_b_id'], $transaction);
            unset($transaction);

            return back()->with('success', 'Sibling relationship removed successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to remove sibling relationship', [
                'sibling_a_id' => $validated['sibling_a_id'],
                'sibling_b_id' => $validated['sibling_b_id'],
                'exception' => $e,
            ]);

            return back()->withErrors(['error' => 'Failed to remove sibling relationship. Please try again.']);
        }
    }

    public function getRelationshipStats(int $individualId): \Illuminate\Http\JsonResponse
    {
        try {
            $result = $this->neo4j->getRelationshipStats($individualId);
            $stats = $result->first() ? [
                'children_count' => $result->first()->get('children_count'),
                'parents_count' => $result->first()->get('parents_count'),
                'spouses_count' => $result->first()->get('spouses_count'),
                'siblings_count' => $result->first()->get('siblings_count'),
            ] : [];

            return response()->json($stats);
        } catch (\Exception $e) {
            Log::error('Failed to get relationship stats', [
                'individual_id' => $individualId,
                'exception' => $e,
            ]);

            return response()->json(['error' => 'Failed to get relationship stats'], 500);
        }
    }
}
