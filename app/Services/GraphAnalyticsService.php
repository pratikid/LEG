<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Individual;
use App\Models\Tree;
use Laudis\Neo4j\Contracts\ClientInterface;

/**
 * Advanced graph analytics service for genealogical relationship insights
 * Provides Neo4j-based algorithms for family tree analysis
 */
final class GraphAnalyticsService
{
    public function __construct(
        private readonly ClientInterface $neo4j,
        private readonly CacheService $cacheService
    ) {}

    /**
     * Find the shortest relationship path between two individuals
     *
     * @param  int  $individualA  First individual ID
     * @param  int  $individualB  Second individual ID
     * @return array{path: array<array{id: int, name: string, relationship: string}>, distance: int, relationship_type: string}
     */
    public function findRelationshipPath(int $individualA, int $individualB): array
    {
        $cacheKey = "relationship_path:{$individualA}:{$individualB}";

        return $this->cacheService->remember($cacheKey, 3600, function () use ($individualA, $individualB) {
            $query = "
                MATCH (a:Individual {id: $individualA})
                MATCH (b:Individual {id: $individualB})
                MATCH path = shortestPath((a)-[*..15]-(b))
                WHERE a <> b
                RETURN [node in nodes(path) | {
                    id: node.id,
                    name: node.first_name + ' ' + node.last_name,
                    birth_year: node.birth_year
                }] as individuals,
                [rel in relationships(path) | type(rel)] as relationships,
                length(path) as distance
                LIMIT 1
            ";

            $result = $this->neo4j->run($query);

            if ($result->count() === 0) {
                return [
                    'path' => [],
                    'distance' => -1,
                    'relationship_type' => 'No relationship found',
                ];
            }

            $record = $result->first();
            $individuals = $record->get('individuals');
            $relationships = $record->get('relationships');
            $distance = $record->get('distance');

            // Build path with relationship labels
            $path = [];
            for ($i = 0; $i < count($individuals); $i++) {
                $individual = $individuals[$i];
                $relationship = $i < count($relationships) ? $this->translateRelationship($relationships[$i]) : '';

                $path[] = [
                    'id' => $individual['id'],
                    'name' => $individual['name'],
                    'birth_year' => $individual['birth_year'] ?? null,
                    'relationship' => $relationship,
                ];
            }

            return [
                'path' => $path,
                'distance' => $distance,
                'relationship_type' => $this->determineRelationshipType($distance, $relationships),
            ];
        });
    }

    /**
     * Find common ancestors between two individuals
     *
     * @param  int  $individualA  First individual ID
     * @param  int  $individualB  Second individual ID
     * @return array<array{id: int, name: string, birth_year: ?int, relationship_to_a: string, relationship_to_b: string}>
     */
    public function findCommonAncestors(int $individualA, int $individualB): array
    {
        $cacheKey = "common_ancestors:{$individualA}:{$individualB}";

        return $this->cacheService->remember($cacheKey, 7200, function () use ($individualA, $individualB) {
            $query = "
                MATCH (a:Individual {id: $individualA})
                MATCH (b:Individual {id: $individualB})
                MATCH (a)-[:CHILD_OF*1..10]->(ancestor)<-[:CHILD_OF*1..10]-(b)
                WHERE a <> b AND ancestor <> a AND ancestor <> b
                RETURN DISTINCT ancestor.id as id,
                       ancestor.first_name + ' ' + ancestor.last_name as name,
                       ancestor.birth_year as birth_year,
                       length(shortestPath((a)-[:CHILD_OF*]->(ancestor))) as distance_to_a,
                       length(shortestPath((b)-[:CHILD_OF*]->(ancestor))) as distance_to_b
                ORDER BY distance_to_a + distance_to_b ASC
                LIMIT 20
            ";

            $result = $this->neo4j->run($query);
            $ancestors = [];

            foreach ($result as $record) {
                $ancestors[] = [
                    'id' => $record->get('id'),
                    'name' => $record->get('name'),
                    'birth_year' => $record->get('birth_year'),
                    'relationship_to_a' => $this->generateAncestorRelationship($record->get('distance_to_a')),
                    'relationship_to_b' => $this->generateAncestorRelationship($record->get('distance_to_b')),
                    'combined_distance' => $record->get('distance_to_a') + $record->get('distance_to_b'),
                ];
            }

            return $ancestors;
        });
    }

    /**
     * Calculate relationship statistics for a family tree
     *
     * @param  int  $treeId  Tree ID
     * @return array{total_individuals: int, total_families: int, generations: int, most_children: array, oldest_person: array, statistics: array}
     */
    public function calculateTreeStatistics(int $treeId): array
    {
        $cacheKey = "tree_statistics:{$treeId}";

        return $this->cacheService->remember($cacheKey, 3600, function () use ($treeId) {
            // Total individuals and families
            $totalQuery = "
                MATCH (i:Individual {tree_id: $treeId})
                OPTIONAL MATCH (f:Family {tree_id: $treeId})
                RETURN count(DISTINCT i) as individuals, count(DISTINCT f) as families
            ";

            $totalResult = $this->neo4j->run($totalQuery)->first();

            // Generation depth
            $generationQuery = "
                MATCH (root:Individual {tree_id: $treeId})
                WHERE NOT (root)-[:CHILD_OF]->()
                MATCH path = (root)<-[:CHILD_OF*]-(descendant)
                RETURN max(length(path)) as max_generations
            ";

            $generationResult = $this->neo4j->run($generationQuery)->first();

            // Person with most children
            $childrenQuery = "
                MATCH (parent:Individual {tree_id: $treeId})<-[:CHILD_OF]-(child)
                WITH parent, count(child) as child_count
                ORDER BY child_count DESC
                LIMIT 1
                RETURN parent.id as id, 
                       parent.first_name + ' ' + parent.last_name as name,
                       child_count
            ";

            $childrenResult = $this->neo4j->run($childrenQuery)->first();

            // Oldest person
            $oldestQuery = "
                MATCH (i:Individual {tree_id: $treeId})
                WHERE i.birth_year IS NOT NULL
                ORDER BY i.birth_year ASC
                LIMIT 1
                RETURN i.id as id,
                       i.first_name + ' ' + i.last_name as name,
                       i.birth_year
            ";

            $oldestResult = $this->neo4j->run($oldestQuery)->first();

            // Additional statistics
            $statsQuery = "
                MATCH (i:Individual {tree_id: $treeId})
                OPTIONAL MATCH (m:Individual {tree_id: $treeId, sex: 'M'})
                OPTIONAL MATCH (f:Individual {tree_id: $treeId, sex: 'F'})
                OPTIONAL MATCH (living:Individual {tree_id: $treeId})
                WHERE living.death_year IS NULL
                RETURN count(DISTINCT m) as males,
                       count(DISTINCT f) as females,
                       count(DISTINCT living) as estimated_living,
                       avg(i.birth_year) as avg_birth_year
            ";

            $statsResult = $this->neo4j->run($statsQuery)->first();

            return [
                'total_individuals' => $totalResult->get('individuals') ?? 0,
                'total_families' => $totalResult->get('families') ?? 0,
                'generations' => ($generationResult->get('max_generations') ?? 0) + 1,
                'most_children' => [
                    'id' => $childrenResult?->get('id'),
                    'name' => $childrenResult?->get('name'),
                    'child_count' => $childrenResult?->get('child_count') ?? 0,
                ],
                'oldest_person' => [
                    'id' => $oldestResult?->get('id'),
                    'name' => $oldestResult?->get('name'),
                    'birth_year' => $oldestResult?->get('birth_year'),
                ],
                'statistics' => [
                    'males' => $statsResult->get('males') ?? 0,
                    'females' => $statsResult->get('females') ?? 0,
                    'estimated_living' => $statsResult->get('estimated_living') ?? 0,
                    'average_birth_year' => round($statsResult->get('avg_birth_year') ?? 0),
                    'gender_ratio' => $this->calculateGenderRatio($statsResult->get('males') ?? 0, $statsResult->get('females') ?? 0),
                ],
            ];
        });
    }

    /**
     * Calculate coefficient of relationship between two individuals
     *
     * @param  int  $individualA  First individual ID
     * @param  int  $individualB  Second individual ID
     * @return array{coefficient: float, description: string, shared_ancestors: int}
     */
    public function calculateCoefficientOfRelationship(int $individualA, int $individualB): array
    {
        $commonAncestors = $this->findCommonAncestors($individualA, $individualB);

        if (empty($commonAncestors)) {
            return [
                'coefficient' => 0.0,
                'description' => 'No genetic relationship detected',
                'shared_ancestors' => 0,
            ];
        }

        $totalCoefficient = 0.0;

        foreach ($commonAncestors as $ancestor) {
            // Calculate coefficient for this ancestor path
            // Formula: (1/2)^(n1 + n2 + 1) where n1 and n2 are distances to common ancestor
            $pathLength = $ancestor['combined_distance'] + 1;
            $ancestorCoefficient = pow(0.5, $pathLength);
            $totalCoefficient += $ancestorCoefficient;
        }

        return [
            'coefficient' => round($totalCoefficient, 6),
            'description' => $this->describeGeneticRelationship($totalCoefficient),
            'shared_ancestors' => count($commonAncestors),
        ];
    }

    /**
     * Find all descendants of an individual up to specified generations
     *
     * @param  int  $individualId  Individual ID
     * @param  int  $maxGenerations  Maximum generations to traverse
     * @return array<array{id: int, name: string, generation: int, relationship: string}>
     */
    public function findDescendants(int $individualId, int $maxGenerations = 10): array
    {
        $cacheKey = "descendants:{$individualId}:{$maxGenerations}";

        return $this->cacheService->remember($cacheKey, 3600, function () use ($individualId, $maxGenerations) {
            $query = "
                MATCH (ancestor:Individual {id: $individualId})
                MATCH path = (ancestor)<-[:CHILD_OF*1..$maxGenerations]-(descendant)
                RETURN descendant.id as id,
                       descendant.first_name + ' ' + descendant.last_name as name,
                       descendant.birth_year as birth_year,
                       length(path) as generation
                ORDER BY generation, descendant.birth_year
            ";

            $result = $this->neo4j->run($query);
            $descendants = [];

            foreach ($result as $record) {
                $generation = $record->get('generation');
                $descendants[] = [
                    'id' => $record->get('id'),
                    'name' => $record->get('name'),
                    'birth_year' => $record->get('birth_year'),
                    'generation' => $generation,
                    'relationship' => $this->generateDescendantRelationship($generation),
                ];
            }

            return $descendants;
        });
    }

    /**
     * Analyze surname distribution in a tree
     *
     * @param  int  $treeId  Tree ID
     * @return array<array{surname: string, count: int, percentage: float}>
     */
    public function analyzeSurnameDistribution(int $treeId): array
    {
        $cacheKey = "surname_distribution:{$treeId}";

        return $this->cacheService->remember($cacheKey, 7200, function () use ($treeId) {
            $query = "
                MATCH (i:Individual {tree_id: $treeId})
                WHERE i.last_name IS NOT NULL AND i.last_name <> ''
                WITH i.last_name as surname, count(*) as count
                WITH surname, count, sum(count) OVER () as total
                RETURN surname, count, round(100.0 * count / total, 2) as percentage
                ORDER BY count DESC
                LIMIT 50
            ";

            $result = $this->neo4j->run($query);
            $distribution = [];

            foreach ($result as $record) {
                $distribution[] = [
                    'surname' => $record->get('surname'),
                    'count' => $record->get('count'),
                    'percentage' => $record->get('percentage'),
                ];
            }

            return $distribution;
        });
    }

    private function translateRelationship(string $relationship): string
    {
        return match ($relationship) {
            'CHILD_OF' => 'child of',
            'PARENT_OF' => 'parent of',
            'SPOUSE_OF' => 'spouse of',
            'SIBLING_OF' => 'sibling of',
            default => mb_strtolower(str_replace('_', ' ', $relationship))
        };
    }

    private function determineRelationshipType(int $distance, array $relationships): string
    {
        if ($distance === 0) {
            return 'Same person';
        }

        if ($distance === 1) {
            return match ($relationships[0] ?? '') {
                'SPOUSE_OF' => 'Spouse',
                'CHILD_OF', 'PARENT_OF' => 'Parent/Child',
                'SIBLING_OF' => 'Sibling',
                default => 'Direct relationship'
            };
        }

        if ($distance === 2) {
            return 'Grandparent/Grandchild or In-law';
        }

        if ($distance <= 4) {
            return 'Close relative (Cousin/Great-grandparent)';
        }

        return 'Distant relative';
    }

    private function generateAncestorRelationship(int $distance): string
    {
        return match ($distance) {
            1 => 'Parent',
            2 => 'Grandparent',
            3 => 'Great-grandparent',
            4 => 'Great-great-grandparent',
            default => str_repeat('Great-', $distance - 2).'grandparent'
        };
    }

    private function generateDescendantRelationship(int $generation): string
    {
        return match ($generation) {
            1 => 'Child',
            2 => 'Grandchild',
            3 => 'Great-grandchild',
            4 => 'Great-great-grandchild',
            default => str_repeat('Great-', $generation - 2).'grandchild'
        };
    }

    private function calculateGenderRatio(int $males, int $females): float
    {
        if ($females === 0) {
            return $males > 0 ? 999.0 : 0.0;
        }

        return round($males / $females, 2);
    }

    private function describeGeneticRelationship(float $coefficient): string
    {
        return match (true) {
            $coefficient >= 0.5 => 'Parent/Child or Full Siblings',
            $coefficient >= 0.25 => 'Half-siblings or Grandparent/Grandchild',
            $coefficient >= 0.125 => 'First Cousins or Great-grandparent/Great-grandchild',
            $coefficient >= 0.0625 => 'Second Cousins or Great-great-grandparent relationship',
            $coefficient >= 0.03125 => 'Third Cousins',
            $coefficient > 0 => 'Distant Cousins',
            default => 'No detectable genetic relationship'
        };
    }
}
