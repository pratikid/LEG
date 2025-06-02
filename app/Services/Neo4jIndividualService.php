<?php

namespace App\Services;

use Laudis\Neo4j\ClientBuilder;

class Neo4jIndividualService
{
    protected $client;

    public function __construct()
    {
        $this->client = ClientBuilder::create()
            ->withDriver('default', 'neo4j://neo4j:password@localhost:7687') // Update credentials as needed
            ->build();
    }

    public function createIndividualNode(array $data)
    {
        $query = 'CREATE (i:Individual {id: $id, first_name: $first_name, last_name: $last_name, birth_date: $birth_date, death_date: $death_date, tree_id: $tree_id}) RETURN i';
        return $this->client->run($query, $data);
    }

    public function createParentChildRelationship($parentId, $childId)
    {
        $query = "
            MATCH (parent:Individual {id: $parentId}), (child:Individual {id: $childId})
            CREATE (parent)-[:PARENT_OF]->(child)
            RETURN parent, child
        ";
        return $this->client->run($query, ['parentId' => $parentId, 'childId' => $childId]);
    }

    public function createSpouseRelationship($spouseAId, $spouseBId)
    {
        $query = "
            MATCH (a:Individual {id: $spouseAId}), (b:Individual {id: $spouseBId})
            MERGE (a)-[:SPOUSE_OF]-(b)
            RETURN a, b
        ";
        return $this->client->run($query, ['spouseAId' => $spouseAId, 'spouseBId' => $spouseBId]);
    }

    public function createSiblingRelationship($siblingAId, $siblingBId)
    {
        $query = "
            MATCH (a:Individual {id: $siblingAId}), (b:Individual {id: $siblingBId})
            MERGE (a)-[:SIBLING_OF]-(b)
            RETURN a, b
        ";
        return $this->client->run($query, ['siblingAId' => $siblingAId, 'siblingBId' => $siblingBId]);
    }

    public function updateIndividualNode(array $data)
    {
        $query = 'MATCH (i:Individual {id: $id}) SET i.first_name = $first_name, i.last_name = $last_name, i.birth_date = $birth_date, i.death_date = $death_date, i.tree_id = $tree_id RETURN i';
        return $this->client->run($query, $data);
    }

    public function deleteIndividualNode($id)
    {
        $query = 'MATCH (i:Individual {id: $id}) DETACH DELETE i';
        return $this->client->run($query, ['id' => $id]);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getAncestors($individualId, $maxDepth = 5, $limit = 20)
    {
        $query = "MATCH (descendant:Individual {id: $individualId})<-[:PARENT_OF*1..$maxDepth]-(ancestor:Individual) RETURN ancestor LIMIT $limit";
        return $this->client->run($query, ['individualId' => $individualId, 'maxDepth' => $maxDepth, 'limit' => $limit]);
    }

    public function getDescendants($individualId, $maxDepth = 5, $limit = 20)
    {
        $query = "MATCH (ancestor:Individual {id: $individualId})-[:PARENT_OF*1..$maxDepth]->(descendant:Individual) RETURN descendant LIMIT $limit";
        return $this->client->run($query, ['individualId' => $individualId, 'maxDepth' => $maxDepth, 'limit' => $limit]);
    }

    public function getSiblings($individualId, $limit = 20)
    {
        $query = "MATCH (p:Individual)-[:PARENT_OF]->(i:Individual {id: $individualId})<-[:PARENT_OF]-(p)-[:PARENT_OF]->(sibling:Individual) WHERE sibling.id <> $individualId RETURN sibling LIMIT $limit";
        return $this->client->run($query, ['individualId' => $individualId, 'limit' => $limit]);
    }

    public function getShortestPath($fromId, $toId, $maxDepth = 10)
    {
        $query = "MATCH (from:Individual {id: $fromId}), (to:Individual {id: $toId}), path = shortestPath((from)-[*..$maxDepth]-(to)) RETURN path";
        return $this->client->run($query, ['fromId' => $fromId, 'toId' => $toId, 'maxDepth' => $maxDepth]);
    }
} 