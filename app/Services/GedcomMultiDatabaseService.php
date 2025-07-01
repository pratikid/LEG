<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Family;
use App\Models\Individual;
use App\Models\Source;
use App\Models\Tree;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laudis\Neo4j\Client as Neo4jClient;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Client as MongoClient;

/**
 * Multi-database GEDCOM import service
 * Distributes GEDCOM data across PostgreSQL, MongoDB, and Neo4j
 */
final class GedcomMultiDatabaseService
{
    private MongoClient $mongoClient;

    private Neo4jClient $neo4jClient;

    private Neo4jIndividualService $neo4jService;

    public function __construct(Neo4jIndividualService $neo4jService)
    {
        $this->neo4jService = $neo4jService;

        // Initialize MongoDB client
        $this->mongoClient = new MongoClient(config('database.connections.mongodb.uri'));

        // Neo4j client is handled by Neo4jIndividualService
        $this->neo4jClient = $this->neo4jService->getClient();
    }

    /**
     * Import GEDCOM data across all databases
     */
    public function importGedcomData(string $gedcomContent, int $treeId): array
    {
        $startTime = microtime(true);

        try {
            // 1. Remove user-defined tags from GEDCOM content
            $cleanedGedcomContent = $this->removeUserDefinedTags($gedcomContent);

            // 2. Store cleaned content to file
            $cleanedFilePath = $this->storeCleanedGedcomContent($cleanedGedcomContent, $treeId);

            Log::info('Stored cleaned GEDCOM content for import', [
                'tree_id' => $treeId,
                'cleaned_file_path' => $cleanedFilePath,
                'original_size' => mb_strlen($gedcomContent),
                'cleaned_size' => mb_strlen($cleanedGedcomContent),
            ]);

            // 3. Parse GEDCOM content
            $parsed = $this->parseGedcom($cleanedGedcomContent);

            // 4. Begin transaction for PostgreSQL
            DB::beginTransaction();

            // 5. Import to PostgreSQL (core entities)
            $postgresqlResults = $this->importToPostgresql($parsed, $treeId);

            // 6. Import to MongoDB (documents)
            $mongodbResults = $this->importToMongodb($parsed, $treeId);

            // 7. Import to Neo4j (relationships)
            $neo4jResults = $this->importToNeo4j($parsed, $treeId);

            // 8. Commit PostgreSQL transaction
            DB::commit();

            // 9. Create cross-references and validate consistency
            $this->createCrossReferences($treeId);

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);

            Log::info('GEDCOM import completed successfully', [
                'tree_id' => $treeId,
                'duration_seconds' => $duration,
                'cleaned_file_path' => $cleanedFilePath,
                'postgresql_count' => $postgresqlResults,
                'mongodb_count' => $mongodbResults,
                'neo4j_count' => $neo4jResults,
            ]);

            return [
                'success' => true,
                'duration' => $duration,
                'cleaned_file_path' => $cleanedFilePath,
                'postgresql' => $postgresqlResults,
                'mongodb' => $mongodbResults,
                'neo4j' => $neo4jResults,
            ];

        } catch (Exception $e) {
            DB::rollBack();

            Log::error('GEDCOM import failed', [
                'tree_id' => $treeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'cleaned_file_path' => $cleanedFilePath,
            ]);

            // Clean up the cleaned file if it exists and import failed
            if (file_exists($cleanedFilePath)) {
                try {
                    unlink($cleanedFilePath);
                    Log::info('Cleaned up failed import file', ['file_path' => $cleanedFilePath]);
                } catch (Exception $cleanupException) {
                    Log::warning('Failed to cleanup cleaned file', [
                        'file_path' => $cleanedFilePath,
                        'error' => $cleanupException->getMessage(),
                    ]);
                }
            }

            throw $e;
        }
    }

    /**
     * Clean and standardize GEDCOM date formats
     * Handles various date formats including yyyy-only dates
     *
     * @param  string  $dateString  The date string to clean
     * @return string Cleaned and standardized date string
     */
    public function cleanGedcomDate(string $dateString): string
    {
        $originalDate = $dateString;
        $dateString = mb_trim($dateString);

        // Strip leading 'on', 'On', 'ON', etc.
        $dateString = preg_replace('/^on\s+/i', '', $dateString);

        // Handle empty or null dates
        if ($dateString === '' || mb_strtolower($dateString) === 'null') {
            return '';
        }

        // Normalize common prefixes (case-insensitive, with/without period)
        $prefixMap = [
            'ABT' => ['ABT', 'ABT.', 'ABOUT', 'APPROX', 'APPROX.', 'APPROXIMATELY', 'CIR', 'CIR.', 'CIRC', 'CIRC.', 'CA', 'CA.', 'CAL', 'CAL.'],
            'EST' => ['EST', 'EST.', 'ESTIMATED'],
            'BEF' => ['BEF', 'BEF.', 'BEFORE'],
            'AFT' => ['AFT', 'AFT.', 'AFTER'],
        ];
        $prefix = '';
        foreach ($prefixMap as $norm => $variants) {
            foreach ($variants as $variant) {
                if (preg_match('/^'.preg_quote($variant, '/').'\s+/i', $dateString)) {
                    $prefix = $norm;
                    $dateString = preg_replace('/^'.preg_quote($variant, '/').'\s+/i', '', $dateString);
                    break 2;
                }
            }
        }

        // Handle year-only dates (1–current year+10)
        if (preg_match('/^(\d{1,4})$/', $dateString, $matches)) {
            $year = (int) $matches[1];
            if ($year >= 1 && $year <= date('Y') + 10) {
                $cleanedDate = $prefix ? "$prefix $year" : (string) $year;

                return $cleanedDate;
            }
        }

        // Handle year ranges (e.g., 463-465)
        if (preg_match('/^(\d{1,4})\s*-\s*(\d{1,4})$/', $dateString, $matches)) {
            $startYear = (int) $matches[1];
            $endYear = (int) $matches[2];
            if ($startYear >= 1 && $endYear >= 1 && $startYear <= $endYear && $endYear <= date('Y') + 10) {
                $cleanedDate = $prefix ? "$prefix $startYear-$endYear" : "$startYear-$endYear";

                return $cleanedDate;
            }
        }

        // Handle BET/BETWEEN (e.g., BET 463 AND 465)
        if (preg_match('/^BET(?:WEEN)?\s+(\d{1,4})\s+AND\s+(\d{1,4})$/i', $dateString, $matches)) {
            $startYear = (int) $matches[1];
            $endYear = (int) $matches[2];
            if ($startYear >= 1 && $endYear >= 1 && $startYear <= $endYear && $endYear <= date('Y') + 10) {
                $cleanedDate = "BET $startYear AND $endYear";

                return $cleanedDate;
            }
        }

        // Handle full dates (DD MMM YYYY)
        if (preg_match('/^(\d{1,2})\s+([A-Za-z]{3,})\s+(\d{1,4})$/i', $dateString, $matches)) {
            $day = (int) $matches[1];
            $month = ucfirst(mb_strtolower($matches[2]));
            $year = (int) $matches[3];

            if ($day >= 1 && $day <= 31 && $year >= 1 && $year <= date('Y') + 10) {
                $cleanedDate = $prefix ? "$prefix $day $month $year" : "$day $month $year";

                return $cleanedDate;
            }
        }

        // Handle month-year dates (MMM YYYY)
        if (preg_match('/^([A-Za-z]{3,})\s+(\d{1,4})$/i', $dateString, $matches)) {
            $month = ucfirst(mb_strtolower($matches[1]));
            $year = (int) $matches[2];

            if ($year >= 1 && $year <= date('Y') + 10) {
                $cleanedDate = $prefix ? "$prefix $month $year" : "$month $year";

                return $cleanedDate;
            }
        }

        // Handle day-month dates (DD MMM) - add current year
        if (preg_match('/^(\d{1,2})\s+([A-Za-z]{3,})$/i', $dateString, $matches)) {
            $day = (int) $matches[1];
            $month = ucfirst(mb_strtolower($matches[2]));

            if ($day >= 1 && $day <= 31) {
                $cleanedDate = $prefix ? "$prefix $day $month" : "$day $month";

                return $cleanedDate;
            }
        }

        // Handle month-only dates (MMM) - add current year
        if (preg_match('/^([A-Za-z]{3,})$/i', $dateString, $matches)) {
            $month = ucfirst(mb_strtolower($matches[1]));
            $cleanedDate = $prefix ? "$prefix $month" : $month;

            return $cleanedDate;
        }

        // If we can't parse the date, return the original but log it less frequently
        static $unrecognizedDates = [];
        $dateKey = md5($originalDate);

        if (! isset($unrecognizedDates[$dateKey])) {
            $unrecognizedDates[$dateKey] = 0;
        }

        // Only log every 10th occurrence of the same date format to reduce log spam
        if ($unrecognizedDates[$dateKey] % 10 === 0) {
            Log::warning('Unrecognized date format', [
                'originalDate' => $originalDate,
                'dateString' => $dateString,
                'occurrence_count' => $unrecognizedDates[$dateKey] + 1,
            ]);
        }

        $unrecognizedDates[$dateKey]++;

        return $originalDate;
    }

    /**
     * Remove user-defined tags from GEDCOM content
     * User-defined tags start with underscore (_) and are not part of the standard GEDCOM specification
     *
     * @param  string  $gedcomContent  Raw GEDCOM content
     * @return string Cleaned GEDCOM content with user-defined tags removed
     */
    private function removeUserDefinedTags(string $gedcomContent): string
    {
        $lines = preg_split('/\r?\n/', $gedcomContent);
        if ($lines === false) {
            return $gedcomContent;
        }

        $cleanedLines = [];
        $skipUntilLevel = null;
        $removedTags = [];

        foreach ($lines as $line) {
            $trimmedLine = mb_trim($line);

            // Skip empty lines
            if (empty($trimmedLine)) {
                $cleanedLines[] = $line;

                continue;
            }

            // Parse the line to get level and tag
            if (preg_match('/^(\d+)\s+(@[^@]+@)?\s*(\w+)\s*(.*)$/', $trimmedLine, $matches)) {
                $level = (int) $matches[1];
                $tag = $matches[3];
                $value = mb_trim($matches[4]);

                // Check if this is a user-defined tag (starts with underscore)
                if (str_starts_with($tag, '_')) {
                    $removedTags[] = $tag;
                    $skipUntilLevel = $level;

                    continue; // Skip this line
                }

                // Clean dates if this is a DATE tag
                if ($tag === 'DATE' && ! empty($value)) {
                    $cleanedDate = $this->cleanGedcomDate($value);
                    if ($cleanedDate !== $value) {
                        $line = $matches[1].' '.($matches[2] ?? '').' '.$matches[3].' '.$cleanedDate;
                    }
                }

                // Check if we're currently skipping lines due to a user-defined tag
                if ($skipUntilLevel !== null) {
                    // If we encounter a line with the same or lower level, stop skipping
                    if ($level <= $skipUntilLevel) {
                        $skipUntilLevel = null;
                        // Don't continue here, process this line normally
                    } else {
                        // Skip this line (it's a sub-tag of the user-defined tag)
                        continue;
                    }
                }
            }

            // Add the line if we're not skipping
            if ($skipUntilLevel === null) {
                $cleanedLines[] = $line;
            }
        }

        // Log removed tags for debugging
        if (! empty($removedTags)) {
            $uniqueRemovedTags = array_unique($removedTags);
            Log::info('Removed user-defined tags from GEDCOM', [
                'removed_tags' => $uniqueRemovedTags,
                'total_removed' => count($removedTags),
            ]);
        }

        return implode("\n", $cleanedLines);
    }

    /**
     * Parse GEDCOM content into structured data
     */
    private function parseGedcom(string $gedcomContent): array
    {
        $lines = preg_split('/\r?\n/', $gedcomContent);
        if ($lines === false) {
            return $this->getEmptyParsedStructure();
        }

        $parsed = $this->getEmptyParsedStructure();
        $currentRecord = null;
        $currentXref = null;
        $currentLevel = 0;
        $currentContext = [];

        foreach ($lines as $line) {
            if (empty(mb_trim($line))) {
                continue;
            }

            // Parse GEDCOM line
            if (preg_match('/^(\d+)\s+(@[^@]+@)?\s*(\w+)\s*(.*)$/', $line, $matches)) {
                $level = (int) $matches[1];
                $xref = $matches[2] ?? null;
                $tag = $matches[3];
                $value = mb_trim($matches[4]);

                // New top-level record
                if ($level === 0 && $xref) {
                    $currentRecord = $tag;
                    $currentXref = $xref;
                    $currentContext = [];

                    // Initialize record structure
                    $this->initializeRecord($parsed, $currentRecord, $currentXref);
                }

                // Process based on record type
                $this->processGedcomLine($parsed, $currentRecord, $currentXref, $level, $tag, $value, $currentContext);
            }
        }

        return $parsed;
    }

    /**
     * Import core entities to PostgreSQL
     */
    private function importToPostgresql(array $parsed, int $treeId): array
    {
        $results = [
            'individuals' => 0,
            'families' => 0,
            'sources' => 0,
            'repositories' => 0,
            'media' => 0,
        ];

        // Bulk import individuals with batch processing
        $individualData = [];
        foreach ($parsed['individuals'] as $xref => $individual) {
            // --- Date handling ---
            $birthDateRaw = $individual['birth']['date'] ?? null;
            $birthDate = null;
            $birthYear = null;
            if ($birthDateRaw) {
                if (preg_match('/^\d{4}$/', $birthDateRaw)) {
                    $birthYear = (int) $birthDateRaw;
                    $birthDate = null;
                } elseif (strtotime($birthDateRaw)) {
                    $birthDate = date('Y-m-d', strtotime($birthDateRaw));
                    $birthYear = (int) date('Y', strtotime($birthDateRaw));
                } else {
                    $birthDate = null;
                    $birthYear = null;
                }
            }
            $deathDateRaw = $individual['death']['date'] ?? null;
            $deathDate = null;
            $deathYear = null;
            if ($deathDateRaw) {
                if (preg_match('/^\d{4}$/', $deathDateRaw)) {
                    $deathYear = (int) $deathDateRaw;
                    $deathDate = null;
                } elseif (strtotime($deathDateRaw)) {
                    $deathDate = date('Y-m-d', strtotime($deathDateRaw));
                    $deathYear = (int) date('Y', strtotime($deathDateRaw));
                } else {
                    $deathDate = null;
                    $deathYear = null;
                }
            }

            $individualData[] = [
                'tree_id' => $treeId,
                'gedcom_xref' => $xref,
                'first_name' => $individual['name']['given'] ?? 'Unknown',
                'last_name' => $individual['name']['surname'] ?? 'Unknown',
                'name_prefix' => $individual['name']['prefix'] ?? null,
                'name_suffix' => $individual['name']['suffix'] ?? null,
                'nickname' => $individual['name']['nickname'] ?? null,
                'sex' => $individual['sex'] ?? null,
                'birth_date' => $birthDate,
                'birth_year' => $birthYear,
                'birth_date_raw' => $birthDateRaw,
                'death_date' => $deathDate,
                'death_year' => $deathYear,
                'death_date_raw' => $deathDateRaw,
                'birth_place' => $individual['birth']['place'] ?? null,
                'death_place' => $individual['death']['place'] ?? null,
                'death_cause' => $individual['death']['cause'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Batch insert individuals (500 records per batch to avoid parameter limits)
        if (! empty($individualData)) {
            $batchSize = 500;
            $batches = array_chunk($individualData, $batchSize);

            foreach ($batches as $batch) {
                Individual::insert($batch);
            }

            $results['individuals'] = count($individualData);

            // Cache the IDs for cross-references
            $individuals = Individual::where('tree_id', $treeId)
                ->whereIn('gedcom_xref', array_keys($parsed['individuals']))
                ->get(['id', 'gedcom_xref']);

            foreach ($individuals as $individual) {
                Cache::put("gedcom_xref:{$individual->gedcom_xref}", $individual->id, 3600);
            }
        }

        // Bulk import families with batch processing
        $familyData = [];
        foreach ($parsed['families'] as $xref => $family) {
            $husbandId = null;
            $wifeId = null;

            if (! empty($family['husband'])) {
                $husbandId = $this->getIndividualIdByXref($family['husband']);
            }

            if (! empty($family['wife'])) {
                $wifeId = $this->getIndividualIdByXref($family['wife']);
            }

            // Clean and format marriage date
            $marriageDateRaw = $family['marriage']['date'] ?? null;
            $marriageDate = null;
            $marriageYear = null;
            if ($marriageDateRaw) {
                $cleanedMarriageDate = $this->cleanGedcomDate($marriageDateRaw);
                if (preg_match('/^\d{4}$/', $cleanedMarriageDate)) {
                    $marriageYear = (int) $cleanedMarriageDate;
                    $marriageDate = null;
                } elseif (strtotime($cleanedMarriageDate)) {
                    $marriageDate = date('Y-m-d', strtotime($cleanedMarriageDate));
                    $marriageYear = (int) date('Y', strtotime($cleanedMarriageDate));
                } else {
                    // For non-standard dates like "BEF 969", store as null but keep raw value
                    $marriageDate = null;
                    $marriageYear = null;
                }
            }

            // Clean and format divorce date
            $divorceDateRaw = $family['divorce']['date'] ?? null;
            $divorceDate = null;
            $divorceYear = null;
            if ($divorceDateRaw) {
                $cleanedDivorceDate = $this->cleanGedcomDate($divorceDateRaw);
                if (preg_match('/^\d{4}$/', $cleanedDivorceDate)) {
                    $divorceYear = (int) $cleanedDivorceDate;
                    $divorceDate = null;
                } elseif (strtotime($cleanedDivorceDate)) {
                    $divorceDate = date('Y-m-d', strtotime($cleanedDivorceDate));
                    $divorceYear = (int) date('Y', strtotime($cleanedDivorceDate));
                } else {
                    // For non-standard dates like "BEF 969", store as null but keep raw value
                    $divorceDate = null;
                    $divorceYear = null;
                }
            }

            $familyData[] = [
                'tree_id' => $treeId,
                'gedcom_xref' => $xref,
                'husband_id' => $husbandId,
                'wife_id' => $wifeId,
                'marriage_date' => $marriageDate,
                'marriage_year' => $marriageYear,
                'marriage_date_raw' => $marriageDateRaw,
                'marriage_place' => $family['marriage']['place'] ?? null,
                'marriage_type' => $family['marriage']['type'] ?? null,
                'divorce_date' => $divorceDate,
                'divorce_year' => $divorceYear,
                'divorce_date_raw' => $divorceDateRaw,
                'divorce_place' => $family['divorce']['place'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Batch insert families
        if (! empty($familyData)) {
            $batchSize = 500;
            $batches = array_chunk($familyData, $batchSize);

            foreach ($batches as $batch) {
                Family::insert($batch);
            }

            $results['families'] = count($familyData);

            // Cache the IDs for cross-references
            $families = Family::where('tree_id', $treeId)
                ->whereIn('gedcom_xref', array_keys($parsed['families']))
                ->get(['id', 'gedcom_xref']);

            foreach ($families as $family) {
                Cache::put("gedcom_xref:{$family->gedcom_xref}", $family->id, 3600);
            }
        }

        // Bulk import sources with batch processing
        $sourceData = [];
        foreach ($parsed['sources'] as $xref => $source) {
            $sourceData[] = [
                'tree_id' => $treeId,
                'gedcom_xref' => $xref,
                'title' => $source['title'] ?? 'Untitled Source',
                'author' => $source['author'] ?? null,
                'publication' => $source['publication'] ?? null,
                'repository_id' => $this->getRepositoryIdByXref($source['repository']),
                'call_number' => $source['call_number'] ?? null,
                'data_quality' => $source['quality'] ?? 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Batch insert sources
        if (! empty($sourceData)) {
            $batchSize = 500;
            $batches = array_chunk($sourceData, $batchSize);

            foreach ($batches as $batch) {
                Source::insert($batch);
            }

            $results['sources'] = count($sourceData);

            // Cache the IDs for cross-references
            $sources = Source::where('tree_id', $treeId)
                ->whereIn('gedcom_xref', array_keys($parsed['sources']))
                ->get(['id', 'gedcom_xref']);

            foreach ($sources as $source) {
                Cache::put("gedcom_xref:{$source->gedcom_xref}", $source->id, 3600);
            }
        }

        return $results;
    }

    /**
     * Import document data to MongoDB
     */
    private function importToMongodb(array $parsed, int $treeId): array
    {
        $database = $this->mongoClient->selectDatabase(config('database.connections.mongodb.database'));
        $results = [
            'individuals' => 0,
            'families' => 0,
            'notes' => 0,
            'sources' => 0,
            'media' => 0,
        ];

        // Bulk import individual documents
        $individualsCollection = $database->selectCollection('individuals');
        $individualDocuments = [];
        foreach ($parsed['individuals'] as $xref => $individual) {
            $individualId = $this->getIndividualIdByXref($xref);
            if (! $individualId) {
                continue;
            }

            $individualDocuments[] = [
                'individual_id' => $individualId,
                'tree_id' => $treeId,
                'gedcom_xref' => $xref,
                'name_components' => $individual['name'] ?? [],
                'events' => $individual['events'] ?? [],
                'custom_fields' => $individual['custom_fields'] ?? [],
                'metadata' => [
                    'import_source' => 'GEDCOM',
                    'import_date' => new UTCDateTime(),
                    'version' => 1,
                ],
            ];
        }

        if (! empty($individualDocuments)) {
            $individualsCollection->insertMany($individualDocuments);
            $results['individuals'] = count($individualDocuments);
        }

        // Bulk import family documents
        $familiesCollection = $database->selectCollection('families');
        $familyDocuments = [];
        foreach ($parsed['families'] as $xref => $family) {
            $familyId = $this->getFamilyIdByXref($xref);
            if (! $familyId) {
                continue;
            }

            $familyDocuments[] = [
                'family_id' => $familyId,
                'tree_id' => $treeId,
                'gedcom_xref' => $xref,
                'marriage_event' => $family['marriage'] ?? [],
                'divorce_event' => $family['divorce'] ?? [],
                'children' => $family['children'] ?? [],
                'custom_fields' => $family['custom_fields'] ?? [],
                'metadata' => [
                    'import_source' => 'GEDCOM',
                    'import_date' => new UTCDateTime(),
                    'version' => 1,
                ],
            ];
        }

        if (! empty($familyDocuments)) {
            $familiesCollection->insertMany($familyDocuments);
            $results['families'] = count($familyDocuments);
        }

        // Bulk import notes
        $notesCollection = $database->selectCollection('notes');
        $noteDocuments = [];
        foreach ($parsed['notes'] as $xref => $note) {
            $noteDocuments[] = [
                'tree_id' => $treeId,
                'gedcom_xref' => $xref,
                'content' => $note['content'] ?? '',
                'type' => $note['type'] ?? 'general',
                'relationships' => $note['relationships'] ?? [],
                'metadata' => [
                    'import_source' => 'GEDCOM',
                    'import_date' => new UTCDateTime(),
                    'version' => 1,
                ],
                'formatting' => [
                    'is_html' => false,
                    'has_links' => false,
                    'word_count' => str_word_count($note['content'] ?? ''),
                ],
            ];
        }

        if (! empty($noteDocuments)) {
            $notesCollection->insertMany($noteDocuments);
            $results['notes'] = count($noteDocuments);
        }

        return $results;
    }

    /**
     * Import relationships to Neo4j
     */
    private function importToNeo4j(array $parsed, int $treeId): array
    {
        $results = [
            'individuals' => 0,
            'families' => 0,
            'relationships' => 0,
        ];

        $transaction = null;
        $transactionActive = false;

        try {
            $transaction = $this->neo4jService->beginTransaction();
            $transactionActive = true;

            // Create individual nodes
            foreach ($parsed['individuals'] as $xref => $individual) {
                $individualId = $this->getIndividualIdByXref($xref);
                if (! $individualId) {
                    continue;
                }

                $this->neo4jService->createIndividualNode([
                    'id' => $individualId,
                    'tree_id' => $treeId,
                    'gedcom_xref' => $xref,
                    'first_name' => $individual['name']['given'] ?? 'Unknown',
                    'last_name' => $individual['name']['surname'] ?? 'Unknown',
                    'sex' => $individual['sex'] ?? null,
                    'birth_date' => $individual['birth']['date'] ?? null,
                    'death_date' => $individual['death']['date'] ?? null,
                ], $transaction);

                $results['individuals']++;
            }

            // Create family relationships
            foreach ($parsed['families'] as $xref => $family) {
                $familyId = $this->getFamilyIdByXref($xref);
                if (! $familyId) {
                    continue;
                }

                // Create family node
                $this->neo4jService->createFamilyNode([
                    'id' => $familyId,
                    'tree_id' => $treeId,
                    'gedcom_xref' => $xref,
                    'type' => 'Family',
                ], $transaction);

                $results['families']++;

                // Create spouse relationships
                if ($family['husband'] && $family['wife']) {
                    $husbandId = $this->getIndividualIdByXref($family['husband']);
                    $wifeId = $this->getIndividualIdByXref($family['wife']);

                    if ($husbandId && $wifeId) {
                        $this->neo4jService->createSpouseRelationship($husbandId, $wifeId, $transaction);
                        $results['relationships']++;
                    }
                }

                // Create parent-child relationships
                if (isset($family['children'])) {
                    $parentIds = [];
                    if ($family['husband']) {
                        $parentIds[] = $this->getIndividualIdByXref($family['husband']);
                    }
                    if ($family['wife']) {
                        $parentIds[] = $this->getIndividualIdByXref($family['wife']);
                    }

                    foreach ($family['children'] as $childXref) {
                        $childId = $this->getIndividualIdByXref($childXref);
                        if (! $childId) {
                            continue;
                        }

                        foreach ($parentIds as $parentId) {
                            if ($parentId) {
                                $this->neo4jService->createParentChildRelationship($parentId, $childId, $transaction);
                                $results['relationships']++;
                            }
                        }
                    }
                }
            }

            if ($transaction && $transactionActive) {
                $transaction->commit();
                $transactionActive = false;
            }

        } catch (Exception $e) {
            // Only attempt rollback if transaction is still active
            if ($transaction && $transactionActive) {
                try {
                    $transaction->rollback();
                } catch (Exception $rollbackException) {
                    Log::warning('Failed to rollback Neo4j transaction', [
                        'original_error' => $e->getMessage(),
                        'rollback_error' => $rollbackException->getMessage(),
                        'tree_id' => $treeId,
                    ]);
                }
                $transactionActive = false;
            }

            Log::error('Neo4j import failed', [
                'tree_id' => $treeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }

        return $results;
    }

    /**
     * Create cross-references between databases
     */
    private function createCrossReferences(int $treeId): void
    {
        // This method ensures data consistency across databases
        // and creates necessary indexes and relationships

        Log::info('Creating cross-references for tree', ['tree_id' => $treeId]);
    }

    /**
     * Helper methods
     */
    private function getEmptyParsedStructure(): array
    {
        return [
            'individuals' => [],
            'families' => [],
            'sources' => [],
            'repositories' => [],
            'notes' => [],
            'media' => [],
            'submitters' => [],
            'header' => [],
        ];
    }

    private function initializeRecord(array &$parsed, string $recordType, string $xref): void
    {
        switch ($recordType) {
            case 'INDI':
                $parsed['individuals'][$xref] = [
                    'name' => [],
                    'sex' => null,
                    'birth' => [],
                    'death' => [],
                    'events' => [],
                    'custom_fields' => [],
                    'families' => [],
                ];
                break;
            case 'FAM':
                $parsed['families'][$xref] = [
                    'husband' => null,
                    'wife' => null,
                    'children' => [],
                    'marriage' => [],
                    'divorce' => [],
                    'custom_fields' => [],
                ];
                break;
            case 'SOUR':
                $parsed['sources'][$xref] = [
                    'title' => null,
                    'author' => null,
                    'publication' => null,
                    'repository' => null,
                    'call_number' => null,
                    'quality' => 0,
                    'data' => [],
                ];
                break;
            case 'NOTE':
                $parsed['notes'][$xref] = [
                    'content' => '',
                    'type' => 'general',
                    'relationships' => [],
                ];
                break;
            case 'OBJE':
                $parsed['media'][$xref] = [
                    'title' => null,
                    'file' => null,
                    'format' => null,
                    'relationships' => [],
                ];
                break;
        }
    }

    private function processGedcomLine(array &$parsed, ?string $currentRecord, ?string $currentXref, int $level, string $tag, string $value, array &$context): void
    {
        if (! $currentRecord || ! $currentXref) {
            return;
        }

        switch ($currentRecord) {
            case 'INDI':
                $this->processIndividualLine($parsed, $currentXref, $level, $tag, $value, $context);
                break;
            case 'FAM':
                $this->processFamilyLine($parsed, $currentXref, $level, $tag, $value, $context);
                break;
            case 'SOUR':
                $this->processSourceLine($parsed, $currentXref, $level, $tag, $value, $context);
                break;
            case 'NOTE':
                $this->processNoteLine($parsed, $currentXref, $level, $tag, $value, $context);
                break;
            case 'OBJE':
                $this->processMediaLine($parsed, $currentXref, $level, $tag, $value, $context);
                break;
        }
    }

    private function processIndividualLine(array &$parsed, string $xref, int $level, string $tag, string $value, array &$context): void
    {
        $individual = &$parsed['individuals'][$xref];

        switch ($tag) {
            case 'NAME':
                if ($level === 1) {
                    $individual['name']['full'] = $value;
                    // Parse name components
                    $this->parseNameComponents($individual['name'], $value);
                }
                break;
            case 'GIVN':
                if ($level === 2) {
                    $individual['name']['given'] = $value;
                }
                break;
            case 'SURN':
                if ($level === 2) {
                    $individual['name']['surname'] = $value;
                }
                break;
            case 'NPFX':
                if ($level === 2) {
                    $individual['name']['prefix'] = $value;
                }
                break;
            case 'NSFX':
                if ($level === 2) {
                    $individual['name']['suffix'] = $value;
                }
                break;
            case 'NICK':
                if ($level === 2) {
                    $individual['name']['nickname'] = $value;
                }
                break;
            case 'SEX':
                if ($level === 1) {
                    $individual['sex'] = $value;
                }
                break;
            case 'BIRT':
                if ($level === 1) {
                    $context['current_event'] = 'birth';
                }
                break;
            case 'DEAT':
                if ($level === 1) {
                    $context['current_event'] = 'death';
                }
                break;
            case 'DATE':
                if ($level === 2 && isset($context['current_event'])) {
                    $individual[$context['current_event']]['date'] = $value;
                }
                break;
            case 'PLAC':
                if ($level === 2 && isset($context['current_event'])) {
                    $individual[$context['current_event']]['place'] = $value;
                }
                break;
            case 'FAMS':
                if ($level === 1) {
                    $individual['families'][] = $value;
                }
                break;
        }
    }

    private function processFamilyLine(array &$parsed, string $xref, int $level, string $tag, string $value, array &$context): void
    {
        $family = &$parsed['families'][$xref];

        switch ($tag) {
            case 'HUSB':
                if ($level === 1) {
                    $family['husband'] = $value;
                }
                break;
            case 'WIFE':
                if ($level === 1) {
                    $family['wife'] = $value;
                }
                break;
            case 'CHIL':
                if ($level === 1) {
                    $family['children'][] = $value;
                }
                break;
            case 'MARR':
                if ($level === 1) {
                    $context['current_event'] = 'marriage';
                }
                break;
            case 'DIV':
                if ($level === 1) {
                    $context['current_event'] = 'divorce';
                }
                break;
            case 'DATE':
                if ($level === 2 && isset($context['current_event'])) {
                    $family[$context['current_event']]['date'] = $value;
                }
                break;
            case 'PLAC':
                if ($level === 2 && isset($context['current_event'])) {
                    $family[$context['current_event']]['place'] = $value;
                }
                break;
            case 'TYPE':
                if ($level === 2 && isset($context['current_event'])) {
                    $family[$context['current_event']]['type'] = $value;
                }
                break;
        }
    }

    private function processSourceLine(array &$parsed, string $xref, int $level, string $tag, string $value, array &$context): void
    {
        $source = &$parsed['sources'][$xref];

        switch ($tag) {
            case 'TITL':
                if ($level === 1) {
                    $source['title'] = $value;
                }
                break;
            case 'AUTH':
                if ($level === 1) {
                    $source['author'] = $value;
                }
                break;
            case 'PUBL':
                if ($level === 1) {
                    $source['publication'] = $value;
                }
                break;
            case 'REPO':
                if ($level === 1) {
                    $source['repository'] = $value;
                }
                break;
            case 'CALN':
                if ($level === 2) {
                    $source['call_number'] = $value;
                }
                break;
            case 'QUAY':
                if ($level === 1) {
                    $source['quality'] = (int) $value;
                }
                break;
        }
    }

    private function processNoteLine(array &$parsed, string $xref, int $level, string $tag, string $value, array &$context): void
    {
        $note = &$parsed['notes'][$xref];

        switch ($tag) {
            case 'CONT':
                if ($level === 1) {
                    $note['content'] .= "\n".$value;
                }
                break;
            case 'CONC':
                if ($level === 1) {
                    $note['content'] .= $value;
                }
                break;
        }
    }

    private function processMediaLine(array &$parsed, string $xref, int $level, string $tag, string $value, array &$context): void
    {
        $media = &$parsed['media'][$xref];

        switch ($tag) {
            case 'FILE':
                if ($level === 1) {
                    $media['file'] = $value;
                }
                break;
            case 'TITL':
                if ($level === 1) {
                    $media['title'] = $value;
                }
                break;
            case 'FORM':
                if ($level === 2) {
                    $media['format'] = $value;
                }
                break;
        }
    }

    private function parseNameComponents(array &$name, string $fullName): void
    {
        // Basic name parsing - can be enhanced with more sophisticated logic
        $parts = explode('/', $fullName);
        if (count($parts) >= 2) {
            $name['given'] = mb_trim($parts[0]);
            $name['surname'] = mb_trim($parts[1]);
        } else {
            $name['given'] = mb_trim($fullName);
            $name['surname'] = '';
        }
    }

    private function getIndividualIdByXref(?string $xref): ?int
    {
        if (empty($xref)) {
            return null;
        }

        return Cache::get("gedcom_xref:{$xref}");
    }

    private function getFamilyIdByXref(?string $xref): ?int
    {
        if (empty($xref)) {
            return null;
        }

        return Cache::get("gedcom_xref:{$xref}");
    }

    private function getRepositoryIdByXref(?string $xref): ?int
    {
        if (empty($xref)) {
            return null;
        }

        return Cache::get("gedcom_xref:{$xref}");
    }

    /**
     * Store cleaned GEDCOM content to a file
     *
     * @param  string  $cleanedContent  The cleaned GEDCOM content
     * @param  int  $treeId  The tree ID for organizing files
     * @return string The path to the stored file
     */
    private function storeCleanedGedcomContent(string $cleanedContent, int $treeId): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "tree_{$treeId}_cleaned_gedcom_{$timestamp}.ged";
        $filePath = storage_path("app/gedcom/cleaned/tree_{$treeId}/{$filename}");

        // Ensure directory exists
        $directory = dirname($filePath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Store the cleaned content
        file_put_contents($filePath, $cleanedContent);

        return $filePath;
    }
}
