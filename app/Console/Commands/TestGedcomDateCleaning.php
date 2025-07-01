<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\GedcomService;
use Illuminate\Console\Command;

final class TestGedcomDateCleaning extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gedcom:test-dates 
                            {--sample : Test with sample date formats}
                            {--file= : Test with a specific GEDCOM file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test GEDCOM date cleaning functionality';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Testing GEDCOM date cleaning...');

        $gedcomService = new GedcomService();

        $testDates = [
            '1293' => 'year_only',
            '1383' => 'year_only',
            '794' => 'year_only',
            '1078' => 'year_only',
            '1141' => 'year_only',
            '1076 or 1088' => 'year_range_or',
            '1030 or 36' => 'year_range_or',
            'abt. 1066 or 1094' => 'approximate_range',
            'BEF 969' => 'before_year',
            'AFT 1200' => 'after_year',
            'BEF 15 JAN 1200' => 'before_full_date',
            'AFT 20 DEC 1300' => 'after_full_date',
            'ABT 1100' => 'approximate_year',
            'EST 1150' => 'estimated_year',
            'CIR 1200' => 'circa_year',
            'BET 1100 AND 1200' => 'between_years',
            '1100-1200' => 'year_range',
            '15 JAN 1200' => 'full_date',
            '20 DEC 1300' => 'full_date',
            'UNKNOWN' => 'unknown_date',
            'UNK' => 'unknown_date',
            '?' => 'unknown_date',
            'null' => 'null_date',
            '' => 'empty_date',
        ];

        $this->info('Testing date cleaning:');
        $this->line('');

        foreach ($testDates as $originalDate => $expectedType) {
            $cleanedDate = $gedcomService->cleanGedcomDate($originalDate);
            $status = $cleanedDate !== $originalDate ? '✓ CLEANED' : '○ UNCHANGED';

            $this->line(sprintf(
                '%-20s → %-20s [%s] (%s)',
                $originalDate,
                $cleanedDate,
                $status,
                $expectedType
            ));
        }

        $this->line('');
        $this->info('Date cleaning test completed!');

        return 0;
    }

    /**
     * Test with sample date formats
     */
    private function testSampleDates(GedcomService $service): void
    {
        $this->info('Testing GEDCOM Date Cleaning with Sample Data');
        $this->line('');

        $testCases = [
            // Year-only dates
            '1980' => '1980',
            '1850' => '1850',
            '9999' => '9999', // Invalid year
            '500' => '500',   // Invalid year

            // Approximate dates
            'ABT 1850' => 'ABT 1850',
            'EST 1900' => 'EST 1900',
            'ABOUT 1800' => 'ABT 1800',
            'APPROXIMATELY 1950' => 'APPROX 1950',

            // Year ranges
            '1980-1990' => '1980-1990',
            'ABT 1850-1860' => 'ABT 1850-1860',
            '1900-1890' => '1900-1890', // Invalid range

            // Between dates
            'BET 1980 AND 1990' => 'BET 1980 AND 1990',
            'BET 1900 AND 1890' => 'BET 1900 AND 1890', // Invalid range

            // Full dates
            '15 Jan 1980' => '15 JAN 1980',
            '20 February 1850' => '20 FEB 1850',
            '1 March 1900' => '1 MAR 1900',

            // Before/After dates
            'BEF 1980' => 'BEF 1980',
            'AFT 1850' => 'AFT 1850',
            'BEF ABT 1900' => 'BEF ABT 1900',

            // Unknown dates
            'UNKNOWN' => 'UNKNOWN',
            'UNK' => 'UNKNOWN',
            '?' => 'UNKNOWN',

            // Edge cases
            '' => '',
            'null' => '',
            'NULL' => '',
        ];

        $this->table(
            ['Original Date', 'Cleaned Date', 'Status'],
            collect($testCases)->map(function ($expected, $original) use ($service) {
                $cleaned = $service->cleanGedcomDate($original);
                $status = $cleaned === $expected ? '✅ PASS' : '❌ FAIL';

                return [
                    $original,
                    $cleaned,
                    $status,
                ];
            })->toArray()
        );

        $this->line('');
        $this->info('Sample date testing completed!');
    }

    /**
     * Test with a specific GEDCOM file
     */
    private function testFileDates(GedcomService $service, string $filePath): void
    {
        if (! file_exists($filePath)) {
            $this->error("File not found: {$filePath}");

            return;
        }

        $this->info("Testing GEDCOM Date Cleaning with file: {$filePath}");
        $this->line('');

        $content = file_get_contents($filePath);
        if ($content === false) {
            $this->error("Failed to read file: {$filePath}");

            return;
        }

        // Extract all DATE lines
        $lines = explode("\n", $content);
        $dateLines = [];
        $cleanedLines = [];

        foreach ($lines as $line) {
            if (preg_match('/^(\d+)\s+(@[^@]+@)?\s*DATE\s+(.+)$/', mb_trim($line), $matches)) {
                $level = $matches[1];
                $xref = $matches[2] ?? '';
                $originalDate = mb_trim($matches[3]);
                $cleanedDate = $service->cleanGedcomDate($originalDate);

                $dateLines[] = [
                    'Level' => $level,
                    'XRef' => $xref,
                    'Original Date' => $originalDate,
                    'Cleaned Date' => $cleanedDate,
                    'Changed' => $originalDate !== $cleanedDate ? 'Yes' : 'No',
                ];

                $cleanedLines[] = "{$level} {$xref} DATE {$cleanedDate}";
            }
        }

        if (empty($dateLines)) {
            $this->warn('No DATE lines found in the file.');

            return;
        }

        $this->table(
            ['Level', 'XRef', 'Original Date', 'Cleaned Date', 'Changed'],
            $dateLines
        );

        $changedCount = count(array_filter($dateLines, fn ($line) => $line['Changed'] === 'Yes'));
        $totalCount = count($dateLines);

        $this->line('');
        $this->info('Summary:');
        $this->line("- Total DATE lines: {$totalCount}");
        $this->line("- Modified lines: {$changedCount}");
        $this->line('- Unchanged lines: '.($totalCount - $changedCount));

        // Save cleaned version
        $cleanedFilePath = $filePath.'.cleaned';
        $cleanedContent = $this->replaceDateLines($content, $cleanedLines);
        file_put_contents($cleanedFilePath, $cleanedContent);

        $this->line("- Cleaned file saved: {$cleanedFilePath}");
    }

    /**
     * Replace DATE lines in content with cleaned versions
     */
    private function replaceDateLines(string $content, array $cleanedLines): string
    {
        $lines = explode("\n", $content);
        $cleanedIndex = 0;

        for ($i = 0; $i < count($lines); $i++) {
            if (preg_match('/^(\d+)\s+(@[^@]+@)?\s*DATE\s+(.+)$/', mb_trim($lines[$i]), $matches)) {
                if (isset($cleanedLines[$cleanedIndex])) {
                    $lines[$i] = $cleanedLines[$cleanedIndex];
                    $cleanedIndex++;
                }
            }
        }

        return implode("\n", $lines);
    }
}
