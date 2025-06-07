<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Group;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GroupController extends Controller
{
    public function index(): View
    {
        $groups = Group::latest()->paginate(10);

        return view('groups.index', compact('groups'));
    }

    public function create(): View
    {
        $trees = \App\Models\Tree::all();
        $error = null;
        if ($trees->isEmpty()) {
            $error = 'No trees available. Please create a tree first.';
        }

        return view('groups.create', compact('trees', 'error'));
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var array{name: string, description: string|null, tree_id: int} $validated */
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
        /** @var array{name: string, description: string|null, tree_id: int} $validated */
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
