<?php

declare(strict_types=1);

/**
 * Script to check for duplicate method declarations in PHP files
 */

// Directory to scan
$directory = __DIR__.'/app/Traits';

// Get all PHP files in the directory
$files = glob($directory.'/*.php');

// Array to store method declarations
$methods = [];

// Process each file
foreach ($files as $file) {
    $content = file_get_contents($file);
    $filename = basename($file);

    // Extract method declarations using regex
    preg_match_all('/function\s+([a-zA-Z0-9_]+)\s*\(/i', $content, $matches, PREG_OFFSET_CAPTURE);

    if (! empty($matches[1])) {
        foreach ($matches[1] as $index => $match) {
            $methodName = $match[0];
            $position = $matches[0][$index][1];

            // Calculate line number
            $line = mb_substr_count(mb_substr($content, 0, $position), "\n") + 1;

            // Store method information
            if (! isset($methods[$methodName])) {
                $methods[$methodName] = [];
            }

            $methods[$methodName][] = [
                'file' => $filename,
                'line' => $line,
            ];
        }
    }
}

// Check for duplicates
$duplicates = [];
foreach ($methods as $methodName => $declarations) {
    if (count($declarations) > 1) {
        // Check if duplicates are in the same file
        $fileGroups = [];
        foreach ($declarations as $declaration) {
            $fileGroups[$declaration['file']][] = $declaration['line'];
        }

        foreach ($fileGroups as $file => $lines) {
            if (count($lines) > 1) {
                $duplicates[] = [
                    'method' => $methodName,
                    'file' => $file,
                    'lines' => $lines,
                ];
            }
        }
    }
}

// Output results
if (empty($duplicates)) {
    echo "No duplicate method declarations found.\n";
} else {
    echo 'Found '.count($duplicates)." methods with duplicate declarations:\n\n";

    foreach ($duplicates as $duplicate) {
        echo "Method: {$duplicate['method']}\n";
        echo "File: {$duplicate['file']}\n";
        echo 'Lines: '.implode(', ', $duplicate['lines'])."\n\n";
    }
}
