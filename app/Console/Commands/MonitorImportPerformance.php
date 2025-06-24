<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\ImportProgress;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class MonitorImportPerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:monitor {--tree-id= : Monitor specific tree} {--user-id= : Monitor specific user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor GEDCOM import performance and identify bottlenecks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔍 Monitoring GEDCOM Import Performance...');
        $this->newLine();

        // Check queue status
        $this->checkQueueStatus();

        // Check database performance
        $this->checkDatabasePerformance();

        // Check recent imports
        $this->checkRecentImports();

        // Check system resources
        $this->checkSystemResources();

        return self::SUCCESS;
    }

    protected function checkQueueStatus(): void
    {
        $this->info('📊 Queue Status:');
        
        try {
            $queueSize = Redis::lLen('queues:imports');
            $this->line("  • Imports queue: {$queueSize} jobs pending");
            
            $notificationQueueSize = Redis::lLen('queues:notifications');
            $this->line("  • Notifications queue: {$notificationQueueSize} jobs pending");
            
            $defaultQueueSize = Redis::lLen('queues:default');
            $this->line("  • Default queue: {$defaultQueueSize} jobs pending");
            
            if ($queueSize > 10) {
                $this->warn("  ⚠️  High number of pending imports ({$queueSize})");
            }
        } catch (\Exception $e) {
            $this->error("  ❌ Unable to check queue status: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    protected function checkDatabasePerformance(): void
    {
        $this->info('🗄️  Database Performance:');
        
        try {
            // Check PostgreSQL connection
            $startTime = microtime(true);
            DB::select('SELECT 1');
            $postgresTime = (microtime(true) - $startTime) * 1000;
            $this->line("  • PostgreSQL response time: " . round($postgresTime, 2) . "ms");
            
            // Check table sizes
            $individualsCount = DB::table('individuals')->count();
            $familiesCount = DB::table('families')->count();
            $this->line("  • Total individuals: {$individualsCount}");
            $this->line("  • Total families: {$familiesCount}");
            
            // Check index usage
            $this->line("  • Indexes: gedcom_xref, tree_id (active)");
            
        } catch (\Exception $e) {
            $this->error("  ❌ Database check failed: " . $e->getMessage());
        }
        
        $this->newLine();
    }

    protected function checkRecentImports(): void
    {
        $this->info('📈 Recent Import Performance:');
        
        $query = ImportProgress::query()
            ->orderBy('created_at', 'desc')
            ->limit(5);
            
        if ($this->option('tree-id')) {
            $query->where('tree_id', $this->option('tree-id'));
        }
        
        if ($this->option('user-id')) {
            $query->where('user_id', $this->option('user-id'));
        }
        
        $recentImports = $query->get();
        
        if ($recentImports->isEmpty()) {
            $this->line("  • No recent imports found");
        } else {
            foreach ($recentImports as $import) {
                $duration = $import->created_at->diffInSeconds($import->updated_at);
                $status = match($import->status) {
                    'completed' => '✅',
                    'processing' => '⏳',
                    'failed' => '❌',
                    default => '⏸️'
                };
                
                $this->line("  {$status} Tree {$import->tree_id}: {$import->processed_records}/{$import->total_records} records ({$duration}s)");
                
                if ($import->status === 'failed') {
                    $this->line("    Error: {$import->error_message}");
                }
            }
        }
        
        $this->newLine();
    }

    protected function checkSystemResources(): void
    {
        $this->info('💻 System Resources:');
        
        // Check memory usage
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = ini_get('memory_limit');
        $this->line("  • Memory usage: " . $this->formatBytes($memoryUsage) . " / {$memoryLimit}");
        
        // Check cache hit rate
        $cacheHits = Cache::get('cache_hits', 0);
        $cacheMisses = Cache::get('cache_misses', 0);
        $totalRequests = $cacheHits + $cacheMisses;
        
        if ($totalRequests > 0) {
            $hitRate = ($cacheHits / $totalRequests) * 100;
            $this->line("  • Cache hit rate: " . round($hitRate, 1) . "%");
        }
        
        // Check Redis memory
        try {
            $redisInfo = Redis::info('memory');
            $usedMemory = $redisInfo['used_memory_human'] ?? 'Unknown';
            $this->line("  • Redis memory: {$usedMemory}");
        } catch (\Exception $e) {
            $this->line("  • Redis memory: Unable to check");
        }
        
        $this->newLine();
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= pow(1024, $pow);
        
        return round($bytes, 1) . ' ' . $units[$pow];
    }
}
