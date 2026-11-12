<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\HandlesGedcomOperations;
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
    use HandlesGedcomOperations;

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
        $stream = fopen('php://temp', 'w+b');
        if ($stream === false) {
            throw new Exception('Unable to allocate temporary stream for GEDCOM import');
        }

        try {
            if (fwrite($stream, $gedcomContent) !== mb_strlen($gedcomContent, '8bit')) {
                throw new Exception('Unable to write GEDCOM content to temporary stream');
            }
            rewind($stream);

            return $this->importGedcomStream($stream, $treeId);
        } finally {
            fclose($stream);
        }
    }

    /**
     * Import GEDCOM records from a stream without retaining the source or parsed file in memory.
     *
     * @param  resource  $stream
     */
    public function importGedcomStream($stream, int $treeId): array
    {
        $startTime = microtime(true);
        $initialMemory = memory_get_usage();
        $familyStream = null;
        $spooledInputStream = null;

        try {
            if (! is_resource($stream)) {
                throw new Exception('Invalid GEDCOM input stream');
            }

            $inputStream = $stream;
            if (! (stream_get_meta_data($stream)['seekable'] ?? false)) {
                $spooledInputStream = tmpfile();
                if ($spooledInputStream === false) {
                    throw new Exception('Unable to allocate temporary GEDCOM input stream');
                }

                while (! feof($stream)) {
                    $chunk = fread($stream, 65536);
                    if ($chunk === false) {
                        throw new Exception('Failed while copying GEDCOM input stream');
                    }
                    if ($chunk === '' && ! feof($stream)) {
                        throw new Exception('GEDCOM input stream stopped before reaching end of file');
                    }
                    if ($chunk !== '' && fwrite($spooledInputStream, $chunk) !== mb_strlen($chunk, '8bit')) {
                        throw new Exception('Failed while spooling GEDCOM input stream');
                    }
                }
                rewind($spooledInputStream);
                $inputStream = $spooledInputStream;
            }

            $this->validateGedcomStream($inputStream);

            $familyStream = tmpfile();
            if ($familyStream === false) {
                throw new Exception('Unable to allocate temporary family relationship stream');
            }

            $results = [
                'individuals' => 0,
                'families' => 0,
                'sources' => 0,
                'notes' => 0,
                'media' => 0,
            ];
            $batches = $this->getEmptyParsedStructure();
            $currentRecord = null;
            $currentXref = null;
            $currentData = [];
            $context = [];
            $lineCount = 0;

            while (($line = fgets($inputStream)) !== false) {
                if (++$lineCount % self::BATCH_SIZE === 0) {
                    $this->checkMemoryUsage();
                }
                if (str_starts_with($line, "\xEF\xBB\xBF")) {
                    $line = mb_substr($line, 1);
                }
                $line = mb_trim($line);
                if ($line === '') {
                    continue;
                }

                if (! preg_match('/^(\d+)\s+(@[^@]+@\s+)?(\w+)(\s+(.+))?$/', $line, $matches)) {
                    continue;
                }

                $level = (int) $matches[1];
                $xref = isset($matches[2]) ? mb_trim($matches[2]) : null;
                $tag = $matches[3];
                $value = isset($matches[5]) ? mb_trim($matches[5]) : '';

                if ($level === 0) {
                    if ($currentRecord !== null && $currentXref !== null) {
                        $this->flushCurrentRecord($currentRecord, $currentXref, $currentData, $batches, $familyStream, $results, $treeId);
                    }

                    $currentRecord = $tag;
                    $currentXref = $xref;
                    $currentData = $this->getEmptyParsedStructure();
                    $context = [];
                    if ($currentXref !== null) {
                        $this->initializeRecord($currentData, $currentRecord, $currentXref);
                    }
                } elseif ($currentRecord !== null && $currentXref !== null) {
                    $this->processGedcomLine($currentData, $currentRecord, $currentXref, $level, $tag, $value, $context);
                }
            }

            if (! feof($inputStream)) {
                throw new Exception('Failed while reading GEDCOM input stream');
            }

            if ($currentRecord !== null && $currentXref !== null) {
                $this->flushCurrentRecord($currentRecord, $currentXref, $currentData, $batches, $familyStream, $results, $treeId);
            }

            $this->flushRecordBatches($batches, $results, $treeId);
            rewind($familyStream);
            $this->importFamilyRelationshipsFromStream($familyStream, $treeId);

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
        } finally {
            if (is_resource($familyStream)) {
                fclose($familyStream);
            }
            if (is_resource($spooledInputStream)) {
                fclose($spooledInputStream);
            }
        }
    }

    /**
     * Verify the complete input before importing any record, preserving all-or-nothing format validation.
     *
     * @param  resource  $stream
     */
    private function validateGedcomStream($stream): void
    {
        $hasHeader = false;
        $hasTrailer = false;

        while (($line = fgets($stream)) !== false) {
            if (str_starts_with($line, "\xEF\xBB\xBF")) {
                $line = mb_substr($line, 1);
            }
            $line = mb_trim($line);
            $hasHeader = $hasHeader || str_starts_with($line, '0 HEAD');
            $hasTrailer = $hasTrailer || str_starts_with($line, '0 TRLR');
        }

        if (! feof($stream)) {
            throw new Exception('Failed while validating GEDCOM input stream');
        }
        if (! $hasHeader || ! $hasTrailer) {
            throw new Exception('Invalid GEDCOM format: Missing HEAD or TRLR');
        }
        if (! rewind($stream)) {
            throw new Exception('Unable to rewind GEDCOM input stream');
        }
    }

    private function flushCurrentRecord(string $recordType, string $xref, array $record, array &$batches, $familyStream, array &$results, int $treeId): void
    {
        $key = match ($recordType) {
            'INDI' => 'individuals',
            'FAM' => 'families',
            'SOUR' => 'sources',
            'NOTE' => 'notes',
            'OBJE' => 'media',
            default => null,
        };
        if ($key === null) {
            return;
        }
        if (! isset($record[$key][$xref])) {
            return;
        }

        $batches[$key][$xref] = $record[$key][$xref];
        if ($key === 'families') {
            $encodedFamily = base64_encode(serialize($record[$key][$xref]));
            if (fwrite($familyStream, $encodedFamily."\n") !== mb_strlen($encodedFamily, '8bit') + 1) {
                throw new Exception('Failed to spool GEDCOM family relationship');
            }
        }

        if (count($batches[$key]) >= self::BATCH_SIZE) {
            $this->flushRecordBatch($key, $batches[$key], $results, $treeId);
            $batches[$key] = [];
        }
    }

    private function flushRecordBatches(array &$batches, array &$results, int $treeId): void
    {
        foreach (array_keys($batches) as $key) {
            $this->flushRecordBatch($key, $batches[$key], $results, $treeId);
            $batches[$key] = [];
        }
    }

    private function flushRecordBatch(string $key, array $batch, array &$results, int $treeId): void
    {
        if ($batch === []) {
            return;
        }

        $results[$key] += match ($key) {
            'individuals' => $this->importIndividualsBatch($batch, $treeId),
            'families' => $this->importFamiliesBatch($batch, $treeId),
            'sources' => $this->importSourcesBatch($batch, $treeId),
            'notes' => $this->importNotesToMongo($batch, $treeId),
            'media' => $this->importMediaToMongo($batch, $treeId),
        };
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
     * Resolve IDs in bounded batches before creating Neo4j relationships.
     *
     * @param  resource  $familyStream
     */
    private function importFamilyRelationshipsFromStream($familyStream, int $treeId): void
    {
        $families = [];
        while (($line = fgets($familyStream)) !== false) {
            $decoded = base64_decode(mb_trim($line), true);
            if ($decoded === false) {
                throw new Exception('Invalid GEDCOM family relationship spool');
            }
            $family = unserialize($decoded, ['allowed_classes' => false]);
            if (! is_array($family)) {
                throw new Exception('Invalid GEDCOM family relationship spool');
            }
            $families[] = ['family' => $family];
            if (count($families) >= self::BATCH_SIZE) {
                $this->importFamilyRelationshipBatch($families, $treeId);
                $families = [];
            }
        }

        if (! feof($familyStream)) {
            throw new Exception('Failed while reading spooled GEDCOM family relationships');
        }
        if ($families !== []) {
            $this->importFamilyRelationshipBatch($families, $treeId);
        }
    }

    private function importFamilyRelationshipBatch(array $families, int $treeId): void
    {
        $xrefs = [];
        foreach ($families as $row) {
            $family = $row['family'];
            if (! empty($family['husband'])) {
                $xrefs[] = $family['husband'];
            }
            if (! empty($family['wife'])) {
                $xrefs[] = $family['wife'];
            }
            foreach ($family['children'] ?? [] as $childXref) {
                $xrefs[] = $childXref;
            }
        }

        $individualIds = [];
        foreach (array_chunk(array_values(array_unique($xrefs)), 500) as $xrefBatch) {
            $individualIds += DB::table('individuals')
                ->where('tree_id', $treeId)
                ->whereIn('gedcom_xref', $xrefBatch)
                ->pluck('id', 'gedcom_xref')
                ->all();
        }

        foreach ($families as $row) {
            $family = $row['family'];
            $husbandId = isset($family['husband']) ? ($individualIds[$family['husband']] ?? null) : null;
            $wifeId = isset($family['wife']) ? ($individualIds[$family['wife']] ?? null) : null;

            if ($husbandId && $wifeId) {
                $this->neo4jService->createSpouseRelationship((int) $husbandId, (int) $wifeId, $treeId);
            }

            foreach ($family['children'] ?? [] as $childXref) {
                $childId = $individualIds[$childXref] ?? null;
                if (! $childId) {
                    continue;
                }
                if ($husbandId) {
                    $this->neo4jService->createParentChildRelationship((int) $husbandId, (int) $childId, $treeId);
                }
                if ($wifeId) {
                    $this->neo4jService->createParentChildRelationship((int) $wifeId, (int) $childId, $treeId);
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

    // Using processGedcomLine from HandlesGedcomOperations trait

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
