<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class CommunityController extends Controller
{
    public function directory(): View
    {
        return view('community.directory');
    }

    public function myGroups(): View
    {
        return view('community.my-groups');
    }

    public function forums(): View
    {
        return view('community.forums');
    }
}
