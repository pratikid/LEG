<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

final class AdminController extends Controller
{
    public function users(): View
    {
        return view('admin.users');
    }

    public function logs(): View
    {
        return view('admin.logs');
    }

    public function settings(): View
    {
        return view('admin.settings');
    }

    public function notifications(): View
    {
        return view('admin.notifications');
    }

    public function importMetrics(): View
    {
        return view('admin.import-metrics');
    }
}
