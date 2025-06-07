<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request): \Illuminate\View\View
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
            $query->whereDate('created_at', '>=', (string) $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', (string) $request->end_date);
        }

        $logs = $query->paginate(20);

        return view('admin.activity-logs.index', compact('logs'));
    }

    public function show(ActivityLog $activityLog): \Illuminate\View\View
    {
        return view('admin.activity-logs.show', compact('activityLog'));
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = ActivityLog::with('user')
            ->orderBy('created_at', 'desc');

        // Apply the same filters as the index method
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }
        if ($request->filled('model_type')) {
            $query->where('model_type', $request->model_type);
        }
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', (string) $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', (string) $request->end_date);
        }

        $logs = $query->get();

        return response()->streamDownload(function () use ($logs) {
            $file = fopen('php://output', 'w');
            if ($file === false) {
                throw new \RuntimeException('Failed to open output stream');
            }

            // Add headers
            fputcsv($file, [
                'ID',
                'User',
                'Action',
                'Model Type',
                'Model ID',
                'Old Values',
                'New Values',
                'IP Address',
                'User Agent',
                'Created At',
            ]);

            // Add data
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->user?->name ?? 'Unknown',
                    $log->action,
                    $log->model_type,
                    $log->model_id,
                    json_encode($log->old_values),
                    json_encode($log->new_values),
                    $log->ip_address,
                    $log->user_agent,
                    $log->created_at,
                ]);
            }

            fclose($file);
        }, 'activity-logs.csv');
    }
}
