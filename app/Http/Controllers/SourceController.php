<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

final class SourceController extends Controller
{
    public function index(): View
    {
        return view('sources.index');
    }

    public function create(): View
    {
        return view('sources.create');
    }

    public function show(int $id): View
    {
        return view('sources.show');
    }

    public function edit(int $id): View
    {
        return view('sources.edit');
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('sources.index')->with('info', 'Update coming soon.');
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('sources.index')->with('info', 'Delete coming soon.');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('sources.index')->with('info', 'Store coming soon.');
    }
}
