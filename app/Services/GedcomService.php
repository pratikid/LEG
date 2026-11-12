<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\HandlesGedcomOperations;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Service for parsing, importing, and exporting GEDCOM files.
 * Handles individuals, families, events, sources, notes, and relationships.
 */
final class GedcomService
{
    use HandlesGedcomOperations;

    /**
     * Parse GEDCOM content into structured arrays for individuals, families, sources, and notes.
     *
     * @param  string  $gedcomContent  Raw GEDCOM file content
     * @return array{
     *     individuals: array<string, array{
     *         name: string|null,
     *         sex: string|null,
     *         birth: array{date: string|null},
     *         death: array{date: string|null},
     *         fams: array<string>,
     *         famc: array<string>
     *     }>,
     *     families: array<string, array{
     *         husb: string|null,
     *         wife: string|null,
     *         chil: array<string>
     *     }>,
     *     sources: array<string, mixed>,
     *     notes: array<string, mixed>
     * }
     */
    public function parse(string $gedcomContent): array
    {
        // Remove user-defined tags before parsing
        $cleanedGedcomContent = $this->removeUserDefinedTags($gedcomContent);

        // Store cleaned content to file
        $cleanedFilePath = $this->storeCleanedGedcomContent($cleanedGedcomContent);

        Log::info('Stored cleaned GEDCOM content', [
            'cleaned_file_path' => $cleanedFilePath,
            'original_size' => mb_strlen($gedcomContent),
            'cleaned_size' => mb_strlen($cleanedGedcomContent),
        ]);

        $lines = preg_split('/\r?\n/', $cleanedGedcomContent);
        if ($lines === false) {
            return [
                'individuals' => [],
                'families' => [],
                'sources' => [],
                'notes' => [],
            ];
        }

        $individuals = [];
        $families = [];
        $current = null;
        $currentXref = null;
        $currentType = null;

        foreach ($lines as $line) {
            if (preg_match('/^(\d+) +(@[^@]+@) +(INDI|FAM)/', $line, $m)) {
                // New record
                $level = (int) $m[1];
                $currentXref = $m[2];
                $currentType = $m[3];
                if ($currentType === 'INDI') {
                    $individuals[$currentXref] = [
                        'name' => null,
                        'sex' => null,
                        'birth' => ['date' => null],
                        'death' => ['date' => null],
                        'fams' => [],
                        'famc' => [],
                    ];
                } elseif ($currentType === 'FAM') {
                    $families[$currentXref] = [
                        'husb' => null,
                        'wife' => null,
                        'chil' => [],
                    ];
                }

                continue;
            }
            if ($currentXref && $currentType === 'INDI') {
                if (preg_match('/^1 NAME (.+)$/', $line, $m)) {
                    $individuals[$currentXref]['name'] = $m[1];
                } elseif (preg_match('/^1 SEX ([MFU])/', $line, $m)) {
                    $individuals[$currentXref]['sex'] = $m[1];
                } elseif (preg_match('/^1 BIRT/', $line)) {
                    $current = 'birth';
                } elseif (preg_match('/^1 DEAT/', $line)) {
                    $current = 'death';
                } elseif (preg_match('/^2 DATE (.+)$/', $line, $m) && $current) {
                    $individuals[$currentXref][$current]['date'] = $m[1];
                } elseif (preg_match('/^1 FAMS (@[^@]+@)/', $line, $m)) {
                    $individuals[$currentXref]['fams'][] = $m[1];
                } elseif (preg_match('/^1 FAMC (@[^@]+@)/', $line, $m)) {
                    $individuals[$currentXref]['famc'][] = $m[1];
                }
            } elseif ($currentXref && $currentType === 'FAM') {
                if (preg_match('/^1 HUSB (@[^@]+@)/', $line, $m)) {
                    $families[$currentXref]['husb'] = $m[1];
                } elseif (preg_match('/^1 WIFE (@[^@]+@)/', $line, $m)) {
                    $families[$currentXref]['wife'] = $m[1];
                } elseif (preg_match('/^1 CHIL (@[^@]+@)/', $line, $m)) {
                    $families[$currentXref]['chil'][] = $m[1];
                }
            }
            // Reset current event context if new level 1 line
            if (preg_match('/^1 /', $line)) {
                $current = null;
            }
        }

        return [
            'individuals' => $individuals,
            'families' => $families,
            'sources' => [],
            'notes' => [],
        ];
    }

    /**
     * Clean and standardize GEDCOM date formats
     * Handles various date formats including yyyy-only dates
     *
     * @param  string  $dateString  The date string to clean
     * @return string Cleaned and standardized date string
     */
    // Using cleanGedcomDate from HandlesGedcomOperations trait

    /**
     * Import parsed GEDCOM data into the database (PostgreSQL, Neo4j) for a given tree.
     *
     * @param array{
     *     individuals: array<string, array{
     *         name: string|null,
     *         sex: string|null,
     *         birth: array{date: string|null},
     *         death: array{date: string|null},
     *         fams: array<string>,
     *         famc: array<string>
     *     }>,
     *     families: array<string, array{
     *         husb: string|null,
     *         wife: string|null,
     *         chil: array<string>
     *     }>,
     *     sources: array<string, mixed>,
     *     notes: array<string, mixed>
     * } $parsed Parsed GEDCOM data (from parse())
     * @param  int  $treeId  Target tree ID
     */
    public function importToDatabase(array $parsed, int $treeId): void
    {
        // 1. Map GEDCOM xrefs to internal IDs
        $xrefToIndividualId = [];
        $xrefToFamilyId = [];

        // 2. Import Individuals
        foreach ($parsed['individuals'] as $xref => $indi) {
            try {
                $firstName = $this->gedcomGivenName($indi['name']);
                $lastName = $this->gedcomSurname($indi['name']);
                if ($firstName === null || $firstName === '') {
                    $firstName = 'Unknown';
                }
                if ($lastName === null || $lastName === '') {
                    $lastName = 'Unknown';
                }
                $maxLength = 120;
                if (mb_strlen($firstName) > $maxLength) {
                    $firstName = mb_substr($firstName, 0, $maxLength).'...';
                }
                if (mb_strlen($lastName) > $maxLength) {
                    $lastName = mb_substr($lastName, 0, $maxLength).'...';
                }
                // --- Date handling ---
                $birthDateRaw = $indi['birth']['date'] ?? null;
                $birthDate = null;
                $birthYear = null;
                if ($birthDateRaw) {
                    if (preg_match('/^\d{4}$/', $birthDateRaw)) {
                        $birthYear = (int) $birthDateRaw;
                        $birthDate = null;
                    } elseif (strtotime($birthDateRaw)) {
                        $birthDate = date('Y-m-d', strtotime($birthDateRaw));
                        $birthYear = (int) date('Y', strtotime($birthDateRaw));
                    } else {
                        $birthDate = null;
                        $birthYear = null;
                    }
                }
                $deathDateRaw = $indi['death']['date'] ?? null;
                $deathDate = null;
                $deathYear = null;
                if ($deathDateRaw) {
                    if (preg_match('/^\d{4}$/', $deathDateRaw)) {
                        $deathYear = (int) $deathDateRaw;
                        $deathDate = null;
                    } elseif (strtotime($deathDateRaw)) {
                        $deathDate = date('Y-m-d', strtotime($deathDateRaw));
                        $deathYear = (int) date('Y', strtotime($deathDateRaw));
                    } else {
                        $deathDate = null;
                        $deathYear = null;
                    }
                }
                $individual = \App\Models\Individual::create([
                    'tree_id' => (string) $treeId,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'sex' => $indi['sex'] ?? null,
                    'birth_date' => $birthDate,
                    'birth_year' => $birthYear,
                    'birth_date_raw' => $birthDateRaw,
                    'death_date' => $deathDate,
                    'death_year' => $deathYear,
                    'death_date_raw' => $deathDateRaw,
                ]);
                $xrefToIndividualId[$xref] = $individual->id;
            } catch (Exception $e) {
                Log::warning("Failed to import individual {$xref}: ".$e->getMessage(), [
                    'xref' => $xref,
                    'name' => $indi['name'] ?? 'Unknown',
                    'error' => $e->getMessage(),
                ]);

                continue;
            }
        }

        // 3. Import Families
        foreach ($parsed['families'] as $xref => $fam) {
            try {
                /*
                $tree = \App\Models\Tree::create([
                    'tree_id' => $treeId,
                    'name' => $fam['name'] ?? null,
                    'description' => $fam['description'] ?? null,
                ]);
                */
                $xrefToFamilyId[$xref] = $treeId;

                // 4. Create Neo4j relationships for family members
                // Spouse relationship
                if (! empty($fam['husb']) && ! empty($fam['wife'])) {
                    $this->addSpouseRelationshipNeo4j(
                        $xrefToIndividualId[$fam['husb']] ?? null,
                        $xrefToIndividualId[$fam['wife']] ?? null
                    );
                }
                // Parent-child relationships
                foreach ($fam['chil'] ?? [] as $childXref) {
                    $fatherId = $xrefToIndividualId[$fam['husb']] ?? null;
                    $motherId = $xrefToIndividualId[$fam['wife']] ?? null;
                    $childId = $xrefToIndividualId[$childXref] ?? null;
                    if ($fatherId && $childId) {
                        $this->addParentChildRelationshipNeo4j($fatherId, $childId);
                    }
                    if ($motherId && $childId) {
                        $this->addParentChildRelationshipNeo4j($motherId, $childId);
                    }
                }

            } catch (Exception $e) {
                // Log the error but continue processing other families
                Log::warning("Failed to import family {$xref}: ".$e->getMessage(), [
                    'xref' => $xref,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }
        }

        // 5. Optionally: Import sources, notes, and link to individuals/families
    }

    /**
     * Export a tree (individuals, families, events, relationships) to a GEDCOM string.
     *
     * @param  int  $treeId  Tree ID to export
     * @return string GEDCOM file content
     */
    public function exportFromDatabase(int $treeId): string
    {
        $output = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n2 FORM LINEAGE-LINKED\n1 CHAR UTF-8\n1 SOUR LEG\n1 SUBM @SUB1@\n";

        // Get all individuals in the tree
        $individuals = \App\Models\Individual::where('tree_id', $treeId)->get();
        $individualMap = [];

        // Helper to format date as D MMM YYYY
        $formatGedcomDate = function ($date) {
            if (! $date) {
                return null;
            }
            $timestamp = strtotime($date);
            if (! $timestamp) {
                return null;
            }

            return mb_strtoupper(date('j M Y', $timestamp));
        };

        // Generate GEDCOM for each individual
        foreach ($individuals as $individual) {
            $xref = '@I'.$individual->id.'@';
            $individualMap[$individual->id] = $xref;

            $output .= "0 {$xref} INDI\n";

            // Name
            if ($individual->first_name || $individual->last_name) {
                $name = $individual->first_name ?? '';
                if ($individual->last_name) {
                    $name .= ' /'.$individual->last_name.'/';
                }
                $output .= "1 NAME {$name}\n";
            }

            // Sex
            if ($individual->sex) {
                $output .= "1 SEX {$individual->sex}\n";
            }

            // Birth
            if ($individual->birth_date) {
                $gedDate = $formatGedcomDate($individual->birth_date);
                if ($gedDate) {
                    $output .= "1 BIRT\n2 DATE {$gedDate}\n";
                }
            }

            // Death
            if ($individual->death_date) {
                $gedDate = $formatGedcomDate($individual->death_date);
                if ($gedDate) {
                    $output .= "1 DEAT\n2 DATE {$gedDate}\n";
                }
            }
        }

        // Get all groups (families) in the tree
        $groups = \App\Models\Group::where('tree_id', $treeId)->get();

        // Generate GEDCOM for each family
        foreach ($groups as $group) {
            $xref = '@F'.$group->id.'@';

            $output .= "0 {$xref} FAM\n";

            // Husband
            if ($group->husband_id && isset($individualMap[$group->husband_id])) {
                $output .= "1 HUSB {$individualMap[$group->husband_id]}\n";
            }

            // Wife
            if ($group->wife_id && isset($individualMap[$group->wife_id])) {
                $output .= "1 WIFE {$individualMap[$group->wife_id]}\n";
            }

            // Children
            $children = \App\Models\Individual::where('tree_id', $treeId)
                ->whereHas('parents', function ($query) use ($group) {
                    $query->where('group_id', $group->id);
                })
                ->get();

            foreach ($children as $child) {
                if (isset($individualMap[$child->id])) {
                    $output .= "1 CHIL {$individualMap[$child->id]}\n";
                }
            }
        }

        // Add submitter record at the end
        $output .= "0 @SUB1@ SUBM\n1 NAME LEG Exporter\n";
        $output .= "0 TRLR\n";

        return $output;
    }

    /**
     * Remove user-defined tags from GEDCOM content
     * User-defined tags start with underscore (_) and are not part of the standard GEDCOM specification
     *
     * @param  string  $gedcomContent  Raw GEDCOM content
     * @return string Cleaned GEDCOM content with user-defined tags removed
     */
    // Using removeUserDefinedTags from HandlesGedcomOperations trait

    /**
     * Store cleaned GEDCOM content to a file
     *
     * @param  string  $cleanedContent  The cleaned GEDCOM content
     * @return string The path to the stored file
     */
    private function storeCleanedGedcomContent(string $cleanedContent): string
    {
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "cleaned_gedcom_{$timestamp}.ged";
        $filePath = storage_path("app/gedcom/cleaned/{$filename}");

        // Ensure directory exists
        $directory = dirname($filePath);
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Store the cleaned content
        file_put_contents($filePath, $cleanedContent);

        return $filePath;
    }

    // --- Helper methods for name parsing ---

    private function gedcomGivenName(?string $name): ?string
    {
        if (! $name) {
            return null;
        }
        // GEDCOM: Given /Surname/
        if (preg_match('/^([^\\/]+)\\s*\\//', $name, $m)) {
            return mb_trim($m[1]);
        }

        return $name;
    }

    private function gedcomSurname(?string $name): ?string
    {
        if (! $name) {
            return null;
        }
        if (preg_match('/\\/([^\\/]+)\\//', $name, $m)) {
            return mb_trim($m[1]);
        }

        return null;
    }

    // --- Neo4j relationship helpers (pseudo-code, adjust for your integration) ---

    private function addSpouseRelationshipNeo4j(?int $husbandId, ?int $wifeId): void
    {
        if (! $husbandId || ! $wifeId) {
            return;
        }
        app(Neo4jIndividualService::class)->createSpouseRelationship($husbandId, $wifeId);
    }

    private function addParentChildRelationshipNeo4j(?int $parentId, ?int $childId): void
    {
        if (! $parentId || ! $childId) {
            return;
        }
        app(Neo4jIndividualService::class)->createParentChildRelationship($parentId, $childId);
    }
}
