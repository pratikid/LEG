<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class SourceController extends Controller
{
    public function index(): View
    {
        return view('sources.index');
    }
    public function create(): View
    {
        return view('sources.create');
    }
    public function show($id): View
    {
        return view('sources.show');
    }
    public function edit($id): View
    {
        return view('sources.edit');
    }
    public function update(Request $request, $id)
    {
        return redirect()->route('sources.index')->with('info', 'Update coming soon.');
    }
    public function destroy($id)
    {
        return redirect()->route('sources.index')->with('info', 'Delete coming soon.');
    }
    public function store(Request $request)
    {
        return redirect()->route('sources.index')->with('info', 'Store coming soon.');
    }
} 