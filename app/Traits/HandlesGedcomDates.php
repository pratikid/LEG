<?php

declare(strict_types=1);

namespace App\Traits;

/**
 * Trait for handling GEDCOM date formatting and cleaning
 */
trait HandlesGedcomDates
{
    /**
     * Clean and standardize a GEDCOM date.
     *
     * @param  string|null  $date  The date to clean
     * @return string|null The cleaned date or null if invalid
     */
    protected function cleanGedcomDate(?string $date): ?string
    {
        if (! $date) {
            return null;
        }

        // Strip prefixes like 'ABT', 'EST', 'BEF', 'AFT', etc.
        $date = preg_replace('/^(ABT|EST|BEF|AFT|CAL|INT)\s+/i', '', $date);

        // Handle year-only dates
        if (preg_match('/^\d{4}$/', $date)) {
            return $date.'-01-01'; // Default to January 1st
        }

        // Handle year ranges like '1900-1950'
        if (preg_match('/^(\d{4})-(\d{4})$/', $date, $matches)) {
            return $matches[1].'-01-01'; // Use the first year
        }

        // Handle 'BETWEEN X AND Y' format
        if (preg_match('/BETWEEN\s+(.+)\s+AND\s+(.+)/i', $date, $matches)) {
            return $this->cleanGedcomDate($matches[1]); // Use the first date
        }

        // Handle 'FROM X TO Y' format
        if (preg_match('/FROM\s+(.+)\s+TO\s+(.+)/i', $date, $matches)) {
            return $this->cleanGedcomDate($matches[1]); // Use the first date
        }

        // Try to parse the date using strtotime
        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return null; // Unparseable date
        }

        return date('Y-m-d', $timestamp);
    }

    /**
     * Format a date for display.
     *
     * @param  string|null  $date  The date to format
     * @param  string  $format  The format to use (default: Y-m-d)
     * @return string|null The formatted date or null if invalid
     */
    protected function formatGedcomDate(?string $date, string $format = 'Y-m-d'): ?string
    {
        if (! $date) {
            return null;
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return null;
        }

        return date($format, $timestamp);
    }

    /**
     * Extract year from a date string.
     *
     * @param  string|null  $date  The date string
     * @return int|null The year or null if invalid
     */
    protected function extractYearFromDate(?string $date): ?int
    {
        if (! $date) {
            return null;
        }

        // Try to extract year from various formats
        if (preg_match('/\b(\d{4})\b/', $date, $matches)) {
            return (int) $matches[1];
        }

        $cleanDate = $this->cleanGedcomDate($date);
        if ($cleanDate && preg_match('/^(\d{4})/', $cleanDate, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }
}
