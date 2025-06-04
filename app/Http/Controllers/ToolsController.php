<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class ToolsController extends Controller
{
    public function templates(): View
    {
        return view('tools.templates');
    }

    public function export(): View
    {
        return view('tools.export');
    }

    public function reports(): View
    {
        return view('tools.reports');
    }
}
