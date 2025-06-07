<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    #[\Override]
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    #[\Override]
    public function render($request, Throwable $exception): SymfonyResponse
    {
        if ($exception instanceof QueryException) {
            $message = $exception->getMessage();
            if (
                (str_contains($message, 'does not exist') || str_contains($message, 'Undefined table')) &&
                app()->environment('local', 'development', 'testing')
            ) {
                return response()->view('errors.missing-table', [
                    'error' => 'A required database table is missing. Please run migrations: php artisan migrate',
                    'details' => $message,
                ], 500);
            }
        }

        return parent::render($request, $exception);
    }
}
