<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class StoryController extends Controller
{
    public function index(): View
    {
        return view('stories.index');
    }
    public function create(): View
    {
        return view('stories.create');
    }
    public function show($id): View
    {
        return view('stories.show');
    }
    public function edit($id): View
    {
        return view('stories.edit');
    }
    public function update(Request $request, $id)
    {
        return redirect()->route('stories.index')->with('info', 'Update coming soon.');
    }
    public function destroy($id)
    {
        return redirect()->route('stories.index')->with('info', 'Delete coming soon.');
    }
    public function store(Request $request)
    {
        return redirect()->route('stories.index')->with('info', 'Store coming soon.');
    }
} 