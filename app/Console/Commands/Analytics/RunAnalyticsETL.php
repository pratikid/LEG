<?php

declare(strict_types=1);

namespace App\Console\Commands\Analytics;

use App\Services\Analytics\AnalyticsETLService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Exception;

/**
 * Laravel Artisan command for running the analytics ETL pipeline
 * Usage: php artisan analytics:etl [--tree=ID] [--force] [--dry-run]
 */
class RunAnalyticsETL extends Command
{
    protected $signature = 'analytics:etl 
                            {--tree= : Process specific tree ID only}
                            {--force : Force reprocessing of all data}
                            {--dry-run : Show what would be processed without executing}
                            {--chunk-size=100 : Number of records to process per chunk}';

    protected $description = 'Run the analytics ETL pipeline to populate data warehouse tables';

    private AnalyticsETLService $etlService;

    public function __construct(AnalyticsETLService $etlService)
    {
        parent::__construct();
        $this->etlService = $etlService;
    }

    public function handle(): int
    {
        $this->info('🚀 Starting Analytics ETL Pipeline');
        $this->newLine();

        try {
            $startTime = microtime(true);

            // Validate prerequisites
            if (!$this->validatePrerequisites()) {
                return Command::FAILURE;
            }

            // Clear cache if force option is used
            if ($this->option('force')) {
                $this->clearCache();
            }

            // Run ETL based on options
            $results = $this->runETL();

            // Display results
            $this->displayResults($results, $startTime);

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('❌ ETL Pipeline failed: ' . $e->getMessage());
            $this->line('Stack trace: ' . $e->getTraceAsString());
            return Command::FAILURE;
        }
    }

    /**
     * Validate system prerequisites before running ETL
     */
    private function validatePrerequisites(): bool
    {
        $this->info('🔍 Validating system prerequisites...');

        // Check database connections
        try {
            DB::connection()->getPdo();
            $this->line('✅ PostgreSQL connection: OK');
        } catch (Exception $e) {
            $this->error('❌ PostgreSQL connection failed: ' . $e->getMessage());
            return false;
        }

        // Check if analytics tables exist
        $requiredTables = [
            'dim_time',
            'dim_location', 
            'fact_individuals',
            'fact_relationships',
            'fact_tree_metrics',
            'analytics_jobs'
        ];

        foreach ($requiredTables as $table) {
            if (!$this->tableExists($table)) {
                $this->error("❌ Required table '{$table}' does not exist. Please run migrations first.");
                return false;
            }
        }
        $this->line('✅ Analytics tables: OK');

        // Check data availability
        $individualsCount = DB::table('individuals')->count();
        $treesCount = DB::table('trees')->count();

        if ($individualsCount === 0) {
            $this->warn('⚠️  No individuals found in database');
            return false;
        }

        if ($treesCount === 0) {
            $this->warn('⚠️  No family trees found in database');
            return false;
        }

        $this->line("✅ Data availability: {$individualsCount} individuals in {$treesCount} trees");

        return true;
    }

    /**
     * Clear analytics cache if force option is used
     */
    private function clearCache(): void
    {
        $this->info('🧹 Clearing analytics cache...');
        
        // Clear analytics-specific cache keys
        cache()->tags(['analytics'])->flush();
        
        // Clear individual processing cache
        $cacheKeys = cache()->get('analytics:individual_cache_keys', []);
        foreach ($cacheKeys as $key) {
            cache()->forget($key);
        }
        cache()->forget('analytics:individual_cache_keys');
        
        $this->line('✅ Cache cleared');
    }

    /**
     * Run the ETL pipeline based on command options
     */
    private function runETL(): array
    {
        if ($this->option('dry-run')) {
            return $this->runDryRun();
        }

        $treeId = $this->option('tree');
        
        if ($treeId) {
            return $this->runTreeSpecificETL((int) $treeId);
        }

        return $this->runFullETL();
    }

    /**
     * Run dry-run to show what would be processed
     */
    private function runDryRun(): array
    {
        $this->info('🔍 Running dry-run analysis...');

        $treesCount = DB::table('trees')->count();
        $individualsCount = DB::table('individuals')->count();
        
        $processedIndividuals = DB::table('fact_individuals')->count();
        $processedRelationships = DB::table('fact_relationships')->count();
        
        $this->table(
            ['Metric', 'Current', 'To Process'],
            [
                ['Trees', $treesCount, $treesCount],
                ['Individuals', $individualsCount, $individualsCount - $processedIndividuals],
                ['Relationships (estimated)', '?', '?'],
                ['Time dimension records', DB::table('dim_time')->count(), 'Auto-populated'],
                ['Location records', DB::table('dim_location')->count(), 'Auto-created as needed']
            ]
        );

        return [
            'trees_processed' => 0,
            'individuals_processed' => 0,
            'relationships_processed' => 0,
            'locations_created' => 0,
            'time_records_created' => 0,
            'duration_seconds' => 0,
            'errors' => [],
            'dry_run' => true
        ];
    }

    /**
     * Run ETL for a specific tree
     */
    private function runTreeSpecificETL(int $treeId): array
    {
        $this->info("🌳 Processing tree ID: {$treeId}");

        $tree = DB::table('trees')->where('id', $treeId)->first();
        if (!$tree) {
            throw new Exception("Tree with ID {$treeId} not found");
        }

        $this->line("Tree: {$tree->name}");

        // Create a progress bar for the tree
        $individualsCount = DB::table('individuals')->where('tree_id', $treeId)->count();
        $progressBar = $this->output->createProgressBar($individualsCount);
        $progressBar->setFormat('Processing: %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');

        $results = $this->etlService->processTree($tree);
        
        $progressBar->finish();
        $this->newLine(2);

        return [
            'trees_processed' => 1,
            'individuals_processed' => $results['individuals'],
            'relationships_processed' => $results['relationships'],
            'locations_created' => $results['locations'],
            'time_records_created' => 0,
            'duration_seconds' => 0,
            'errors' => []
        ];
    }

    /**
     * Run full ETL for all trees
     */
    private function runFullETL(): array
    {
        $this->info('🌍 Running full ETL pipeline for all trees...');
        
        return $this->etlService->runFullETL();
    }

    /**
     * Display ETL results in a formatted table
     */
    private function displayResults(array $results, float $startTime): void
    {
        $duration = round(microtime(true) - $startTime, 2);
        
        $this->newLine();
        $this->info('📊 ETL Pipeline Results');
        $this->newLine();

        if (!empty($results['dry_run'])) {
            $this->warn('This was a dry-run. No data was processed.');
            return;
        }

        $this->table(
            ['Metric', 'Count'],
            [
                ['Trees Processed', number_format($results['trees_processed'] ?? 0)],
                ['Individuals Processed', number_format($results['individuals_processed'] ?? 0)],
                ['Relationships Processed', number_format($results['relationships_processed'] ?? 0)],
                ['Locations Created', number_format($results['locations_created'] ?? 0)],
                ['Time Records Available', number_format($results['time_records_created'] ?? 0)],
                ['Total Duration', $duration . ' seconds'],
                ['Processing Rate', $this->calculateProcessingRate($results, $duration)],
                ['Errors', count($results['errors'] ?? [])]
            ]
        );

        // Display errors if any
        if (!empty($results['errors'])) {
            $this->newLine();
            $this->error('❌ Errors encountered:');
            foreach ($results['errors'] as $error) {
                $this->line('  • ' . $error);
            }
        }

        // Display success message
        if (empty($results['errors'])) {
            $this->newLine();
            $this->info('✅ Analytics ETL Pipeline completed successfully!');
            $this->line('Data warehouse tables have been populated and are ready for analytics queries.');
        }
    }

    /**
     * Calculate processing rate for display
     */
    private function calculateProcessingRate(array $results, float $duration): string
    {
        if ($duration <= 0) {
            return 'N/A';
        }

        $totalRecords = ($results['individuals_processed'] ?? 0) + ($results['relationships_processed'] ?? 0);
        $rate = round($totalRecords / $duration, 2);

        return number_format($rate) . ' records/sec';
    }

    /**
     * Check if a database table exists
     */
    private function tableExists(string $tableName): bool
    {
        try {
            return DB::getSchemaBuilder()->hasTable($tableName);
        } catch (Exception $e) {
            return false;
        }
    }
}
