<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\ImportGedcomJob;
use App\Models\Tree;
use App\Models\User;
use App\Services\ImportPerformanceTracker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

final class ImportMethodComparisonTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Tree $tree;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->tree = Tree::factory()->create(['user_id' => $this->user->id]);
        
        Queue::fake();
    }

    public function test_can_select_standard_import_method(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('trees.import'), [
                'gedcom' => $this->createTestGedcomFile(),
                'tree_id' => $this->tree->id,
                'import_method' => 'standard',
            ]);

        $response->assertRedirect(route('trees.index'));
        
        Queue::assertPushed(ImportGedcomJob::class, function ($job) {
            return $job->importMethod === 'standard';
        });
    }

    public function test_can_select_optimized_import_method(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('trees.import'), [
                'gedcom' => $this->createTestGedcomFile(),
                'tree_id' => $this->tree->id,
                'import_method' => 'optimized',
            ]);

        $response->assertRedirect(route('trees.index'));
        
        Queue::assertPushed(ImportGedcomJob::class, function ($job) {
            return $job->importMethod === 'optimized';
        });
    }

    public function test_import_form_shows_method_selection(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('trees.import.form'));

        $response->assertStatus(200);
        $response->assertSee('Import Method');
        $response->assertSee('Standard Import (Multi-Database)');
        $response->assertSee('Optimized Import (Parallel Processing)');
    }

    public function test_validation_requires_import_method(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('trees.import'), [
                'gedcom' => $this->createTestGedcomFile(),
                'tree_id' => $this->tree->id,
                // Missing import_method
            ]);

        $response->assertSessionHasErrors(['import_method']);
    }

    public function test_validation_accepts_valid_import_methods(): void
    {
        $validMethods = ['standard', 'optimized'];
        
        foreach ($validMethods as $method) {
            $response = $this->actingAs($this->user)
                ->post(route('trees.import'), [
                    'gedcom' => $this->createTestGedcomFile(),
                    'tree_id' => $this->tree->id,
                    'import_method' => $method,
                ]);

            $response->assertRedirect(route('trees.index'));
            $response->assertSessionHasNoErrors();
        }
    }

    public function test_validation_rejects_invalid_import_methods(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('trees.import'), [
                'gedcom' => $this->createTestGedcomFile(),
                'tree_id' => $this->tree->id,
                'import_method' => 'invalid_method',
            ]);

        $response->assertSessionHasErrors(['import_method']);
    }

    public function test_performance_tracker_can_track_metrics(): void
    {
        $tracker = app(ImportPerformanceTracker::class);
        
        $metrics = [
            'import_method' => 'standard',
            'tree_id' => $this->tree->id,
            'user_id' => $this->user->id,
            'duration_seconds' => 10.5,
            'file_size_bytes' => 1024,
            'total_records' => 100,
            'records_per_second' => 9.52,
            'bytes_per_second' => 97.52,
            'individuals_count' => 50,
            'families_count' => 30,
            'sources_count' => 20,
            'memory_used_mb' => 25.5,
            'timestamp' => now()->toISOString(),
            'success' => true,
        ];
        
        // This should not throw an exception
        $tracker->trackImportMetrics(
            $metrics['import_method'],
            $metrics['tree_id'],
            $metrics['user_id'],
            $metrics,
            $metrics['duration_seconds'],
            $metrics['file_size_bytes'],
            $metrics['total_records']
        );
        
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function test_performance_tracker_can_track_failures(): void
    {
        $tracker = app(ImportPerformanceTracker::class);
        
        // This should not throw an exception
        $tracker->trackImportFailure(
            'optimized',
            $this->tree->id,
            $this->user->id,
            'Test error message',
            5.2,
            512
        );
        
        $this->assertTrue(true); // If we get here, no exception was thrown
    }

    public function test_can_access_import_metrics_api(): void
    {
        $response = $this->get('/api/v1/import-metrics/summary');
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data' => [
                'total_imports',
                'success_rates',
                'performance_improvements',
                'average_metrics',
            ],
        ]);
    }

    public function test_can_access_admin_import_metrics_page(): void
    {
        // Create an admin user
        $adminUser = User::factory()->create();
        $adminUser->roles()->attach(1); // Assuming role ID 1 is admin
        
        $response = $this->actingAs($adminUser)
            ->get(route('admin.import-metrics'));
        
        $response->assertStatus(200);
        $response->assertSee('Import Performance Metrics');
    }

    private function createTestGedcomFile(): \Illuminate\Http\Testing\File
    {
        $gedcomContent = "0 HEAD\n1 GEDC\n2 VERS 5.5.5\n0 @I1@ INDI\n1 NAME John /Doe/\n1 SEX M\n0 TRLR";
        
        return \Illuminate\Http\Testing\File::createWithContent('test.ged', $gedcomContent);
    }
} 