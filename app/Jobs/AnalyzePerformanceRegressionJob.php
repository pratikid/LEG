<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Services\PerformanceMonitoringService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class AnalyzePerformanceRegressionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly string $metricType,
        private readonly array $currentMetrics
    ) {}

    public function handle(PerformanceMonitoringService $monitoringService): void
    {
        $monitoringService->detectPerformanceRegression($this->metricType, $this->currentMetrics);
    }
}
