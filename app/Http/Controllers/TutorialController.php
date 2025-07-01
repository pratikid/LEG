<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class TutorialController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function markAsCompleted(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $tutorial = $request->input('tutorial');
        if (! is_string($tutorial)) {
            return response()->json(['error' => 'Invalid tutorial identifier'], 400);
        }

        // Store the completed tutorial in the user's preferences
        /** @var array<string, mixed> $preferences */
        $preferences = is_array($user->preferences) ? $user->preferences : [];
        /** @var array<int, string> $completedTutorials */
        $completedTutorials = is_array($preferences['completed_tutorials'] ?? null) ? $preferences['completed_tutorials'] : [];
        $completedTutorials[] = $tutorial;

        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'preferences' => json_encode(array_merge($preferences, [
                    'completed_tutorials' => array_unique($completedTutorials),
                ])),
            ]);

        return response()->json(['success' => true]);
    }

    public function resetTutorials(Request $request): JsonResponse
    {
        $user = Auth::user();
        if (! $user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        /** @var array<string, mixed> $preferences */
        $preferences = is_array($user->preferences) ? $user->preferences : [];
        DB::table('users')
            ->where('id', $user->id)
            ->update([
                'preferences' => json_encode(array_merge($preferences, [
                    'completed_tutorials' => [],
                ])),
            ]);

        return response()->json(['success' => true]);
    }
}
