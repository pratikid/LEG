<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use App\Models\Group;

class GroupController extends Controller
{
    public function index(): View
    {
        $groups = Group::latest()->paginate(10);
        return view('groups.index', compact('groups'));
    }

    public function create(): View
    {
        return view('groups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'tree_id' => ['required', 'integer', 'exists:trees,id'],
        ]);
        Group::create($validated);
        return redirect()->route('groups.index')->with('success', 'Group created successfully.');
    }

    public function show(int $id): View
    {
        $group = Group::findOrFail($id);
        return view('groups.show', compact('group'));
    }

    public function edit(int $id): View
    {
        $group = Group::findOrFail($id);
        return view('groups.edit', compact('group'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $group = Group::findOrFail($id);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'tree_id' => ['required', 'integer', 'exists:trees,id'],
        ]);
        $group->update($validated);
        return redirect()->route('groups.index')->with('success', 'Group updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $group = Group::findOrFail($id);
        $group->delete();
        return redirect()->route('groups.index')->with('success', 'Group deleted successfully.');
    }
} 