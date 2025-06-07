<?php

namespace App\Http\Controllers;

use App\Models\TimelineEvent;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimelineReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function generateTimelineReport(Request $request): \Illuminate\Http\Response
    {
        $events = TimelineEvent::where(function ($query) {
            $query->where('user_id', Auth::id())
                ->orWhere('is_public', true);
        })
            ->orderBy('event_date', 'desc')
            ->get();

        $pdf = PDF::loadView('timeline.reports.timeline', [
            'events' => $events,
            'user' => Auth::user(),
            'generatedAt' => now(),
        ]);

        return $pdf->download('timeline-report.pdf');
    }

    public function generateEventReport(TimelineEvent $timelineEvent): \Illuminate\Http\Response
    {
        if (! $timelineEvent->is_public && $timelineEvent->user_id !== Auth::id()) {
            abort(403);
        }

        $pdf = PDF::loadView('timeline.reports.event', [
            'event' => $timelineEvent,
            'user' => Auth::user(),
            'generatedAt' => now(),
        ]);

        return $pdf->download('event-report.pdf');
    }

    public function generateTypeReport(Request $request): \Illuminate\Http\Response
    {
        $validated = $request->validate(['type' => 'required|in:birth,death,marriage,divorce,immigration,other']);
        $type = (string) $validated['type'];

        $events = TimelineEvent::where(function ($query) {
            $query->where('user_id', Auth::id())
                ->orWhere('is_public', true);
        })
            ->where('event_type', $type)
            ->orderBy('event_date', 'desc')
            ->get();

        $pdf = PDF::loadView('timeline.reports.type', [
            'events' => $events,
            'type' => $type,
            'user' => Auth::user(),
            'generatedAt' => now(),
        ]);

        return $pdf->download("{$type}-events-report.pdf");
    }
}
