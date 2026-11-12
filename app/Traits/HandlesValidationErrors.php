<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Trait for handling validation errors consistently
 */
trait HandlesValidationErrors
{
    /**
     * Handle validation exception and return a consistent response.
     *
     * @param  string  $message  Custom error message
     */
    protected function handleValidationException(ValidationException $exception, string $message = 'Validation failed'): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'errors' => $exception->errors(),
        ], 422);
    }

    /**
     * Execute a callback and handle validation exceptions.
     *
     * @param  callable  $callback  The callback to execute
     * @param  string  $errorMessage  Custom error message for validation failures
     * @return mixed The callback result or error response
     */
    protected function tryValidation(callable $callback, string $errorMessage = 'Validation failed')
    {
        try {
            return $callback();
        } catch (ValidationException $e) {
            return $this->handleValidationException($e, $errorMessage);
        }
    }
}
