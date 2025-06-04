<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;

class HelpController extends Controller
{
    public function userGuide(): View
    {
        return view('help.user-guide');
    }

    public function tutorials(): View
    {
        return view('help.tutorials');
    }

    public function support(): View
    {
        return view('help.support');
    }
}
