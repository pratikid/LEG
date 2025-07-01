<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

final class LoginController extends Controller
{
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $loginField = $request->input('email') ? 'email' : 'username';
        $loginValue = $request->input($loginField);
        // If username is used, treat it as email for validation and authentication
        $request->merge(['email' => $loginValue]);
        try {
            /** @var array{email: string, password: string} $credentials */
            $credentials = $request->validate([
                'email' => [
                    'required',
                    function (
                        string $attribute,
                        mixed $value,
                        Closure $fail
                    ): void {
                        if ($value === 'admin@admin.com') {
                            return;
                        }
                        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $fail(__('validation.email', ['attribute' => $attribute]));
                        }
                    },
                ],
                'password' => ['required'],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        }

        // Allow admin login with username 'admin@admin.com' and password 'admin'
        if ($credentials['email'] === 'admin@admin.com' && $credentials['password'] === 'admin') {
            // Ensure the admin user exists in the database
            $admin = \App\Models\User::firstOrCreate(
                ['email' => 'admin@admin.com'],
                [
                    'name' => 'Admin',
                    'role' => 'admin',
                    'is_active' => true,
                    'is_admin' => true,
                    'password' => bcrypt('admin'),
                ]
            );
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
