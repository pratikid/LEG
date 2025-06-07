<?php

namespace App\Services;

use Laudis\Neo4j\ClientBuilder;

class Neo4jIndividualService
{
    protected $client;

    public function __construct()
    {
        $host = env('NEO4J_HOST', 'neo4j');
        $port = env('NEO4J_PORT', 7687);
        $user = env('NEO4J_USERNAME', 'neo4j');
        $pass = env('NEO4J_PASSWORD', 'password123');
        $scheme = env('NEO4J_SCHEME', 'bolt');
        $uri = env('NEO4J_URI', "$scheme://$user:$pass@$host:$port");

        $this->client = ClientBuilder::create()
            ->withDriver('default', $uri)
            ->build();
    }

    public function beginTransaction()
    {
        return $this->client->beginTransaction();
    }

    public function createIndividualNode(array $data, $transaction = null)
    {
        $query = 'CREATE (i:Individual {id: $id, first_name: $first_name, last_name: $last_name, birth_date: $birth_date, death_date: $death_date, tree_id: $tree_id, created_at: datetime(), updated_at: datetime()}) RETURN i';

        return $transaction ? $transaction->run($query, $data) : $this->client->run($query, $data);
    }

    public function createParentChildRelationship($parentId, $childId, $transaction = null)
    {
        $query = '
            MATCH (parent:Individual {id: $parentId}), (child:Individual {id: $childId})
            MERGE (parent)-[r:PARENT_OF]->(child)
            ON CREATE SET r.created_at = datetime()
            RETURN parent, child
        ';

        return $transaction ? $transaction->run($query, ['parentId' => $parentId, 'childId' => $childId])
                           : $this->client->run($query, ['parentId' => $parentId, 'childId' => $childId]);
    }

    public function createSpouseRelationship($spouseAId, $spouseBId, $transaction = null)
    {
        $query = '
            MATCH (a:Individual {id: $spouseAId}), (b:Individual {id: $spouseBId})
            MERGE (a)-[r:SPOUSE_OF]-(b)
            ON CREATE SET r.created_at = datetime()
            RETURN a, b
        ';

        return $transaction ? $transaction->run($query, ['spouseAId' => $spouseAId, 'spouseBId' => $spouseBId])
                           : $this->client->run($query, ['spouseAId' => $spouseAId, 'spouseBId' => $spouseBId]);
    }

    public function createSiblingRelationship($siblingAId, $siblingBId, $transaction = null)
    {
        $query = '
            MATCH (a:Individual {id: $siblingAId}), (b:Individual {id: $siblingBId})
            MERGE (a)-[r:SIBLING_OF]-(b)
            ON CREATE SET r.created_at = datetime()
            RETURN a, b
        ';

        return $transaction ? $transaction->run($query, ['siblingAId' => $siblingAId, 'siblingBId' => $siblingBId])
                           : $this->client->run($query, ['siblingAId' => $siblingAId, 'siblingBId' => $siblingBId]);
    }

    public function updateIndividualNode(array $data, $transaction = null)
    {
        $query = 'MATCH (i:Individual {id: $id}) 
                 SET i.first_name = $first_name, 
                     i.last_name = $last_name, 
                     i.birth_date = $birth_date, 
                     i.death_date = $death_date, 
                     i.tree_id = $tree_id,
                     i.updated_at = datetime() 
                 RETURN i';

        return $transaction ? $transaction->run($query, $data) : $this->client->run($query, $data);
    }

    public function deleteIndividualNode($id, $transaction = null)
    {
        $query = 'MATCH (i:Individual {id: $id}) DETACH DELETE i';

        return $transaction ? $transaction->run($query, ['id' => $id]) : $this->client->run($query, ['id' => $id]);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getAncestors($individualId, $maxDepth = 5, $limit = 20, $transaction = null)
    {
        $query = '
            MATCH (descendant:Individual {id: $individualId})<-[:PARENT_OF*1..$maxDepth]-(ancestor:Individual)
            RETURN ancestor
            LIMIT $limit
        ';

        return $transaction ? $transaction->run($query, [
            'individualId' => $individualId,
            'maxDepth' => $maxDepth,
            'limit' => $limit,
        ])
            : $this->client->run($query, [
                'individualId' => $individualId,
                'maxDepth' => $maxDepth,
                'limit' => $limit,
            ]);
    }

    public function getDescendants($individualId, $maxDepth = 5, $limit = 20, $transaction = null)
    {
        $query = '
            MATCH (ancestor:Individual {id: $individualId})-[:PARENT_OF*1..$maxDepth]->(descendant:Individual)
            RETURN descendant
            LIMIT $limit
        ';

        return $transaction ? $transaction->run($query, [
            'individualId' => $individualId,
            'maxDepth' => $maxDepth,
            'limit' => $limit,
        ])
            : $this->client->run($query, [
                'individualId' => $individualId,
                'maxDepth' => $maxDepth,
                'limit' => $limit,
            ]);
    }

    public function getSiblings($individualId, $limit = 20, $transaction = null)
    {
        $query = '
            MATCH (p:Individual)-[:PARENT_OF]->(i:Individual {id: $individualId})<-[:PARENT_OF]-(p)-[:PARENT_OF]->(sibling:Individual)
            WHERE sibling.id <> $individualId
            RETURN sibling
            LIMIT $limit
        ';

        return $transaction ? $transaction->run($query, ['individualId' => $individualId, 'limit' => $limit])
                           : $this->client->run($query, ['individualId' => $individualId, 'limit' => $limit]);
    }

    public function getShortestPath($fromId, $toId, $maxDepth = 10, $transaction = null)
    {
        $query = '
            MATCH (from:Individual {id: $fromId}), (to:Individual {id: $toId})
            MATCH path = shortestPath((from)-[*..$maxDepth]-(to))
            RETURN path
        ';

        return $transaction ? $transaction->run($query, [
            'fromId' => $fromId,
            'toId' => $toId,
            'maxDepth' => $maxDepth,
        ])
            : $this->client->run($query, [
                'fromId' => $fromId,
                'toId' => $toId,
                'maxDepth' => $maxDepth,
            ]);
    }

    public function getTreeData($treeId, $transaction = null)
    {
        $query = '
            MATCH (i:Individual {tree_id: $treeId})
            OPTIONAL MATCH (i)-[r]-(related:Individual {tree_id: $treeId})
            RETURN i, r, related
        ';

        return $transaction ? $transaction->run($query, ['treeId' => $treeId])
                           : $this->client->run($query, ['treeId' => $treeId]);
    }

    public function updateNodeTimestamp(string $label, int $id, $transaction = null): void
    {
        $query = sprintf('
            MATCH (n:%s {id: $id})
            SET n.updated_at = datetime()
        ', $label);

        $transaction ? $transaction->run($query, ['id' => $id, 'label' => $label])
                    : $this->client->run($query, ['id' => $id, 'label' => $label]);
    }

    public function createGroupMemberRelationship(int $groupId, int $individualId, $transaction = null): void
    {
        $query = '
            MATCH (group:Group {id: $groupId})
            MATCH (individual:Individual {id: $individualId})
            MERGE (group)-[r:MEMBER_OF]->(individual)
            ON CREATE SET r.created_at = datetime()
        ';

        $transaction ? $transaction->run($query, [
            'groupId' => $groupId,
            'individualId' => $individualId,
        ])
            : $this->client->run($query, [
                'groupId' => $groupId,
                'individualId' => $individualId,
            ]);
    }

    public function createSourceCitationRelationship(int $sourceId, int $individualId, $transaction = null): void
    {
        $query = '
            MATCH (source:Source {id: $sourceId})
            MATCH (individual:Individual {id: $individualId})
            MERGE (source)-[r:CITES]->(individual)
            ON CREATE SET r.created_at = datetime()
        ';

        $transaction ? $transaction->run($query, [
            'sourceId' => $sourceId,
            'individualId' => $individualId,
        ])
            : $this->client->run($query, [
                'sourceId' => $sourceId,
                'individualId' => $individualId,
            ]);
    }

    public function createNoteRelationship(int $noteId, int $individualId, $transaction = null): void
    {
        $query = '
            MATCH (note:Note {id: $noteId})
            MATCH (individual:Individual {id: $individualId})
            MERGE (note)-[r:NOTES]->(individual)
            ON CREATE SET r.created_at = datetime()
        ';

        $transaction ? $transaction->run($query, [
            'noteId' => $noteId,
            'individualId' => $individualId,
        ])
            : $this->client->run($query, [
                'noteId' => $noteId,
                'individualId' => $individualId,
            ]);
    }

    public function createMediaRelationship(int $mediaId, int $individualId, $transaction = null): void
    {
        $query = '
            MATCH (media:Media {id: $mediaId})
            MATCH (individual:Individual {id: $individualId})
            MERGE (media)-[r:MEDIA_OF]->(individual)
            ON CREATE SET r.created_at = datetime()
        ';

        $transaction ? $transaction->run($query, [
            'mediaId' => $mediaId,
            'individualId' => $individualId,
        ])
            : $this->client->run($query, [
                'mediaId' => $mediaId,
                'individualId' => $individualId,
            ]);
    }

    public function createTreeNode(array $data, $transaction = null)
    {
        $query = '
            CREATE (t:Tree {id: $id, name: $name, description: $description, created_at: datetime(), updated_at: datetime()})
            RETURN t
        ';

        return $transaction ? $transaction->run($query, $data) : $this->client->run($query, $data);
    }

    public function linkIndividualToTree($individualId, $treeId, $transaction = null)
    {
        $query = '
            MATCH (i:Individual {id: $individualId})
            MATCH (t:Tree {id: $treeId})
            CREATE (t)-[r:CONTAINS {created_at: datetime()}]->(i)
            RETURN t, i
        ';

        return $transaction ? $transaction->run($query, ['individualId' => $individualId, 'treeId' => $treeId])
                           : $this->client->run($query, ['individualId' => $individualId, 'treeId' => $treeId]);
    }

    public function getTreeIndividuals($treeId)
    {
        $query = '
            MATCH (t:Tree {id: $treeId})-[:CONTAINS]->(i:Individual)
            RETURN i
        ';

        return $this->client->run($query, ['treeId' => $treeId]);
    }

    public function updateTreeNode(array $data, $transaction = null)
    {
        $query = '
            MATCH (t:Tree {id: $id})
            SET t.name = $name,
                t.description = $description,
                t.updated_at = datetime()
            RETURN t
        ';

        return $transaction ? $transaction->run($query, $data) : $this->client->run($query, $data);
    }

    public function deleteTreeNode($id, $transaction = null)
    {
        $query = '
            MATCH (t:Tree {id: $id})
            DETACH DELETE t
        ';

        return $transaction ? $transaction->run($query, ['id' => $id]) : $this->client->run($query, ['id' => $id]);
    }
}
