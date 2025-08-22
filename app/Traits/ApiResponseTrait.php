<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Trait for API controllers to handle common response patterns
 */
trait ApiResponseTrait
{
    /**
     * Format paginated response.
     *
     * @param LengthAwarePaginator $paginator
     * @param array $additionalData Additional data to include in the response
     * @return JsonResponse
     */
    protected function paginatedResponse(LengthAwarePaginator $paginator, array $additionalData = []): JsonResponse
    {
        $response = [
            'data' => $paginator->items(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ];
        
        if (!empty($additionalData)) {
            $response = array_merge($response, $additionalData);
        }
        
        return response()->json($response);
    }
    
    /**
     * Format a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse($data = null, string $message = 'Success', int $statusCode = 200): JsonResponse
    {
        $response = ['message' => $message];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return response()->json($response, $statusCode);
    }
    
    /**
     * Format an error response.
     *
     * @param string $message
     * @param array $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function errorResponse(string $message = 'Error', array $errors = [], int $statusCode = 400): JsonResponse
    {
        $response = ['message' => $message];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        return response()->json($response, $statusCode);
    }
}