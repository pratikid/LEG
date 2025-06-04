<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Individual;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndividualController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $individuals = Individual::latest()->paginate(10);

        return view('individuals.index', compact('individuals'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        $trees = \App\Models\Tree::all();
        $error = null;
        if ($trees->isEmpty()) {
            $error = 'No trees available. Please create a tree first.';
        }

        return view('individuals.create', compact('trees', 'error'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'death_date' => ['nullable', 'date', 'after:birth_date'],
            'tree_id' => ['required', 'integer', 'exists:trees,id'],
        ]);
        Individual::create($validated);

        return redirect()->route('individuals.index')->with('success', 'Individual created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id): View
    {
        $individual = Individual::findOrFail((int) $id);
        $allIndividuals = Individual::all();
        $error = null;
        if ($allIndividuals->isEmpty() || ($allIndividuals->count() === 1 && $allIndividuals->first()->id === $individual->id)) {
            $error = 'No other individuals available for relationships.';
        }

        return view('individuals.show', compact('individual', 'allIndividuals', 'error'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id): View
    {
        $individual = Individual::findOrFail((int) $id);

        return view('individuals.edit', compact('individual'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $individual = Individual::findOrFail((int) $id);
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'death_date' => ['nullable', 'date', 'after:birth_date'],
            'tree_id' => ['required', 'integer', 'exists:trees,id'],
        ]);
        $individual->update($validated);

        return redirect()->route('individuals.index')->with('success', 'Individual updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $individual = Individual::findOrFail((int) $id);
        $individual->delete();

        return redirect()->route('individuals.index')->with('success', 'Individual deleted successfully.');
    }

    /**
     * Show a timeline view of individuals (future feature).
     */
    public function timeline(): View
    {
        // Placeholder implementation
        return view('individuals.timeline');
    }
}
