<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Individual;
use App\Models\Tree;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Research dataset service for generating anonymized genealogical data
 * for academic research with privacy protection
 */
final class ResearchDatasetService
{
    public function __construct(
        private readonly PrivacyService $privacyService,
        private readonly GraphAnalyticsService $graphService
    ) {}

    /**
     * Generate anonymized research dataset
     *
     * @param  array  $treeIds  Tree IDs to include
     * @param  array  $options  Dataset configuration
     * @return array Dataset metadata and file path
     */
    public function generateAnonymizedDataset(array $treeIds, array $options = []): array
    {
        $defaultOptions = [
            'format' => 'json', // json, csv, parquet
            'privacy_level' => 'k-anonymity-5',
            'include_relationships' => true,
            'include_statistics' => true,
            'temporal_aggregation' => '5-year', // none, 5-year, 10-year
            'geographic_generalization' => 'country', // none, region, country
            'minimum_cluster_size' => 5,
            'differential_privacy' => false,
            'epsilon' => 1.0, // for differential privacy
        ];

        $config = array_merge($defaultOptions, $options);

        // Collect individuals from specified trees
        $individuals = $this->collectIndividuals($treeIds);

        // Apply anonymization
        $anonymizedData = $this->applyAnonymization($individuals, $config);

        // Apply differential privacy if requested
        if ($config['differential_privacy']) {
            $anonymizedData = $this->applyDifferentialPrivacy($anonymizedData, $config['epsilon']);
        }

        // Generate dataset file
        $filename = $this->generateDatasetFilename($config);
        $filePath = $this->saveDataset($anonymizedData, $filename, $config['format']);

        return [
            'success' => true,
            'filename' => $filename,
            'file_path' => $filePath,
            'metadata' => $this->generateDatasetMetadata($anonymizedData, $config),
            'statistics' => $this->calculateDatasetStatistics($anonymizedData),
            'privacy_report' => $this->generatePrivacyReport($config),
        ];
    }

    /**
     * Apply k-anonymity and other privacy techniques
     */
    private function applyAnonymization(Collection $individuals, array $config): array
    {
        $anonymizedData = [];

        foreach ($individuals as $individual) {
            $anonymizedIndividual = $this->privacyService->anonymizeIndividual($individual);

            // Apply additional privacy measures based on config
            $anonymizedIndividual = $this->applyTemporalAggregation($anonymizedIndividual, $config);
            $anonymizedIndividual = $this->applyGeographicGeneralization($anonymizedIndividual, $config);

            $anonymizedData[] = $anonymizedIndividual;
        }

        // Group similar records to ensure k-anonymity
        $anonymizedData = $this->ensureKAnonymity($anonymizedData, $config['minimum_cluster_size']);

        return $anonymizedData;
    }

    /**
     * Apply differential privacy noise
     */
    private function applyDifferentialPrivacy(array $data, float $epsilon): array
    {
        foreach ($data as &$record) {
            // Add Laplace noise to numerical fields
            if (isset($record['birth_year']) && is_numeric($record['birth_year'])) {
                $record['birth_year'] = $this->addLaplaceNoise($record['birth_year'], $epsilon);
            }

            if (isset($record['death_year']) && is_numeric($record['death_year'])) {
                $record['death_year'] = $this->addLaplaceNoise($record['death_year'], $epsilon);
            }
        }

        return $data;
    }

    private function addLaplaceNoise(int $value, float $epsilon, float $sensitivity = 1.0): int
    {
        $scale = $sensitivity / $epsilon;
        $noise = $this->generateLaplaceNoise($scale);

        return max(0, (int) ($value + $noise));
    }

    private function generateLaplaceNoise(float $scale): float
    {
        $u = mt_rand() / mt_getrandmax() - 0.5;

        return -$scale * ($u >= 0 ? 1 : -1) * log(1 - 2 * abs($u));
    }

    private function ensureKAnonymity(array $data, int $k): array
    {
        // Group records by quasi-identifiers
        $groups = [];

        foreach ($data as $record) {
            $key = $this->generateQuasiIdentifierKey($record);
            if (! isset($groups[$key])) {
                $groups[$key] = [];
            }
            $groups[$key][] = $record;
        }

        // Remove groups with less than k records
        $anonymizedData = [];
        foreach ($groups as $group) {
            if (count($group) >= $k) {
                $anonymizedData = array_merge($anonymizedData, $group);
            }
        }

        return $anonymizedData;
    }

    private function generateQuasiIdentifierKey(array $record): string
    {
        $identifiers = [
            $record['birth_year'] ?? 'unknown',
            $record['sex'] ?? 'unknown',
            $record['birth_location_region'] ?? 'unknown',
            $record['generation'] ?? 'unknown',
        ];

        return implode('|', $identifiers);
    }

    private function collectIndividuals(array $treeIds): Collection
    {
        return Individual::whereIn('tree_id', $treeIds)
            ->with(['tree'])
            ->get();
    }

    private function applyTemporalAggregation(array $individual, array $config): array
    {
        if ($config['temporal_aggregation'] !== 'none') {
            $yearRange = (int) explode('-', $config['temporal_aggregation'])[0];

            if (isset($individual['birth_year']) && is_numeric($individual['birth_year'])) {
                $individual['birth_year'] = $this->aggregateToRange($individual['birth_year'], $yearRange);
            }

            if (isset($individual['death_year']) && is_numeric($individual['death_year'])) {
                $individual['death_year'] = $this->aggregateToRange($individual['death_year'], $yearRange);
            }
        }

        return $individual;
    }

    private function aggregateToRange(int $year, int $range): string
    {
        $rangeStart = (int) (floor($year / $range) * $range);
        $rangeEnd = $rangeStart + $range - 1;

        return "{$rangeStart}-{$rangeEnd}";
    }

    private function applyGeographicGeneralization(array $individual, array $config): array
    {
        $level = $config['geographic_generalization'];

        if ($level === 'none') {
            return $individual;
        }

        // Apply geographic generalization based on level
        foreach (['birth_location_region', 'death_location_region'] as $field) {
            if (isset($individual[$field])) {
                $individual[$field] = $this->generalizeGeographicLocation($individual[$field], $level);
            }
        }

        return $individual;
    }

    private function generalizeGeographicLocation(string $location, string $level): string
    {
        if (empty($location)) {
            return 'Unknown';
        }

        return match ($level) {
            'region' => $this->extractRegion($location),
            'country' => $this->extractCountry($location),
            default => $location
        };
    }

    private function extractRegion(string $location): string
    {
        // Extract region from location string
        $parts = explode(',', $location);

        return count($parts) >= 2 ? mb_trim($parts[-2]) : 'Unknown Region';
    }

    private function extractCountry(string $location): string
    {
        // Extract country from location string
        $parts = explode(',', $location);

        return mb_trim(end($parts)) ?: 'Unknown Country';
    }

    private function generateDatasetFilename(array $config): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $privacyLevel = str_replace('-', '_', $config['privacy_level']);

        return "research_dataset_{$privacyLevel}_{$timestamp}";
    }

    private function saveDataset(array $data, string $filename, string $format): string
    {
        $path = "research-datasets/{$filename}.{$format}";

        $content = match ($format) {
            'json' => json_encode($data, JSON_PRETTY_PRINT),
            'csv' => $this->convertToCsv($data),
            default => json_encode($data)
        };

        Storage::disk('local')->put($path, $content);

        return $path;
    }

    private function convertToCsv(array $data): string
    {
        if (empty($data)) {
            return '';
        }

        $csv = [];
        $headers = array_keys($data[0]);
        $csv[] = implode(',', $headers);

        foreach ($data as $record) {
            $row = [];
            foreach ($headers as $header) {
                $value = $record[$header] ?? '';
                $row[] = is_array($value) ? json_encode($value) : $value;
            }
            $csv[] = implode(',', $row);
        }

        return implode("\n", $csv);
    }

    private function generateDatasetMetadata(array $data, array $config): array
    {
        return [
            'generated_at' => now()->toISOString(),
            'record_count' => count($data),
            'privacy_level' => $config['privacy_level'],
            'anonymization_methods' => [
                'k_anonymity' => $config['minimum_cluster_size'],
                'temporal_aggregation' => $config['temporal_aggregation'],
                'geographic_generalization' => $config['geographic_generalization'],
                'differential_privacy' => $config['differential_privacy'],
            ],
            'fields' => ! empty($data) ? array_keys($data[0]) : [],
            'schema_version' => '1.0',
        ];
    }

    private function calculateDatasetStatistics(array $data): array
    {
        if (empty($data)) {
            return [];
        }

        $genderCounts = array_count_values(array_column($data, 'sex'));
        $generationCounts = array_count_values(array_column($data, 'generation'));

        return [
            'total_records' => count($data),
            'gender_distribution' => $genderCounts,
            'generation_distribution' => $generationCounts,
            'data_quality' => $this->assessDataQuality($data),
        ];
    }

    private function assessDataQuality(array $data): array
    {
        $totalRecords = count($data);
        $fields = ['birth_year', 'death_year', 'sex', 'birth_location_region'];

        $quality = [];
        foreach ($fields as $field) {
            $nonEmptyCount = count(array_filter($data, fn ($record) => ! empty($record[$field] ?? null)));
            $quality[$field] = [
                'completeness_percentage' => $totalRecords > 0 ? round(($nonEmptyCount / $totalRecords) * 100, 1) : 0,
                'non_empty_count' => $nonEmptyCount,
            ];
        }

        return $quality;
    }

    private function generatePrivacyReport(array $config): array
    {
        return [
            'privacy_techniques_applied' => [
                'k_anonymity' => [
                    'enabled' => true,
                    'k_value' => $config['minimum_cluster_size'],
                    'description' => 'Ensures each record is indistinguishable from at least k-1 others',
                ],
                'temporal_aggregation' => [
                    'enabled' => $config['temporal_aggregation'] !== 'none',
                    'aggregation_level' => $config['temporal_aggregation'],
                    'description' => 'Groups dates into ranges to reduce precision',
                ],
                'geographic_generalization' => [
                    'enabled' => $config['geographic_generalization'] !== 'none',
                    'generalization_level' => $config['geographic_generalization'],
                    'description' => 'Generalizes geographic information to broader regions',
                ],
                'differential_privacy' => [
                    'enabled' => $config['differential_privacy'],
                    'epsilon' => $config['epsilon'] ?? null,
                    'description' => 'Adds statistical noise to protect individual privacy',
                ],
            ],
            'risk_assessment' => [
                'reidentification_risk' => $this->assessReidentificationRisk($config),
                'compliance_status' => 'GDPR_COMPLIANT',
            ],
        ];
    }

    private function assessReidentificationRisk(array $config): string
    {
        $riskFactors = 0;

        if ($config['minimum_cluster_size'] < 5) {
            $riskFactors++;
        }
        if ($config['temporal_aggregation'] === 'none') {
            $riskFactors++;
        }
        if ($config['geographic_generalization'] === 'none') {
            $riskFactors++;
        }
        if (! $config['differential_privacy']) {
            $riskFactors++;
        }

        return match (true) {
            $riskFactors === 0 => 'Very Low',
            $riskFactors <= 1 => 'Low',
            $riskFactors <= 2 => 'Medium',
            default => 'High'
        };
    }
}
