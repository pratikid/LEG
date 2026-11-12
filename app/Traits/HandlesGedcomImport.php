<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\ImportProgress;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * Trait for handling GEDCOM import operations
 */
trait HandlesGedcomImport
{
    use HandlesGedcomDates;

    /**
     * Clean and store the GEDCOM file content.
     *
     * @param  string  $gedcomContent  The raw GEDCOM content
     * @param  string  $filename  The name of the file
     * @return string The path to the stored file
     */
    protected function cleanAndStoreGedcomContent(string $gedcomContent, string $filename): string
    {
        // Remove BOM if present
        $gedcomContent = $this->removeBOM($gedcomContent);

        // Normalize line endings
        $gedcomContent = $this->normalizeLineEndings($gedcomContent);

        // Store the cleaned content
        $path = 'gedcom/'.$filename;
        Storage::put($path, $gedcomContent);

        return $path;
    }

    /**
     * Remove Byte Order Mark (BOM) from the content.
     *
     * @param  string  $content  The content to process
     * @return string The content without BOM
     */
    protected function removeBOM(string $content): string
    {
        // UTF-8 BOM is 0xEF 0xBB 0xBF
        if (mb_substr($content, 0, 3) === "\xEF\xBB\xBF") {
            return mb_substr($content, 3);
        }

        return $content;
    }

    /**
     * Normalize line endings to LF.
     *
     * @param  string  $content  The content to process
     * @return string The content with normalized line endings
     */
    protected function normalizeLineEndings(string $content): string
    {
        // Convert all line endings to LF
        return str_replace(["\r\n", "\r"], "\n", $content);
    }

    /**
     * Handle GEDCOM import failure.
     *
     * @param  ImportProgress  $progress  The import progress record
     * @param  Exception  $exception  The exception that occurred
     * @param  string|null  $filePath  The path to the GEDCOM file
     */
    protected function handleImportFailure(ImportProgress $progress, Exception $exception, ?string $filePath = null): void
    {
        // Update import progress
        $progress->update([
            'status' => ImportProgress::STATUS_FAILED,
            'error_message' => $exception->getMessage(),
        ]);

        // Log the error
        Log::error('GEDCOM import failed', [
            'message' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
            'file_path' => $filePath,
            'user_id' => $progress->user_id,
            'tree_id' => $progress->tree_id,
        ]);

        // Clean up the file if needed
        if ($filePath && Storage::exists($filePath)) {
            Storage::delete($filePath);
        }
    }

    /**
     * Extract level, tag, and value from a GEDCOM line.
     *
     * @param  string  $line  The GEDCOM line to parse
     * @return array{level: int, tag: string, value: string} The parsed components
     */
    protected function parseGedcomLine(string $line): array
    {
        $line = mb_trim($line);

        if (empty($line)) {
            return ['level' => 0, 'tag' => '', 'value' => ''];
        }

        // Basic pattern: level + tag + optional value
        if (preg_match('/^(\d+)\s+([A-Z0-9_]+)(?:\s+(.*))?$/', $line, $matches)) {
            return [
                'level' => (int) $matches[1],
                'tag' => $matches[2],
                'value' => $matches[3] ?? '',
            ];
        }

        // Handle pointer references
        if (preg_match('/^(\d+)\s+(@[A-Z0-9:]+@)\s+([A-Z0-9_]+)(?:\s+(.*))?$/', $line, $matches)) {
            return [
                'level' => (int) $matches[1],
                'tag' => $matches[3],
                'value' => $matches[2].(isset($matches[4]) ? ' '.$matches[4] : ''),
            ];
        }

        // Default fallback
        return ['level' => 0, 'tag' => 'UNKNOWN', 'value' => $line];
    }

    /**
     * Update import progress.
     *
     * @param  ImportProgress  $progress  The import progress record
     * @param  int  $processedRecords  The number of processed records
     * @param  string|null  $statusMessage  Optional status message
     */
    protected function updateImportProgress(ImportProgress $progress, int $processedRecords, ?string $statusMessage = null): void
    {
        $progress->update([
            'processed_records' => $processedRecords,
            'status_message' => $statusMessage ?: "Processed $processedRecords of {$progress->total_records} records",
        ]);
    }
}
