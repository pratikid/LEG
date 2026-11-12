<?php

declare(strict_types=1);

namespace App\Traits;

use Closure;
use Exception;
use Laudis\Neo4j\Contracts\ClientInterface;
use Laudis\Neo4j\Contracts\TransactionInterface;
use Laudis\Neo4j\Types\CypherList;

/**
 * Trait for handling Neo4j transactions consistently
 */
trait HandlesNeo4jTransactions
{
    /**
     * Execute a callback within a Neo4j transaction.
     *
     * @param  ClientInterface  $client  The Neo4j client
     * @param  Closure  $callback  The callback to execute within the transaction
     * @return mixed The result of the callback
     *
     * @throws Exception If an error occurs during the transaction
     */
    protected function executeInTransaction(ClientInterface $client, Closure $callback)
    {
        $transaction = $client->beginTransaction();

        try {
            $result = $callback($transaction);
            $transaction->commit();

            return $result;
        } catch (Exception $e) {
            $transaction->rollback();
            throw $e;
        }
    }

    /**
     * Check if a node exists in Neo4j.
     *
     * @param  string  $label  The node label
     * @param  string  $property  The property to check
     * @param  mixed  $value  The value to check for
     * @return bool Whether the node exists
     */
    protected function nodeExists(TransactionInterface $transaction, string $label, string $property, $value): bool
    {
        $query = "MATCH (n:$label {`$property`: \$value}) RETURN count(n) as count";
        $result = $transaction->run($query, ['value' => $value]);

        return $result->first()->get('count') > 0;
    }

    /**
     * Create a node if it doesn't exist.
     *
     * @param  string  $label  The node label
     * @param  array  $properties  The node properties
     * @return CypherList The result of the creation query
     */
    protected function createNodeIfNotExists(TransactionInterface $transaction, string $label, array $properties): CypherList
    {
        $propertyString = $this->formatPropertiesForCypher($properties);
        $query = "MERGE (n:$label {$propertyString}) RETURN n";

        return $transaction->run($query, $properties);
    }

    /**
     * Format properties for a Cypher query.
     *
     * @param  array  $properties  The properties to format
     * @return string The formatted property string
     */
    protected function formatPropertiesForCypher(array $properties): string
    {
        $formattedProperties = [];

        foreach ($properties as $key => $value) {
            $formattedProperties[] = "$key: \$$key";
        }

        return implode(', ', $formattedProperties);
    }

    /**
     * Create a relationship between two nodes.
     *
     * @param  string  $startLabel  The start node label
     * @param  string  $startProperty  The start node property
     * @param  mixed  $startValue  The start node property value
     * @param  string  $endLabel  The end node label
     * @param  string  $endProperty  The end node property
     * @param  mixed  $endValue  The end node property value
     * @param  string  $relationshipType  The relationship type
     * @param  array  $properties  The relationship properties
     * @return CypherList The result of the relationship creation
     */
    protected function createRelationship(
        TransactionInterface $transaction,
        string $startLabel,
        string $startProperty,
        $startValue,
        string $endLabel,
        string $endProperty,
        $endValue,
        string $relationshipType,
        array $properties = []
    ): CypherList {
        $relationshipProps = '';

        if (! empty($properties)) {
            $relationshipProps = ' '.$this->formatPropertiesForCypher($properties);
        }

        $query = "MATCH (a:$startLabel {`$startProperty`: \$startValue}), (b:$endLabel {`$endProperty`: \$endValue}) "
             ."MERGE (a)-[r:$relationshipType{$relationshipProps}]->(b) "
             .'RETURN a, r, b';

        $params = [
            'startValue' => $startValue,
            'endValue' => $endValue,
        ];

        return $transaction->run($query, array_merge($params, $properties));
    }
}
