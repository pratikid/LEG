<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class GroupController extends Controller
{
    public function index(): View
    {
        // TODO: Fetch groups
        return view('groups.index');
    }

    public function create(): View
    {
        return view('groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        // TODO: Implement validation and storage logic
        return redirect()->route('groups.index')->with('success', 'Group created successfully.');
    }

    public function show(int $id): View
    {
        // TODO: Fetch group by ID
        return view('groups.show', compact('id'));
    }

    public function edit(int $id): View
    {
        // TODO: Fetch group by ID
        return view('groups.edit', compact('id'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        // TODO: Implement update logic
        return redirect()->route('groups.index')->with('success', 'Group updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        // TODO: Implement delete logic
        return redirect()->route('groups.index')->with('success', 'Group deleted successfully.');
    }
} 