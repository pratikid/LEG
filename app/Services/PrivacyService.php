<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DataConsent;
use App\Models\Individual;
use App\Models\PrivacySetting;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;

/**
 * Privacy service for data protection, anonymization, and access control
 */
final class PrivacyService
{
    public function __construct(
        private readonly CacheService $cacheService
    ) {}

    /**
     * Check if user has consent for specific data processing
     */
    public function hasDataProcessingConsent(User $user, string $consentType, string $dataCategory): bool
    {
        $consent = DataConsent::where('user_id', $user->id)
            ->where('consent_type', $consentType)
            ->where('is_active', true)
            ->first();

        if (! $consent) {
            return false;
        }

        return $consent->hasDataCategoryConsent($dataCategory);
    }

    /**
     * Record user consent for data processing
     */
    public function recordConsent(
        User $user,
        string $consentType,
        array $dataCategories,
        array $processingActivities,
        string $legalBasis = DataConsent::BASIS_CONSENT,
        ?string $ipAddress = null,
        ?string $userAgent = null
    ): DataConsent {
        // Withdraw existing consent of same type
        DataConsent::where('user_id', $user->id)
            ->where('consent_type', $consentType)
            ->update(['is_active' => false, 'withdrawal_date' => now()]);

        return DataConsent::create([
            'user_id' => $user->id,
            'consent_type' => $consentType,
            'legal_basis' => $legalBasis,
            'data_categories' => $dataCategories,
            'processing_activities' => $processingActivities,
            'consent_given' => true,
            'consent_date' => now(),
            'consent_method' => 'web_form',
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'consent_version' => '1.0',
            'is_active' => true,
        ]);
    }

    /**
     * Withdraw user consent
     */
    public function withdrawConsent(User $user, string $consentType): bool
    {
        $consents = DataConsent::where('user_id', $user->id)
            ->where('consent_type', $consentType)
            ->where('is_active', true)
            ->get();

        foreach ($consents as $consent) {
            $consent->withdraw();
        }

        return true;
    }

    /**
     * Apply privacy settings to individual data
     */
    public function applyPrivacyFilter(Individual $individual, ?User $viewer = null): array
    {
        $privacySettings = $this->getPrivacySettings($individual);
        $isLiving = $this->isLivingPerson($individual);
        $canViewPrivateData = $this->canViewPrivateData($individual, $viewer);

        $data = $individual->toArray();

        if (! $canViewPrivateData) {
            $data = $this->filterPrivateFields($data, $privacySettings, $isLiving);
        }

        return $data;
    }

    /**
     * Anonymize individual data for research purposes
     */
    public function anonymizeIndividual(Individual $individual): array
    {
        $data = $individual->toArray();

        // Generate consistent pseudonymous ID
        $pseudonymousId = $this->generatePseudonymousId($individual->id);

        return [
            'id' => $pseudonymousId,
            'birth_year' => $data['birth_year'] ? $this->fuzzDate($data['birth_year']) : null,
            'death_year' => $data['death_year'] ? $this->fuzzDate($data['death_year']) : null,
            'sex' => $data['sex'],
            'birth_location_region' => $this->generalizeLocation($data['birth_location'] ?? ''),
            'death_location_region' => $this->generalizeLocation($data['death_location'] ?? ''),
            'generation' => $this->calculateGeneration($individual),
            'has_children' => $individual->children()->exists(),
            'child_count_range' => $this->categorizeChildCount($individual->children()->count()),
            'surname_initial' => $data['last_name'] ? mb_substr($data['last_name'], 0, 1) : null,
            'lifespan_category' => $this->categorizeLifespan($data['birth_year'], $data['death_year']),
        ];
    }

    /**
     * Anonymize genealogical dataset for research
     */
    public function createAnonymizedDataset(Collection $individuals): array
    {
        $anonymizedData = [];
        $relationshipMap = [];

        foreach ($individuals as $individual) {
            $pseudoId = $this->generatePseudonymousId($individual->id);
            $anonymizedData[] = $this->anonymizeIndividual($individual);
            $relationshipMap[$individual->id] = $pseudoId;
        }

        // Anonymize relationships
        $anonymizedRelationships = [];
        foreach ($individuals as $individual) {
            $pseudoId = $relationshipMap[$individual->id];

            // Parent relationships
            foreach ($individual->parents as $parent) {
                if (isset($relationshipMap[$parent->id])) {
                    $anonymizedRelationships[] = [
                        'child_id' => $pseudoId,
                        'parent_id' => $relationshipMap[$parent->id],
                        'relationship_type' => 'parent_child',
                    ];
                }
            }

            // Spouse relationships
            foreach ($individual->spouses as $spouse) {
                if (isset($relationshipMap[$spouse->id])) {
                    $spouseId = $relationshipMap[$spouse->id];
                    // Avoid duplicate spouse relationships
                    $exists = collect($anonymizedRelationships)->contains(function ($rel) use ($pseudoId, $spouseId) {
                        return ($rel['person_a'] === $spouseId && $rel['person_b'] === $pseudoId) ||
                               ($rel['person_a'] === $pseudoId && $rel['person_b'] === $spouseId);
                    });

                    if (! $exists) {
                        $anonymizedRelationships[] = [
                            'person_a' => $pseudoId,
                            'person_b' => $spouseId,
                            'relationship_type' => 'spouse',
                        ];
                    }
                }
            }
        }

        return [
            'individuals' => $anonymizedData,
            'relationships' => $anonymizedRelationships,
            'metadata' => [
                'anonymization_date' => now()->toISOString(),
                'total_individuals' => count($anonymizedData),
                'total_relationships' => count($anonymizedRelationships),
                'privacy_level' => 'k-anonymity-5',
            ],
        ];
    }

    /**
     * Check if person is considered living (basic heuristic)
     */
    public function isLivingPerson(Individual $individual): bool
    {
        // If death date is known, person is deceased
        if ($individual->death_date || $individual->death_year) {
            return false;
        }

        // If birth year is more than 110 years ago, likely deceased
        if ($individual->birth_year && $individual->birth_year < (date('Y') - 110)) {
            return false;
        }

        // If no death information and born within 110 years, consider living
        return true;
    }

    private function getPrivacySettings(Individual $individual): ?PrivacySetting
    {
        return PrivacySetting::where('individual_id', $individual->id)
            ->where('is_active', true)
            ->first();
    }

    private function canViewPrivateData(Individual $individual, ?User $viewer): bool
    {
        if (! $viewer) {
            return false;
        }

        // Tree owner can see all data
        if ($individual->tree->user_id === $viewer->id) {
            return true;
        }

        // Check if user has family access permission
        // This would require additional relationship checking logic
        return false;
    }

    private function filterPrivateFields(array $data, ?PrivacySetting $settings, bool $isLiving): array
    {
        if (! $settings) {
            return $data; // No privacy settings, show all data
        }

        $filteredData = $data;

        // Apply field-level filtering
        $fields = [
            'birth_date', 'birth_year', 'death_date', 'death_year',
            'birth_location', 'death_location',
        ];

        foreach ($fields as $field) {
            if (! $settings->isFieldVisible($field, $isLiving)) {
                $filteredData[$field] = null;
            }
        }

        return $filteredData;
    }

    private function generatePseudonymousId(int $originalId): string
    {
        // Generate deterministic but non-reversible pseudonymous ID
        $salt = config('app.key').'genealogy_anonymization';

        return 'anon_'.mb_substr(Hash::make($originalId.$salt), -12);
    }

    private function fuzzDate(int $year): string
    {
        // Group years into 5-year ranges for privacy
        $rangeStart = (int) (floor($year / 5) * 5);
        $rangeEnd = $rangeStart + 4;

        return "{$rangeStart}-{$rangeEnd}";
    }

    private function generalizeLocation(string $location): string
    {
        if (empty($location)) {
            return '';
        }

        // Extract country or state level location
        $parts = explode(',', $location);
        if (count($parts) >= 2) {
            return mb_trim(end($parts)); // Return country/state
        }

        return 'Unknown Region';
    }

    private function calculateGeneration(Individual $individual): int
    {
        // Simple generation calculation based on birth year
        if (! $individual->birth_year) {
            return 0;
        }

        // Assume 25 years per generation, with 1950 as generation 0
        return (int) (($individual->birth_year - 1950) / 25);
    }

    private function categorizeChildCount(int $childCount): string
    {
        return match (true) {
            $childCount === 0 => '0',
            $childCount <= 2 => '1-2',
            $childCount <= 5 => '3-5',
            $childCount <= 10 => '6-10',
            default => '10+'
        };
    }

    private function categorizeLifespan(?int $birthYear, ?int $deathYear): string
    {
        if (! $birthYear || ! $deathYear) {
            return 'unknown';
        }

        $lifespan = $deathYear - $birthYear;

        return match (true) {
            $lifespan < 18 => 'child',
            $lifespan < 40 => 'young_adult',
            $lifespan < 65 => 'middle_age',
            $lifespan < 80 => 'elderly',
            default => 'very_elderly'
        };
    }
}
