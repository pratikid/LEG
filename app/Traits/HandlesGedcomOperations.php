<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Log;

/**
 * Trait for handling common GEDCOM operations
 */
trait HandlesGedcomOperations
{
    /**
     * Clean and standardize a GEDCOM date string
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
                $monthMap = [
                    'Jan' => 'JAN', 'Feb' => 'FEB', 'Mar' => 'MAR', 'Apr' => 'APR',
                    'May' => 'MAY', 'Jun' => 'JUN', 'Jul' => 'JUL', 'Aug' => 'AUG',
                    'Sep' => 'SEP', 'Oct' => 'OCT', 'Nov' => 'NOV', 'Dec' => 'DEC',
                    'January' => 'JAN', 'February' => 'FEB', 'March' => 'MAR', 'April' => 'APR',
                    'June' => 'JUN', 'July' => 'JUL', 'August' => 'AUG', 'September' => 'SEP',
                    'October' => 'OCT', 'November' => 'NOV', 'December' => 'DEC',
                ];
                $standardMonth = $monthMap[$month] ?? mb_strtoupper($month);
                $cleanedDate = $prefix ? "$prefix $day $standardMonth $year" : "$day $standardMonth $year";

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

        // Handle unknown dates
        if (preg_match('/^(UNKNOWN|UNK|\?)$/i', $dateString)) {
            return 'UNKNOWN';
        }

        // If we can't parse the date, return the original but log it
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
    protected function removeUserDefinedTags(string $gedcomContent): string
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
     * Process GEDCOM line
     */
    protected function processGedcomLine(array &$parsed, ?string $currentRecord, ?string $currentXref, int $level, string $tag, string $value, array &$context): void
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
}
