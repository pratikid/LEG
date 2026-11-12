<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Bus\Batch;
use Illuminate\Bus\PendingBatch;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Throwable;

/**
 * Trait for handling queue operations
 */
trait HandlesQueueOperations
{
    /**
     * Dispatch a job to the queue.
     *
     * @param  mixed  $job  The job to dispatch
     * @param  string|null  $queue  The queue name
     * @param  int|null  $delay  The delay in seconds
     * @return mixed The dispatched job
     */
    protected function dispatchJob($job, ?string $queue = null, ?int $delay = null)
    {
        if ($queue) {
            $job->onQueue($queue);
        }

        if ($delay) {
            $job->delay($delay);
        }

        return dispatch($job);
    }

    /**
     * Dispatch a job to the queue with a specific connection.
     *
     * @param  mixed  $job  The job to dispatch
     * @param  string  $connection  The connection name
     * @param  string|null  $queue  The queue name
     * @param  int|null  $delay  The delay in seconds
     * @return mixed The dispatched job
     */
    protected function dispatchJobWithConnection($job, string $connection, ?string $queue = null, ?int $delay = null)
    {
        $job->onConnection($connection);

        return $this->dispatchJob($job, $queue, $delay);
    }

    /**
     * Dispatch a job to the queue synchronously (run immediately).
     *
     * @param  mixed  $job  The job to dispatch
     * @return mixed The result of the job
     */
    protected function dispatchJobSync($job)
    {
        return dispatch_sync($job);
    }

    /**
     * Dispatch a job to the queue after the response has been sent to the browser.
     *
     * @param  mixed  $job  The job to dispatch
     * @param  string|null  $queue  The queue name
     * @return mixed The dispatched job
     */
    protected function dispatchJobAfterResponse($job, ?string $queue = null)
    {
        if ($queue) {
            $job->onQueue($queue);
        }

        return dispatch($job)->afterResponse();
    }

    /**
     * Create a new job batch.
     *
     * @param  array  $jobs  The jobs to include in the batch
     * @param  string|null  $name  The batch name
     * @param  int|null  $maxExceptions  The maximum number of exceptions allowed
     * @return PendingBatch The pending batch
     */
    protected function createJobBatch(array $jobs, ?string $name = null, ?int $maxExceptions = null): PendingBatch
    {
        $batch = Bus::batch($jobs);

        if ($name) {
            $batch->name($name);
        }

        if ($maxExceptions !== null) {
            $batch->allowFailures($maxExceptions);
        }

        return $batch;
    }

    /**
     * Dispatch a batch of jobs to the queue.
     *
     * @param  array  $jobs  The jobs to include in the batch
     * @param  string|null  $name  The batch name
     * @param  int|null  $maxExceptions  The maximum number of exceptions allowed
     * @param  callable|null  $then  Callback to execute when the batch is finished
     * @param  callable|null  $catch  Callback to execute when the batch fails
     * @param  callable|null  $finally  Callback to execute when the batch is finished (success or failure)
     * @return Batch The dispatched batch
     */
    protected function dispatchJobBatch(
        array $jobs,
        ?string $name = null,
        ?int $maxExceptions = null,
        ?callable $then = null,
        ?callable $catch = null,
        ?callable $finally = null
    ): Batch {
        $batch = $this->createJobBatch($jobs, $name, $maxExceptions);

        if ($then) {
            $batch->then($then);
        }

        if ($catch) {
            $batch->catch($catch);
        }

        if ($finally) {
            $batch->finally($finally);
        }

        return $batch->dispatch();
    }

    /**
     * Dispatch a chain of jobs to the queue.
     *
     * @param  array  $jobs  The jobs to include in the chain
     * @param  string|null  $queue  The queue name
     * @param  int|null  $delay  The delay in seconds
     * @param  string|null  $connection  The connection name
     * @return mixed The dispatched chain
     */
    protected function dispatchJobChain(
        array $jobs,
        ?string $queue = null,
        ?int $delay = null,
        ?string $connection = null
    ) {
        $chain = Bus::chain($jobs);

        if ($queue) {
            $chain->onQueue($queue);
        }

        if ($delay) {
            $chain->delay($delay);
        }

        if ($connection) {
            $chain->onConnection($connection);
        }

        return $chain->dispatch();
    }

    /**
     * Get the number of jobs in a queue.
     *
     * @param  string  $queue  The queue name
     * @param  string|null  $connection  The connection name
     * @return int The number of jobs
     */
    protected function getQueueSize(string $queue, ?string $connection = null): int
    {
        $connection = $connection ?? config('queue.default');

        return Queue::connection($connection)->size($queue);
    }

    /**
     * Clear a queue.
     *
     * @param  string  $queue  The queue name
     * @param  string|null  $connection  The connection name
     * @return int The number of jobs cleared
     */
    protected function clearQueue(string $queue, ?string $connection = null): int
    {
        $connection = $connection ?? config('queue.default');
        $size = $this->getQueueSize($queue, $connection);

        Queue::connection($connection)->clear($queue);

        return $size;
    }

    /**
     * Retry a failed job.
     *
     * @param  string  $id  The failed job ID
     * @return bool Whether the retry was successful
     */
    protected function retryFailedJob(string $id): bool
    {
        try {
            Artisan::call('queue:retry', ['id' => $id]);

            return true;
        } catch (Throwable $e) {
            Log::error('Failed to retry job', [
                'job_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Retry all failed jobs.
     *
     * @return bool Whether the retry was successful
     */
    protected function retryAllFailedJobs(): bool
    {
        try {
            Artisan::call('queue:retry', ['id' => 'all']);

            return true;
        } catch (Throwable $e) {
            Log::error('Failed to retry all jobs', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete a failed job.
     *
     * @param  string  $id  The failed job ID
     * @return bool Whether the deletion was successful
     */
    protected function deleteFailedJob(string $id): bool
    {
        try {
            Artisan::call('queue:forget', ['id' => $id]);

            return true;
        } catch (Throwable $e) {
            Log::error('Failed to delete job', [
                'job_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete all failed jobs.
     *
     * @return bool Whether the deletion was successful
     */
    protected function deleteAllFailedJobs(): bool
    {
        try {
            Artisan::call('queue:flush');

            return true;
        } catch (Throwable $e) {
            Log::error('Failed to delete all jobs', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Get all failed jobs.
     *
     * @return array The failed jobs
     */
    protected function getFailedJobs(): array
    {
        return app('queue.failer')->all();
    }

    /**
     * Get a specific failed job.
     *
     * @param  string  $id  The failed job ID
     * @return object|null The failed job or null if not found
     */
    protected function getFailedJob(string $id): ?object
    {
        return app('queue.failer')->find($id);
    }

    /**
     * Determine the appropriate queue for a job based on its priority.
     *
     * @param  string  $jobClass  The job class name
     * @param  string  $priority  The priority (high, medium, low)
     * @return string The queue name
     */
    protected function determineJobQueue(string $jobClass, string $priority = 'medium'): string
    {
        // Get the base queue name from the job class
        $baseQueue = $this->getBaseQueueName($jobClass);

        // Append priority suffix
        switch (mb_strtolower($priority)) {
            case 'high':
                return "{$baseQueue}-high";

            case 'low':
                return "{$baseQueue}-low";

            case 'medium':
            default:
                return $baseQueue;
        }
    }

    /**
     * Get the base queue name from a job class.
     *
     * @param  string  $jobClass  The job class name
     * @return string The base queue name
     */
    protected function getBaseQueueName(string $jobClass): string
    {
        // Extract the class name without namespace
        $className = class_basename($jobClass);

        // Remove 'Job' suffix if present
        $baseName = preg_replace('/Job$/', '', $className);

        // Convert to kebab case
        return mb_strtolower(preg_replace('/[A-Z]/', '-$0', lcfirst($baseName)));
    }

    /**
     * Determine the appropriate delay for a job based on its priority and type.
     *
     * @param  string  $jobClass  The job class name
     * @param  string  $priority  The priority (high, medium, low)
     * @return int The delay in seconds
     */
    protected function determineJobDelay(string $jobClass, string $priority = 'medium'): int
    {
        // Base delays by priority
        $delays = [
            'high' => 0,
            'medium' => 30,
            'low' => 60,
        ];

        // Get the base delay from priority
        $baseDelay = $delays[mb_strtolower($priority)] ?? $delays['medium'];

        // Adjust delay based on job type (example implementation)
        if (str_contains($jobClass, 'Email') || str_contains($jobClass, 'Notification')) {
            // Email and notification jobs might need additional delay to prevent rate limiting
            return $baseDelay + 15;
        }

        if (str_contains($jobClass, 'Report') || str_contains($jobClass, 'Export')) {
            // Report and export jobs are less time-sensitive
            return $baseDelay + 30;
        }

        return $baseDelay;
    }

    /**
     * Handle a job exception by logging it and potentially retrying the job.
     *
     * @param  Job  $job  The job instance
     * @param  Throwable  $exception  The exception that occurred
     * @param  int  $maxAttempts  The maximum number of attempts
     * @param  int  $backoff  The backoff time in seconds
     */
    protected function handleJobException(Job $job, Throwable $exception, int $maxAttempts = 3, int $backoff = 60): void
    {
        // Log the exception
        Log::error('Job failed', [
            'job' => get_class($job),
            'queue' => $job->getQueue(),
            'attempts' => $job->attempts(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // Check if the job should be retried
        if ($job->attempts() < $maxAttempts) {
            // Calculate backoff with exponential increase
            $retryAfter = $backoff * pow(2, $job->attempts() - 1);

            // Release the job back to the queue with a delay
            $job->release($retryAfter);
        } else {
            // Delete the job if max attempts reached
            $job->delete();

            // Log that the job has been abandoned
            Log::warning('Job abandoned after max attempts', [
                'job' => get_class($job),
                'queue' => $job->getQueue(),
                'max_attempts' => $maxAttempts,
            ]);
        }
    }

    /**
     * Split a large job into smaller chunks for better queue processing.
     *
     * @param  array  $items  The items to process
     * @param  string  $jobClass  The job class name
     * @param  int  $chunkSize  The size of each chunk
     * @param  array  $additionalParams  Additional parameters for the job constructor
     * @param  string|null  $queue  The queue name
     * @param  string|null  $connection  The connection name
     * @return Batch The dispatched batch
     */
    protected function splitJobIntoChunks(
        array $items,
        string $jobClass,
        int $chunkSize = 100,
        array $additionalParams = [],
        ?string $queue = null,
        ?string $connection = null
    ): Batch {
        // Split items into chunks
        $chunks = array_chunk($items, $chunkSize);

        // Create a job for each chunk
        $jobs = [];
        foreach ($chunks as $chunk) {
            $job = new $jobClass($chunk, ...$additionalParams);

            if ($queue) {
                $job->onQueue($queue);
            }

            if ($connection) {
                $job->onConnection($connection);
            }

            $jobs[] = $job;
        }

        // Create a batch name based on the job class
        $batchName = class_basename($jobClass).' ('.count($items).' items in '.count($chunks).' chunks)';

        // Dispatch the batch
        return $this->dispatchJobBatch($jobs, $batchName);
    }

    /**
     * Monitor a job batch and return its progress.
     *
     * @param  string  $batchId  The batch ID
     * @return array The batch progress information
     */
    protected function getJobBatchProgress(string $batchId): array
    {
        $batch = Bus::findBatch($batchId);

        if (! $batch) {
            return [
                'found' => false,
                'message' => 'Batch not found',
            ];
        }

        return [
            'found' => true,
            'id' => $batch->id,
            'name' => $batch->name,
            'total_jobs' => $batch->totalJobs,
            'pending_jobs' => $batch->pendingJobs,
            'failed_jobs' => $batch->failedJobs,
            'processed_jobs' => $batch->processedJobs(),
            'progress' => $batch->progress(),
            'finished' => $batch->finished(),
            'cancelled' => $batch->cancelled(),
            'created_at' => $batch->created_at,
            'finished_at' => $batch->finished_at,
        ];
    }

    /**
     * Cancel a job batch.
     *
     * @param  string  $batchId  The batch ID
     * @return bool Whether the cancellation was successful
     */
    protected function cancelJobBatch(string $batchId): bool
    {
        try {
            $batch = Bus::findBatch($batchId);

            if (! $batch) {
                return false;
            }

            $batch->cancel();

            return true;
        } catch (Throwable $e) {
            Log::error('Failed to cancel batch', [
                'batch_id' => $batchId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
