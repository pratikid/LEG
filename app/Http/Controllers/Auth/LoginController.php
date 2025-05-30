<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => [
                'required',
                function (
                    $attribute,
                    $value,
                    $fail
                ) {
                    if ($value === 'admin@admin.com') {
                        return;
                    }
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $fail(__('validation.email', ['attribute' => $attribute]));
                    }
                },
            ],
            'password' => ['required'],
        ]);

        // Allow admin login with username 'admin@admin.com' and password 'admin'
        if ($credentials['email'] === 'admin@admin.com' && $credentials['password'] === 'admin') {
            // Log in a fake admin user (without DB)
            $admin = new \App\Models\User([
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'role' => 'admin',
                'is_active' => true,
                'is_admin' => true,
                'password' => bcrypt('admin'),
            ]);
            Auth::login($admin, true);
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => __('auth.failed'),
        ])->onlyInput('email');
    }
} 