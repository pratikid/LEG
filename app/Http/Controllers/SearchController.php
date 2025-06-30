<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Family;
use App\Models\Individual;
use App\Models\Source;
use App\Models\Story;
use App\Models\Tree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SearchController extends Controller
{
    /**
     * Display search results.
     */
    public function index(Request $request): View
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all');
        $treeId = $request->get('tree_id');

        if (empty($query)) {
            return view('search.index', [
                'query' => '',
                'individuals' => collect(),
                'families' => collect(),
                'events' => collect(),
                'sources' => collect(),
                'stories' => collect(),
                'trees' => collect(),
                'totalResults' => 0,
            ]);
        }

        $results = [];

        // Search individuals
        if ($type === 'all' || $type === 'individuals') {
            $individualsQuery = Individual::with(['tree', 'familiesAsHusband', 'familiesAsWife'])
                ->whereHas('tree', function ($q) {
                    $q->where('user_id', Auth::id());
                });

            if ($treeId) {
                $individualsQuery->where('tree_id', $treeId);
            }

            $individuals = $individualsQuery->where(function ($q) use ($query) {
                $q->where('first_name', 'like', "%{$query}%")
                  ->orWhere('last_name', 'like', "%{$query}%")
                  ->orWhere('middle_name', 'like', "%{$query}%")
                  ->orWhere('maiden_name', 'like', "%{$query}%")
                  ->orWhere('nickname', 'like', "%{$query}%")
                  ->orWhere('notes', 'like', "%{$query}%");
            })->paginate(10);

            $results['individuals'] = $individuals;
        }

        // Search families
        if ($type === 'all' || $type === 'families') {
            $familiesQuery = Family::with(['tree', 'husband', 'wife', 'children'])
                ->whereHas('tree', function ($q) {
                    $q->where('user_id', Auth::id());
                });

            if ($treeId) {
                $familiesQuery->where('tree_id', $treeId);
            }

            $families = $familiesQuery->where(function ($q) use ($query) {
                $q->where('marriage_date', 'like', "%{$query}%")
                  ->orWhere('marriage_place', 'like', "%{$query}%")
                  ->orWhere('notes', 'like', "%{$query}%")
                  ->orWhereHas('husband', function ($hq) use ($query) {
                      $hq->where('first_name', 'like', "%{$query}%")
                         ->orWhere('last_name', 'like', "%{$query}%");
                  })
                  ->orWhereHas('wife', function ($wq) use ($query) {
                      $wq->where('first_name', 'like', "%{$query}%")
                         ->orWhere('last_name', 'like', "%{$query}%");
                  });
            })->paginate(10);

            $results['families'] = $families;
        }

        // Search events
        if ($type === 'all' || $type === 'events') {
            $eventsQuery = Event::with(['tree', 'individual', 'family'])
                ->whereHas('tree', function ($q) {
                    $q->where('user_id', Auth::id());
                });

            if ($treeId) {
                $eventsQuery->where('tree_id', $treeId);
            }

            $events = $eventsQuery->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('event_place', 'like', "%{$query}%")
                  ->orWhere('event_city', 'like', "%{$query}%")
                  ->orWhere('event_state', 'like', "%{$query}%")
                  ->orWhere('event_country', 'like', "%{$query}%");
            })->paginate(10);

            $results['events'] = $events;
        }

        // Search sources
        if ($type === 'all' || $type === 'sources') {
            $sourcesQuery = Source::with(['tree', 'repository'])
                ->whereHas('tree', function ($q) {
                    $q->where('user_id', Auth::id());
                });

            if ($treeId) {
                $sourcesQuery->where('tree_id', $treeId);
            }

            $sources = $sourcesQuery->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('author', 'like', "%{$query}%")
                  ->orWhere('publication_info', 'like', "%{$query}%")
                  ->orWhere('notes', 'like', "%{$query}%");
            })->paginate(10);

            $results['sources'] = $sources;
        }

        // Search stories
        if ($type === 'all' || $type === 'stories') {
            $storiesQuery = Story::with(['tree', 'individuals'])
                ->whereHas('tree', function ($q) {
                    $q->where('user_id', Auth::id());
                });

            if ($treeId) {
                $storiesQuery->where('tree_id', $treeId);
            }

            $stories = $storiesQuery->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('summary', 'like', "%{$query}%");
            })->paginate(10);

            $results['stories'] = $stories;
        }

        // Search trees
        if ($type === 'all' || $type === 'trees') {
            $treesQuery = Tree::with('user')
                ->where('user_id', Auth::id());

            $trees = $treesQuery->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })->paginate(10);

            $results['trees'] = $trees;
        }

        // Calculate total results
        $totalResults = 0;
        foreach ($results as $result) {
            $totalResults += $result->total();
        }

        // Get user's trees for filter dropdown
        $userTrees = Tree::where('user_id', Auth::id())->get();

        return view('search.index', array_merge($results, [
            'query' => $query,
            'type' => $type,
            'treeId' => $treeId,
            'userTrees' => $userTrees,
            'totalResults' => $totalResults,
        ]));
    }

    /**
     * Get search suggestions for autocomplete.
     */
    public function suggestions(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $suggestions = [];

        // Individual name suggestions
        $individuals = Individual::whereHas('tree', function ($q) {
            $q->where('user_id', Auth::id());
        })
        ->where(function ($q) use ($query) {
            $q->where('first_name', 'like', "%{$query}%")
              ->orWhere('last_name', 'like', "%{$query}%");
        })
        ->limit(5)
        ->get(['first_name', 'last_name']);

        foreach ($individuals as $individual) {
            $suggestions[] = [
                'type' => 'individual',
                'text' => $individual->first_name . ' ' . $individual->last_name,
                'value' => $individual->first_name . ' ' . $individual->last_name,
            ];
        }

        // Event title suggestions
        $events = Event::whereHas('tree', function ($q) {
            $q->where('user_id', Auth::id());
        })
        ->where('title', 'like', "%{$query}%")
        ->limit(5)
        ->get(['title']);

        foreach ($events as $event) {
            $suggestions[] = [
                'type' => 'event',
                'text' => $event->title,
                'value' => $event->title,
            ];
        }

        // Source title suggestions
        $sources = Source::whereHas('tree', function ($q) {
            $q->where('user_id', Auth::id());
        })
        ->where('title', 'like', "%{$query}%")
        ->limit(5)
        ->get(['title']);

        foreach ($sources as $source) {
            $suggestions[] = [
                'type' => 'source',
                'text' => $source->title,
                'value' => $source->title,
            ];
        }

        return response()->json($suggestions);
    }
}
