<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\EventRequest;
use App\Models\Event;
use App\Models\Family;
use App\Models\Individual;
use App\Models\Tree;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class EventController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Event::with(['tree', 'individual', 'family', 'creator'])
            ->whereHas('tree', function ($q) {
                $q->where('user_id', Auth::id());
            });

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('event_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('event_date', '<=', $request->end_date);
        }

        // Search by title or description
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $events = $query->orderBy('event_date', 'desc')
            ->paginate(20);

        $eventTypes = Event::getEventTypes();

        return view('events.index', compact('events', 'eventTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $trees = Tree::where('user_id', Auth::id())->get();
        $individuals = Individual::whereHas('tree', function ($q) {
            $q->where('user_id', Auth::id());
        })->get();
        $families = Family::whereHas('tree', function ($q) {
            $q->where('user_id', Auth::id());
        })->get();
        $eventTypes = Event::getEventTypes();

        return view('events.create', compact('trees', 'individuals', 'families', 'eventTypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EventRequest $request): RedirectResponse
    {
        $event = Event::create(array_merge(
            $request->validated(),
            ['created_by' => Auth::id()]
        ));

        return redirect()->route('events.show', $event)
            ->with('success', 'Event created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Event $event): View
    {
        $this->authorize('view', $event);

        $event->load(['tree', 'individual', 'family', 'creator']);

        return view('events.show', compact('event'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Event $event): View
    {
        $this->authorize('update', $event);

        $trees = Tree::where('user_id', Auth::id())->get();
        $individuals = Individual::whereHas('tree', function ($q) {
            $q->where('user_id', Auth::id());
        })->get();
        $families = Family::whereHas('tree', function ($q) {
            $q->where('user_id', Auth::id());
        })->get();
        $eventTypes = Event::getEventTypes();

        return view('events.edit', compact('event', 'trees', 'individuals', 'families', 'eventTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EventRequest $request, Event $event): RedirectResponse
    {
        $this->authorize('update', $event);

        $event->update($request->validated());

        return redirect()->route('events.show', $event)
            ->with('success', 'Event updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Event $event): RedirectResponse
    {
        $this->authorize('delete', $event);

        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Event deleted successfully.');
    }

    /**
     * Display events in calendar view.
     */
    public function calendar(Request $request): View
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $events = Event::with(['tree', 'individual', 'family'])
            ->whereHas('tree', function ($q) {
                $q->where('user_id', Auth::id());
            })
            ->whereYear('event_date', $year)
            ->whereMonth('event_date', $month)
            ->get()
            ->groupBy(function ($event) {
                return $event->event_date->format('Y-m-d');
            });

        return view('events.calendar', compact('events', 'year', 'month'));
    }
}
