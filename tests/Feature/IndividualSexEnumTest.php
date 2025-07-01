<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Individual;
use App\Models\Tree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class IndividualSexEnumTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Tree $tree;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->tree = Tree::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_enum_values_are_correct(): void
    {
        $expectedValues = ['M', 'F', 'U'];
        $actualValues = Individual::getSexValues();

        $this->assertEquals($expectedValues, $actualValues);
    }

    public function test_valid_sex_values_are_accepted(): void
    {
        $this->assertTrue(Individual::isValidSex('M'));
        $this->assertTrue(Individual::isValidSex('F'));
        $this->assertTrue(Individual::isValidSex('U'));
    }

    public function test_invalid_sex_value_is_rejected(): void
    {
        $this->assertFalse(Individual::isValidSex('INVALID'));
        $this->assertFalse(Individual::isValidSex('X'));
        $this->assertFalse(Individual::isValidSex(''));
    }

    public function test_can_create_individual_with_male_sex(): void
    {
        $individual = Individual::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'sex' => 'M',
            'tree_id' => $this->tree->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertEquals('M', $individual->sex);
        $this->assertTrue($individual->isMale());
        $this->assertFalse($individual->isFemale());
        $this->assertFalse($individual->isUnknownSex());
        $this->assertTrue($individual->hasKnownSex());
        $this->assertEquals('Male', $individual->getSexLabel());
    }

    public function test_can_create_individual_with_female_sex(): void
    {
        $individual = Individual::create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'sex' => 'F',
            'tree_id' => $this->tree->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertEquals('F', $individual->sex);
        $this->assertFalse($individual->isMale());
        $this->assertTrue($individual->isFemale());
        $this->assertFalse($individual->isUnknownSex());
        $this->assertTrue($individual->hasKnownSex());
        $this->assertEquals('Female', $individual->getSexLabel());
    }

    public function test_can_create_individual_with_unknown_sex(): void
    {
        $individual = Individual::create([
            'first_name' => 'Unknown',
            'last_name' => 'Person',
            'sex' => 'U',
            'tree_id' => $this->tree->id,
            'user_id' => $this->user->id,
        ]);

        $this->assertEquals('U', $individual->sex);
        $this->assertFalse($individual->isMale());
        $this->assertFalse($individual->isFemale());
        $this->assertTrue($individual->isUnknownSex());
        $this->assertFalse($individual->hasKnownSex());
        $this->assertEquals('Unknown', $individual->getSexLabel());
    }

    public function test_sex_scopes_work_correctly(): void
    {
        // Create individuals with different sex values
        Individual::create([
            'first_name' => 'John',
            'last_name' => 'Male',
            'sex' => 'M',
            'tree_id' => $this->tree->id,
            'user_id' => $this->user->id,
        ]);

        Individual::create([
            'first_name' => 'Jane',
            'last_name' => 'Female',
            'sex' => 'F',
            'tree_id' => $this->tree->id,
            'user_id' => $this->user->id,
        ]);

        Individual::create([
            'first_name' => 'Unknown',
            'last_name' => 'Person',
            'sex' => 'U',
            'tree_id' => $this->tree->id,
            'user_id' => $this->user->id,
        ]);

        // Test individual scopes
        $this->assertEquals(1, Individual::whereMale()->count());
        $this->assertEquals(1, Individual::whereFemale()->count());
        $this->assertEquals(1, Individual::whereUnknownSex()->count());
        $this->assertEquals(2, Individual::whereKnownSex()->count());

        // Test combined scopes
        $this->assertEquals(2, Individual::whereSexIn(['M', 'F'])->count());
        $this->assertEquals(3, Individual::whereSexIn(['M', 'F', 'U'])->count());
    }

    public function test_sex_constants_are_defined(): void
    {
        $this->assertEquals('M', Individual::SEX_MALE);
        $this->assertEquals('F', Individual::SEX_FEMALE);
        $this->assertEquals('U', Individual::SEX_UNKNOWN);
    }
}
