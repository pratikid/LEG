<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class IndividualController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        // TODO: Fetch individuals
        return view('individuals.index');
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
        // TODO: Implement validation and storage logic
        return redirect()->route('individuals.index')->with('success', 'Individual created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): View
    {
        // TODO: Fetch individual by ID
        return view('individuals.show', compact('id'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id): View
    {
        // TODO: Fetch individual by ID
        return view('individuals.edit', compact('id'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        // TODO: Implement update logic
        return redirect()->route('individuals.index')->with('success', 'Individual updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): RedirectResponse
    {
        // TODO: Implement delete logic
        return redirect()->route('individuals.index')->with('success', 'Individual deleted successfully.');
    }
}
