<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Models\Tree;

class TreeController extends Controller
{
    public function index(): View
    {
        $trees = Tree::latest()->paginate(10);
        return view('trees.index', compact('trees'));
    }

    public function import(): \Illuminate\View\View
    {
        return view('trees.import');
    }

    public function handleImport(\Illuminate\Http\Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'gedcom' => ['required', 'file', 'mimes:ged,gedcom', 'max:10240'],
        ]);
        // TODO: Implement actual GEDCOM parsing and import logic
        $path = $request->file('gedcom')->store('gedcoms', 'private');
        return redirect()->route('trees.index')->with('success', 'GEDCOM file uploaded successfully.');
    }

    public function create(): View
    {
        return view('trees.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $tree = Tree::create(array_merge($validated, ['user_id' => $request->user()->id]));
        return redirect()->route('trees.index')->with('success', 'Tree created successfully.');
    }

    public function show($id): View
    {
        $tree = Tree::findOrFail((int) $id);
        return view('trees.show', compact('tree'));
    }

    public function edit($id): View
    {
        $tree = Tree::findOrFail((int) $id);
        return view('trees.edit', compact('tree'));
    }

    public function update(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $tree = Tree::findOrFail((int) $id);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);
        $tree->update($validated);
        return redirect()->route('trees.index')->with('success', 'Tree updated successfully.');
    }

    public function destroy($id): \Illuminate\Http\RedirectResponse
    {
        $tree = Tree::findOrFail((int) $id);
        $tree->delete();
        return redirect()->route('trees.index')->with('success', 'Tree deleted successfully.');
    }
} 