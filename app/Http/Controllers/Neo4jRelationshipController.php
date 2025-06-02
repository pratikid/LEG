<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Individual;
use App\Services\Neo4jIndividualService;

class Neo4jRelationshipController extends Controller
{
    protected $neo4j;

    public function __construct(Neo4jIndividualService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    // Add a parent-child relationship
    public function addParentChild(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|integer|exists:individuals,id',
            'child_id' => 'required|integer|exists:individuals,id',
        ]);
        $this->neo4j->createParentChildRelationship($request->parent_id, $request->child_id);
        return back()->with('success', 'Parent-child relationship added in Neo4j!');
    }

    // Add a spouse relationship
    public function addSpouse(Request $request)
    {
        $request->validate([
            'spouse_a_id' => 'required|integer|exists:individuals,id',
            'spouse_b_id' => 'required|integer|exists:individuals,id',
        ]);
        $this->neo4j->createSpouseRelationship($request->spouse_a_id, $request->spouse_b_id);
        return back()->with('success', 'Spouse relationship added in Neo4j!');
    }

    // Get all children of a parent
    public function getChildren($parentId)
    {
        $client = $this->neo4j->getClient();
        $query = 'MATCH (p:Individual {id: $parentId})-[:PARENT_OF]->(c:Individual) RETURN c';
        $result = $client->run($query, ['parentId' => $parentId]);
        $children = collect($result)->map(fn($r) => $r->get('c'))->toArray();
        return response()->json($children);
    }

    // Get all parents of a child
    public function getParents($childId)
    {
        $client = $this->neo4j->getClient();
        $query = 'MATCH (p:Individual)-[:PARENT_OF]->(c:Individual {id: $childId}) RETURN p';
        $result = $client->run($query, ['childId' => $childId]);
        $parents = collect($result)->map(fn($r) => $r->get('p'))->toArray();
        return response()->json($parents);
    }

    // Get all spouses of an individual
    public function getSpouses($individualId)
    {
        $client = $this->neo4j->getClient();
        $query = 'MATCH (a:Individual {id: $individualId})-[:SPOUSE_OF]-(b:Individual) RETURN b';
        $result = $client->run($query, ['individualId' => $individualId]);
        $spouses = collect($result)->map(fn($r) => $r->get('b'))->toArray();
        return response()->json($spouses);
    }

    // Get all ancestors of an individual
    public function getAncestors(Request $request, $id)
    {
        $maxDepth = $request->query('maxDepth', 5);
        $limit = $request->query('limit', 20);
        $result = $this->neo4j->getAncestors($id, $maxDepth, $limit);
        $ancestors = collect($result)->map(fn($r) => $r->get('ancestor'))->toArray();
        return response()->json($ancestors);
    }

    // Get all descendants of an individual
    public function getDescendants(Request $request, $id)
    {
        $maxDepth = $request->query('maxDepth', 5);
        $limit = $request->query('limit', 20);
        $result = $this->neo4j->getDescendants($id, $maxDepth, $limit);
        $descendants = collect($result)->map(fn($r) => $r->get('descendant'))->toArray();
        return response()->json($descendants);
    }

    // Get all siblings of an individual
    public function getSiblings(Request $request, $id)
    {
        $limit = $request->query('limit', 20);
        $result = $this->neo4j->getSiblings($id, $limit);
        $siblings = collect($result)->map(fn($r) => $r->get('sibling'))->toArray();
        return response()->json($siblings);
    }

    // Get shortest path between two individuals
    public function getShortestPath(Request $request, $fromId, $toId)
    {
        $maxDepth = $request->query('maxDepth', 10);
        $result = $this->neo4j->getShortestPath($fromId, $toId, $maxDepth);
        $paths = collect($result)->map(fn($r) => $r->get('path'))->toArray();
        return response()->json($paths);
    }

    // Add a sibling relationship
    public function addSibling(Request $request)
    {
        $request->validate([
            'sibling_a_id' => 'required|integer|exists:individuals,id',
            'sibling_b_id' => 'required|integer|exists:individuals,id',
        ]);
        $this->neo4j->createSiblingRelationship($request->sibling_a_id, $request->sibling_b_id);
        return back()->with('success', 'Sibling relationship added in Neo4j!');
    }

    // Remove a parent-child relationship
    public function removeParentChild(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|integer|exists:individuals,id',
            'child_id' => 'required|integer|exists:individuals,id',
        ]);
        $client = $this->neo4j->getClient();
        $query = 'MATCH (p:Individual {id: $parentId})-[r:PARENT_OF]->(c:Individual {id: $childId}) DELETE r';
        $client->run($query, ['parentId' => $request->parent_id, 'childId' => $request->child_id]);
        return back()->with('success', 'Parent-child relationship removed in Neo4j!');
    }

    // Remove a spouse relationship
    public function removeSpouse(Request $request)
    {
        $request->validate([
            'spouse_a_id' => 'required|integer|exists:individuals,id',
            'spouse_b_id' => 'required|integer|exists:individuals,id',
        ]);
        $client = $this->neo4j->getClient();
        $query = 'MATCH (a:Individual {id: $spouseAId})-[r:SPOUSE_OF]-(b:Individual {id: $spouseBId}) DELETE r';
        $client->run($query, ['spouseAId' => $request->spouse_a_id, 'spouseBId' => $request->spouse_b_id]);
        return back()->with('success', 'Spouse relationship removed in Neo4j!');
    }

    // Remove a sibling relationship
    public function removeSibling(Request $request)
    {
        $request->validate([
            'sibling_a_id' => 'required|integer|exists:individuals,id',
            'sibling_b_id' => 'required|integer|exists:individuals,id',
        ]);
        $client = $this->neo4j->getClient();
        $query = 'MATCH (a:Individual {id: $siblingAId})-[r:SIBLING_OF]-(b:Individual {id: $siblingBId}) DELETE r';
        $client->run($query, ['siblingAId' => $request->sibling_a_id, 'siblingBId' => $request->sibling_b_id]);
        return back()->with('success', 'Sibling relationship removed in Neo4j!');
    }
} 