<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\TimelineEventRequest;
use App\Models\TimelineEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimelineEventController extends Controller
{
    public function __construct()
    {
        // Only apply authorization to actions that modify data
        $this->middleware('auth')->except(['index', 'show']);
        $this->authorizeResource(TimelineEvent::class, 'timelineEvent', [
            'except' => ['index', 'show'],
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $query = TimelineEvent::query();

        // For guests and non-admin users, only show public events or their own events
        if (! $request->user() || ! $request->user()->is_admin) {
            $query->where(function ($q) use ($request) {
                $q->where('is_public', true);
                if ($request->user()) {
                    $q->orWhere('user_id', $request->user()->id);
                }
            });
        }

        // Search term
        if ($request->filled('search')) {
            /** @var string $searchTerm */
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Event type filter
        if ($request->filled('event_type')) {
            /** @var string $eventType */
            $eventType = $request->input('event_type');
            $query->where('event_type', $eventType);
        }

        // Date range filter
        if ($request->filled('date_range')) {
            /** @var string $dateRange */
            $dateRange = $request->input('date_range');
            switch ($dateRange) {
                case 'last_week':
                    $query->where('event_date', '>=', now()->subWeek());
                    break;
                case 'last_month':
                    $query->where('event_date', '>=', now()->subMonth());
                    break;
                case 'last_year':
                    $query->where('event_date', '>=', now()->subYear());
                    break;
                case 'custom':
                    if ($request->filled('start_date')) {
                        /** @var string $startDate */
                        $startDate = $request->input('start_date');
                        $query->where('event_date', '>=', $startDate);
                    }
                    if ($request->filled('end_date')) {
                        /** @var string $endDate */
                        $endDate = $request->input('end_date');
                        $query->where('event_date', '<=', $endDate);
                    }
                    break;
            }
        }

        // Location filter
        if ($request->filled('location')) {
            /** @var string $location */
            $location = $request->input('location');
            $query->where('location', 'like', "%{$location}%");
        }

        // Visibility filter (only for authenticated users)
        if ($request->user() && $request->filled('visibility')) {
            /** @var string $visibility */
            $visibility = $request->input('visibility');
            $query->where('is_public', $visibility === 'public');
        }

        // Sorting
        if ($request->filled('sort')) {
            /** @var string $sortString */
            $sortString = $request->input('sort');
            [$column, $direction] = explode('_', $sortString);
            $query->orderBy($column === 'date' ? 'event_date' : $column, $direction);
        } else {
            $query->orderBy('event_date', 'desc');
        }

        $events = $query->paginate(10)->withQueryString();

        return view('timeline.index', compact('events'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): \Illuminate\Contracts\View\View
    {
        return view('timeline.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TimelineEventRequest $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = Auth::id();
        $validated['is_public'] = $request->boolean('is_public');
        TimelineEvent::create($validated);

        return redirect()->route('timeline.index')
            ->with('success', 'Timeline event created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TimelineEvent $timelineEvent): \Illuminate\Contracts\View\View
    {
        // Check if the event is public or belongs to the authenticated user
        if (! $timelineEvent->is_public && (! $timelineEvent->user_id || $timelineEvent->user_id !== Auth::id())) {
            abort(403, 'This event is private.');
        }

        return view('timeline.show', compact('timelineEvent'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimelineEvent $timelineEvent): \Illuminate\Contracts\View\View
    {
        return view('timeline.edit', compact('timelineEvent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TimelineEventRequest $request, TimelineEvent $timelineEvent): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_public'] = $request->boolean('is_public');
        $timelineEvent->update($validated);

        return redirect()->route('timeline.index')
            ->with('success', 'Timeline event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimelineEvent $timelineEvent): \Illuminate\Http\RedirectResponse
    {
        $timelineEvent->delete();

        return redirect()->route('timeline.index')
            ->with('success', 'Timeline event deleted successfully.');
    }
}
