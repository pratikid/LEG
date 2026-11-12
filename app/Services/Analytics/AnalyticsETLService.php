<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use App\Models\Individual;
use App\Models\Tree;
use App\Services\Neo4jIndividualService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;

/**
 * ETL Service for populating analytics warehouse tables
 * Extracts data from operational tables and Neo4j into analytics-optimized structure
 */
final class AnalyticsETLService
{
    private Neo4jIndividualService $neo4j;
    private array $locationCache = [];
    private array $timeCache = [];

    public function __construct(Neo4jIndividualService $neo4j)
    {
        $this->neo4j = $neo4j;
    }

    /**
     * Run full ETL pipeline for all trees
     */
    public function runFullETL(): array
    {
        $startTime = microtime(true);
        $results = [
            'trees_processed' => 0,
            'individuals_processed' => 0,
            'relationships_processed' => 0,
            'locations_created' => 0,
            'time_records_created' => 0,
            'duration_seconds' => 0,
            'errors' => []
        ];

        try {
            Log::info('Starting full analytics ETL pipeline');

            // Step 1: Populate dimension tables
            $this->populateTimeDimension();
            $results['time_records_created'] = DB::table('dim_time')->count();

            // Step 2: Process each tree
            Tree::chunk(10, function (Collection $trees) use (&$results) {
                foreach ($trees as $tree) {
                    try {
                        $treeResults = $this->processTree($tree);
                        $results['trees_processed']++;
                        $results['individuals_processed'] += $treeResults['individuals'];
                        $results['relationships_processed'] += $treeResults['relationships'];
                        $results['locations_created'] += $treeResults['locations'];
                    } catch (Exception $e) {
                        $results['errors'][] = "Tree {$tree->id}: " . $e->getMessage();
                        Log::error('ETL error processing tree', [
                            'tree_id' => $tree->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            });

            $results['duration_seconds'] = round(microtime(true) - $startTime, 2);
            Log::info('Analytics ETL pipeline completed', $results);

        } catch (Exception $e) {
            $results['errors'][] = "Global ETL error: " . $e->getMessage();
            Log::error('Analytics ETL pipeline failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $results;
    }

    /**
     * Process a single tree through ETL pipeline
     */
    public function processTree(Tree $tree): array
    {
        $results = [
            'individuals' => 0,
            'relationships' => 0,
            'locations' => 0,
            'tree_metrics' => false
        ];

        Log::info("Processing tree {$tree->id} for analytics");

        // Process individuals
        $tree->individuals()->chunk(100, function (Collection $individuals) use (&$results) {
            foreach ($individuals as $individual) {
                $this->processIndividual($individual);
                $results['individuals']++;
            }
        });

        // Process relationships from Neo4j
        $relationshipResults = $this->processTreeRelationships($tree);
        $results['relationships'] = $relationshipResults['count'];

        // Generate tree-level metrics
        $this->generateTreeMetrics($tree);
        $results['tree_metrics'] = true;

        // Count unique locations processed for this tree
        $results['locations'] = DB::table('dim_location')
            ->whereIn('id', function ($query) use ($tree) {
                $query->select('birth_location_key')
                    ->from('fact_individuals')
                    ->where('tree_id', $tree->id)
                    ->whereNotNull('birth_location_key')
                    ->union(
                        DB::table('fact_individuals')
                            ->select('death_location_key')
                            ->where('tree_id', $tree->id)
                            ->whereNotNull('death_location_key')
                    );
            })
            ->count();

        return $results;
    }

    /**
     * Process individual into fact_individuals table
     */
    private function processIndividual(Individual $individual): void
    {
        // Check if already processed recently
        $cacheKey = "analytics:individual:{$individual->id}";
        if (Cache::has($cacheKey)) {
            return;
        }

        $birthDateKey = $this->getOrCreateTimeKey($individual->birth_date);
        $deathDateKey = $this->getOrCreateTimeKey($individual->death_date);
        $birthLocationKey = $this->getOrCreateLocationKey($individual->birth_place);
        $deathLocationKey = $this->getOrCreateLocationKey($individual->death_place);

        // Calculate computed fields
        $lifespanDays = $this->calculateLifespanDays($individual->birth_date, $individual->death_date);
        $migrationDistance = $this->calculateMigrationDistance(
            $individual->birth_place,
            $individual->death_place
        );
        $generation = $this->calculateGeneration($individual);

        // Normalize names for analytics
        $firstNameKey = $this->normalizeNameKey($individual->first_name);
        $lastNameKey = $this->normalizeNameKey($individual->last_name);

        DB::table('fact_individuals')->updateOrInsert(
            ['individual_id' => $individual->id],
            [
                'tree_id' => $individual->tree_id,
                'birth_date_key' => $birthDateKey,
                'death_date_key' => $deathDateKey,
                'birth_location_key' => $birthLocationKey,
                'death_location_key' => $deathLocationKey,
                'lifespan_days' => $lifespanDays,
                'birth_year' => $individual->birth_date?->year,
                'death_year' => $individual->death_date?->year,
                'birth_decade' => $individual->birth_date ? intval($individual->birth_date->year / 10) * 10 : null,
                'death_decade' => $individual->death_date ? intval($individual->death_date->year / 10) * 10 : null,
                'birth_century' => $individual->birth_date ? intval(($individual->birth_date->year - 1) / 100) + 1 : null,
                'death_century' => $individual->death_date ? intval(($individual->death_date->year - 1) / 100) + 1 : null,
                'sex' => $individual->sex ?? 'U',
                'generation' => $generation,
                'migration_distance_km' => $migrationDistance,
                'first_name_key' => $firstNameKey,
                'last_name_key' => $lastNameKey,
                'name_length' => strlen(trim(($individual->first_name ?? '') . ' ' . ($individual->last_name ?? ''))),
                'name_syllables' => $this->countSyllables(trim(($individual->first_name ?? '') . ' ' . ($individual->last_name ?? ''))),
                'updated_at' => now()
            ]
        );

        // Cache for 1 hour to avoid reprocessing
        Cache::put($cacheKey, true, 3600);
    }

    /**
     * Process relationships from Neo4j into fact_relationships table
     */
    private function processTreeRelationships(Tree $tree): array
    {
        $processed = 0;
        
        try {
            // Get all individuals in tree for relationship processing
            $individualIds = $tree->individuals()->pluck('id')->toArray();
            
            foreach ($individualIds as $individualId) {
                // Process parent relationships
                $parents = $this->neo4j->getParents($individualId);
                foreach ($parents as $parentRecord) {
                    $parentNode = $parentRecord->get('p');
                    if ($parentNode) {
                        $this->processRelationshipFact(
                            $tree->id,
                            $parentNode->getProperty('id'),
                            $individualId,
                            'PARENT_OF'
                        );
                        $processed++;
                    }
                }

                // Process spouse relationships  
                $spouses = $this->neo4j->getSpouses($individualId);
                foreach ($spouses as $spouseRecord) {
                    $spouseNode = $spouseRecord->get('b');
                    if ($spouseNode) {
                        $spouseId = $spouseNode->getProperty('id');
                        // Only process once per relationship pair
                        if ($individualId < $spouseId) {
                            $this->processRelationshipFact(
                                $tree->id,
                                $individualId,
                                $spouseId,
                                'SPOUSE_OF'
                            );
                            $processed++;
                        }
                    }
                }

                // Process sibling relationships
                $siblings = $this->neo4j->getSiblings($individualId);
                foreach ($siblings as $siblingRecord) {
                    $siblingNode = $siblingRecord->get('s');
                    if ($siblingNode) {
                        $siblingId = $siblingNode->getProperty('id');
                        // Only process once per relationship pair
                        if ($individualId < $siblingId) {
                            $this->processRelationshipFact(
                                $tree->id,
                                $individualId,
                                $siblingId,
                                'SIBLING_OF'
                            );
                            $processed++;
                        }
                    }
                }
            }

        } catch (Exception $e) {
            Log::error('Error processing tree relationships', [
                'tree_id' => $tree->id,
                'error' => $e->getMessage()
            ]);
        }

        return ['count' => $processed];
    }

    /**
     * Process a single relationship fact
     */
    private function processRelationshipFact(int $treeId, int $individualAId, int $individualBId, string $relationshipType): void
    {
        $individualA = Individual::find($individualAId);
        $individualB = Individual::find($individualBId);
        
        if (!$individualA || !$individualB) {
            return;
        }

        $generationDiff = $this->calculateGenerationDifference($individualA, $individualB);
        $ageDiff = $this->calculateAgeDifference($individualA, $individualB);

        DB::table('fact_relationships')->updateOrInsert(
            [
                'individual_a_id' => $individualAId,
                'individual_b_id' => $individualBId,
                'relationship_type' => $relationshipType
            ],
            [
                'tree_id' => $treeId,
                'degree_of_separation' => 1, // Direct relationship
                'generation_diff' => $generationDiff,
                'age_diff_years' => $ageDiff,
                'is_direct_relationship' => true,
                'updated_at' => now()
            ]
        );
    }

    /**
     * Generate tree-level aggregate metrics
     */
    private function generateTreeMetrics(Tree $tree): void
    {
        $snapshotDate = now()->toDateString();

        // Basic counts
        $individualCount = $tree->individuals()->count();
        $familyCount = DB::table('families')->where('tree_id', $tree->id)->count();
        $relationshipCount = DB::table('fact_relationships')->where('tree_id', $tree->id)->count();

        // Demographic metrics
        $demographics = DB::table('fact_individuals')
            ->where('tree_id', $tree->id)
            ->selectRaw('
                SUM(CASE WHEN sex = ? THEN 1 ELSE 0 END) as male_count,
                SUM(CASE WHEN sex = ? THEN 1 ELSE 0 END) as female_count,
                SUM(CASE WHEN sex = ? THEN 1 ELSE 0 END) as unknown_sex_count,
                AVG(CASE WHEN lifespan_days IS NOT NULL THEN lifespan_days / 365.25 ELSE NULL END) as avg_lifespan_years,
                MAX(birth_year) - MIN(birth_year) as birth_year_span,
                COUNT(DISTINCT birth_location_key) + COUNT(DISTINCT death_location_key) as unique_locations,
                AVG(migration_distance_km) as avg_migration_distance_km,
                MAX(migration_distance_km) as max_migration_distance_km,
                MAX(generation) as generation_count
            ', ['M', 'F', 'U'])
            ->first();

        // Completeness metrics
        $completeness = DB::table('fact_individuals')
            ->where('tree_id', $tree->id)
            ->selectRaw('
                (COUNT(CASE WHEN birth_date_key IS NOT NULL THEN 1 END) * 100.0 / COUNT(*)) as birth_date_completeness,
                (COUNT(CASE WHEN death_date_key IS NOT NULL THEN 1 END) * 100.0 / COUNT(*)) as death_date_completeness,
                (COUNT(CASE WHEN birth_location_key IS NOT NULL OR death_location_key IS NOT NULL THEN 1 END) * 100.0 / COUNT(*)) as location_completeness
            ')
            ->first();

        DB::table('fact_tree_metrics')->updateOrInsert(
            [
                'tree_id' => $tree->id,
                'snapshot_date' => $snapshotDate
            ],
            [
                'individual_count' => $individualCount,
                'family_count' => $familyCount,
                'generation_count' => $demographics->generation_count ?? 0,
                'relationship_count' => $relationshipCount,
                'male_count' => $demographics->male_count ?? 0,
                'female_count' => $demographics->female_count ?? 0,
                'unknown_sex_count' => $demographics->unknown_sex_count ?? 0,
                'avg_lifespan_years' => $demographics->avg_lifespan_years,
                'birth_year_span' => $demographics->birth_year_span,
                'unique_locations' => $demographics->unique_locations ?? 0,
                'max_migration_distance_km' => $demographics->max_migration_distance_km,
                'avg_migration_distance_km' => $demographics->avg_migration_distance_km,
                'birth_date_completeness' => $completeness->birth_date_completeness ?? 0,
                'death_date_completeness' => $completeness->death_date_completeness ?? 0,
                'location_completeness' => $completeness->location_completeness ?? 0,
                'updated_at' => now()
            ]
        );
    }

    /**
     * Populate time dimension table with date range
     */
    private function populateTimeDimension(): void
    {
        $startDate = Carbon::create(1000, 1, 1); // Start from year 1000
        $endDate = Carbon::create(2100, 12, 31); // Go to year 2100

        $existingCount = DB::table('dim_time')->count();
        if ($existingCount > 0) {
            Log::info("Time dimension already populated with {$existingCount} records");
            return;
        }

        Log::info('Populating time dimension table');
        
        $batchSize = 1000;
        $records = [];
        
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $records[] = [
                'date' => $date->toDateString(),
                'year' => $date->year,
                'month' => $date->month,
                'day' => $date->day,
                'quarter' => $date->quarter,
                'week_of_year' => $date->weekOfYear,
                'day_of_week' => $date->dayOfWeek,
                'month_name' => $date->monthName,
                'day_name' => $date->dayName,
                'is_weekend' => $date->isWeekend(),
                'decade' => intval($date->year / 10) * 10,
                'century' => intval(($date->year - 1) / 100) + 1,
                'created_at' => now(),
                'updated_at' => now()
            ];

            if (count($records) >= $batchSize) {
                DB::table('dim_time')->insert($records);
                $records = [];
            }
        }

        if (!empty($records)) {
            DB::table('dim_time')->insert($records);
        }

        Log::info('Time dimension populated successfully');
    }

    /**
     * Get or create time dimension key for a date
     */
    private function getOrCreateTimeKey(?Carbon $date): ?int
    {
        if (!$date) {
            return null;
        }

        $dateString = $date->toDateString();
        
        if (isset($this->timeCache[$dateString])) {
            return $this->timeCache[$dateString];
        }

        $timeRecord = DB::table('dim_time')
            ->where('date', $dateString)
            ->first();

        if ($timeRecord) {
            $this->timeCache[$dateString] = $timeRecord->id;
            return $timeRecord->id;
        }

        return null;
    }

    /**
     * Get or create location dimension key for a location string
     */
    private function getOrCreateLocationKey(?string $location): ?int
    {
        if (!$location || empty(trim($location))) {
            return null;
        }

        $locationKey = md5(strtolower(trim($location)));
        
        if (isset($this->locationCache[$locationKey])) {
            return $this->locationCache[$locationKey];
        }

        $locationRecord = DB::table('dim_location')
            ->where('location_key', $locationKey)
            ->first();

        if ($locationRecord) {
            $this->locationCache[$locationKey] = $locationRecord->id;
            return $locationRecord->id;
        }

        // Create new location record
        $normalizedLocation = $this->normalizeLocation($location);
        $geoData = $this->geocodeLocation($normalizedLocation);

        $locationId = DB::table('dim_location')->insertGetId([
            'location_key' => $locationKey,
            'raw_location' => $location,
            'normalized_location' => $normalizedLocation,
            'city' => $geoData['city'] ?? null,
            'state' => $geoData['state'] ?? null,
            'country' => $geoData['country'] ?? null,
            'latitude' => $geoData['latitude'] ?? null,
            'longitude' => $geoData['longitude'] ?? null,
            'confidence_score' => $geoData['confidence'] ?? 0,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        $this->locationCache[$locationKey] = $locationId;
        return $locationId;
    }

    // Helper methods

    private function calculateLifespanDays(?Carbon $birthDate, ?Carbon $deathDate): ?int
    {
        if (!$birthDate || !$deathDate) {
            return null;
        }
        return $birthDate->diffInDays($deathDate);
    }

    private function calculateMigrationDistance(?string $birthPlace, ?string $deathPlace): ?float
    {
        // Placeholder - would implement actual geographic distance calculation
        if (!$birthPlace || !$deathPlace || $birthPlace === $deathPlace) {
            return 0.0;
        }
        return null; // TODO: Implement Haversine distance calculation
    }

    private function calculateGeneration(Individual $individual): ?int
    {
        // Placeholder - would implement generation calculation from tree root
        return null; // TODO: Calculate using Neo4j shortest path to tree root
    }

    private function calculateGenerationDifference(Individual $a, Individual $b): ?int
    {
        // Placeholder - would calculate generation difference
        return null;
    }

    private function calculateAgeDifference(Individual $a, Individual $b): ?float
    {
        if (!$a->birth_date || !$b->birth_date) {
            return null;
        }
        return $a->birth_date->diffInYears($b->birth_date, false);
    }

    private function normalizeNameKey(?string $name): ?string
    {
        if (!$name) {
            return null;
        }
        return strtolower(trim(preg_replace('/[^a-zA-Z]/', '', $name)));
    }

    private function normalizeLocation(string $location): string
    {
        // Basic location normalization
        return trim(preg_replace('/\s+/', ' ', $location));
    }

    private function geocodeLocation(string $location): array
    {
        // Placeholder for geocoding service integration
        // Would integrate with Google Geocoding API, Nominatim, etc.
        return [
            'city' => null,
            'state' => null,
            'country' => null,
            'latitude' => null,
            'longitude' => null,
            'confidence' => 0
        ];
    }

    private function countSyllables(string $text): int
    {
        // Simple syllable counting algorithm
        $text = strtolower($text);
        $vowels = 'aeiouy';
        $count = 0;
        $prevWasVowel = false;
        
        for ($i = 0; $i < strlen($text); $i++) {
            $isVowel = strpos($vowels, $text[$i]) !== false;
            if ($isVowel && !$prevWasVowel) {
                $count++;
            }
            $prevWasVowel = $isVowel;
        }
        
        return max(1, $count);
    }
}
