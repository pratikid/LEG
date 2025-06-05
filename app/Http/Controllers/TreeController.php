<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Tree;
use Illuminate\Http\Request;
use Illuminate\View\View;
use App\Services\GedcomService;

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
        $path = $request->file('gedcom')->store('gedcoms', 'private');
        $content = file_get_contents(storage_path('app/private/' . $path));

        // Parse and import GEDCOM
        $gedcomService = new GedcomService();
        $parsed = $gedcomService->parse($content);
        // TODO: Choose/create tree for import. For now, create a new tree per import.
        $tree = \App\Models\Tree::create([
            'name' => 'Imported Tree ' . now()->format('Y-m-d H:i:s'),
            'user_id' => $request->user()->id,
            'description' => 'Imported from GEDCOM',
        ]);
        $gedcomService->importToDatabase($parsed, $tree->id);

        return redirect()->route('trees.index')->with('success', 'GEDCOM file imported successfully.');
    }

    /**
     * Export a tree as a GEDCOM file.
     *
     * @param int $id Tree ID
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportGedcom($id)
    {
        $tree = \App\Models\Tree::findOrFail((int) $id);
        $gedcomService = new GedcomService();
        $gedcomContent = $gedcomService->exportFromDatabase($tree->id);
        $filename = 'tree_' . $tree->id . '_' . now()->format('Ymd_His') . '.ged';
        return response()->streamDownload(function () use ($gedcomContent) {
            echo $gedcomContent;
        }, $filename, [
            'Content-Type' => 'text/plain',
        ]);
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
