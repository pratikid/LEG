<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TutorialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function markAsCompleted(Request $request)
    {
        $user = Auth::user();
        $tutorial = $request->input('tutorial');

        // Store the completed tutorial in the user's preferences
        $completedTutorials = $user->preferences['completed_tutorials'] ?? [];
        $completedTutorials[] = $tutorial;

        $user->preferences = array_merge($user->preferences ?? [], [
            'completed_tutorials' => array_unique($completedTutorials),
        ]);

        $user->save();

        return response()->json(['success' => true]);
    }

    public function resetTutorials(Request $request)
    {
        $user = Auth::user();
        $user->preferences = array_merge($user->preferences ?? [], [
            'completed_tutorials' => [],
        ]);
        $user->save();

        return response()->json(['success' => true]);
    }
}
