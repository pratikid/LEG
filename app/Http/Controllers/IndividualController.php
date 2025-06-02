<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Individual;

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
        return view('individuals.create');
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
    public function show(int $id): View
    {
        $individual = Individual::findOrFail($id);
        $allIndividuals = Individual::all();
        return view('individuals.show', compact('individual', 'allIndividuals'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        $individual = Individual::findOrFail($id);
        return view('individuals.edit', compact('individual'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $individual = Individual::findOrFail($id);
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
    public function destroy(int $id): RedirectResponse
    {
        $individual = Individual::findOrFail($id);
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
