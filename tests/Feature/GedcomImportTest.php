<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Family;
use App\Models\Individual;
use App\Models\Tree;
use App\Models\User;
use App\Services\GedcomMultiDatabaseService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GedcomImportTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tree $tree;
    private GedcomMultiDatabaseService $gedcomService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->tree = Tree::factory()->create(['user_id' => $this->user->id]);
        $this->gedcomService = app(GedcomMultiDatabaseService::class);
    }

    public function test_can_import_basic_gedcom_file(): void
    {
        $gedcomContent = $this->getBasicGedcomContent();
        
        $result = $this->gedcomService->importGedcomData($gedcomContent, $this->tree->id);
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('postgresql', $result);
        $this->assertArrayHasKey('mongodb', $result);
        $this->assertArrayHasKey('neo4j', $result);
        
        // Verify individuals were created
        $this->assertEquals(2, Individual::where('tree_id', $this->tree->id)->count());
        
        // Verify families were created
        $this->assertEquals(1, Family::where('tree_id', $this->tree->id)->count());
    }

    public function test_can_handle_large_gedcom_file(): void
    {
        $gedcomContent = $this->getLargeGedcomContent();
        
        $result = $this->gedcomService->importGedcomData($gedcomContent, $this->tree->id);
        
        $this->assertTrue($result['success']);
        $this->assertGreaterThan(10, Individual::where('tree_id', $this->tree->id)->count());
    }

    public function test_can_handle_malformed_gedcom_file(): void
    {
        $malformedGedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n0 @I1@ INDI\n1 NAME John /Doe/\n1 SEX M\n0 @F1@ FAM\n1 HUSB @I1@\n1 CHIL @I2@\n0 @I2@ INDI\n1 NAME Jane /Doe/\n1 SEX F\n0 TRLR";
        
        $result = $this->gedcomService->importGedcomData($malformedGedcom, $this->tree->id);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(2, Individual::where('tree_id', $this->tree->id)->count());
    }

    public function test_can_handle_empty_gedcom_file(): void
    {
        $emptyGedcom = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n0 TRLR";
        
        $result = $this->gedcomService->importGedcomData($emptyGedcom, $this->tree->id);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(0, Individual::where('tree_id', $this->tree->id)->count());
    }

    public function test_can_handle_gedcom_with_special_characters(): void
    {
        $gedcomWithSpecialChars = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n0 @I1@ INDI\n1 NAME José María /García-López/\n1 SEX M\n1 BIRT\n2 DATE 15 JAN 1980\n2 PLAC Madrid, España\n0 TRLR";
        
        $result = $this->gedcomService->importGedcomData($gedcomWithSpecialChars, $this->tree->id);
        
        $this->assertTrue($result['success']);
        
        $individual = Individual::where('tree_id', $this->tree->id)->first();
        $this->assertEquals('José María', $individual->first_name);
        $this->assertEquals('García-López', $individual->last_name);
    }

    public function test_can_handle_gedcom_with_multiple_marriages(): void
    {
        $gedcomWithMultipleMarriages = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n0 @I1@ INDI\n1 NAME John /Doe/\n1 SEX M\n0 @I2@ INDI\n1 NAME Jane /Smith/\n1 SEX F\n0 @I3@ INDI\n1 NAME Mary /Johnson/\n1 SEX F\n0 @F1@ FAM\n1 HUSB @I1@\n1 WIFE @I2@\n1 MARR\n2 DATE 1990\n0 @F2@ FAM\n1 HUSB @I1@\n1 WIFE @I3@\n1 MARR\n2 DATE 2000\n0 TRLR";
        
        $result = $this->gedcomService->importGedcomData($gedcomWithMultipleMarriages, $this->tree->id);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(3, Individual::where('tree_id', $this->tree->id)->count());
        $this->assertEquals(2, Family::where('tree_id', $this->tree->id)->count());
    }

    public function test_can_handle_gedcom_with_complex_dates(): void
    {
        $gedcomWithComplexDates = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n0 @I1@ INDI\n1 NAME John /Doe/\n1 SEX M\n1 BIRT\n2 DATE ABT 1980\n2 PLAC New York\n1 DEAT\n2 DATE AFT 2020\n2 PLAC California\n0 TRLR";
        
        $result = $this->gedcomService->importGedcomData($gedcomWithComplexDates, $this->tree->id);
        
        $this->assertTrue($result['success']);
        
        $individual = Individual::where('tree_id', $this->tree->id)->first();
        $this->assertStringContainsString('ABT 1980', $individual->birth_date_raw ?? '');
        $this->assertStringContainsString('AFT 2020', $individual->death_date_raw ?? '');
    }

    public function test_can_handle_gedcom_with_sources(): void
    {
        $gedcomWithSources = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n0 @S1@ SOUR\n1 TITL Birth Certificate\n1 AUTH John Smith\n0 @I1@ INDI\n1 NAME John /Doe/\n1 SEX M\n2 SOUR @S1@\n0 TRLR";
        
        $result = $this->gedcomService->importGedcomData($gedcomWithSources, $this->tree->id);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(1, Individual::where('tree_id', $this->tree->id)->count());
    }

    public function test_can_handle_gedcom_with_notes(): void
    {
        $gedcomWithNotes = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n0 @N1@ NOTE\n1 CONT This is a test note\n1 CONT with multiple lines\n0 @I1@ INDI\n1 NAME John /Doe/\n1 SEX M\n2 NOTE @N1@\n0 TRLR";
        
        $result = $this->gedcomService->importGedcomData($gedcomWithNotes, $this->tree->id);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(1, Individual::where('tree_id', $this->tree->id)->count());
    }

    public function test_can_handle_gedcom_with_media(): void
    {
        $gedcomWithMedia = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n0 @M1@ OBJE\n1 FILE photo.jpg\n1 TITL Family Photo\n0 @I1@ INDI\n1 NAME John /Doe/\n1 SEX M\n2 OBJE @M1@\n0 TRLR";
        
        $result = $this->gedcomService->importGedcomData($gedcomWithMedia, $this->tree->id);
        
        $this->assertTrue($result['success']);
        $this->assertEquals(1, Individual::where('tree_id', $this->tree->id)->count());
    }

    public function test_import_fails_with_invalid_tree_id(): void
    {
        $gedcomContent = $this->getBasicGedcomContent();
        $invalidTreeId = 99999;
        
        $this->expectException(\Exception::class);
        $this->gedcomService->importGedcomData($gedcomContent, $invalidTreeId);
    }

    public function test_import_rolls_back_on_error(): void
    {
        $initialCount = Individual::where('tree_id', $this->tree->id)->count();
        
        // Create a GEDCOM that will cause an error (invalid format)
        $invalidGedcom = "INVALID GEDCOM FORMAT";
        
        try {
            $this->gedcomService->importGedcomData($invalidGedcom, $this->tree->id);
        } catch (\Exception $e) {
            // Expected to fail
        }
        
        $finalCount = Individual::where('tree_id', $this->tree->id)->count();
        $this->assertEquals($initialCount, $finalCount);
    }

    private function getBasicGedcomContent(): string
    {
        return "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n0 @I1@ INDI\n1 NAME John /Doe/\n1 SEX M\n1 BIRT\n2 DATE 15 JAN 1980\n2 PLAC New York\n0 @I2@ INDI\n1 NAME Jane /Doe/\n1 SEX F\n1 BIRT\n2 DATE 20 MAR 1985\n2 PLAC California\n0 @F1@ FAM\n1 HUSB @I1@\n1 WIFE @I2@\n1 MARR\n2 DATE 10 JUN 2010\n2 PLAC Las Vegas\n0 TRLR";
    }

    private function getLargeGedcomContent(): string
    {
        $content = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n";
        
        // Create 20 individuals
        for ($i = 1; $i <= 20; $i++) {
            $content .= "0 @I{$i}@ INDI\n";
            $content .= "1 NAME Individual{$i} /Family/\n";
            $content .= "1 SEX " . ($i % 2 === 0 ? 'F' : 'M') . "\n";
            $content .= "1 BIRT\n";
            $content .= "2 DATE " . (1980 + $i) . "\n";
        }
        
        // Create 10 families
        for ($i = 1; $i <= 10; $i++) {
            $content .= "0 @F{$i}@ FAM\n";
            $content .= "1 HUSB @I" . ($i * 2 - 1) . "@\n";
            $content .= "1 WIFE @I" . ($i * 2) . "@\n";
            $content .= "1 MARR\n";
            $content .= "2 DATE " . (2000 + $i) . "\n";
        }
        
        $content .= "0 TRLR";
        
        return $content;
    }
} 