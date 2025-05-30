<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class TreeController extends Controller
{
    public function index(): View
    {
        return view('trees.index');
    }

    public function import(): \Illuminate\View\View
    {
        return view('trees.import');
    }

    public function handleImport(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'gedcom' => ['required', 'file', 'mimes:ged,gedcom', 'max:10240'], // 10MB max
        ]);
        // TODO: Implement actual GEDCOM parsing and import logic
        return redirect()->route('trees.index')->with('success', 'GEDCOM file uploaded successfully.');
    }

    public function create(): View
    {
        return view('trees.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        // TODO: Implement validation and storage logic
        return redirect()->route('trees.index')->with('success', 'Tree created successfully.');
    }

    public function show(int $id): View
    {
        // TODO: Fetch tree by ID
        return view('trees.show', compact('id'));
    }

    public function edit(int $id): View
    {
        // TODO: Fetch tree by ID
        return view('trees.edit', compact('id'));
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        // TODO: Implement update logic
        return redirect()->route('trees.index')->with('success', 'Tree updated successfully.');
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        // TODO: Implement delete logic
        return redirect()->route('trees.index')->with('success', 'Tree deleted successfully.');
    }
} 