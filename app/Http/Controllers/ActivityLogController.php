<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Traits\HandlesActivityLogFiltering;
use Illuminate\Http\Request;
use RuntimeException;

final class ActivityLogController extends Controller
{
    use HandlesActivityLogFiltering;
    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
    }

    public function index(Request $request): \Illuminate\View\View
    {
        $query = $this->getFilteredActivityLogQuery($request);

        $logs = $query->paginate(20);

        return view('admin.activity-logs.index', compact('logs'));
    }

    public function show(ActivityLog $activityLog): \Illuminate\View\View
    {
        return view('admin.activity-logs.show', compact('activityLog'));
    }

    public function export(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $query = $this->getFilteredActivityLogQuery($request);

        $logs = $query->get();

        return response()->streamDownload(function () use ($logs) {
            $file = fopen('php://output', 'w');
            if ($file === false) {
                throw new RuntimeException('Failed to open output stream');
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
                    (string) $log->id,
                    $log->user->name ?? 'Unknown',
                    (string) $log->action,
                    (string) $log->model_type,
                    (string) $log->model_id,
                    json_encode($log->old_values),
                    json_encode($log->new_values),
                    (string) $log->ip_address,
                    (string) $log->user_agent,
                    (string) $log->created_at,
                ]);
            }

            fclose($file);
        }, 'activity-logs.csv');
    }
}
