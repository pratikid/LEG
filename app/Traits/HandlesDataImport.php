<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Family;
use App\Models\Individual;
use App\Models\Media;
use App\Models\Note;
use App\Models\Repository;
use App\Models\Source;
use App\Models\Tree;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * Trait for handling data import operations
 */
trait HandlesDataImport
{
    /**
     * Import tree data from JSON format.
     *
     * @param  string  $jsonPath  Path to the JSON file
     * @param  int  $userId  User ID to associate with imported data
     * @param  string|null  $treeName  Optional custom name for the imported tree
     * @param  string  $disk  Storage disk where the JSON file is located
     * @return Tree|null The imported tree or null if import failed
     */
    protected function importTreeFromJson(
        string $jsonPath,
        int $userId,
        ?string $treeName = null,
        string $disk = 'local'
    ): ?Tree {
        // Check if the file exists
        if (! Storage::disk($disk)->exists($jsonPath)) {
            Log::error('JSON import file not found: '.$jsonPath);

            return null;
        }

        // Read and decode the JSON file
        $jsonContent = Storage::disk($disk)->get($jsonPath);
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            Log::error('Invalid JSON file: '.$jsonPath.' - '.json_last_error_msg());

            return null;
        }

        // Validate the JSON structure
        if (! isset($data['tree']) || ! isset($data['individuals']) || ! isset($data['families'])) {
            Log::error('Invalid JSON structure: missing required sections');

            return null;
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create the tree
            $tree = new Tree();
            $tree->name = $treeName ?? ($data['tree']['name'] ?? 'Imported Tree');
            $tree->description = $data['tree']['description'] ?? '';
            $tree->is_public = $data['tree']['is_public'] ?? false;
            $tree->user_id = $userId;
            $tree->save();

            // Import individuals
            $individualIdMap = $this->importIndividuals($data['individuals'], $tree->id, $userId);

            // Import families
            $familyIdMap = $this->importFamilies($data['families'], $tree->id, $individualIdMap);

            // Import sources if present
            $sourceIdMap = [];
            if (isset($data['sources'])) {
                $sourceIdMap = $this->importSources($data['sources'], $tree->id, $userId);
            }

            // Import repositories if present
            $repositoryIdMap = [];
            if (isset($data['repositories'])) {
                $repositoryIdMap = $this->importRepositories($data['repositories'], $tree->id);

                // Update source repository references
                $this->updateSourceRepositoryReferences($sourceIdMap, $repositoryIdMap);
            }

            // Import notes if present
            if (isset($data['notes'])) {
                $this->importNotes($data['notes'], $tree->id, $userId);
            }

            // Import media if present
            if (isset($data['media'])) {
                $this->importMedia($data['media'], $tree->id, $userId);
            }

            DB::commit();

            return $tree;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to import tree from JSON: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Import individuals from JSON data.
     *
     * @param  array  $individuals  The individuals data
     * @param  int  $treeId  Tree ID to associate with imported individuals
     * @param  int  $userId  User ID to associate with imported individuals
     * @return array Mapping of original IDs to new IDs
     */
    protected function importIndividuals(array $individuals, int $treeId, int $userId): array
    {
        $idMap = [];

        foreach ($individuals as $individualData) {
            $originalId = $individualData['id'];

            $individual = new Individual();
            $individual->tree_id = $treeId;
            $individual->user_id = $userId;
            $individual->gedcom_xref = $individualData['gedcom_xref'] ?? Str::uuid()->toString();
            $individual->given_name = $individualData['given_name'] ?? '';
            $individual->surname = $individualData['surname'] ?? '';
            $individual->nickname = $individualData['nickname'] ?? null;
            $individual->sex = $individualData['sex'] ?? Individual::SEX_UNKNOWN;
            $individual->birth_date = $individualData['birth_date'] ?? null;
            $individual->birth_place = $individualData['birth_place'] ?? null;
            $individual->death_date = $individualData['death_date'] ?? null;
            $individual->death_place = $individualData['death_place'] ?? null;
            $individual->occupation = $individualData['occupation'] ?? null;
            $individual->note = $individualData['note'] ?? null;
            $individual->save();

            $idMap[$originalId] = $individual->id;
        }

        return $idMap;
    }

    /**
     * Import families from JSON data.
     *
     * @param  array  $families  The families data
     * @param  int  $treeId  Tree ID to associate with imported families
     * @param  array  $individualIdMap  Mapping of individual IDs
     * @return array Mapping of original IDs to new IDs
     */
    protected function importFamilies(array $families, int $treeId, array $individualIdMap): array
    {
        $idMap = [];

        foreach ($families as $familyData) {
            $originalId = $familyData['id'];

            $family = new Family();
            $family->tree_id = $treeId;
            $family->gedcom_xref = $familyData['gedcom_xref'] ?? Str::uuid()->toString();

            // Map husband and wife IDs
            if (isset($familyData['husband_id']) && isset($individualIdMap[$familyData['husband_id']])) {
                $family->husband_id = $individualIdMap[$familyData['husband_id']];
            }

            if (isset($familyData['wife_id']) && isset($individualIdMap[$familyData['wife_id']])) {
                $family->wife_id = $individualIdMap[$familyData['wife_id']];
            }

            $family->marriage_date = $familyData['marriage_date'] ?? null;
            $family->marriage_place = $familyData['marriage_place'] ?? null;
            $family->marriage_type = $familyData['marriage_type'] ?? null;
            $family->divorce_date = $familyData['divorce_date'] ?? null;
            $family->divorce_place = $familyData['divorce_place'] ?? null;
            $family->save();

            $idMap[$originalId] = $family->id;

            // Add children to the family
            if (isset($familyData['children']) && is_array($familyData['children'])) {
                $childIds = [];

                foreach ($familyData['children'] as $childId) {
                    if (isset($individualIdMap[$childId])) {
                        $childIds[] = $individualIdMap[$childId];
                    }
                }

                if (! empty($childIds)) {
                    $family->children()->attach($childIds);
                }
            }
        }

        return $idMap;
    }

    /**
     * Import sources from JSON data.
     *
     * @param  array  $sources  The sources data
     * @param  int  $treeId  Tree ID to associate with imported sources
     * @param  int  $userId  User ID to associate with imported sources
     * @return array Mapping of original IDs to new IDs
     */
    protected function importSources(array $sources, int $treeId, int $userId): array
    {
        $idMap = [];

        foreach ($sources as $sourceData) {
            $originalId = $sourceData['id'];

            $source = new Source();
            $source->tree_id = $treeId;
            $source->user_id = $userId;
            $source->gedcom_xref = $sourceData['gedcom_xref'] ?? Str::uuid()->toString();
            $source->title = $sourceData['title'] ?? '';
            $source->author = $sourceData['author'] ?? null;
            $source->publication = $sourceData['publication'] ?? null;
            $source->call_number = $sourceData['call_number'] ?? null;
            $source->data_quality = $sourceData['data_quality'] ?? null;
            $source->citation = $sourceData['citation'] ?? null;

            // Repository ID will be updated later after repositories are imported
            $source->repository_id = null;

            $source->save();

            $idMap[$originalId] = [
                'new_id' => $source->id,
                'repository_id' => $sourceData['repository_id'] ?? null,
            ];
        }

        return $idMap;
    }

    /**
     * Import repositories from JSON data.
     *
     * @param  array  $repositories  The repositories data
     * @param  int  $treeId  Tree ID to associate with imported repositories
     * @return array Mapping of original IDs to new IDs
     */
    protected function importRepositories(array $repositories, int $treeId): array
    {
        $idMap = [];

        foreach ($repositories as $repositoryData) {
            $originalId = $repositoryData['id'];

            $repository = new Repository();
            $repository->tree_id = $treeId;
            $repository->gedcom_xref = $repositoryData['gedcom_xref'] ?? Str::uuid()->toString();
            $repository->name = $repositoryData['name'] ?? '';
            $repository->address = $repositoryData['address'] ?? null;
            $repository->save();

            $idMap[$originalId] = $repository->id;
        }

        return $idMap;
    }

    /**
     * Update source repository references.
     *
     * @param  array  $sourceIdMap  Mapping of source IDs
     * @param  array  $repositoryIdMap  Mapping of repository IDs
     */
    protected function updateSourceRepositoryReferences(array $sourceIdMap, array $repositoryIdMap): void
    {
        foreach ($sourceIdMap as $originalId => $sourceInfo) {
            $newSourceId = $sourceInfo['new_id'];
            $originalRepositoryId = $sourceInfo['repository_id'];

            if ($originalRepositoryId !== null && isset($repositoryIdMap[$originalRepositoryId])) {
                $newRepositoryId = $repositoryIdMap[$originalRepositoryId];

                Source::where('id', $newSourceId)->update([
                    'repository_id' => $newRepositoryId,
                ]);
            }
        }
    }

    /**
     * Import notes from JSON data.
     *
     * @param  array  $notes  The notes data
     * @param  int  $treeId  Tree ID to associate with imported notes
     * @param  int  $userId  User ID to associate with imported notes
     * @return array Mapping of original IDs to new IDs
     */
    protected function importNotes(array $notes, int $treeId, int $userId): array
    {
        $idMap = [];

        foreach ($notes as $noteData) {
            $originalId = $noteData['id'];

            $note = new Note();
            $note->tree_id = $treeId;
            $note->user_id = $userId;
            $note->gedcom_xref = $noteData['gedcom_xref'] ?? Str::uuid()->toString();
            $note->text = $noteData['text'] ?? '';
            $note->save();

            $idMap[$originalId] = $note->id;
        }

        return $idMap;
    }

    /**
     * Import media from JSON data.
     *
     * @param  array  $mediaItems  The media data
     * @param  int  $treeId  Tree ID to associate with imported media
     * @param  int  $userId  User ID to associate with imported media
     * @return array Mapping of original IDs to new IDs
     */
    protected function importMedia(array $mediaItems, int $treeId, int $userId): array
    {
        $idMap = [];

        foreach ($mediaItems as $mediaData) {
            $originalId = $mediaData['id'];

            $media = new Media();
            $media->tree_id = $treeId;
            $media->user_id = $userId;
            $media->title = $mediaData['title'] ?? '';
            $media->description = $mediaData['description'] ?? null;
            $media->file_name = $mediaData['file_name'] ?? '';
            $media->file_type = $mediaData['file_type'] ?? '';
            $media->file_path = $mediaData['file_path'] ?? '';
            $media->save();

            $idMap[$originalId] = $media->id;
        }

        return $idMap;
    }

    /**
     * Import data from CSV file.
     *
     * @param  string  $csvPath  Path to the CSV file
     * @param  string  $entityType  Type of entity to import (individuals, families, etc.)
     * @param  int  $treeId  Tree ID to associate with imported data
     * @param  int  $userId  User ID to associate with imported data
     * @param  string  $disk  Storage disk where the CSV file is located
     * @return int Number of records imported
     */
    protected function importFromCsv(
        string $csvPath,
        string $entityType,
        int $treeId,
        int $userId,
        string $disk = 'local'
    ): int {
        // Check if the file exists
        if (! Storage::disk($disk)->exists($csvPath)) {
            Log::error('CSV import file not found: '.$csvPath);

            return 0;
        }

        // Read the CSV file
        $csvContent = Storage::disk($disk)->get($csvPath);
        $tempFile = tempnam(sys_get_temp_dir(), 'csv_import');
        file_put_contents($tempFile, $csvContent);

        $handle = fopen($tempFile, 'r');
        if ($handle === false) {
            Log::error('Failed to open CSV file for reading');

            return 0;
        }

        // Read headers
        $headers = fgetcsv($handle);
        if ($headers === false) {
            fclose($handle);
            unlink($tempFile);
            Log::error('Failed to read CSV headers');

            return 0;
        }

        // Start a database transaction
        DB::beginTransaction();

        try {
            $importCount = 0;

            // Process rows
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($headers, $row);

                if ($data === false) {
                    Log::warning('Skipping CSV row: column count mismatch');

                    continue;
                }

                // Import the entity
                $success = $this->importEntityFromCsvRow($data, $entityType, $treeId, $userId);

                if ($success) {
                    $importCount++;
                }
            }

            DB::commit();
            fclose($handle);
            unlink($tempFile);

            return $importCount;
        } catch (Exception $e) {
            DB::rollBack();
            fclose($handle);
            unlink($tempFile);
            Log::error('Failed to import from CSV: '.$e->getMessage());

            return 0;
        }
    }

    /**
     * Import a single entity from a CSV row.
     *
     * @param  array  $data  The row data
     * @param  string  $entityType  Type of entity to import
     * @param  int  $treeId  Tree ID to associate with imported data
     * @param  int  $userId  User ID to associate with imported data
     * @return bool Whether the import was successful
     */
    protected function importEntityFromCsvRow(
        array $data,
        string $entityType,
        int $treeId,
        int $userId
    ): bool {
        switch ($entityType) {
            case 'individuals':
                return $this->importIndividualFromCsvRow($data, $treeId, $userId);

            case 'families':
                return $this->importFamilyFromCsvRow($data, $treeId);

            case 'sources':
                return $this->importSourceFromCsvRow($data, $treeId, $userId);

            case 'repositories':
                return $this->importRepositoryFromCsvRow($data, $treeId);

            case 'media':
                return $this->importMediaFromCsvRow($data, $treeId, $userId);

            default:
                Log::warning('Unknown entity type for CSV import: '.$entityType);

                return false;
        }
    }

    /**
     * Import an individual from a CSV row.
     *
     * @param  array  $data  The row data
     * @param  int  $treeId  Tree ID to associate with imported data
     * @param  int  $userId  User ID to associate with imported data
     * @return bool Whether the import was successful
     */
    protected function importIndividualFromCsvRow(array $data, int $treeId, int $userId): bool
    {
        $validator = Validator::make($data, [
            'Given Name' => 'required|string|max:255',
            'Surname' => 'required|string|max:255',
            'Sex' => 'nullable|string|in:M,F,U',
        ]);

        if ($validator->fails()) {
            Log::warning('Invalid individual data in CSV: '.json_encode($validator->errors()->toArray()));

            return false;
        }

        $individual = new Individual();
        $individual->tree_id = $treeId;
        $individual->user_id = $userId;
        $individual->gedcom_xref = $data['GEDCOM Xref'] ?? Str::uuid()->toString();
        $individual->given_name = $data['Given Name'];
        $individual->surname = $data['Surname'];
        $individual->nickname = $data['Nickname'] ?? null;
        $individual->sex = $data['Sex'] ?? Individual::SEX_UNKNOWN;
        $individual->birth_date = $data['Birth Date'] ?? null;
        $individual->birth_place = $data['Birth Place'] ?? null;
        $individual->death_date = $data['Death Date'] ?? null;
        $individual->death_place = $data['Death Place'] ?? null;
        $individual->occupation = $data['Occupation'] ?? null;
        $individual->note = $data['Note'] ?? null;
        $individual->save();

        return true;
    }

    /**
     * Import a family from a CSV row.
     *
     * @param  array  $data  The row data
     * @param  int  $treeId  Tree ID to associate with imported data
     * @return bool Whether the import was successful
     */
    protected function importFamilyFromCsvRow(array $data, int $treeId): bool
    {
        $family = new Family();
        $family->tree_id = $treeId;
        $family->gedcom_xref = $data['GEDCOM Xref'] ?? Str::uuid()->toString();

        // Look up husband and wife by name if IDs are not provided
        if (! empty($data['Husband Name']) && empty($data['Husband ID'])) {
            $husband = $this->findIndividualByName($data['Husband Name'], $treeId);
            if ($husband) {
                $family->husband_id = $husband->id;
            }
        } else {
            $family->husband_id = $data['Husband ID'] ?? null;
        }

        if (! empty($data['Wife Name']) && empty($data['Wife ID'])) {
            $wife = $this->findIndividualByName($data['Wife Name'], $treeId);
            if ($wife) {
                $family->wife_id = $wife->id;
            }
        } else {
            $family->wife_id = $data['Wife ID'] ?? null;
        }

        $family->marriage_date = $data['Marriage Date'] ?? null;
        $family->marriage_place = $data['Marriage Place'] ?? null;
        $family->marriage_type = $data['Marriage Type'] ?? null;
        $family->divorce_date = $data['Divorce Date'] ?? null;
        $family->divorce_place = $data['Divorce Place'] ?? null;
        $family->save();

        return true;
    }

    /**
     * Import a source from a CSV row.
     *
     * @param  array  $data  The row data
     * @param  int  $treeId  Tree ID to associate with imported data
     * @param  int  $userId  User ID to associate with imported data
     * @return bool Whether the import was successful
     */
    protected function importSourceFromCsvRow(array $data, int $treeId, int $userId): bool
    {
        $validator = Validator::make($data, [
            'Title' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::warning('Invalid source data in CSV: '.json_encode($validator->errors()->toArray()));

            return false;
        }

        $source = new Source();
        $source->tree_id = $treeId;
        $source->user_id = $userId;
        $source->gedcom_xref = $data['GEDCOM Xref'] ?? Str::uuid()->toString();
        $source->title = $data['Title'];
        $source->author = $data['Author'] ?? null;
        $source->publication = $data['Publication'] ?? null;
        $source->call_number = $data['Call Number'] ?? null;
        $source->data_quality = $data['Data Quality'] ?? null;
        $source->citation = $data['Citation'] ?? null;

        // Look up repository by name if ID is not provided
        if (! empty($data['Repository Name']) && empty($data['Repository ID'])) {
            $repository = Repository::where('name', $data['Repository Name'])
                ->where('tree_id', $treeId)
                ->first();

            if ($repository) {
                $source->repository_id = $repository->id;
            }
        } else {
            $source->repository_id = $data['Repository ID'] ?? null;
        }

        $source->save();

        return true;
    }

    /**
     * Import a repository from a CSV row.
     *
     * @param  array  $data  The row data
     * @param  int  $treeId  Tree ID to associate with imported data
     * @return bool Whether the import was successful
     */
    protected function importRepositoryFromCsvRow(array $data, int $treeId): bool
    {
        $validator = Validator::make($data, [
            'Name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::warning('Invalid repository data in CSV: '.json_encode($validator->errors()->toArray()));

            return false;
        }

        $repository = new Repository();
        $repository->tree_id = $treeId;
        $repository->gedcom_xref = $data['GEDCOM Xref'] ?? Str::uuid()->toString();
        $repository->name = $data['Name'];
        $repository->address = $data['Address'] ?? null;
        $repository->save();

        return true;
    }

    /**
     * Import a media item from a CSV row.
     *
     * @param  array  $data  The row data
     * @param  int  $treeId  Tree ID to associate with imported data
     * @param  int  $userId  User ID to associate with imported data
     * @return bool Whether the import was successful
     */
    protected function importMediaFromCsvRow(array $data, int $treeId, int $userId): bool
    {
        $validator = Validator::make($data, [
            'Title' => 'required|string|max:255',
            'File Name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            Log::warning('Invalid media data in CSV: '.json_encode($validator->errors()->toArray()));

            return false;
        }

        $media = new Media();
        $media->tree_id = $treeId;
        $media->user_id = $userId;
        $media->title = $data['Title'];
        $media->description = $data['Description'] ?? null;
        $media->file_name = $data['File Name'];
        $media->file_type = $data['File Type'] ?? '';
        $media->file_path = $data['File Path'] ?? '';
        $media->save();

        return true;
    }

    /**
     * Find an individual by name.
     *
     * @param  string  $name  The name to search for
     * @param  int  $treeId  Tree ID to limit search
     * @return Individual|null The found individual or null
     */
    protected function findIndividualByName(string $name, int $treeId): ?Individual
    {
        // Split the name into given name and surname
        $nameParts = explode(' ', $name, 2);

        if (count($nameParts) === 2) {
            $givenName = $nameParts[0];
            $surname = $nameParts[1];

            return Individual::where('tree_id', $treeId)
                ->where('given_name', $givenName)
                ->where('surname', $surname)
                ->first();
        }

        // If name can't be split, search in both fields
        return Individual::where('tree_id', $treeId)
            ->where(function ($query) use ($name) {
                $query->where('given_name', $name)
                    ->orWhere('surname', $name);
            })
            ->first();
    }
}
