<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\ImportProgress;
use App\Models\Tree;
use App\Models\User;
use App\Notifications\GedcomImportCompleted;
use App\Notifications\GedcomImportFailed;
use App\Services\GedcomMultiDatabaseService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class ImportGedcomJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1800; // 30 minutes

    public int $tries = 3;

    public int $maxExceptions = 3;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected string $filePath,
        protected int $treeId,
        protected int $userId,
        protected ?string $originalFileName = null
    ) {
        $this->onQueue('imports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $importProgress = null;

        try {
            Log::info('Starting GEDCOM import job', [
                'file_path' => $this->filePath,
                'tree_id' => $this->treeId,
                'user_id' => $this->userId,
            ]);

            // Create or update import progress
            $importProgress = ImportProgress::updateOrCreate(
                [
                    'user_id' => $this->userId,
                    'tree_id' => $this->treeId,
                ],
                [
                    'status' => ImportProgress::STATUS_PROCESSING,
                    'processed_records' => 0,
                    'error_message' => null,
                ]
            );

            // Update progress - Reading file
            $importProgress->update([
                'processed_records' => 0,
                'total_records' => 0,
                'status_message' => 'Reading GEDCOM file...',
            ]);

            // Read the GEDCOM file
            if (! Storage::disk('local')->exists($this->filePath)) {
                throw new Exception("GEDCOM file not found at path: {$this->filePath}");
            }

            $content = Storage::disk('local')->get($this->filePath);
            if ($content === null || $content === false) {
                throw new Exception("Failed to read GEDCOM file content from path: {$this->filePath}");
            }

            if (empty(mb_trim($content))) {
                throw new Exception("GEDCOM file is empty at path: {$this->filePath}");
            }

            // Update progress - Processing file
            $importProgress->update([
                'status_message' => 'Processing GEDCOM data...',
            ]);

            // Use the multi-database service for import
            $gedcomService = app(GedcomMultiDatabaseService::class);
            $importResults = $gedcomService->importGedcomData($content, $this->treeId);

            // Update progress to completed
            $totalRecords = $importResults['postgresql']['individuals'] + $importResults['postgresql']['families'];
            $importProgress->update([
                'status' => ImportProgress::STATUS_COMPLETED,
                'processed_records' => $totalRecords,
                'total_records' => $totalRecords,
                'status_message' => 'Import completed successfully',
            ]);

            // Get the tree and user for notification
            $tree = Tree::findOrFail($this->treeId);
            $user = User::findOrFail($this->userId);

            // Clean up the uploaded file
            Storage::disk('local')->delete($this->filePath);

            // Send success notification with cleaned file path
            $user->notify(new GedcomImportCompleted(
                $tree,
                [
                    'individuals' => $importResults['postgresql']['individuals'],
                    'families' => $importResults['postgresql']['families'],
                ],
                $this->originalFileName,
                $importResults['cleaned_file_path'] ?? null
            ));

            Log::info('GEDCOM import completed successfully', [
                'tree_id' => $this->treeId,
                'user_id' => $this->userId,
                'individuals_count' => $importResults['postgresql']['individuals'],
                'families_count' => $importResults['postgresql']['families'],
                'cleaned_file_path' => $importResults['cleaned_file_path'] ?? null,
                'duration_seconds' => $importResults['duration'] ?? 0,
            ]);

        } catch (Exception $e) {
            Log::error('GEDCOM import failed', [
                'file_path' => $this->filePath,
                'tree_id' => $this->treeId,
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Update progress to failed with truncated error message
            if ($importProgress) {
                // Truncate error message to prevent database field overflow
                $errorMessage = $e->getMessage();
                if (mb_strlen($errorMessage) > 1000) {
                    $errorMessage = mb_substr($errorMessage, 0, 997).'...';
                }

                $importProgress->update([
                    'status' => ImportProgress::STATUS_FAILED,
                    'error_message' => $errorMessage,
                    'status_message' => 'Import failed: '.mb_substr($errorMessage, 0, 200),
                ]);
            }

            // Clean up the uploaded file on failure
            if (Storage::disk('local')->exists($this->filePath)) {
                Storage::disk('local')->delete($this->filePath);
            }

            // Send failure notification
            $user = User::find($this->userId);
            if ($user) {
                $user->notify(new GedcomImportFailed($this->originalFileName, $e->getMessage()));
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(Throwable $exception): void
    {
        Log::error('GEDCOM import job failed permanently', [
            'file_path' => $this->filePath,
            'tree_id' => $this->treeId,
            'user_id' => $this->userId,
            'error' => $exception->getMessage(),
        ]);

        // Update progress to failed with truncated error message
        $errorMessage = $exception->getMessage();
        if (mb_strlen($errorMessage) > 1000) {
            $errorMessage = mb_substr($errorMessage, 0, 997).'...';
        }

        ImportProgress::where([
            'user_id' => $this->userId,
            'tree_id' => $this->treeId,
        ])->update([
            'status' => ImportProgress::STATUS_FAILED,
            'error_message' => $errorMessage,
        ]);

        // Clean up the uploaded file
        if (Storage::disk('local')->exists($this->filePath)) {
            Storage::disk('local')->delete($this->filePath);
        }

        // Send failure notification
        $user = User::find($this->userId);
        if ($user) {
            $user->notify(new GedcomImportFailed($this->originalFileName, $exception->getMessage()));
        }
    }
}
