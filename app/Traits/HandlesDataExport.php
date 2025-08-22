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
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Trait for handling data export operations
 */
trait HandlesDataExport
{
    /**
     * Export tree data to JSON format.
     *
     * @param Tree $tree The tree to export
     * @param bool $includeMedia Whether to include media references
     * @param bool $includePrivateData Whether to include private data
     * @param string $disk Storage disk to use
     * @return string The path to the generated JSON file
     */
    protected function exportTreeToJson(
        Tree $tree,
        bool $includeMedia = true,
        bool $includePrivateData = false,
        string $disk = 'local'
    ): string {
        // Generate a unique filename
        $filename = 'tree_' . $tree->id . '_' . Str::uuid() . '.json';
        $directory = 'exports';
        $path = $directory . '/' . $filename;
        
        // Ensure the directory exists
        Storage::disk($disk)->makeDirectory($directory);
        
        // Build the export data
        $exportData = [
            'tree' => $this->formatTreeForExport($tree),
            'individuals' => [],
            'families' => [],
            'sources' => [],
            'repositories' => [],
            'notes' => [],
        ];
        
        // Add individuals
        $individuals = Individual::where('tree_id', $tree->id)->get();
        foreach ($individuals as $individual) {
            $exportData['individuals'][] = $this->formatIndividualForExport($individual, $includePrivateData);
        }
        
        // Add families
        $families = Family::where('tree_id', $tree->id)->get();
        foreach ($families as $family) {
            $exportData['families'][] = $this->formatFamilyForExport($family, $includePrivateData);
        }
        
        // Add sources
        $sources = Source::where('tree_id', $tree->id)->get();
        foreach ($sources as $source) {
            $exportData['sources'][] = $this->formatSourceForExport($source);
        }
        
        // Add repositories
        $repositories = Repository::where('tree_id', $tree->id)->get();
        foreach ($repositories as $repository) {
            $exportData['repositories'][] = $this->formatRepositoryForExport($repository);
        }
        
        // Add notes
        $notes = \App\Models\Note::where('tree_id', $tree->id)->get();


        foreach ($notes as $note) {
            $exportData['notes'][] = $this->formatNoteForExport($note);
        }
        
        // Add media if requested
        if ($includeMedia) {
            $exportData['media'] = [];
            $media = Media::where('tree_id', $tree->id)->get();
            foreach ($media as $mediaItem) {
                $exportData['media'][] = $this->formatMediaForExport($mediaItem);
            }
        }
        
        // Write the JSON content to the file
        Storage::disk($disk)->put($path, json_encode($exportData, JSON_PRETTY_PRINT));
        
        return $path;
    }
    
    /**
     * Export tree data to CSV format.
     *
     * @param Tree $tree The tree to export
     * @param string $entityType The entity type to export (individuals, families, sources, etc.)
     * @param bool $includePrivateData Whether to include private data
     * @param string $disk Storage disk to use
     * @return string The path to the generated CSV file
     */
    protected function exportTreeToCsv(
        Tree $tree,
        string $entityType,
        bool $includePrivateData = false,
        string $disk = 'local'
    ): string {
        // Generate a unique filename
        $filename = 'tree_' . $tree->id . '_' . $entityType . '_' . Str::uuid() . '.csv';
        $directory = 'exports';
        $path = $directory . '/' . $filename;
        
        // Ensure the directory exists
        Storage::disk($disk)->makeDirectory($directory);
        
        // Get the data to export
        $data = $this->getEntityDataForCsvExport($tree, $entityType, $includePrivateData);
        
        // Create CSV content
        $csvContent = $this->generateCsvContent($data['headers'], $data['rows']);
        
        // Write the CSV content to the file
        Storage::disk($disk)->put($path, $csvContent);
        
        return $path;
    }
    
    /**
     * Generate CSV content from headers and rows.
     *
     * @param array $headers The CSV headers
     * @param array $rows The CSV data rows
     * @return string The generated CSV content
     */
    protected function generateCsvContent(array $headers, array $rows): string
    {
        $handle = fopen('php://temp', 'r+');
        
        // Add headers
        fputcsv($handle, $headers);
        
        // Add data rows
        foreach ($rows as $row) {
            fputcsv($handle, $row);
        }
        
        // Get the content
        rewind($handle);
        $content = stream_get_contents($handle);
        fclose($handle);
        
        return $content;
    }
    
    /**
     * Get entity data for CSV export.
     *
     * @param Tree $tree The tree
     * @param string $entityType The entity type to export
     * @param bool $includePrivateData Whether to include private data
     * @return array The headers and rows for CSV export
     */
    protected function getEntityDataForCsvExport(
        Tree $tree,
        string $entityType,
        bool $includePrivateData = false
    ): array {
        $headers = [];
        $rows = [];
        
        switch ($entityType) {
            case 'individuals':
                $headers = [
                    'ID', 'GEDCOM Xref', 'Given Name', 'Surname', 'Nickname', 'Sex',
                    'Birth Date', 'Birth Place', 'Death Date', 'Death Place', 'Occupation', 'Note'
                ];
                
                $individuals = Individual::where('tree_id', $tree->id)->get();
                foreach ($individuals as $individual) {
                    $row = [
                        $individual->id,
                        $individual->gedcom_xref,
                        $individual->given_name,
                        $individual->surname,
                        $individual->nickname,
                        $individual->sex,
                        $individual->birth_date,
                        $individual->birth_place,
                        $individual->death_date,
                        $individual->death_place,
                        $individual->occupation,
                        $individual->note,
                    ];
                    $rows[] = $row;
                }
                break;
                
            case 'families':
                $headers = [
                    'ID', 'GEDCOM Xref', 'Husband ID', 'Husband Name', 'Wife ID', 'Wife Name',
                    'Marriage Date', 'Marriage Place', 'Marriage Type', 'Divorce Date', 'Divorce Place'
                ];
                
                $families = Family::where('tree_id', $tree->id)->get();
                foreach ($families as $family) {
                    $row = [
                        $family->id,
                        $family->gedcom_xref,
                        $family->husband_id,
                        $family->husband ? $family->husband->display_name : '',
                        $family->wife_id,
                        $family->wife ? $family->wife->display_name : '',
                        $family->marriage_date,
                        $family->marriage_place,
                        $family->marriage_type,
                        $family->divorce_date,
                        $family->divorce_place,
                    ];
                    $rows[] = $row;
                }
                break;
                
            case 'sources':
                $headers = [
                    'ID', 'GEDCOM Xref', 'Title', 'Author', 'Publication',
                    'Repository ID', 'Repository Name', 'Call Number', 'Data Quality', 'Citation'
                ];
                
                $sources = Source::where('tree_id', $tree->id)->get();
                foreach ($sources as $source) {
                    $row = [
                        $source->id,
                        $source->gedcom_xref,
                        $source->title,
                        $source->author,
                        $source->publication,
                        $source->repository_id,
                        $source->repository ? $source->repository->name : '',
                        $source->call_number,
                        $source->data_quality,
                        $source->citation,
                    ];
                    $rows[] = $row;
                }
                break;
                
            case 'repositories':
                $headers = ['ID', 'GEDCOM Xref', 'Name', 'Address'];
                
                $repositories = Repository::where('tree_id', $tree->id)->get();
                foreach ($repositories as $repository) {
                    $row = [
                        $repository->id,
                        $repository->gedcom_xref,
                        $repository->name,
                        $repository->address,
                    ];
                    $rows[] = $row;
                }
                break;
                
            case 'media':
                $headers = ['ID', 'Title', 'Description', 'File Name', 'File Type', 'File Path'];
                
                $media = Media::where('tree_id', $tree->id)->get();
                foreach ($media as $mediaItem) {
                    $row = [
                        $mediaItem->id,
                        $mediaItem->title,
                        $mediaItem->description,
                        $mediaItem->file_name,
                        $mediaItem->file_type,
                        $mediaItem->file_path,
                    ];
                    $rows[] = $row;
                }
                break;
                
            default:
                // Unknown entity type
                $headers = ['Error'];
                $rows[] = ['Unknown entity type: ' . $entityType];
                break;
        }
        
        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }
    
    /**
     * Format a tree for export.
     *
     * @param Tree $tree The tree to format
     * @return array The formatted tree data
     */
    protected function formatTreeForExport(Tree $tree): array
    {
        return [
            'id' => $tree->id,
            'name' => $tree->name,
            'description' => $tree->description,
            'is_public' => $tree->is_public,
            'created_at' => $tree->created_at->toIso8601String(),
            'updated_at' => $tree->updated_at->toIso8601String(),
            'user_id' => $tree->user_id,
            'user_name' => $tree->user ? $tree->user->name : null,
        ];
    }
    
    /**
     * Format an individual for export.
     *
     * @param Individual $individual The individual to format
     * @param bool $includePrivateData Whether to include private data
     * @return array The formatted individual data
     */
    protected function formatIndividualForExport(Individual $individual, bool $includePrivateData = false): array
    {
        $data = [
            'id' => $individual->id,
            'gedcom_xref' => $individual->gedcom_xref,
            'given_name' => $individual->given_name,
            'surname' => $individual->surname,
            'nickname' => $individual->nickname,
            'sex' => $individual->sex,
            'birth_date' => $individual->birth_date,
            'birth_place' => $individual->birth_place,
            'death_date' => $individual->death_date,
            'death_place' => $individual->death_place,
            'occupation' => $individual->occupation,
            'note' => $individual->note,
            'families_as_husband' => $individual->familiesAsHusband->pluck('id')->toArray(),
            'families_as_wife' => $individual->familiesAsWife->pluck('id')->toArray(),
            'families_as_child' => $individual->familiesAsChild->pluck('id')->toArray(),
        ];
        
        // Include private data if requested
        if ($includePrivateData) {
            $data['created_at'] = $individual->created_at->toIso8601String();
            $data['updated_at'] = $individual->updated_at->toIso8601String();
            $data['user_id'] = $individual->user_id;
        }
        
        return $data;
    }
    
    /**
     * Format a family for export.
     *
     * @param Family $family The family to format
     * @param bool $includePrivateData Whether to include private data
     * @return array The formatted family data
     */
    protected function formatFamilyForExport(Family $family, bool $includePrivateData = false): array
    {
        $data = [
            'id' => $family->id,
            'gedcom_xref' => $family->gedcom_xref,
            'husband_id' => $family->husband_id,
            'wife_id' => $family->wife_id,
            'marriage_date' => $family->marriage_date,
            'marriage_place' => $family->marriage_place,
            'marriage_type' => $family->marriage_type,
            'divorce_date' => $family->divorce_date,
            'divorce_place' => $family->divorce_place,
            'children' => $family->children->pluck('id')->toArray(),
        ];
        
        // Include private data if requested
        if ($includePrivateData) {
            $data['created_at'] = $family->created_at->toIso8601String();
            $data['updated_at'] = $family->updated_at->toIso8601String();
        }
        
        return $data;
    }
    
    /**
     * Format a source for export.
     *
     * @param Source $source The source to format
     * @return array The formatted source data
     */
    protected function formatSourceForExport(Source $source): array
    {
        return [
            'id' => $source->id,
            'gedcom_xref' => $source->gedcom_xref,
            'title' => $source->title,
            'author' => $source->author,
            'publication' => $source->publication,
            'repository_id' => $source->repository_id,
            'call_number' => $source->call_number,
            'data_quality' => $source->data_quality,
            'citation' => $source->citation,
            'created_at' => $source->created_at->toIso8601String(),
            'updated_at' => $source->updated_at->toIso8601String(),
            'user_id' => $source->user_id,
        ];
    }
    
    /**
     * Format a repository for export.
     *
     * @param Repository $repository The repository to format
     * @return array The formatted repository data
     */
    protected function formatRepositoryForExport(Repository $repository): array
    {
        return [
            'id' => $repository->id,
            'gedcom_xref' => $repository->gedcom_xref,
            'name' => $repository->name,
            'address' => $repository->address,
            'created_at' => $repository->created_at->toIso8601String(),
            'updated_at' => $repository->updated_at->toIso8601String(),
            'tree_id' => $repository->tree_id,
        ];
    }
    
    /**
     * Format a note for export.
     *
     * @param Note $note The note to format
     * @return array The formatted note data
     */
    protected function formatNoteForExport(Note $note): array
    {
        return [
            'id' => $note->id,
            'gedcom_xref' => $note->gedcom_xref,
            'text' => $note->text,
            'created_at' => $note->created_at->toIso8601String(),
            'updated_at' => $note->updated_at->toIso8601String(),
            'tree_id' => $note->tree_id,
            'user_id' => $note->user_id,
        ];
    }
    
    /**
     * Format a media item for export.
     *
     * @param Media $media The media item to format
     * @return array The formatted media data
     */
    protected function formatMediaForExport(Media $media): array
    {
        return [
            'id' => $media->id,
            'title' => $media->title,
            'description' => $media->description,
            'file_name' => $media->file_name,
            'file_type' => $media->file_type,
            'file_path' => $media->file_path,
            'created_at' => $media->created_at->toIso8601String(),
            'updated_at' => $media->updated_at->toIso8601String(),
            'tree_id' => $media->tree_id,
            'user_id' => $media->user_id,
        ];
    }
}