<?php

declare(strict_types=1);

namespace App\Services;

use Laudis\Neo4j\Client;
use Laudis\Neo4j\ClientBuilder;
use Laudis\Neo4j\Contracts\TransactionInterface;

class Neo4jIndividualService
{
    protected Client $client;

    public function __construct()
    {
        $host = config('database.neo4j.host', 'neo4j');
        $port = config('database.neo4j.port', 7687);
        $user = config('database.neo4j.username', 'neo4j');
        $pass = config('database.neo4j.password', 'password123');
        $scheme = config('database.neo4j.scheme', 'bolt');
        $uri = config('database.neo4j.uri', "$scheme://$user:$pass@$host:$port");

        $this->client = ClientBuilder::create()
            ->withDriver('default', $uri)
            ->build();
    }

    public function beginTransaction(): TransactionInterface
    {
        return $this->client->beginTransaction();
    }

    /**
     * @param array{
     *     id: int,
     *     first_name: string,
     *     last_name: string,
     *     birth_date: string|null,
     *     death_date: string|null,
     *     tree_id: int
     * } $data
     */
    public function createIndividualNode(array $data, ?TransactionInterface $transaction = null): mixed
    {
        $query = '
            MERGE (i:Individual {id: $id})
            ON CREATE SET
                i.first_name = $first_name,
                i.last_name  = $last_name,
                i.birth_date = $birth_date,
                i.death_date = $death_date,
                i.tree_id    = $tree_id,
                i.created_at = datetime(),
                i.updated_at = datetime()
            RETURN i
        ';

        return $transaction ? $transaction->run($query, $data) : $this->client->run($query, $data);
    }

    public function createParentChildRelationship(int $parentId, int $childId, ?TransactionInterface $transaction = null): mixed
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

    public function createSpouseRelationship(int $spouseAId, int $spouseBId, ?TransactionInterface $transaction = null): mixed
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

    public function createSiblingRelationship(int $siblingAId, int $siblingBId, ?TransactionInterface $transaction = null): mixed
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

    /**
     * @param array{
     *     id: int,
     *     first_name: string,
     *     last_name: string,
     *     birth_date: string|null,
     *     death_date: string|null,
     *     tree_id: int
     * } $data
     */
    public function updateIndividualNode(array $data, ?TransactionInterface $transaction = null): mixed
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

    public function deleteIndividualNode(int $id, ?TransactionInterface $transaction = null): mixed
    {
        $query = 'MATCH (i:Individual {id: $id}) DETACH DELETE i';

        return $transaction ? $transaction->run($query, ['id' => $id]) : $this->client->run($query, ['id' => $id]);
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getAncestors(int $individualId, int $maxDepth = 5, int $limit = 20, ?TransactionInterface $transaction = null): mixed
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

    public function getDescendants(int $individualId, int $maxDepth = 5, int $limit = 20, ?TransactionInterface $transaction = null): mixed
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

    public function getSiblings(int $individualId, int $limit = 20, ?TransactionInterface $transaction = null): mixed
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

    public function getShortestPath(int $fromId, int $toId, int $maxDepth = 10, ?TransactionInterface $transaction = null): mixed
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

    public function getTreeData(int $treeId, ?TransactionInterface $transaction = null): mixed
    {
        $query = '
            MATCH (i:Individual {tree_id: $treeId})
            OPTIONAL MATCH (i)-[r]-(related:Individual {tree_id: $treeId})
            RETURN i, r, related
        ';

        return $transaction ? $transaction->run($query, ['treeId' => $treeId])
                           : $this->client->run($query, ['treeId' => $treeId]);
    }

    public function updateNodeTimestamp(string $label, int $id, ?TransactionInterface $transaction = null): void
    {
        $query = sprintf('
            MATCH (n:%s {id: $id})
            SET n.updated_at = datetime()
        ', $label);

        $transaction ? $transaction->run($query, ['id' => $id, 'label' => $label])
                    : $this->client->run($query, ['id' => $id, 'label' => $label]);
    }

    public function createGroupMemberRelationship(int $groupId, int $individualId, ?TransactionInterface $transaction = null): void
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

    public function createSourceCitationRelationship(int $sourceId, int $individualId, ?TransactionInterface $transaction = null): void
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

    public function createNoteRelationship(int $noteId, int $individualId, ?TransactionInterface $transaction = null): void
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

    public function createMediaRelationship(int $mediaId, int $individualId, ?TransactionInterface $transaction = null): void
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

    /**
     * @param array{
     *     id: int,
     *     name: string,
     *     description: string|null,
     *     user_id: int
     * } $data
     */
    public function createTreeNode(array $data, ?TransactionInterface $transaction = null): mixed
    {
        $query = 'CREATE (t:Tree {id: $id, name: $name, description: $description, user_id: $user_id, created_at: datetime(), updated_at: datetime()}) RETURN t';

        return $transaction ? $transaction->run($query, $data) : $this->client->run($query, $data);
    }

    public function linkIndividualToTree(int $individualId, int $treeId, ?TransactionInterface $transaction = null): mixed
    {
        $query = '
            MATCH (i:Individual {id: $individualId})
            MATCH (t:Tree {id: $treeId})
            MERGE (i)-[r:BELONGS_TO]->(t)
            ON CREATE SET r.created_at = datetime()
            RETURN i, t
        ';

        return $transaction ? $transaction->run($query, [
            'individualId' => $individualId,
            'treeId' => $treeId,
        ])
            : $this->client->run($query, [
                'individualId' => $individualId,
                'treeId' => $treeId,
            ]);
    }

    public function getTreeIndividuals(int $treeId): mixed
    {
        $query = '
            MATCH (i:Individual)-[:BELONGS_TO]->(t:Tree {id: $treeId})
            RETURN i
        ';

        return $this->client->run($query, ['treeId' => $treeId]);
    }

    /**
     * @param array{
     *     id: int,
     *     name: string,
     *     description: string|null,
     *     user_id: int
     * } $data
     */
    public function updateTreeNode(array $data, ?TransactionInterface $transaction = null): mixed
    {
        $query = 'MATCH (t:Tree {id: $id}) 
                 SET t.name = $name, 
                     t.description = $description, 
                     t.user_id = $user_id,
                     t.updated_at = datetime() 
                 RETURN t';

        return $transaction ? $transaction->run($query, $data) : $this->client->run($query, $data);
    }

    public function deleteTreeNode(int $id, ?TransactionInterface $transaction = null): mixed
    {
        $query = 'MATCH (t:Tree {id: $id}) DETACH DELETE t';

        return $transaction ? $transaction->run($query, ['id' => $id]) : $this->client->run($query, ['id' => $id]);
    }

    public function deleteParentChildRelationship(int $parentId, int $childId, ?TransactionInterface $transaction = null): mixed
    {
        $query = 'MATCH (p:Individual {id: $parentId})-[r:PARENT_OF]->(c:Individual {id: $childId}) DELETE r';

        return $transaction ? $transaction->run($query, ['parentId' => $parentId, 'childId' => $childId])
                           : $this->client->run($query, ['parentId' => $parentId, 'childId' => $childId]);
    }

    public function deleteSpouseRelationship(int $spouseAId, int $spouseBId, ?TransactionInterface $transaction = null): mixed
    {
        $query = 'MATCH (a:Individual {id: $spouseAId})-[r:SPOUSE_OF]-(b:Individual {id: $spouseBId}) DELETE r';

        return $transaction ? $transaction->run($query, ['spouseAId' => $spouseAId, 'spouseBId' => $spouseBId])
                           : $this->client->run($query, ['spouseAId' => $spouseAId, 'spouseBId' => $spouseBId]);
    }

    public function deleteSiblingRelationship(int $siblingAId, int $siblingBId, ?TransactionInterface $transaction = null): mixed
    {
        $query = 'MATCH (a:Individual {id: $siblingAId})-[r:SIBLING_OF]-(b:Individual {id: $siblingBId}) DELETE r';

        return $transaction ? $transaction->run($query, ['siblingAId' => $siblingAId, 'siblingBId' => $siblingBId])
                           : $this->client->run($query, ['siblingAId' => $siblingAId, 'siblingBId' => $siblingBId]);
    }

    public function deleteAllRelationships(int $individualId, ?TransactionInterface $transaction = null): mixed
    {
        $query = 'MATCH (i:Individual {id: $id})-[r]-(n) DELETE r';

        return $transaction ? $transaction->run($query, ['id' => $individualId])
                           : $this->client->run($query, ['id' => $individualId]);
    }

    public function getExistingRelationships(int $individualId, ?TransactionInterface $transaction = null): mixed
    {
        $query = '
            MATCH (i:Individual {id: $id})-[r]-(n)
            RETURN type(r) as type, n.id as related_id
        ';

        return $transaction ? $transaction->run($query, ['id' => $individualId])
                           : $this->client->run($query, ['id' => $individualId]);
    }

    public function validateTreeExists(int $treeId, ?TransactionInterface $transaction = null): bool
    {
        $query = 'MATCH (t:Tree {id: $id}) RETURN count(t) as count';

        $result = $transaction ? $transaction->run($query, ['id' => $treeId])
                              : $this->client->run($query, ['id' => $treeId]);

        return $result->first()->get('count') > 0;
    }

    public function getTreeStats(int $treeId, ?TransactionInterface $transaction = null): mixed
    {
        $query = '
            MATCH (t:Tree {id: $id})
            OPTIONAL MATCH (t)<-[:BELONGS_TO]-(i:Individual)
            OPTIONAL MATCH (i)-[r]-(n)
            RETURN 
                count(DISTINCT i) as total_individuals,
                count(DISTINCT CASE WHEN type(r) = "PARENT_OF" THEN r END) as parent_relationships,
                count(DISTINCT CASE WHEN type(r) = "SPOUSE_OF" THEN r END) as spouse_relationships,
                count(DISTINCT CASE WHEN type(r) = "SIBLING_OF" THEN r END) as sibling_relationships
        ';

        return $transaction ? $transaction->run($query, ['id' => $treeId])
                           : $this->client->run($query, ['id' => $treeId]);
    }

    public function deleteTreeRelationships(int $treeId, ?TransactionInterface $transaction = null): mixed
    {
        $query = '
            MATCH (t:Tree {id: $id})<-[:BELONGS_TO]-(i:Individual)-[r]-(n)
            DELETE r
        ';

        return $transaction ? $transaction->run($query, ['id' => $treeId])
                           : $this->client->run($query, ['id' => $treeId]);
    }

    public function validateRelationship(int $fromId, int $toId, string $type, ?TransactionInterface $transaction = null): bool
    {
        $query = '
            MATCH (a:Individual {id: $fromId}), (b:Individual {id: $toId})
            WHERE NOT (a)-[:PARENT_OF|SPOUSE_OF|SIBLING_OF]-(b)
            RETURN count(*) as count
        ';

        $result = $transaction ? $transaction->run($query, ['fromId' => $fromId, 'toId' => $toId])
                              : $this->client->run($query, ['fromId' => $fromId, 'toId' => $toId]);

        return $result->first()->get('count') > 0;
    }

    public function validateNoCycles(int $fromId, int $toId, string $type, ?TransactionInterface $transaction = null): bool
    {
        $query = '
            MATCH path = (a:Individual {id: $fromId})-[:PARENT_OF*1..10]->(b:Individual {id: $toId})
            RETURN count(path) as count
        ';

        $result = $transaction ? $transaction->run($query, ['fromId' => $fromId, 'toId' => $toId])
                              : $this->client->run($query, ['fromId' => $fromId, 'toId' => $toId]);

        return $result->first()->get('count') === 0;
    }

    public function getRelationshipStats(int $individualId, ?TransactionInterface $transaction = null): mixed
    {
        $query = '
            MATCH (i:Individual {id: $id})
            OPTIONAL MATCH (i)-[r]-(n)
            RETURN 
                count(DISTINCT CASE WHEN type(r) = "PARENT_OF" AND i.id = startNode(r).id THEN endNode(r) END) as children_count,
                count(DISTINCT CASE WHEN type(r) = "PARENT_OF" AND i.id = endNode(r).id THEN startNode(r) END) as parents_count,
                count(DISTINCT CASE WHEN type(r) = "SPOUSE_OF" THEN n END) as spouses_count,
                count(DISTINCT CASE WHEN type(r) = "SIBLING_OF" THEN n END) as siblings_count
        ';

        return $transaction ? $transaction->run($query, ['id' => $individualId])
                           : $this->client->run($query, ['id' => $individualId]);
    }

    public function getChildren(int $parentId, ?TransactionInterface $transaction = null): mixed
    {
        $query = 'MATCH (p:Individual {id: $parentId})-[:PARENT_OF]->(c:Individual) RETURN c';

        return $transaction ? $transaction->run($query, ['parentId' => $parentId])
                           : $this->client->run($query, ['parentId' => $parentId]);
    }

    public function getParents(int $childId, ?TransactionInterface $transaction = null): mixed
    {
        $query = 'MATCH (p:Individual)-[:PARENT_OF]->(c:Individual {id: $childId}) RETURN p';

        return $transaction ? $transaction->run($query, ['childId' => $childId])
                           : $this->client->run($query, ['childId' => $childId]);
    }

    public function getSpouses(int $individualId, ?TransactionInterface $transaction = null): mixed
    {
        $query = 'MATCH (a:Individual {id: $individualId})-[:SPOUSE_OF]-(b:Individual) RETURN b';

        return $transaction ? $transaction->run($query, ['individualId' => $individualId])
                           : $this->client->run($query, ['individualId' => $individualId]);
    }
}
