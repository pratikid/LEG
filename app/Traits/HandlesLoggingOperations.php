<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Trait for handling logging operations
 */
trait HandlesLoggingOperations
{
    /**
     * Log an emergency message.
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logEmergency(string $message, array $context = []): void
    {
        Log::emergency($message, $this->enrichLogContext($context));
    }
    
    /**
     * Log an alert message.
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logAlert(string $message, array $context = []): void
    {
        Log::alert($message, $this->enrichLogContext($context));
    }
    
    /**
     * Log a critical message.
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logCritical(string $message, array $context = []): void
    {
        Log::critical($message, $this->enrichLogContext($context));
    }
    
    /**
     * Log an error message.
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error($message, $this->enrichLogContext($context));
    }
    
    /**
     * Log a warning message.
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logWarning(string $message, array $context = []): void
    {
        Log::warning($message, $this->enrichLogContext($context));
    }
    
    /**
     * Log a notice message.
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logNotice(string $message, array $context = []): void
    {
        Log::notice($message, $this->enrichLogContext($context));
    }
    
    /**
     * Log an info message.
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info($message, $this->enrichLogContext($context));
    }
    
    /**
     * Log a debug message.
     *
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logDebug(string $message, array $context = []): void
    {
        Log::debug($message, $this->enrichLogContext($context));
    }
    
    /**
     * Log a message with an arbitrary level.
     *
     * @param string $level The log level
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        Log::log($level, $message, $this->enrichLogContext($context));
    }
    
    /**
     * Log an exception.
     *
     * @param \Throwable $exception The exception to log
     * @param string $level The log level
     * @param string $message Optional message to include
     * @param array $context Additional context data
     * @return void
     */
    protected function logException(Throwable $exception, string $level = 'error', string $message = '', array $context = []): void
    {
        $exceptionContext = [
            'exception' => $exception,
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => $exception->getTraceAsString(),
            'previous' => $exception->getPrevious() ? [
                'message' => $exception->getPrevious()->getMessage(),
                'file' => $exception->getPrevious()->getFile(),
                'line' => $exception->getPrevious()->getLine(),
            ] : null,
        ];
        
        $logMessage = $message ?: $exception->getMessage();
        $mergedContext = array_merge($exceptionContext, $context);
        
        $this->log($level, $logMessage, $this->enrichLogContext($mergedContext));
    }
    
    /**
     * Log a database query.
     *
     * @param string $query The SQL query
     * @param array $bindings The query bindings
     * @param float $time The query execution time in milliseconds
     * @param array $context Additional context data
     * @return void
     */
    protected function logQuery(string $query, array $bindings = [], float $time = 0.0, array $context = []): void
    {
        $queryContext = [
            'query' => $query,
            'bindings' => $bindings,
            'time_ms' => $time,
        ];
        
        $mergedContext = array_merge($queryContext, $context);
        
        $this->logDebug('Database query executed', $this->enrichLogContext($mergedContext));
    }
    
    /**
     * Log a model event.
     *
     * @param string $event The event name (created, updated, deleted, etc.)
     * @param string $model The model class name
     * @param mixed $modelId The model ID
     * @param array $attributes The model attributes
     * @param array $context Additional context data
     * @return void
     */
    protected function logModelEvent(string $event, string $model, $modelId, array $attributes = [], array $context = []): void
    {
        $modelContext = [
            'model' => $model,
            'model_id' => $modelId,
            'event' => $event,
            'attributes' => $attributes,
        ];
        
        $mergedContext = array_merge($modelContext, $context);
        
        $this->logInfo("Model {$event}: {$model}", $this->enrichLogContext($mergedContext));
    }
    
    /**
     * Log an API request.
     *
     * @param string $method The HTTP method
     * @param string $endpoint The API endpoint
     * @param array $requestData The request data
     * @param int $statusCode The response status code
     * @param array $responseData The response data
     * @param float $duration The request duration in milliseconds
     * @param array $context Additional context data
     * @return void
     */
    protected function logApiRequest(
        string $method,
        string $endpoint,
        array $requestData = [],
        int $statusCode = 0,
        array $responseData = [],
        float $duration = 0.0,
        array $context = []
    ): void {
        $apiContext = [
            'method' => $method,
            'endpoint' => $endpoint,
            'request_data' => $requestData,
            'status_code' => $statusCode,
            'response_data' => $responseData,
            'duration_ms' => $duration,
        ];
        
        $mergedContext = array_merge($apiContext, $context);
        $level = $statusCode >= 400 ? 'error' : 'info';
        
        $this->log($level, "API {$method} {$endpoint} ({$statusCode})", $this->enrichLogContext($mergedContext));
    }
    
    /**
     * Log an authentication event.
     *
     * @param string $event The event name (login, logout, failed, etc.)
     * @param string $username The username
     * @param bool $success Whether the authentication was successful
     * @param string $reason The reason for failure (if applicable)
     * @param array $context Additional context data
     * @return void
     */
    protected function logAuthEvent(
        string $event,
        string $username,
        bool $success = true,
        string $reason = '',
        array $context = []
    ): void {
        $authContext = [
            'event' => $event,
            'username' => $username,
            'success' => $success,
            'reason' => $reason,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
        
        $mergedContext = array_merge($authContext, $context);
        $level = $success ? 'info' : 'warning';
        
        $this->log($level, "Auth {$event}: {$username}", $this->enrichLogContext($mergedContext));
    }
    
    /**
     * Log a job event.
     *
     * @param string $event The event name (dispatched, processing, processed, failed, etc.)
     * @param string $job The job class name
     * @param string $queue The queue name
     * @param array $payload The job payload
     * @param array $context Additional context data
     * @return void
     */
    protected function logJobEvent(
        string $event,
        string $job,
        string $queue = 'default',
        array $payload = [],
        array $context = []
    ): void {
        $jobContext = [
            'event' => $event,
            'job' => $job,
            'queue' => $queue,
            'payload' => $payload,
        ];
        
        $mergedContext = array_merge($jobContext, $context);
        $level = $event === 'failed' ? 'error' : 'info';
        
        $this->log($level, "Job {$event}: {$job}", $this->enrichLogContext($mergedContext));
    }
    
    /**
     * Log a performance metric.
     *
     * @param string $name The metric name
     * @param float $value The metric value
     * @param string $unit The metric unit
     * @param array $context Additional context data
     * @return void
     */
    protected function logPerformanceMetric(
        string $name,
        float $value,
        string $unit = 'ms',
        array $context = []
    ): void {
        $metricContext = [
            'metric' => $name,
            'value' => $value,
            'unit' => $unit,
        ];
        
        $mergedContext = array_merge($metricContext, $context);
        
        $this->logInfo("Performance metric: {$name} = {$value}{$unit}", $this->enrichLogContext($mergedContext));
    }
    
    /**
     * Log a deprecation notice.
     *
     * @param string $feature The deprecated feature
     * @param string $alternative The alternative to use
     * @param string $sinceVersion The version since deprecation
     * @param string $removeVersion The version when it will be removed
     * @param array $context Additional context data
     * @return void
     */
    protected function logDeprecation(
        string $feature,
        string $alternative = '',
        string $sinceVersion = '',
        string $removeVersion = '',
        array $context = []
    ): void {
        $deprecationContext = [
            'feature' => $feature,
            'alternative' => $alternative,
            'since_version' => $sinceVersion,
            'remove_version' => $removeVersion,
            'backtrace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1] ?? null,
        ];
        
        $mergedContext = array_merge($deprecationContext, $context);
        $message = "Deprecation notice: {$feature}";
        
        if ($alternative) {
            $message .= ". Use {$alternative} instead";
        }
        
        if ($removeVersion) {
            $message .= ". Will be removed in version {$removeVersion}";
        }
        
        $this->logWarning($message, $this->enrichLogContext($mergedContext));
    }
    
    /**
     * Log a security event.
     *
     * @param string $event The security event type
     * @param string $description The event description
     * @param array $data The event data
     * @param array $context Additional context data
     * @return void
     */
    protected function logSecurityEvent(
        string $event,
        string $description,
        array $data = [],
        array $context = []
    ): void {
        $securityContext = [
            'event' => $event,
            'description' => $description,
            'data' => $data,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
        ];
        
        $mergedContext = array_merge($securityContext, $context);
        
        $this->logAlert("Security event: {$event}", $this->enrichLogContext($mergedContext));
    }
    
    /**
     * Get the log channel for a specific context.
     *
     * @param string $context The context name
     * @return \Illuminate\Log\LogManager The log channel
     */
    protected function getLogChannel(string $context = 'default')
    {
        $channelMap = [
            'api' => 'api',
            'auth' => 'auth',
            'job' => 'queue',
            'security' => 'security',
            'performance' => 'performance',
            'database' => 'database',
            'default' => config('logging.default'),
        ];
        
        $channel = $channelMap[$context] ?? $channelMap['default'];
        
        return Log::channel($channel);
    }
    
    /**
     * Enrich the log context with additional data.
     *
     * @param array $context The original context
     * @return array The enriched context
     */
    protected function enrichLogContext(array $context): array
    {
        // Add common context data
        $enrichedContext = array_merge([
            'request_id' => request()->header('X-Request-ID') ?? (string) uniqid('req-'),
            'user_id' => auth()->id(),
            'timestamp' => now()->toIso8601String(),
        ], $context);
        
        // Add custom context data from implementing class if available
        if (method_exists($this, 'getCustomLogContext')) {
            $enrichedContext = array_merge($enrichedContext, $this->getCustomLogContext());
        }
        
        return $enrichedContext;
    }
    
    /**
     * Log a batch of messages.
     *
     * @param array $messages Array of messages with level, message, and context
     * @return void
     */
    protected function logBatch(array $messages): void
    {
        foreach ($messages as $message) {
            $level = $message['level'] ?? 'info';
            $text = $message['message'] ?? '';
            $context = $message['context'] ?? [];
            
            $this->log($level, $text, $context);
        }
    }
    
    /**
     * Log with context for the current class.
     *
     * @param string $level The log level
     * @param string $message The message to log
     * @param array $context The context data
     * @return void
     */
    protected function logWithClassContext(string $level, string $message, array $context = []): void
    {
        $classContext = [
            'class' => get_class($this),
            'method' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'] ?? null,
        ];
        
        $mergedContext = array_merge($classContext, $context);
        
        $this->log($level, $message, $this->enrichLogContext($mergedContext));
    }
    
    /**
     * Log the start of a process.
     *
     * @param string $process The process name
     * @param array $params The process parameters
     * @param array $context Additional context data
     * @return string The process ID for correlation
     */
    protected function logProcessStart(string $process, array $params = [], array $context = []): string
    {
        $processId = uniqid('proc-');
        
        $processContext = [
            'process_id' => $processId,
            'process' => $process,
            'params' => $params,
            'start_time' => microtime(true),
        ];
        
        $mergedContext = array_merge($processContext, $context);
        
        $this->logInfo("Process started: {$process}", $this->enrichLogContext($mergedContext));
        
        return $processId;
    }
    
    /**
     * Log the end of a process.
     *
     * @param string $processId The process ID from logProcessStart
     * @param string $process The process name
     * @param bool $success Whether the process was successful
     * @param array $result The process result
     * @param array $context Additional context data
     * @return void
     */
    protected function logProcessEnd(
        string $processId,
        string $process,
        bool $success = true,
        array $result = [],
        array $context = []
    ): void {
        $processContext = [
            'process_id' => $processId,
            'process' => $process,
            'success' => $success,
            'result' => $result,
            'end_time' => microtime(true),
            'duration_ms' => isset($context['start_time']) 
                ? (microtime(true) - $context['start_time']) * 1000 
                : null,
        ];
        
        $mergedContext = array_merge($processContext, $context);
        $level = $success ? 'info' : 'error';
        
        $this->log($level, "Process ended: {$process}", $this->enrichLogContext($mergedContext));
    }
}