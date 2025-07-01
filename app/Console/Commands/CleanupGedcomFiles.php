<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class CleanupGedcomFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gedcom:cleanup 
                            {--days=30 : Number of days to keep cleaned files}
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old cleaned GEDCOM files from storage';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');
        $cutoffDate = now()->subDays($days);

        $cleanedDir = storage_path('app/gedcom/cleaned');

        if (! is_dir($cleanedDir)) {
            $this->info('No cleaned GEDCOM files directory found.');

            return 0;
        }

        $deletedCount = 0;
        $deletedSize = 0;

        // Recursively find all .ged files
        $files = File::allFiles($cleanedDir);

        foreach ($files as $file) {
            if ($file->getExtension() === 'ged') {
                $fileModified = \Carbon\Carbon::createFromTimestamp($file->getMTime());

                if ($fileModified->lt($cutoffDate)) {
                    $fileSize = $file->getSize();

                    if ($dryRun) {
                        $this->line("Would delete: {$file->getPathname()} (modified: {$fileModified->format('Y-m-d H:i:s')})");
                    } else {
                        if (File::delete($file->getPathname())) {
                            $this->line("Deleted: {$file->getPathname()}");
                            $deletedCount++;
                            $deletedSize += $fileSize;
                        } else {
                            $this->error("Failed to delete: {$file->getPathname()}");
                        }
                    }
                }
            }
        }

        // Clean up empty directories
        if (! $dryRun) {
            $this->cleanupEmptyDirectories($cleanedDir);
        }

        if ($dryRun) {
            $this->info("Dry run completed. Found {$deletedCount} files that would be deleted.");
        } else {
            $this->info("Cleanup completed. Deleted {$deletedCount} files (".$this->formatBytes($deletedSize).').');
        }

        return 0;
    }

    /**
     * Recursively remove empty directories
     */
    private function cleanupEmptyDirectories(string $directory): void
    {
        $items = scandir($directory);

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory.DIRECTORY_SEPARATOR.$item;

            if (is_dir($path)) {
                $this->cleanupEmptyDirectories($path);

                // Remove directory if it's empty
                if (count(scandir($path)) === 2) { // Only . and ..
                    rmdir($path);
                    $this->line("Removed empty directory: {$path}");
                }
            }
        }
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }
}
