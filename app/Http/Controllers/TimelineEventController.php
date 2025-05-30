<?php

namespace App\Http\Controllers;

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
            'except' => ['index', 'show']
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = TimelineEvent::query();

        // For guests and non-admin users, only show public events or their own events
        if (!$request->user() || !$request->user()->is_admin) {
            $query->where(function ($q) use ($request) {
                $q->where('is_public', true);
                if ($request->user()) {
                    $q->orWhere('user_id', $request->user()->id);
                }
            });
        }

        // Search term
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('title', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Event type filter
        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        // Date range filter
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
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
                        $query->where('event_date', '>=', $request->start_date);
                    }
                    if ($request->filled('end_date')) {
                        $query->where('event_date', '<=', $request->end_date);
                    }
                    break;
            }
        }

        // Location filter
        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Visibility filter (only for authenticated users)
        if ($request->user() && $request->filled('visibility')) {
            $query->where('is_public', $request->visibility === 'public');
        }

        // Sorting
        if ($request->filled('sort')) {
            [$column, $direction] = explode('_', $request->sort);
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
    public function create()
    {
        return view('timeline.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'event_type' => 'required|in:birth,death,marriage,divorce,immigration,other',
            'location' => 'nullable|string|max:255',
            'is_public' => 'boolean',
        ]);

        $validated['user_id'] = Auth::id();
        $validated['is_public'] = $request->boolean('is_public');

        TimelineEvent::create($validated);

        return redirect()->route('timeline.index')
            ->with('success', 'Timeline event created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TimelineEvent $timelineEvent)
    {
        // Check if the event is public or belongs to the authenticated user
        if (!$timelineEvent->is_public && (!$timelineEvent->user_id || $timelineEvent->user_id !== Auth::id())) {
            abort(403, 'This event is private.');
        }

        return view('timeline.show', compact('timelineEvent'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimelineEvent $timelineEvent)
    {
        return view('timeline.edit', compact('timelineEvent'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TimelineEvent $timelineEvent)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|date',
            'event_type' => 'required|in:birth,death,marriage,divorce,immigration,other',
            'location' => 'nullable|string|max:255',
            'is_public' => 'boolean',
        ]);

        $validated['is_public'] = $request->boolean('is_public');

        $timelineEvent->update($validated);

        return redirect()->route('timeline.index')
            ->with('success', 'Timeline event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimelineEvent $timelineEvent)
    {
        $timelineEvent->delete();

        return redirect()->route('timeline.index')
            ->with('success', 'Timeline event deleted successfully.');
    }
}
