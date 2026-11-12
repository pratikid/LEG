<?php

declare(strict_types=1);

namespace App\Traits;

use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Trait for handling error operations
 */
trait HandlesErrors
{
    /**
     * Handle a generic exception.
     *
     * @param  Exception  $exception  The exception to handle
     * @param  Request|null  $request  The HTTP request
     * @return JsonResponse The JSON response
     */
    protected function handleException(Exception $exception, ?Request $request = null): JsonResponse
    {
        // Log the exception
        $this->logException($exception, $request);

        // Handle specific exception types
        if ($exception instanceof ValidationException) {
            return $this->handleValidationException($exception);
        }

        if ($exception instanceof AuthenticationException) {
            return $this->handleAuthenticationException($exception);
        }

        if ($exception instanceof AuthorizationException) {
            return $this->handleAuthorizationException($exception);
        }

        if ($exception instanceof ModelNotFoundException) {
            return $this->handleModelNotFoundException($exception);
        }

        if ($exception instanceof NotFoundHttpException) {
            return $this->handleNotFoundHttpException($exception);
        }

        if ($exception instanceof HttpException) {
            return $this->handleHttpException($exception);
        }

        if ($exception instanceof QueryException) {
            return $this->handleQueryException($exception);
        }

        // Handle generic exceptions
        return $this->handleGenericException($exception);
    }

    /**
     * Log an exception.
     *
     * @param  Exception  $exception  The exception to log
     * @param  Request|null  $request  The HTTP request
     */
    protected function logException(Exception $exception, ?Request $request = null): void
    {
        $context = [
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ];

        if ($request) {
            $context['url'] = $request->fullUrl();
            $context['method'] = $request->method();
            $context['ip'] = $request->ip();
            $context['user_agent'] = $request->userAgent();

            if ($request->user()) {
                $context['user_id'] = $request->user()->id;
            }
        }

        Log::error('Exception caught', $context);
    }

    /**
     * Handle a validation exception.
     *
     * @param  ValidationException  $exception  The validation exception
     * @return JsonResponse The JSON response
     */
    protected function handleValidationException(ValidationException $exception): JsonResponse
    {
        return response()->json([
            'message' => 'The given data was invalid.',
            'errors' => $exception->errors(),
        ], 422);
    }

    /**
     * Handle an authentication exception.
     *
     * @param  AuthenticationException  $exception  The authentication exception
     * @return JsonResponse The JSON response
     */
    protected function handleAuthenticationException(AuthenticationException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage() ?: 'Unauthenticated.',
        ], 401);
    }

    /**
     * Handle an authorization exception.
     *
     * @param  AuthorizationException  $exception  The authorization exception
     * @return JsonResponse The JSON response
     */
    protected function handleAuthorizationException(AuthorizationException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage() ?: 'This action is unauthorized.',
        ], 403);
    }

    /**
     * Handle a model not found exception.
     *
     * @param  ModelNotFoundException  $exception  The model not found exception
     * @return JsonResponse The JSON response
     */
    protected function handleModelNotFoundException(ModelNotFoundException $exception): JsonResponse
    {
        $model = basename(str_replace('\\', '/', $exception->getModel()));

        return response()->json([
            'message' => "The requested {$model} was not found.",
        ], 404);
    }

    /**
     * Handle a not found HTTP exception.
     *
     * @param  NotFoundHttpException  $exception  The not found HTTP exception
     * @return JsonResponse The JSON response
     */
    protected function handleNotFoundHttpException(NotFoundHttpException $exception): JsonResponse
    {
        return response()->json([
            'message' => 'The requested resource was not found.',
        ], 404);
    }

    /**
     * Handle an HTTP exception.
     *
     * @param  HttpException  $exception  The HTTP exception
     * @return JsonResponse The JSON response
     */
    protected function handleHttpException(HttpException $exception): JsonResponse
    {
        return response()->json([
            'message' => $exception->getMessage() ?: 'HTTP error occurred.',
        ], $exception->getStatusCode());
    }

    /**
     * Handle a query exception.
     *
     * @param  QueryException  $exception  The query exception
     * @return JsonResponse The JSON response
     */
    protected function handleQueryException(QueryException $exception): JsonResponse
    {
        $errorCode = $exception->errorInfo[1] ?? null;

        // Handle specific database error codes
        if ($errorCode === 1062) {
            return response()->json([
                'message' => 'A database entry with this information already exists.',
            ], 409);
        }

        if ($errorCode === 1451) {
            return response()->json([
                'message' => 'This resource cannot be deleted because it is referenced by other resources.',
            ], 409);
        }

        // Generic database error
        return response()->json([
            'message' => 'A database error occurred.',
        ], 500);
    }

    /**
     * Handle a generic exception.
     *
     * @param  Exception  $exception  The exception
     * @return JsonResponse The JSON response
     */
    protected function handleGenericException(Exception $exception): JsonResponse
    {
        $statusCode = 500;

        $message = $exception->getMessage();

        // In production, don't expose internal error messages
        if (app()->environment('production') && $statusCode === 500) {
            $message = 'An unexpected error occurred.';
        }

        return response()->json([
            'message' => $message,
        ], $statusCode);
    }

    /**
     * Format an exception for debugging.
     *
     * @param  Exception  $exception  The exception
     * @return array The formatted exception
     */
    protected function formatExceptionForDebugging(Exception $exception): array
    {
        return [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'trace' => collect($exception->getTrace())
                ->map(function ($trace) {
                    return array_intersect_key($trace, array_flip(['file', 'line', 'function', 'class']));
                })
                ->toArray(),
        ];
    }

    /**
     * Get a user-friendly error message for a specific exception.
     *
     * @param  Exception  $exception  The exception
     * @return string The user-friendly error message
     */
    protected function getUserFriendlyErrorMessage(Exception $exception): string
    {
        // Map exception classes to user-friendly messages
        $messages = [
            ValidationException::class => 'The data you provided is invalid.',
            AuthenticationException::class => 'You need to log in to access this resource.',
            AuthorizationException::class => 'You do not have permission to perform this action.',
            ModelNotFoundException::class => 'The requested resource was not found.',
            NotFoundHttpException::class => 'The requested page or resource was not found.',
            QueryException::class => 'A database error occurred while processing your request.',
        ];

        foreach ($messages as $class => $message) {
            if ($exception instanceof $class) {
                return $message;
            }
        }

        return 'An unexpected error occurred. Please try again later.';
    }

    /**
     * Check if an exception should be reported.
     *
     * @param  Exception  $exception  The exception
     * @return bool Whether the exception should be reported
     */
    protected function shouldReportException(Exception $exception): bool
    {
        // Don't report these exception types
        $dontReport = [
            ValidationException::class,
            AuthenticationException::class,
            AuthorizationException::class,
            ModelNotFoundException::class,
            NotFoundHttpException::class,
        ];

        foreach ($dontReport as $class) {
            if ($exception instanceof $class) {
                return false;
            }
        }

        return true;
    }
}
