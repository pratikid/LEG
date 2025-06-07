<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function show(): \Illuminate\View\View
    {
        return view('profile');
    }

    public function update(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        /** @var array{name: string, email: string, profile_photo?: \Illuminate\Http\UploadedFile} $validated */
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'profile_photo' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            if ($file) {
                $path = $file->store('profile-photos', 'public');
                if ($path !== false) {
                    $user->profile_photo_path = $path;
                }
            }
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        /** @var array{current_password: string, password: string} $validated */
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->password = Hash::make($validated['password']);
        $user->save();

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateNotifications(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        $user->email_notifications = $request->boolean('email_notifications');
        $user->sms_notifications = $request->boolean('sms_notifications');
        $user->save();

        return back()->with('success', 'Notification preferences updated.');
    }

    public function destroy(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('success', 'Your account has been deleted.');
    }

    public function settings(): \Illuminate\View\View
    {
        return view('profile.settings');
    }

    public function preferences(): \Illuminate\View\View
    {
        return view('profile.preferences');
    }
}
