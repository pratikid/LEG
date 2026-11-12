<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Trait for handling activity log filtering operations
 */
trait HandlesActivityLogFiltering
{
    /**
     * Apply common activity log filters to a query
     *
     * @param  Request  $request  The HTTP request
     * @return Builder The filtered query
     */
    protected function getFilteredActivityLogQuery(Request $request): Builder
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        return $query;
    }
}
