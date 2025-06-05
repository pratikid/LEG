<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Service for parsing, importing, and exporting GEDCOM files.
 * Handles individuals, families, events, sources, notes, and relationships.
 */
class GedcomService
{
    /**
     * Parse GEDCOM content into structured arrays for individuals, families, sources, and notes.
     *
     * @param string $gedcomContent Raw GEDCOM file content
     * @return array [
     *   'individuals' => [...],
     *   'families' => [...],
     *   'sources' => [...],
     *   'notes' => [...],
     * ]
     */
    public function parse(string $gedcomContent): array
    {
        // TODO: Implement full GEDCOM parsing logic (multi-level, cross-references, events, etc.)
        return [
            'individuals' => [],
            'families' => [],
            'sources' => [],
            'notes' => [],
        ];
    }

    /**
     * Import parsed GEDCOM data into the database (PostgreSQL, Neo4j) for a given tree.
     *
     * @param array $parsed Parsed GEDCOM data (from parse())
     * @param int $treeId Target tree ID
     * @return void
     */
    public function importToDatabase(array $parsed, int $treeId): void
    {
        // 1. Map GEDCOM xrefs to internal IDs
        $xrefToIndividualId = [];
        $xrefToFamilyId = [];

        // 2. Import Individuals
        foreach ($parsed['individuals'] as $xref => $indi) {
            $individual = \App\Models\Individual::create([
                'tree_id' => $treeId,
                'first_name' => $this->gedcomGivenName($indi['name']),
                'last_name' => $this->gedcomSurname($indi['name']),
                'sex' => $indi['sex'] ?? null,
                'birth_date' => $indi['birth']['date'] ?? null,
                'death_date' => $indi['death']['date'] ?? null,
                // Add more fields as needed
            ]);
            $xrefToIndividualId[$xref] = $individual->id;
        }

        // 3. Import Families/Groups
        foreach ($parsed['families'] as $xref => $fam) {
            $group = \App\Models\Group::create([
                'tree_id' => $treeId,
                // Optionally: store husband_id, wife_id, marriage_date, etc.
                // 'husband_id' => $xrefToIndividualId[$fam['husb']] ?? null,
                // 'wife_id' => $xrefToIndividualId[$fam['wife']] ?? null,
                // 'marriage_date' => $fam['marriage']['date'] ?? null,
            ]);
            $xrefToFamilyId[$xref] = $group->id;

            // 4. Create Neo4j relationships for family members
            // Spouse relationship
            if (!empty($fam['husb']) && !empty($fam['wife'])) {
                $this->addSpouseRelationshipNeo4j(
                    $xrefToIndividualId[$fam['husb']] ?? null,
                    $xrefToIndividualId[$fam['wife']] ?? null
                );
            }
            // Parent-child relationships
            foreach ($fam['chil'] as $childXref) {
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
        }

        // 5. Optionally: Import sources, notes, and link to individuals/families
    }

    /**
     * Export a tree (individuals, families, events, relationships) to a GEDCOM string.
     *
     * @param int $treeId Tree ID to export
     * @return string GEDCOM file content
     */
    public function exportFromDatabase(int $treeId): string
    {
        // TODO: Gather all data for the tree and generate GEDCOM-compliant output
        return '';
    }

    // --- Helper methods for name parsing ---

    private function gedcomGivenName(?string $name): ?string
    {
        if (!$name) return null;
        // GEDCOM: Given /Surname/
        if (preg_match('/^([^\\/]+)\\s*\\//', $name, $m)) {
            return trim($m[1]);
        }
        return $name;
    }

    private function gedcomSurname(?string $name): ?string
    {
        if (!$name) return null;
        if (preg_match('/\\/([^\\/]+)\\//', $name, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    // --- Neo4j relationship helpers (pseudo-code, adjust for your integration) ---

    private function addSpouseRelationshipNeo4j(?int $husbandId, ?int $wifeId): void
    {
        if (!$husbandId || !$wifeId) return;
        // Use your Neo4j service/client here
        // Example:
        // app(Neo4jRelationshipService::class)->addSpouse($husbandId, $wifeId);
    }

    private function addParentChildRelationshipNeo4j(?int $parentId, ?int $childId): void
    {
        if (!$parentId || !$childId) return;
        // Use your Neo4j service/client here
        // Example:
        // app(Neo4jRelationshipService::class)->addParentChild($parentId, $childId);
    }
} 