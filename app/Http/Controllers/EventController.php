<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class EventController extends Controller
{
    public function calendar(): View
    {
        return view('events.calendar');
    }

    public function index(): View
    {
        return view('events.index');
    }

    public function create(): View
    {
        return view('events.create');
    }

    public function show(int $id): View
    {
        return view('events.show');
    }

    public function edit(int $id): View
    {
        return view('events.edit');
    }

    public function update(Request $request, int $id): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('events.index')->with('info', 'Update coming soon.');
    }

    public function destroy(int $id): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('events.index')->with('info', 'Delete coming soon.');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        return redirect()->route('events.index')->with('info', 'Store coming soon.');
    }
}
