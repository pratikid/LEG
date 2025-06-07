<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TimelinePreferencesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        /** @var array{
            node_color: string,
            node_shape: string,
            node_size: string,
            show_dates?: bool,
            show_location?: bool,
            show_description?: bool
        } $validated */
        $validated = $request->validate([
            'node_color' => 'required|in:amber,blue,green,red,purple',
            'node_shape' => 'required|in:circle,square,diamond',
            'node_size' => 'required|in:small,medium,large',
            'show_dates' => 'boolean',
            'show_location' => 'boolean',
            'show_description' => 'boolean',
        ]);

        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login');
        }

        /** @var array<string, mixed> $preferences */
        $preferences = is_array($user->preferences) ? $user->preferences : [];

        // Update preferences
        $preferences = array_merge($preferences, [
            'node_color' => $validated['node_color'],
            'node_shape' => $validated['node_shape'],
            'node_size' => $validated['node_size'],
            'show_dates' => $request->boolean('show_dates'),
            'show_location' => $request->boolean('show_location'),
            'show_description' => $request->boolean('show_description'),
        ]);

        $user->preferences = $preferences;
        $user->save();

        return redirect()->back()->with('success', 'Timeline preferences updated successfully.');
    }
}
