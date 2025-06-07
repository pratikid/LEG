<?php

namespace App\Http\Controllers;

use App\Models\Individual;
use App\Services\Neo4jIndividualService;
use Illuminate\Http\Request;

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
            'parent_id' => 'required|integer|exists:individuals,id',
            'child_id' => 'required|integer|exists:individuals,id',
        ]);
        $this->neo4j->createParentChildRelationship($validated['parent_id'], $validated['child_id']);

        return back()->with('success', 'Parent-child relationship added in Neo4j!');
    }

    // Add a spouse relationship
    public function addSpouse(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array{spouse_a_id: int, spouse_b_id: int} $validated */
        $validated = $request->validate([
            'spouse_a_id' => 'required|integer|exists:individuals,id',
            'spouse_b_id' => 'required|integer|exists:individuals,id',
        ]);
        $this->neo4j->createSpouseRelationship($validated['spouse_a_id'], $validated['spouse_b_id']);

        return back()->with('success', 'Spouse relationship added in Neo4j!');
    }

    // Get all children of a parent
    public function getChildren(int $parentId): \Illuminate\Http\JsonResponse
    {
        $client = $this->neo4j->getClient();
        $query = 'MATCH (p:Individual {id: $parentId})-[:PARENT_OF]->(c:Individual) RETURN c';
        $result = $client->run($query, ['parentId' => $parentId]);
        /** @var array<int, array<string, mixed>> $children */
        $children = collect($result->toArray())->map(fn ($r) => $r->get('c')->toArray())->toArray();

        return response()->json($children);
    }

    // Get all parents of a child
    public function getParents(int $childId): \Illuminate\Http\JsonResponse
    {
        $client = $this->neo4j->getClient();
        $query = 'MATCH (p:Individual)-[:PARENT_OF]->(c:Individual {id: $childId}) RETURN p';
        $result = $client->run($query, ['childId' => $childId]);
        /** @var array<int, array<string, mixed>> $parents */
        $parents = collect($result->toArray())->map(fn ($r) => $r->get('p')->toArray())->toArray();

        return response()->json($parents);
    }

    // Get all spouses of an individual
    public function getSpouses(int $individualId): \Illuminate\Http\JsonResponse
    {
        $client = $this->neo4j->getClient();
        $query = 'MATCH (a:Individual {id: $individualId})-[:SPOUSE_OF]-(b:Individual) RETURN b';
        $result = $client->run($query, ['individualId' => $individualId]);
        /** @var array<int, array<string, mixed>> $spouses */
        $spouses = collect($result->toArray())->map(fn ($r) => $r->get('b')->toArray())->toArray();

        return response()->json($spouses);
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
            'sibling_a_id' => 'required|integer|exists:individuals,id',
            'sibling_b_id' => 'required|integer|exists:individuals,id',
        ]);
        $this->neo4j->createSiblingRelationship($validated['sibling_a_id'], $validated['sibling_b_id']);

        return back()->with('success', 'Sibling relationship added in Neo4j!');
    }

    // Remove a parent-child relationship
    public function removeParentChild(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array{parent_id: int, child_id: int} $validated */
        $validated = $request->validate([
            'parent_id' => 'required|integer|exists:individuals,id',
            'child_id' => 'required|integer|exists:individuals,id',
        ]);
        $client = $this->neo4j->getClient();
        $query = 'MATCH (p:Individual {id: $parentId})-[r:PARENT_OF]->(c:Individual {id: $childId}) DELETE r';
        $client->run($query, ['parentId' => $validated['parent_id'], 'childId' => $validated['child_id']]);

        return back()->with('success', 'Parent-child relationship removed in Neo4j!');
    }

    // Remove a spouse relationship
    public function removeSpouse(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array{spouse_a_id: int, spouse_b_id: int} $validated */
        $validated = $request->validate([
            'spouse_a_id' => 'required|integer|exists:individuals,id',
            'spouse_b_id' => 'required|integer|exists:individuals,id',
        ]);
        $client = $this->neo4j->getClient();
        $query = 'MATCH (a:Individual {id: $spouseAId})-[r:SPOUSE_OF]-(b:Individual {id: $spouseBId}) DELETE r';
        $client->run($query, ['spouseAId' => $validated['spouse_a_id'], 'spouseBId' => $validated['spouse_b_id']]);

        return back()->with('success', 'Spouse relationship removed in Neo4j!');
    }

    // Remove a sibling relationship
    public function removeSibling(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array{sibling_a_id: int, sibling_b_id: int} $validated */
        $validated = $request->validate([
            'sibling_a_id' => 'required|integer|exists:individuals,id',
            'sibling_b_id' => 'required|integer|exists:individuals,id',
        ]);
        $client = $this->neo4j->getClient();
        $query = 'MATCH (a:Individual {id: $siblingAId})-[r:SIBLING_OF]-(b:Individual {id: $siblingBId}) DELETE r';
        $client->run($query, ['siblingAId' => $validated['sibling_a_id'], 'siblingBId' => $validated['sibling_b_id']]);

        return back()->with('success', 'Sibling relationship removed in Neo4j!');
    }
}
