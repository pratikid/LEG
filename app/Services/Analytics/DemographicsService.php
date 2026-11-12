<?php

declare(strict_types=1);

namespace App\Services\Analytics;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * Demographics Analytics Service
 * Provides demographic insights and population analysis for family trees
 */
final class DemographicsService
{
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_PREFIX = 'demographics:';

    /**
     * Get demographic overview for a tree
     */
    public function getTreeDemographics(int $treeId): array
    {
        $cacheKey = self::CACHE_PREFIX . "tree:{$treeId}";
        
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($treeId) {
            return [
                'population_summary' => $this->getPopulationSummary($treeId),
                'age_demographics' => $this->getAgeDemographics($treeId),
                'gender_distribution' => $this->getGenderDistribution($treeId),
                'lifespan_analysis' => $this->getLifespanAnalysis($treeId),
                'birth_trends' => $this->getBirthTrends($treeId),
                'generation_statistics' => $this->getGenerationStatistics($treeId)
            ];
        });
    }

    /**
     * Get population summary statistics
     */
    public function getPopulationSummary(int $treeId): array
    {
        $metrics = DB::table('fact_tree_metrics')
            ->where('tree_id', $treeId)
            ->orderBy('snapshot_date', 'desc')
            ->first();

        if (!$metrics) {
            return $this->getEmptyPopulationSummary();
        }

        $totalIndividuals = $metrics->individual_count;
        $livingEstimate = $this->estimateLivingIndividuals($treeId);

        return [
            'total_individuals' => $totalIndividuals,
            'estimated_living' => $livingEstimate,
            'estimated_deceased' => $totalIndividuals - $livingEstimate,
            'total_families' => $metrics->family_count,
            'total_generations' => $metrics->generation_count,
            'data_completeness' => [
                'birth_dates' => round($metrics->birth_date_completeness, 1),
                'death_dates' => round($metrics->death_date_completeness, 1),
                'locations' => round($metrics->location_completeness, 1)
            ],
            'last_updated' => $metrics->updated_at
        ];
    }

    /**
     * Get age demographics and distribution
     */
    public function getAgeDemographics(int $treeId): array
    {
        // Current age distribution (estimated for living individuals)
        $currentAges = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('birth_year')
            ->whereNull('death_year') // Likely living
            ->selectRaw('
                CASE 
                    WHEN ? - birth_year < 18 THEN "0-17"
                    WHEN ? - birth_year < 25 THEN "18-24"
                    WHEN ? - birth_year < 35 THEN "25-34" 
                    WHEN ? - birth_year < 45 THEN "35-44"
                    WHEN ? - birth_year < 55 THEN "45-54"
                    WHEN ? - birth_year < 65 THEN "55-64"
                    WHEN ? - birth_year < 75 THEN "65-74"
                    ELSE "75+"
                END as age_group,
                COUNT(*) as count
            ', array_fill(0, 7, now()->year))
            ->groupBy('age_group')
            ->get()
            ->keyBy('age_group')
            ->map(fn($item) => $item->count)
            ->toArray();

        // Age at death distribution  
        $deathAges = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('lifespan_days')
            ->selectRaw('
                CASE 
                    WHEN lifespan_days / 365.25 < 1 THEN "Infant (<1)"
                    WHEN lifespan_days / 365.25 < 18 THEN "Child (1-17)"
                    WHEN lifespan_days / 365.25 < 35 THEN "Young Adult (18-34)"
                    WHEN lifespan_days / 365.25 < 55 THEN "Middle Age (35-54)"
                    WHEN lifespan_days / 365.25 < 75 THEN "Senior (55-74)"
                    ELSE "Elderly (75+)"
                END as age_group,
                COUNT(*) as count
            ')
            ->groupBy('age_group')
            ->get()
            ->keyBy('age_group')
            ->map(fn($item) => $item->count)
            ->toArray();

        return [
            'current_age_distribution' => $currentAges,
            'death_age_distribution' => $deathAges,
            'median_age_at_death' => $this->calculateMedianLifespan($treeId),
            'life_expectancy_trends' => $this->getLifeExpectancyTrends($treeId)
        ];
    }

    /**
     * Get gender distribution analysis
     */
    public function getGenderDistribution(int $treeId): array
    {
        $distribution = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->select('sex', DB::raw('COUNT(*) as count'))
            ->groupBy('sex')
            ->get()
            ->keyBy('sex')
            ->map(fn($item) => $item->count)
            ->toArray();

        $total = array_sum($distribution);
        $percentages = [];
        
        if ($total > 0) {
            foreach ($distribution as $sex => $count) {
                $percentages[$sex] = round(($count / $total) * 100, 1);
            }
        }

        // Gender distribution by generation
        $byGeneration = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('generation')
            ->select('generation', 'sex', DB::raw('COUNT(*) as count'))
            ->groupBy(['generation', 'sex'])
            ->orderBy('generation')
            ->get()
            ->groupBy('generation')
            ->map(function ($group) {
                return $group->keyBy('sex')->map(fn($item) => $item->count)->toArray();
            })
            ->toArray();

        return [
            'total_distribution' => [
                'counts' => $distribution,
                'percentages' => $percentages
            ],
            'by_generation' => $byGeneration,
            'gender_ratio' => $this->calculateGenderRatio($distribution),
            'birth_sex_ratio' => $this->getBirthSexRatio($treeId)
        ];
    }

    /**
     * Get detailed lifespan analysis
     */
    public function getLifespanAnalysis(int $treeId): array
    {
        $lifespans = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('lifespan_days')
            ->selectRaw('
                AVG(lifespan_days / 365.25) as avg_lifespan,
                MIN(lifespan_days / 365.25) as min_lifespan,
                MAX(lifespan_days / 365.25) as max_lifespan,
                STDDEV(lifespan_days / 365.25) as lifespan_stddev,
                COUNT(*) as total_with_lifespan
            ')
            ->first();

        if (!$lifespans || $lifespans->total_with_lifespan === 0) {
            return $this->getEmptyLifespanAnalysis();
        }

        // Lifespan by gender
        $lifespanByGender = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('lifespan_days')
            ->whereIn('sex', ['M', 'F'])
            ->select('sex', DB::raw('AVG(lifespan_days / 365.25) as avg_lifespan'))
            ->groupBy('sex')
            ->get()
            ->keyBy('sex')
            ->map(fn($item) => round($item->avg_lifespan, 1))
            ->toArray();

        // Lifespan by birth century
        $lifespanByCentury = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('lifespan_days')
            ->whereNotNull('birth_century')
            ->select('birth_century', DB::raw('AVG(lifespan_days / 365.25) as avg_lifespan'))
            ->groupBy('birth_century')
            ->orderBy('birth_century')
            ->get()
            ->keyBy('birth_century')
            ->map(fn($item) => round($item->avg_lifespan, 1))
            ->toArray();

        return [
            'overall' => [
                'average_years' => round($lifespans->avg_lifespan, 1),
                'minimum_years' => round($lifespans->min_lifespan, 1),
                'maximum_years' => round($lifespans->max_lifespan, 1),
                'standard_deviation' => round($lifespans->lifespan_stddev ?? 0, 1),
                'sample_size' => $lifespans->total_with_lifespan
            ],
            'by_gender' => $lifespanByGender,
            'by_century' => $lifespanByCentury,
            'percentiles' => $this->calculateLifespanPercentiles($treeId)
        ];
    }

    /**
     * Get birth trends over time
     */
    public function getBirthTrends(int $treeId): array
    {
        // Births by decade
        $birthsByDecade = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('birth_decade')
            ->select('birth_decade', DB::raw('COUNT(*) as births'))
            ->groupBy('birth_decade')
            ->orderBy('birth_decade')
            ->get()
            ->keyBy('birth_decade')
            ->map(fn($item) => $item->births)
            ->toArray();

        // Births by season (for individuals with full birth dates)
        $birthsBySeason = DB::table('fact_individuals fi')
            ->join('dim_time dt', 'fi.birth_date_key', '=', 'dt.id')
            ->where('fi.tree_id', $treeId)
            ->selectRaw('
                CASE 
                    WHEN dt.month IN (12, 1, 2) THEN "Winter"
                    WHEN dt.month IN (3, 4, 5) THEN "Spring"
                    WHEN dt.month IN (6, 7, 8) THEN "Summer"
                    ELSE "Fall"
                END as season,
                COUNT(*) as births
            ')
            ->groupBy('season')
            ->get()
            ->keyBy('season')
            ->map(fn($item) => $item->births)
            ->toArray();

        // Population growth rate
        $growthRate = $this->calculatePopulationGrowthRate($treeId);

        return [
            'by_decade' => $birthsByDecade,
            'by_season' => $birthsBySeason,
            'peak_birth_decade' => $this->findPeakBirthDecade($birthsByDecade),
            'population_growth_rate' => $growthRate,
            'birth_rate_trends' => $this->analyzeBirthRateTrends($birthsByDecade)
        ];
    }

    /**
     * Get generation-based statistics
     */
    public function getGenerationStatistics(int $treeId): array
    {
        $stats = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('generation')
            ->selectRaw('
                generation,
                COUNT(*) as individuals,
                AVG(CASE WHEN lifespan_days IS NOT NULL THEN lifespan_days / 365.25 END) as avg_lifespan,
                SUM(CASE WHEN sex = "M" THEN 1 ELSE 0 END) as males,
                SUM(CASE WHEN sex = "F" THEN 1 ELSE 0 END) as females
            ')
            ->groupBy('generation')
            ->orderBy('generation')
            ->get()
            ->map(function ($stat) {
                return [
                    'generation' => $stat->generation,
                    'individual_count' => $stat->individuals,
                    'average_lifespan' => $stat->avg_lifespan ? round($stat->avg_lifespan, 1) : null,
                    'gender_distribution' => [
                        'male' => $stat->males,
                        'female' => $stat->females,
                        'ratio' => $stat->females > 0 ? round($stat->males / $stat->females, 2) : null
                    ]
                ];
            })
            ->keyBy('generation')
            ->toArray();

        return [
            'by_generation' => $stats,
            'generation_span' => $this->calculateGenerationSpan($treeId),
            'average_generation_size' => $this->calculateAverageGenerationSize($stats)
        ];
    }

    /**
     * Compare demographics between two trees
     */
    public function compareTrees(int $treeId1, int $treeId2): array
    {
        $tree1Demographics = $this->getTreeDemographics($treeId1);
        $tree2Demographics = $this->getTreeDemographics($treeId2);

        return [
            'tree_1' => $tree1Demographics,
            'tree_2' => $tree2Demographics,
            'comparison' => $this->calculateDemographicComparison($tree1Demographics, $tree2Demographics)
        ];
    }

    /**
     * Get demographic insights and recommendations
     */
    public function getDemographicInsights(int $treeId): array
    {
        $demographics = $this->getTreeDemographics($treeId);
        $insights = [];

        // Population size insights
        $totalPop = $demographics['population_summary']['total_individuals'];
        if ($totalPop < 50) {
            $insights[] = [
                'type' => 'small_tree',
                'message' => 'This is a relatively small family tree. Consider expanding research to include more distant relatives.',
                'priority' => 'low'
            ];
        } elseif ($totalPop > 1000) {
            $insights[] = [
                'type' => 'large_tree',
                'message' => 'This is a comprehensive family tree with rich genealogical data.',
                'priority' => 'info'
            ];
        }

        // Data completeness insights
        $completeness = $demographics['population_summary']['data_completeness'];
        if ($completeness['birth_dates'] < 70) {
            $insights[] = [
                'type' => 'missing_birth_dates',
                'message' => 'Many individuals are missing birth dates. This limits demographic analysis accuracy.',
                'priority' => 'medium'
            ];
        }

        // Gender ratio insights
        $genderDist = $demographics['gender_distribution']['total_distribution']['percentages'];
        $malePercent = $genderDist['M'] ?? 0;
        $femalePercent = $genderDist['F'] ?? 0;
        
        if (abs($malePercent - $femalePercent) > 10) {
            $insights[] = [
                'type' => 'gender_imbalance',
                'message' => 'Significant gender imbalance detected. This may indicate research bias or naming traditions.',
                'priority' => 'medium'
            ];
        }

        return [
            'insights' => $insights,
            'recommendations' => $this->generateRecommendations($demographics)
        ];
    }

    // Helper methods

    private function getEmptyPopulationSummary(): array
    {
        return [
            'total_individuals' => 0,
            'estimated_living' => 0,
            'estimated_deceased' => 0,
            'total_families' => 0,
            'total_generations' => 0,
            'data_completeness' => [
                'birth_dates' => 0,
                'death_dates' => 0,
                'locations' => 0
            ],
            'last_updated' => null
        ];
    }

    private function getEmptyLifespanAnalysis(): array
    {
        return [
            'overall' => [
                'average_years' => null,
                'minimum_years' => null,
                'maximum_years' => null,
                'standard_deviation' => null,
                'sample_size' => 0
            ],
            'by_gender' => [],
            'by_century' => [],
            'percentiles' => []
        ];
    }

    private function estimateLivingIndividuals(int $treeId): int
    {
        // Estimate living individuals based on birth year and no death date
        return DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('birth_year')
            ->whereNull('death_year')
            ->where('birth_year', '>', now()->year - 120) // Reasonable max age
            ->count();
    }

    private function calculateMedianLifespan(int $treeId): ?float
    {
        $lifespans = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('lifespan_days')
            ->orderBy(DB::raw('lifespan_days / 365.25'))
            ->pluck(DB::raw('lifespan_days / 365.25'))
            ->toArray();

        if (empty($lifespans)) {
            return null;
        }

        $count = count($lifespans);
        $middle = floor($count / 2);

        if ($count % 2 == 0) {
            return round(($lifespans[$middle - 1] + $lifespans[$middle]) / 2, 1);
        } else {
            return round($lifespans[$middle], 1);
        }
    }

    private function getLifeExpectancyTrends(int $treeId): array
    {
        return DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('lifespan_days')
            ->whereNotNull('birth_decade')
            ->selectRaw('
                birth_decade,
                AVG(lifespan_days / 365.25) as avg_lifespan,
                COUNT(*) as sample_size
            ')
            ->groupBy('birth_decade')
            ->having('sample_size', '>=', 3) // Minimum sample size for trend
            ->orderBy('birth_decade')
            ->get()
            ->map(function ($item) {
                return [
                    'decade' => $item->birth_decade,
                    'life_expectancy' => round($item->avg_lifespan, 1),
                    'sample_size' => $item->sample_size
                ];
            })
            ->toArray();
    }

    private function calculateGenderRatio(array $distribution): ?float
    {
        $males = $distribution['M'] ?? 0;
        $females = $distribution['F'] ?? 0;

        if ($females === 0) {
            return null;
        }

        return round($males / $females, 3);
    }

    private function getBirthSexRatio(int $treeId): ?float
    {
        // Calculate sex ratio at birth (typically around 1.05 males per female)
        $births = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('birth_year')
            ->whereIn('sex', ['M', 'F'])
            ->select('sex', DB::raw('COUNT(*) as count'))
            ->groupBy('sex')
            ->get()
            ->keyBy('sex')
            ->map(fn($item) => $item->count)
            ->toArray();

        if (empty($births['F']) || $births['F'] === 0) {
            return null;
        }

        return round(($births['M'] ?? 0) / $births['F'], 3);
    }

    private function calculateLifespanPercentiles(int $treeId): array
    {
        $lifespans = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('lifespan_days')
            ->orderBy(DB::raw('lifespan_days / 365.25'))
            ->pluck(DB::raw('lifespan_days / 365.25'))
            ->toArray();

        if (empty($lifespans)) {
            return [];
        }

        $count = count($lifespans);
        $percentiles = [];

        foreach ([25, 50, 75, 90, 95] as $percentile) {
            $index = ceil($percentile / 100 * $count) - 1;
            $percentiles["p{$percentile}"] = round($lifespans[$index], 1);
        }

        return $percentiles;
    }

    private function findPeakBirthDecade(array $birthsByDecade): ?array
    {
        if (empty($birthsByDecade)) {
            return null;
        }

        $peakDecade = array_keys($birthsByDecade, max($birthsByDecade))[0];
        $peakCount = $birthsByDecade[$peakDecade];

        return [
            'decade' => $peakDecade,
            'birth_count' => $peakCount
        ];
    }

    private function calculatePopulationGrowthRate(int $treeId): array
    {
        $decades = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('birth_decade')
            ->select('birth_decade', DB::raw('COUNT(*) as births'))
            ->groupBy('birth_decade')
            ->orderBy('birth_decade')
            ->get()
            ->toArray();

        if (count($decades) < 2) {
            return ['rate' => null, 'trend' => 'insufficient_data'];
        }

        // Calculate average growth rate between consecutive decades
        $growthRates = [];
        for ($i = 1; $i < count($decades); $i++) {
            $previous = $decades[$i - 1];
            $current = $decades[$i];
            
            if ($previous->births > 0) {
                $growthRate = (($current->births - $previous->births) / $previous->births) * 100;
                $growthRates[] = $growthRate;
            }
        }

        if (empty($growthRates)) {
            return ['rate' => null, 'trend' => 'insufficient_data'];
        }

        $avgGrowthRate = array_sum($growthRates) / count($growthRates);
        
        return [
            'rate' => round($avgGrowthRate, 1),
            'trend' => $avgGrowthRate > 5 ? 'expanding' : ($avgGrowthRate < -5 ? 'declining' : 'stable')
        ];
    }

    private function analyzeBirthRateTrends(array $birthsByDecade): array
    {
        if (count($birthsByDecade) < 3) {
            return ['trend' => 'insufficient_data', 'periods' => []];
        }

        $decades = array_keys($birthsByDecade);
        $births = array_values($birthsByDecade);
        
        // Simple trend analysis: compare first third to last third
        $firstThird = array_slice($births, 0, floor(count($births) / 3));
        $lastThird = array_slice($births, -floor(count($births) / 3));
        
        $firstAvg = array_sum($firstThird) / count($firstThird);
        $lastAvg = array_sum($lastThird) / count($lastThird);
        
        $trendDirection = $lastAvg > $firstAvg * 1.1 ? 'increasing' : 
                         ($lastAvg < $firstAvg * 0.9 ? 'decreasing' : 'stable');

        return [
            'trend' => $trendDirection,
            'early_average' => round($firstAvg, 1),
            'recent_average' => round($lastAvg, 1),
            'change_percent' => round((($lastAvg - $firstAvg) / $firstAvg) * 100, 1)
        ];
    }

    private function calculateGenerationSpan(int $treeId): array
    {
        $generations = DB::table('fact_individuals')
            ->where('tree_id', $treeId)
            ->whereNotNull('generation')
            ->selectRaw('MIN(generation) as earliest, MAX(generation) as latest')
            ->first();

        if (!$generations) {
            return ['span' => 0, 'earliest' => null, 'latest' => null];
        }

        return [
            'span' => $generations->latest - $generations->earliest + 1,
            'earliest' => $generations->earliest,
            'latest' => $generations->latest
        ];
    }

    private function calculateAverageGenerationSize(array $stats): ?float
    {
        if (empty($stats)) {
            return null;
        }

        $totalIndividuals = array_sum(array_column($stats, 'individual_count'));
        $generationCount = count($stats);

        return round($totalIndividuals / $generationCount, 1);
    }

    private function calculateDemographicComparison(array $tree1, array $tree2): array
    {
        return [
            'population_difference' => $tree1['population_summary']['total_individuals'] - $tree2['population_summary']['total_individuals'],
            'lifespan_difference' => ($tree1['lifespan_analysis']['overall']['average_years'] ?? 0) - ($tree2['lifespan_analysis']['overall']['average_years'] ?? 0),
            'data_quality_comparison' => [
                'birth_dates' => $tree1['population_summary']['data_completeness']['birth_dates'] - $tree2['population_summary']['data_completeness']['birth_dates'],
                'death_dates' => $tree1['population_summary']['data_completeness']['death_dates'] - $tree2['population_summary']['data_completeness']['death_dates']
            ]
        ];
    }

    private function generateRecommendations(array $demographics): array
    {
        $recommendations = [];
        
        $completeness = $demographics['population_summary']['data_completeness'];
        
        if ($completeness['birth_dates'] < 80) {
            $recommendations[] = 'Focus on collecting birth dates to improve demographic analysis accuracy.';
        }
        
        if ($completeness['locations'] < 60) {
            $recommendations[] = 'Add location data to enable geographic and migration analysis.';
        }

        $totalPop = $demographics['population_summary']['total_individuals'];
        if ($totalPop < 100) {
            $recommendations[] = 'Consider expanding research to collateral lines to build a more comprehensive tree.';
        }

        return $recommendations;
    }
}
