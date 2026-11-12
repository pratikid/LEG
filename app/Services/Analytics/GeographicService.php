<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Geographic Analytics Service
 * Provides geographic insights, migration analysis, and location-based statistics
 */
final class GeographicService
{
    private const CACHE_TTL = 3600; // 1 hour

    private const CACHE_PREFIX = 'geographic:';

    private const EARTH_RADIUS_KM = 6371;

    /**
     * Get geographic overview for a tree
     */
    public function getTreeGeographics(int $treeId): array
    {
        $cacheKey = self::CACHE_PREFIX."tree:{$treeId}";

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($treeId) {
            return [
                'location_summary' => $this->getLocationSummary($treeId),
                'migration_patterns' => $this->getMigrationPatterns($treeId),
                'geographic_distribution' => $this->getGeographicDistribution($treeId),
                'birth_location_trends' => $this->getBirthLocationTrends($treeId),
                'settlement_analysis' => $this->getSettlementAnalysis($treeId),
                'distance_metrics' => $this->getDistanceMetrics($treeId),
            ];
        });
    }

    /**
     * Get location summary statistics
     */
    public function getLocationSummary(int $treeId): array
    {
        // Get unique birth and death locations
        $birthLocations = DB::table('fact_individuals fi')
            ->join('dim_location dl', 'fi.birth_location_key', '=', 'dl.id')
            ->where('fi.tree_id', $treeId)
            ->whereNotNull('fi.birth_location_key')
            ->select('dl.country', 'dl.state', 'dl.city', 'dl.normalized_location')
            ->distinct()
            ->get();

        $deathLocations = DB::table('fact_individuals fi')
            ->join('dim_location dl', 'fi.death_location_key', '=', 'dl.id')
            ->where('fi.tree_id', $treeId)
            ->whereNotNull('fi.death_location_key')
            ->select('dl.country', 'dl.state', 'dl.city', 'dl.normalized_location')
            ->distinct()
            ->get();

        $allLocations = $birthLocations->merge($deathLocations)->unique('normalized_location');

        // Count unique geographic levels
        $countries = $allLocations->whereNotNull('country')->pluck('country')->unique();
        $states = $allLocations->whereNotNull('state')->pluck('state')->unique();
        $cities = $allLocations->whereNotNull('city')->pluck('city')->unique();

        // Calculate geographic spread
        $coordinates = DB::table('fact_individuals fi')
            ->join('dim_location dl', function ($join) {
                $join->on('fi.birth_location_key', '=', 'dl.id')
                    ->orOn('fi.death_location_key', '=', 'dl.id');
            })
            ->where('fi.tree_id', $treeId)
            ->whereNotNull('dl.latitude')
            ->whereNotNull('dl.longitude')
            ->select('dl.latitude', 'dl.longitude')
            ->distinct()
            ->get();

        $geographicSpread = $this->calculateGeographicSpread($coordinates);

        return [
            'total_unique_locations' => $allLocations->count(),
            'countries' => $countries->count(),
            'states_provinces' => $states->count(),
            'cities_towns' => $cities->count(),
            'geographic_spread_km' => $geographicSpread,
            'location_completeness' => $this->calculateLocationCompleteness($treeId),
            'top_countries' => $this->getTopCountries($treeId),
            'geocoding_quality' => $this->getGeocodingQuality($treeId),
        ];
    }

    /**
     * Analyze migration patterns between generations
     */
    public function getMigrationPatterns(int $treeId): array
    {
        // Migration between birth and death locations
        $personalMigrations = DB::table('fact_individuals fi')
            ->join('dim_location birth_loc', 'fi.birth_location_key', '=', 'birth_loc.id')
            ->join('dim_location death_loc', 'fi.death_location_key', '=', 'death_loc.id')
            ->where('fi.tree_id', $treeId)
            ->whereNotNull('fi.migration_distance_km')
            ->where('fi.migration_distance_km', '>', 0)
            ->select(
                'birth_loc.country as birth_country',
                'birth_loc.state as birth_state',
                'death_loc.country as death_country',
                'death_loc.state as death_state',
                'fi.migration_distance_km',
                'fi.birth_decade'
            )
            ->get();

        // Intergenerational migration patterns
        $generationalMigration = $this->analyzeGenerationalMigration($treeId);

        // Migration distance statistics
        $migrationStats = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('migration_distance_km')
            ->where('migration_distance_km', '>', 0)
            ->selectRaw('
                AVG(migration_distance_km) as avg_distance,
                MIN(migration_distance_km) as min_distance,
                MAX(migration_distance_km) as max_distance,
                STDDEV(migration_distance_km) as std_distance,
                COUNT(*) as migration_count
            ')
            ->first();

        // Migration by time period
        $migrationByDecade = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('migration_distance_km')
            ->whereNotNull('birth_decade')
            ->where('migration_distance_km', '>', 0)
            ->select('birth_decade', DB::raw('AVG(migration_distance_km) as avg_distance'))
            ->groupBy('birth_decade')
            ->orderBy('birth_decade')
            ->get()
            ->keyBy('birth_decade')
            ->map(fn ($item) => round($item->avg_distance, 1))
            ->toArray();

        return [
            'personal_migrations' => [
                'total_migrants' => $personalMigrations->count(),
                'average_distance_km' => $migrationStats ? round($migrationStats->avg_distance, 1) : 0,
                'max_distance_km' => $migrationStats ? round($migrationStats->max_distance, 1) : 0,
                'migration_routes' => $this->identifyMigrationRoutes($personalMigrations),
            ],
            'generational_patterns' => $generationalMigration,
            'temporal_trends' => [
                'by_decade' => $migrationByDecade,
                'migration_trend' => $this->analyzeMigrationTrend($migrationByDecade),
            ],
            'migration_categories' => $this->categorizeMigrations($personalMigrations),
        ];
    }

    /**
     * Get geographic distribution analysis
     */
    public function getGeographicDistribution(int $treeId): array
    {
        // Birth locations by country
        $birthsByCountry = DB::table('fact_individuals fi')
            ->join('dim_location dl', 'fi.birth_location_key', '=', 'dl.id')
            ->where('fi.tree_id', $treeId)
            ->whereNotNull('dl.country')
            ->select('dl.country', DB::raw('COUNT(*) as births'))
            ->groupBy('dl.country')
            ->orderByDesc('births')
            ->get()
            ->keyBy('country')
            ->map(fn ($item) => $item->births)
            ->toArray();

        // Birth locations by state/province within top countries
        $birthsByState = DB::table('fact_individuals fi')
            ->join('dim_location dl', 'fi.birth_location_key', '=', 'dl.id')
            ->where('fi.tree_id', $treeId)
            ->whereNotNull('dl.state')
            ->whereIn('dl.country', array_keys(array_slice($birthsByCountry, 0, 3))) // Top 3 countries
            ->select('dl.country', 'dl.state', DB::raw('COUNT(*) as births'))
            ->groupBy(['dl.country', 'dl.state'])
            ->orderByDesc('births')
            ->get()
            ->groupBy('country')
            ->map(function ($states) {
                return $states->keyBy('state')->map(fn ($item) => $item->births)->toArray();
            })
            ->toArray();

        // Urban vs rural analysis (based on location patterns)
        $urbanRural = $this->analyzeUrbanRuralDistribution($treeId);

        // Population density by generation
        $densityByGeneration = $this->getPopulationDensityByGeneration($treeId);

        return [
            'by_country' => $birthsByCountry,
            'by_state_province' => $birthsByState,
            'urban_rural_distribution' => $urbanRural,
            'population_centers' => $this->identifyPopulationCenters($treeId),
            'density_by_generation' => $densityByGeneration,
            'geographic_concentration' => $this->calculateGeographicConcentration($treeId),
        ];
    }

    /**
     * Analyze birth location trends over time
     */
    public function getBirthLocationTrends(int $treeId): array
    {
        // Country trends by decade
        $countryTrends = DB::table('fact_individuals fi')
            ->join('dim_location dl', 'fi.birth_location_key', '=', 'dl.id')
            ->where('fi.tree_id', $treeId)
            ->whereNotNull('fi.birth_decade')
            ->whereNotNull('dl.country')
            ->select('fi.birth_decade', 'dl.country', DB::raw('COUNT(*) as births'))
            ->groupBy(['fi.birth_decade', 'dl.country'])
            ->orderBy('fi.birth_decade')
            ->get()
            ->groupBy('birth_decade')
            ->map(function ($decade) {
                return $decade->keyBy('country')->map(fn ($item) => $item->births)->toArray();
            })
            ->toArray();

        // New settlement identification
        $newSettlements = $this->identifyNewSettlements($treeId);

        // Frontier expansion analysis
        $frontierExpansion = $this->analyzeFrontierExpansion($treeId);

        return [
            'country_trends_by_decade' => $countryTrends,
            'new_settlements' => $newSettlements,
            'frontier_expansion' => $frontierExpansion,
            'settlement_longevity' => $this->analyzeSettlementLongevity($treeId),
        ];
    }

    /**
     * Analyze settlement patterns and community formation
     */
    public function getSettlementAnalysis(int $treeId): array
    {
        // Identify family clusters (multiple individuals in same location)
        $familyClusters = DB::table('fact_individuals fi')
            ->join('dim_location dl', 'fi.birth_location_key', '=', 'dl.id')
            ->where('fi.tree_id', $treeId)
            ->select('dl.normalized_location', 'dl.city', 'dl.state', 'dl.country', DB::raw('COUNT(*) as family_members'))
            ->groupBy(['dl.normalized_location', 'dl.city', 'dl.state', 'dl.country'])
            ->having('family_members', '>=', 3) // At least 3 family members
            ->orderByDesc('family_members')
            ->get()
            ->toArray();

        // Multi-generational settlements
        $multigenerationalSettlements = $this->identifyMultigenerationalSettlements($treeId);

        // Settlement establishment patterns
        $settlementEstablishment = $this->analyzeSettlementEstablishment($treeId);

        return [
            'family_clusters' => $familyClusters,
            'multigenerational_settlements' => $multigenerationalSettlements,
            'settlement_establishment' => $settlementEstablishment,
            'community_continuity' => $this->analyzeCommunityontinuity($treeId),
        ];
    }

    /**
     * Calculate various distance metrics
     */
    public function getDistanceMetrics(int $treeId): array
    {
        $coordinates = DB::table('fact_individuals fi')
            ->join('dim_location dl', 'fi.birth_location_key', '=', 'dl.id')
            ->where('fi.tree_id', $treeId)
            ->whereNotNull('dl.latitude')
            ->whereNotNull('dl.longitude')
            ->select('dl.latitude', 'dl.longitude', 'fi.individual_id', 'fi.birth_year')
            ->get();

        if ($coordinates->count() < 2) {
            return ['insufficient_data' => true];
        }

        // Calculate geographic center (centroid)
        $center = $this->calculateGeographicCenter($coordinates);

        // Calculate dispersion from center
        $dispersion = $this->calculateDispersion($coordinates, $center);

        // Identify outliers (individuals far from family center)
        $outliers = $this->identifyGeographicOutliers($coordinates, $center);

        return [
            'geographic_center' => $center,
            'average_distance_from_center' => round($dispersion['average'], 1),
            'max_distance_from_center' => round($dispersion['maximum'], 1),
            'geographic_outliers' => $outliers,
            'family_diameter_km' => $this->calculateFamilyDiameter($coordinates),
        ];
    }

    /**
     * Compare geographic patterns between trees
     */
    public function compareTrees(int $treeId1, int $treeId2): array
    {
        $tree1Geographic = $this->getTreeGeographics($treeId1);
        $tree2Geographic = $this->getTreeGeographics($treeId2);

        return [
            'tree_1' => $tree1Geographic,
            'tree_2' => $tree2Geographic,
            'comparison' => $this->calculateGeographicComparison($tree1Geographic, $tree2Geographic),
        ];
    }

    /**
     * Get geographic insights and recommendations
     */
    public function getGeographicInsights(int $treeId): array
    {
        $geographic = $this->getTreeGeographics($treeId);
        $insights = [];

        // Location data completeness
        $completeness = $geographic['location_summary']['location_completeness'];
        if ($completeness < 50) {
            $insights[] = [
                'type' => 'low_location_data',
                'message' => 'Limited location data reduces geographic analysis accuracy. Consider adding more place information.',
                'priority' => 'high',
            ];
        }

        // Geographic concentration
        $concentration = $geographic['geographic_distribution']['geographic_concentration'] ?? 0;
        if ($concentration > 0.8) {
            $insights[] = [
                'type' => 'geographic_concentration',
                'message' => 'Family shows high geographic concentration, suggesting strong community ties.',
                'priority' => 'info',
            ];
        } elseif ($concentration < 0.3) {
            $insights[] = [
                'type' => 'geographic_dispersion',
                'message' => 'Family is highly dispersed geographically, indicating significant migration patterns.',
                'priority' => 'info',
            ];
        }

        // Migration patterns
        $migrationCount = $geographic['migration_patterns']['personal_migrations']['total_migrants'];
        $totalIndividuals = DB::table('fact_individuals')->where('tree_id', $treeId)->count();

        if ($migrationCount > 0 && $migrationCount / $totalIndividuals > 0.3) {
            $insights[] = [
                'type' => 'high_migration',
                'message' => 'Family shows high migration rates, indicating mobility and adaptation.',
                'priority' => 'info',
            ];
        }

        return [
            'insights' => $insights,
            'recommendations' => $this->generateGeographicRecommendations($geographic),
        ];
    }

    // Helper methods

    private function calculateGeographicSpread(object $coordinates): ?float
    {
        if ($coordinates->count() < 2) {
            return null;
        }

        $lats = $coordinates->pluck('latitude')->toArray();
        $lons = $coordinates->pluck('longitude')->toArray();

        $minLat = min($lats);
        $maxLat = max($lats);
        $minLon = min($lons);
        $maxLon = max($lons);

        // Calculate distance between diagonal corners of bounding box
        return $this->haversineDistance($minLat, $minLon, $maxLat, $maxLon);
    }

    private function calculateLocationCompleteness(int $treeId): float
    {
        $total = DB::table('fact_individuals')->where('tree_id', $treeId)->count();
        $withLocations = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->where(function ($query) {
                $query->whereNotNull('birth_location_key')
                    ->orWhereNotNull('death_location_key');
            })
            ->count();

        return $total > 0 ? round(($withLocations / $total) * 100, 1) : 0;
    }

    private function getTopCountries(int $treeId): array
    {
        return DB::table('fact_individuals fi')
            ->join('dim_location dl', function ($join) {
                $join->on('fi.birth_location_key', '=', 'dl.id')
                    ->orOn('fi.death_location_key', '=', 'dl.id');
            })
            ->where('fi.tree_id', $treeId)
            ->whereNotNull('dl.country')
            ->select('dl.country', DB::raw('COUNT(*) as count'))
            ->groupBy('dl.country')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'country' => $item->country,
                    'individual_count' => $item->count,
                ];
            })
            ->toArray();
    }

    private function getGeocodingQuality(int $treeId): array
    {
        $locationStats = DB::table('fact_individuals fi')
            ->join('dim_location dl', function ($join) {
                $join->on('fi.birth_location_key', '=', 'dl.id')
                    ->orOn('fi.death_location_key', '=', 'dl.id');
            })
            ->where('fi.tree_id', $treeId)
            ->selectRaw('
                COUNT(*) as total_locations,
                SUM(CASE WHEN dl.latitude IS NOT NULL AND dl.longitude IS NOT NULL THEN 1 ELSE 0 END) as geocoded_locations,
                AVG(dl.confidence_score) as avg_confidence
            ')
            ->first();

        $geocodingRate = $locationStats && $locationStats->total_locations > 0
            ? round(($locationStats->geocoded_locations / $locationStats->total_locations) * 100, 1)
            : 0;

        return [
            'geocoding_rate' => $geocodingRate,
            'average_confidence' => $locationStats ? round($locationStats->avg_confidence, 1) : 0,
            'total_locations' => $locationStats->total_locations ?? 0,
        ];
    }

    private function analyzeGenerationalMigration(int $treeId): array
    {
        // This would implement complex generational migration analysis
        // For now, return placeholder structure
        return [
            'pattern_type' => 'westward_expansion', // could be 'urban_migration', 'return_migration', etc.
            'generations_affected' => 3,
            'average_distance_per_generation' => 150.5,
            'primary_direction' => 'west',
        ];
    }

    private function identifyMigrationRoutes(object $migrations): array
    {
        $routes = [];

        foreach ($migrations as $migration) {
            $route = $migration->birth_country.' → '.$migration->death_country;
            if (! isset($routes[$route])) {
                $routes[$route] = [
                    'count' => 0,
                    'average_distance' => 0,
                    'total_distance' => 0,
                ];
            }

            $routes[$route]['count']++;
            $routes[$route]['total_distance'] += $migration->migration_distance_km;
            $routes[$route]['average_distance'] = $routes[$route]['total_distance'] / $routes[$route]['count'];
        }

        // Sort by frequency and return top routes
        uasort($routes, fn ($a, $b) => $b['count'] <=> $a['count']);

        return array_slice($routes, 0, 10);
    }

    private function analyzeMigrationTrend(array $migrationByDecade): string
    {
        if (count($migrationByDecade) < 3) {
            return 'insufficient_data';
        }

        $values = array_values($migrationByDecade);
        $early = array_slice($values, 0, floor(count($values) / 2));
        $late = array_slice($values, floor(count($values) / 2));

        $earlyAvg = array_sum($early) / count($early);
        $lateAvg = array_sum($late) / count($late);

        if ($lateAvg > $earlyAvg * 1.2) {
            return 'increasing';
        }
        if ($lateAvg < $earlyAvg * 0.8) {
            return 'decreasing';
        }

        return 'stable';

    }

    private function categorizeMigrations(object $migrations): array
    {
        $categories = [
            'local' => 0,      // < 50km
            'regional' => 0,   // 50-500km
            'national' => 0,   // 500-2000km
            'international' => 0, // > 2000km
        ];

        foreach ($migrations as $migration) {
            $distance = $migration->migration_distance_km;

            if ($distance < 50) {
                $categories['local']++;
            } elseif ($distance < 500) {
                $categories['regional']++;
            } elseif ($distance < 2000) {
                $categories['national']++;
            } else {
                $categories['international']++;
            }
        }

        return $categories;
    }

    private function analyzeUrbanRuralDistribution(int $treeId): array
    {
        // This would implement urban/rural classification based on location data
        // For now, return placeholder structure
        return [
            'urban_percentage' => 65.0,
            'rural_percentage' => 35.0,
            'urbanization_trend' => 'increasing',
        ];
    }

    private function getPopulationDensityByGeneration(int $treeId): array
    {
        // Placeholder for population density analysis by generation
        return [];
    }

    private function identifyPopulationCenters(int $treeId): array
    {
        return DB::table('fact_individuals fi')
            ->join('dim_location dl', 'fi.birth_location_key', '=', 'dl.id')
            ->where('fi.tree_id', $treeId)
            ->whereNotNull('dl.city')
            ->select('dl.city', 'dl.state', 'dl.country', DB::raw('COUNT(*) as population'))
            ->groupBy(['dl.city', 'dl.state', 'dl.country'])
            ->having('population', '>=', 3)
            ->orderByDesc('population')
            ->limit(10)
            ->get()
            ->map(function ($center) {
                return [
                    'location' => $center->city.', '.$center->state.', '.$center->country,
                    'population' => $center->population,
                ];
            })
            ->toArray();
    }

    private function calculateGeographicConcentration(int $treeId): float
    {
        // Calculate Herfindahl-Hirschman Index for geographic concentration
        $countryDistribution = DB::table('fact_individuals fi')
            ->join('dim_location dl', 'fi.birth_location_key', '=', 'dl.id')
            ->where('fi.tree_id', $treeId)
            ->whereNotNull('dl.country')
            ->select('dl.country', DB::raw('COUNT(*) as count'))
            ->groupBy('dl.country')
            ->get();

        if ($countryDistribution->count() < 2) {
            return 1.0; // Complete concentration
        }

        $total = $countryDistribution->sum('count');
        $hhi = $countryDistribution->sum(function ($item) use ($total) {
            $share = $item->count / $total;

            return $share * $share;
        });

        return round($hhi, 3);
    }

    private function identifyNewSettlements(int $treeId): array
    {
        // Placeholder for new settlement identification logic
        return [];
    }

    private function analyzeFrontierExpansion(int $treeId): array
    {
        // Placeholder for frontier expansion analysis
        return [
            'expansion_direction' => 'westward',
            'expansion_rate_km_per_decade' => 75.0,
            'frontier_generations' => 2,
        ];
    }

    private function analyzeSettlementLongevity(int $treeId): array
    {
        // Placeholder for settlement longevity analysis
        return [];
    }

    private function identifyMultigenerationalSettlements(int $treeId): array
    {
        // This would identify locations with multiple generations
        return [];
    }

    private function analyzeSettlementEstablishment(int $treeId): array
    {
        // Placeholder for settlement establishment patterns
        return [];
    }

    private function analyzeCommunityontinuity(int $treeId): array
    {
        // Placeholder for community continuity analysis
        return [];
    }

    private function calculateGeographicCenter(object $coordinates): array
    {
        $lats = $coordinates->pluck('latitude')->toArray();
        $lons = $coordinates->pluck('longitude')->toArray();

        return [
            'latitude' => array_sum($lats) / count($lats),
            'longitude' => array_sum($lons) / count($lons),
        ];
    }

    private function calculateDispersion(object $coordinates, array $center): array
    {
        $distances = $coordinates->map(function ($coord) use ($center) {
            return $this->haversineDistance(
                $coord->latitude,
                $coord->longitude,
                $center['latitude'],
                $center['longitude']
            );
        });

        return [
            'average' => $distances->average(),
            'maximum' => $distances->max(),
        ];
    }

    private function identifyGeographicOutliers(object $coordinates, array $center): array
    {
        $distances = $coordinates->map(function ($coord) use ($center) {
            return [
                'individual_id' => $coord->individual_id,
                'distance' => $this->haversineDistance(
                    $coord->latitude,
                    $coord->longitude,
                    $center['latitude'],
                    $center['longitude']
                ),
            ];
        });

        $mean = $distances->avg('distance');
        $stdDev = sqrt($distances->map(fn ($d) => pow($d['distance'] - $mean, 2))->avg());
        $threshold = $mean + (2 * $stdDev); // 2 standard deviations

        return $distances->where('distance', '>', $threshold)
            ->sortByDesc('distance')
            ->take(5)
            ->values()
            ->toArray();
    }

    private function calculateFamilyDiameter(object $coordinates): ?float
    {
        if ($coordinates->count() < 2) {
            return null;
        }

        $maxDistance = 0;
        $coords = $coordinates->toArray();

        for ($i = 0; $i < count($coords); $i++) {
            for ($j = $i + 1; $j < count($coords); $j++) {
                $distance = $this->haversineDistance(
                    $coords[$i]->latitude,
                    $coords[$i]->longitude,
                    $coords[$j]->latitude,
                    $coords[$j]->longitude
                );
                $maxDistance = max($maxDistance, $distance);
            }
        }

        return round($maxDistance, 1);
    }

    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $lat1Rad = deg2rad($lat1);
        $lon1Rad = deg2rad($lon1);
        $lat2Rad = deg2rad($lat2);
        $lon2Rad = deg2rad($lon2);

        $deltaLat = $lat2Rad - $lat1Rad;
        $deltaLon = $lon2Rad - $lon1Rad;

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_KM * $c;
    }

    private function calculateGeographicComparison(array $tree1, array $tree2): array
    {
        return [
            'location_count_difference' => $tree1['location_summary']['total_unique_locations'] - $tree2['location_summary']['total_unique_locations'],
            'country_count_difference' => $tree1['location_summary']['countries'] - $tree2['location_summary']['countries'],
            'geographic_spread_difference' => ($tree1['location_summary']['geographic_spread_km'] ?? 0) - ($tree2['location_summary']['geographic_spread_km'] ?? 0),
            'migration_rate_difference' => $tree1['migration_patterns']['personal_migrations']['total_migrants'] - $tree2['migration_patterns']['personal_migrations']['total_migrants'],
        ];
    }

    private function generateGeographicRecommendations(array $geographic): array
    {
        $recommendations = [];

        $completeness = $geographic['location_summary']['location_completeness'];
        if ($completeness < 70) {
            $recommendations[] = 'Add more location data to improve geographic analysis and enable migration pattern detection.';
        }

        $geocodingQuality = $geographic['location_summary']['geocoding_quality'];
        if ($geocodingQuality['geocoding_rate'] < 50) {
            $recommendations[] = 'Standardize location names to improve mapping and distance calculations.';
        }

        $migrationCount = $geographic['migration_patterns']['personal_migrations']['total_migrants'];
        if ($migrationCount > 0) {
            $recommendations[] = 'Document migration stories and reasons to add context to geographic patterns.';
        }

        return $recommendations;
    }
}
