<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminController extends Controller
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
} 