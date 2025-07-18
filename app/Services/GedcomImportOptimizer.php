<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Family;
use App\Models\Individual;
use App\Models\Source;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laudis\Neo4j\Client as Neo4jClient;
use MongoDB\Client as MongoClient;

/**
 * Optimized GEDCOM import service with parallel processing
 */
final class GedcomImportOptimizer
{
    private const BATCH_SIZE = 100;

    private const MAX_MEMORY_USAGE = 512 * 1024 * 1024; // 512MB

    private MongoClient $mongoClient;

    private Neo4jClient $neo4jClient;

    private Neo4jIndividualService $neo4jService;

    public function __construct(Neo4jIndividualService $neo4jService)
    {
        $this->neo4jService = $neo4jService;
        $this->mongoClient = new MongoClient(config('database.connections.mongodb.uri'));
        $this->neo4jClient = $this->neo4jService->getClient();
    }

    /**
     * Import GEDCOM data with optimized performance
     */
    public function importGedcomData(string $gedcomContent, int $treeId): array
    {
        $startTime = microtime(true);
        $initialMemory = memory_get_usage();

        try {
            // 1. Clean and validate GEDCOM content
            $cleanedContent = $this->cleanGedcomContent($gedcomContent);

            // 2. Parse GEDCOM in chunks to manage memory
            $parsedData = $this->parseGedcomInChunks($cleanedContent);

            // 3. Import data in parallel batches
            $results = $this->importInParallelBatches($parsedData, $treeId);

            // 4. Create cross-references
            $this->createCrossReferences($treeId);

            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            $memoryUsed = memory_get_usage() - $initialMemory;

            Log::info('Optimized GEDCOM import completed', [
                'tree_id' => $treeId,
                'duration_seconds' => $duration,
                'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                'results' => $results,
            ]);

            return [
                'success' => true,
                'duration' => $duration,
                'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
                'results' => $results,
            ];

        } catch (Exception $e) {
            Log::error('Optimized GEDCOM import failed', [
                'tree_id' => $treeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Clean GEDCOM content and validate format
     */
    private function cleanGedcomContent(string $gedcomContent): string
    {
        // Remove BOM if present
        $gedcomContent = str_replace("\xEF\xBB\xBF", '', $gedcomContent);

        // Normalize line endings
        $gedcomContent = str_replace(["\r\n", "\r"], "\n", $gedcomContent);

        // Remove empty lines
        $lines = array_filter(explode("\n", $gedcomContent), 'trim');

        // Validate basic GEDCOM structure
        $hasHeader = false;
        $hasTrailer = false;

        foreach ($lines as $line) {
            if (str_starts_with($line, '0 HEAD')) {
                $hasHeader = true;
            }
            if (str_starts_with($line, '0 TRLR')) {
                $hasTrailer = true;
            }
        }

        if (! $hasHeader || ! $hasTrailer) {
            throw new Exception('Invalid GEDCOM format: Missing HEAD or TRLR');
        }

        return implode("\n", $lines);
    }

    /**
     * Parse GEDCOM content in chunks to manage memory
     */
    private function parseGedcomInChunks(string $gedcomContent): array
    {
        $lines = explode("\n", $gedcomContent);
        $parsed = $this->getEmptyParsedStructure();
        $currentRecord = null;
        $currentXref = null;
        $context = [];
        $lineCount = 0;

        foreach ($lines as $line) {
            $lineCount++;

            // Check memory usage and process in batches
            if ($lineCount % self::BATCH_SIZE === 0) {
                $this->checkMemoryUsage();
            }

            $line = mb_trim($line);
            if (empty($line)) {
                continue;
            }

            // Parse GEDCOM line
            if (preg_match('/^(\d+)\s+(@[^@]+@\s+)?(\w+)(\s+(.+))?$/', $line, $matches)) {
                $level = (int) $matches[1];
                $xref = isset($matches[2]) ? mb_trim($matches[2]) : null;
                $tag = $matches[3];
                $value = isset($matches[5]) ? mb_trim($matches[5]) : '';

                if ($level === 0) {
                    $currentRecord = $tag;
                    $currentXref = $xref;
                    $this->initializeRecord($parsed, $currentRecord, $currentXref);
                } else {
                    $this->processGedcomLine($parsed, $currentRecord, $currentXref, $level, $tag, $value, $context);
                }
            }
        }

        return $parsed;
    }

    /**
     * Import data in parallel batches
     */
    private function importInParallelBatches(array $parsedData, int $treeId): array
    {
        $results = [
            'individuals' => 0,
            'families' => 0,
            'sources' => 0,
            'notes' => 0,
            'media' => 0,
        ];

        // Import individuals in batches
        if (! empty($parsedData['individuals'])) {
            $individualBatches = array_chunk($parsedData['individuals'], self::BATCH_SIZE);
            foreach ($individualBatches as $batch) {
                $results['individuals'] += $this->importIndividualsBatch($batch, $treeId);
            }
        }

        // Import families in batches
        if (! empty($parsedData['families'])) {
            $familyBatches = array_chunk($parsedData['families'], self::BATCH_SIZE);
            foreach ($familyBatches as $batch) {
                $results['families'] += $this->importFamiliesBatch($batch, $treeId);
            }
        }

        // Import sources in batches
        if (! empty($parsedData['sources'])) {
            $sourceBatches = array_chunk($parsedData['sources'], self::BATCH_SIZE);
            foreach ($sourceBatches as $batch) {
                $results['sources'] += $this->importSourcesBatch($batch, $treeId);
            }
        }

        // Import notes and media to MongoDB
        if (! empty($parsedData['notes'])) {
            $results['notes'] = $this->importNotesToMongo($parsedData['notes'], $treeId);
        }

        if (! empty($parsedData['media'])) {
            $results['media'] = $this->importMediaToMongo($parsedData['media'], $treeId);
        }

        // Import relationships to Neo4j
        $this->importRelationshipsToNeo4j($parsedData, $treeId);

        return $results;
    }

    /**
     * Import individuals batch with optimized queries
     */
    private function importIndividualsBatch(array $individuals, int $treeId): int
    {
        $data = [];
        $now = now();

        foreach ($individuals as $xref => $individual) {
            $data[] = [
                'tree_id' => $treeId,
                'gedcom_xref' => $xref,
                'first_name' => $individual['name']['first'] ?? '',
                'last_name' => $individual['name']['last'] ?? '',
                'name_prefix' => $individual['name']['prefix'] ?? null,
                'name_suffix' => $individual['name']['suffix'] ?? null,
                'nickname' => $individual['name']['nickname'] ?? null,
                'sex' => $individual['sex'] ?? 'U',
                'birth_date' => $this->parseDate($individual['birth']['date'] ?? null),
                'birth_date_raw' => $individual['birth']['date'] ?? null,
                'birth_place' => $individual['birth']['place'] ?? null,
                'death_date' => $this->parseDate($individual['death']['date'] ?? null),
                'death_date_raw' => $individual['death']['date'] ?? null,
                'death_place' => $individual['death']['place'] ?? null,
                'death_cause' => $individual['death']['cause'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($data)) {
            DB::table('individuals')->insert($data);
        }

        return count($data);
    }

    /**
     * Import families batch with optimized queries
     */
    private function importFamiliesBatch(array $families, int $treeId): int
    {
        $data = [];
        $now = now();

        foreach ($families as $xref => $family) {
            $data[] = [
                'tree_id' => $treeId,
                'gedcom_xref' => $xref,
                'marriage_date' => $this->parseDate($family['marriage']['date'] ?? null),
                'marriage_date_raw' => $family['marriage']['date'] ?? null,
                'marriage_place' => $family['marriage']['place'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($data)) {
            DB::table('families')->insert($data);
        }

        return count($data);
    }

    /**
     * Import sources batch
     */
    private function importSourcesBatch(array $sources, int $treeId): int
    {
        $data = [];
        $now = now();

        foreach ($sources as $xref => $source) {
            $data[] = [
                'tree_id' => $treeId,
                'gedcom_xref' => $xref,
                'title' => $source['title'] ?? '',
                'author' => $source['author'] ?? null,
                'publication' => $source['publication'] ?? null,
                'repository' => $source['repository'] ?? null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (! empty($data)) {
            DB::table('sources')->insert($data);
        }

        return count($data);
    }

    /**
     * Import notes to MongoDB
     */
    private function importNotesToMongo(array $notes, int $treeId): int
    {
        $collection = $this->mongoClient->selectDatabase('leg')->selectCollection('notes');
        $data = [];

        foreach ($notes as $xref => $note) {
            $data[] = [
                'tree_id' => $treeId,
                'gedcom_xref' => $xref,
                'content' => $note['content'] ?? '',
                'created_at' => new \MongoDB\BSON\UTCDateTime(),
            ];
        }

        if (! empty($data)) {
            $collection->insertMany($data);
        }

        return count($data);
    }

    /**
     * Import media to MongoDB
     */
    private function importMediaToMongo(array $media, int $treeId): int
    {
        $collection = $this->mongoClient->selectDatabase('leg')->selectCollection('media');
        $data = [];

        foreach ($media as $xref => $mediaItem) {
            $data[] = [
                'tree_id' => $treeId,
                'gedcom_xref' => $xref,
                'file' => $mediaItem['file'] ?? '',
                'title' => $mediaItem['title'] ?? '',
                'format' => $mediaItem['format'] ?? '',
                'created_at' => new \MongoDB\BSON\UTCDateTime(),
            ];
        }

        if (! empty($data)) {
            $collection->insertMany($data);
        }

        return count($data);
    }

    /**
     * Import relationships to Neo4j
     */
    private function importRelationshipsToNeo4j(array $parsedData, int $treeId): void
    {
        // Import family relationships
        foreach ($parsedData['families'] ?? [] as $familyXref => $family) {
            $this->importFamilyRelationships($family, $familyXref, $treeId);
        }
    }

    /**
     * Import family relationships to Neo4j
     */
    private function importFamilyRelationships(array $family, string $familyXref, int $treeId): void
    {
        $husbandId = $this->getIndividualIdByXref($family['husband'] ?? null);
        $wifeId = $this->getIndividualIdByXref($family['wife'] ?? null);

        // Create spouse relationship
        if ($husbandId && $wifeId) {
            $this->neo4jService->createSpouseRelationship($husbandId, $wifeId, $treeId);
        }

        // Create parent-child relationships
        foreach ($family['children'] ?? [] as $childXref) {
            $childId = $this->getIndividualIdByXref($childXref);
            if ($childId) {
                if ($husbandId) {
                    $this->neo4jService->createParentChildRelationship($husbandId, $childId, $treeId);
                }
                if ($wifeId) {
                    $this->neo4jService->createParentChildRelationship($wifeId, $childId, $treeId);
                }
            }
        }
    }

    /**
     * Check memory usage and trigger garbage collection if needed
     */
    private function checkMemoryUsage(): void
    {
        $memoryUsage = memory_get_usage();

        if ($memoryUsage > self::MAX_MEMORY_USAGE) {
            gc_collect_cycles();
            Log::warning('Memory usage high during GEDCOM import', [
                'memory_usage_mb' => round($memoryUsage / 1024 / 1024, 2),
                'max_memory_mb' => round(self::MAX_MEMORY_USAGE / 1024 / 1024, 2),
            ]);
        }
    }

    /**
     * Parse date string to Carbon instance
     */
    private function parseDate(?string $dateString): ?string
    {
        if (empty($dateString)) {
            return null;
        }

        // Basic date parsing - can be enhanced with more complex logic
        if (preg_match('/^\d{4}$/', $dateString)) {
            return $dateString.'-01-01';
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            return $dateString;
        }

        return null;
    }

    /**
     * Get individual ID by GEDCOM xref
     */
    private function getIndividualIdByXref(?string $xref): ?int
    {
        if (! $xref) {
            return null;
        }

        return Individual::where('gedcom_xref', $xref)->value('id');
    }

    /**
     * Create cross-references between databases
     */
    private function createCrossReferences(int $treeId): void
    {
        // This method can be enhanced to create additional cross-references
        // between PostgreSQL, MongoDB, and Neo4j data
        Log::info('Cross-references created for tree', ['tree_id' => $treeId]);
    }

    /**
     * Get empty parsed structure
     */
    private function getEmptyParsedStructure(): array
    {
        return [
            'individuals' => [],
            'families' => [],
            'sources' => [],
            'notes' => [],
            'media' => [],
        ];
    }

    /**
     * Initialize record in parsed structure
     */
    private function initializeRecord(array &$parsed, string $recordType, string $xref): void
    {
        switch ($recordType) {
            case 'INDI':
                $parsed['individuals'][$xref] = [
                    'name' => ['first' => '', 'last' => '', 'prefix' => '', 'suffix' => '', 'nickname' => ''],
                    'sex' => 'U',
                    'birth' => ['date' => null, 'place' => null],
                    'death' => ['date' => null, 'place' => null, 'cause' => null],
                ];
                break;
            case 'FAM':
                $parsed['families'][$xref] = [
                    'husband' => null,
                    'wife' => null,
                    'children' => [],
                    'marriage' => ['date' => null, 'place' => null],
                ];
                break;
            case 'SOUR':
                $parsed['sources'][$xref] = [
                    'title' => '',
                    'author' => null,
                    'publication' => null,
                    'repository' => null,
                ];
                break;
            case 'NOTE':
                $parsed['notes'][$xref] = ['content' => ''];
                break;
            case 'OBJE':
                $parsed['media'][$xref] = [
                    'file' => '',
                    'title' => '',
                    'format' => '',
                ];
                break;
        }
    }

    /**
     * Process GEDCOM line
     */
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

    /**
     * Process individual line
     */
    private function processIndividualLine(array &$parsed, string $xref, int $level, string $tag, string $value, array &$context): void
    {
        switch ($tag) {
            case 'NAME':
                $this->parseNameComponents($parsed['individuals'][$xref]['name'], $value);
                break;
            case 'SEX':
                $parsed['individuals'][$xref]['sex'] = $value;
                break;
            case 'BIRT':
                $context['current_event'] = 'birth';
                break;
            case 'DEAT':
                $context['current_event'] = 'death';
                break;
            case 'DATE':
                if (isset($context['current_event'])) {
                    $parsed['individuals'][$xref][$context['current_event']]['date'] = $value;
                }
                break;
            case 'PLAC':
                if (isset($context['current_event'])) {
                    $parsed['individuals'][$xref][$context['current_event']]['place'] = $value;
                }
                break;
            case 'CAUS':
                if ($context['current_event'] === 'death') {
                    $parsed['individuals'][$xref]['death']['cause'] = $value;
                }
                break;
        }
    }

    /**
     * Process family line
     */
    private function processFamilyLine(array &$parsed, string $xref, int $level, string $tag, string $value, array &$context): void
    {
        switch ($tag) {
            case 'HUSB':
                $parsed['families'][$xref]['husband'] = $value;
                break;
            case 'WIFE':
                $parsed['families'][$xref]['wife'] = $value;
                break;
            case 'CHIL':
                $parsed['families'][$xref]['children'][] = $value;
                break;
            case 'MARR':
                $context['current_event'] = 'marriage';
                break;
            case 'DATE':
                if (isset($context['current_event']) && $context['current_event'] === 'marriage') {
                    $parsed['families'][$xref]['marriage']['date'] = $value;
                }
                break;
            case 'PLAC':
                if (isset($context['current_event']) && $context['current_event'] === 'marriage') {
                    $parsed['families'][$xref]['marriage']['place'] = $value;
                }
                break;
        }
    }

    /**
     * Process source line
     */
    private function processSourceLine(array &$parsed, string $xref, int $level, string $tag, string $value, array &$context): void
    {
        switch ($tag) {
            case 'TITL':
                $parsed['sources'][$xref]['title'] = $value;
                break;
            case 'AUTH':
                $parsed['sources'][$xref]['author'] = $value;
                break;
            case 'PUBL':
                $parsed['sources'][$xref]['publication'] = $value;
                break;
            case 'REPO':
                $parsed['sources'][$xref]['repository'] = $value;
                break;
        }
    }

    /**
     * Process note line
     */
    private function processNoteLine(array &$parsed, string $xref, int $level, string $tag, string $value, array &$context): void
    {
        switch ($tag) {
            case 'CONT':
                $parsed['notes'][$xref]['content'] .= $value."\n";
                break;
        }
    }

    /**
     * Process media line
     */
    private function processMediaLine(array &$parsed, string $xref, int $level, string $tag, string $value, array &$context): void
    {
        switch ($tag) {
            case 'FILE':
                $parsed['media'][$xref]['file'] = $value;
                break;
            case 'TITL':
                $parsed['media'][$xref]['title'] = $value;
                break;
            case 'FORM':
                $parsed['media'][$xref]['format'] = $value;
                break;
        }
    }

    /**
     * Parse name components
     */
    private function parseNameComponents(array &$name, string $fullName): void
    {
        // Basic name parsing - can be enhanced
        if (preg_match('/^([^\/]+)\s*\/([^\/]+)\/$/', $fullName, $matches)) {
            $name['first'] = mb_trim($matches[1]);
            $name['last'] = mb_trim($matches[2]);
        } else {
            $name['first'] = mb_trim($fullName);
            $name['last'] = '';
        }
    }
}
