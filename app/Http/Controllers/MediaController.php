<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(): View
    {
        return view('media.index');
    }
    public function create(): View
    {
        return view('media.create');
    }
    public function show($id): View
    {
        return view('media.show');
    }
    public function edit($id): View
    {
        return view('media.edit');
    }
    public function update(Request $request, $id)
    {
        return redirect()->route('media.index')->with('info', 'Update coming soon.');
    }
    public function destroy($id)
    {
        return redirect()->route('media.index')->with('info', 'Delete coming soon.');
    }
    public function store(Request $request)
    {
        return redirect()->route('media.index')->with('info', 'Store coming soon.');
    }
} 