<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * Trait for handling authentication operations
 */
trait HandlesAuthenticationOperations
{
    /**
     * Attempt to authenticate a user.
     *
     * @param  array  $credentials  The credentials
     * @param  bool  $remember  Whether to remember the user
     * @param  string|null  $guard  The authentication guard
     * @return bool True if authentication was successful, false otherwise
     */
    protected function attemptLogin(array $credentials, bool $remember = false, ?string $guard = null): bool
    {
        return Auth::guard($guard)->attempt($credentials, $remember);
    }

    /**
     * Log a user in.
     *
     * @param  Authenticatable  $user  The user
     * @param  bool  $remember  Whether to remember the user
     * @param  string|null  $guard  The authentication guard
     */
    protected function login(Authenticatable $user, bool $remember = false, ?string $guard = null): void
    {
        Auth::guard($guard)->login($user, $remember);
    }

    /**
     * Log a user in for a single request.
     *
     * @param  Authenticatable  $user  The user
     * @param  string|null  $guard  The authentication guard
     */
    protected function loginUsingId(int $id, bool $remember = false, ?string $guard = null): void
    {
        Auth::guard($guard)->loginUsingId($id, $remember);
    }

    /**
     * Log a user in for a single request.
     *
     * @param  Authenticatable  $user  The user
     * @param  string|null  $guard  The authentication guard
     * @return void
     */
    protected function onceUsingId(int $id, ?string $guard = null): bool
    {
        return Auth::guard($guard)->onceUsingId($id);
    }

    /**
     * Log a user in for a single request.
     *
     * @param  Authenticatable  $user  The user
     * @param  string|null  $guard  The authentication guard
     */
    protected function loginOnce(Authenticatable $user, ?string $guard = null): void
    {
        Auth::guard($guard)->once(['id' => $user->getAuthIdentifier()]);
    }

    /**
     * Log the current user out.
     *
     * @param  string|null  $guard  The authentication guard
     */
    protected function logout(?string $guard = null): void
    {
        Auth::guard($guard)->logout();
    }

    /**
     * Get the currently authenticated user.
     *
     * @param  string|null  $guard  The authentication guard
     * @return Authenticatable|null The authenticated user
     */
    protected function getAuthenticatedUser(?string $guard = null): ?Authenticatable
    {
        return Auth::guard($guard)->user();
    }

    /**
     * Get the ID of the currently authenticated user.
     *
     * @param  string|null  $guard  The authentication guard
     * @return int|string|null The authenticated user ID
     */
    protected function getAuthenticatedUserId(?string $guard = null)
    {
        return Auth::guard($guard)->id();
    }

    /**
     * Determine if the current user is authenticated.
     *
     * @param  string|null  $guard  The authentication guard
     * @return bool True if the user is authenticated, false otherwise
     */
    protected function isAuthenticated(?string $guard = null): bool
    {
        return Auth::guard($guard)->check();
    }

    /**
     * Determine if the current user is a guest.
     *
     * @param  string|null  $guard  The authentication guard
     * @return bool True if the user is a guest, false otherwise
     */
    protected function isGuest(?string $guard = null): bool
    {
        return Auth::guard($guard)->guest();
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials  The credentials
     * @param  string|null  $guard  The authentication guard
     * @return bool True if the credentials are valid, false otherwise
     */
    protected function validateCredentials(array $credentials, ?string $guard = null): bool
    {
        return Auth::guard($guard)->validate($credentials);
    }

    /**
     * Set the current user.
     *
     * @param  Authenticatable  $user  The user
     * @param  string|null  $guard  The authentication guard
     */
    protected function setUser(Authenticatable $user, ?string $guard = null): void
    {
        Auth::guard($guard)->setUser($user);
    }

    /**
     * Get the authentication guard.
     *
     * @param  string|null  $name  The guard name
     * @return \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard The guard
     */
    protected function getAuthGuard(?string $name = null)
    {
        return Auth::guard($name);
    }

    /**
     * Get the name of the authentication guard.
     *
     * @return string|null The guard name
     */
    protected function getAuthGuardName(): ?string
    {
        return Auth::getDefaultDriver();
    }

    /**
     * Set the default authentication guard.
     *
     * @param  string  $name  The guard name
     */
    protected function setDefaultAuthGuard(string $name): void
    {
        Auth::setDefaultDriver($name);
    }

    /**
     * Get all of the available authentication guards.
     *
     * @return array The available guards
     */
    protected function getAvailableAuthGuards(): array
    {
        return array_keys(config('auth.guards'));
    }

    /**
     * Determine if a user matches the given credentials.
     *
     * @param  Authenticatable  $user  The user
     * @param  array  $credentials  The credentials
     * @return bool True if the user matches the credentials, false otherwise
     */
    protected function userMatchesCredentials(Authenticatable $user, array $credentials): bool
    {
        $provider = Auth::createUserProvider(config('auth.guards.'.Auth::getDefaultDriver().'.provider'));

        if (! $provider) {
            return false;
        }

        return $provider->validateCredentials($user, $credentials);
    }

    /**
     * Hash a password.
     *
     * @param  string  $password  The password
     * @return string The hashed password
     */
    protected function hashPassword(string $password): string
    {
        return Hash::make($password);
    }

    /**
     * Check if a password matches a hash.
     *
     * @param  string  $password  The password
     * @param  string  $hashedPassword  The hashed password
     * @return bool True if the password matches the hash, false otherwise
     */
    protected function checkPassword(string $password, string $hashedPassword): bool
    {
        return Hash::check($password, $hashedPassword);
    }

    /**
     * Determine if a password needs to be rehashed.
     *
     * @param  string  $hashedPassword  The hashed password
     * @return bool True if the password needs to be rehashed, false otherwise
     */
    protected function passwordNeedsRehash(string $hashedPassword): bool
    {
        return Hash::needsRehash($hashedPassword);
    }

    /**
     * Rehash a password.
     *
     * @param  string  $hashedPassword  The hashed password
     * @return string The rehashed password
     */
    protected function rehashPassword(string $hashedPassword): string
    {
        if ($this->passwordNeedsRehash($hashedPassword)) {
            return $this->hashPassword($hashedPassword);
        }

        return $hashedPassword;
    }

    /**
     * Generate a random password.
     *
     * @param  int  $length  The password length
     * @return string The random password
     */
    protected function generateRandomPassword(int $length = 10): string
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_=+[]{}|;:,.<>?';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, mb_strlen($characters) - 1)];
        }

        return $password;
    }

    /**
     * Throw an authentication exception.
     *
     * @param  string  $message  The exception message
     * @param  array  $guards  The guards that were checked
     *
     * @throws AuthenticationException
     */
    protected function throwAuthenticationException(string $message = 'Unauthenticated.', array $guards = []): void
    {
        throw new AuthenticationException($message, $guards);
    }

    /**
     * Get the authentication model.
     *
     * @param  string|null  $guard  The authentication guard
     * @return string The authentication model
     */
    protected function getAuthenticationModel(?string $guard = null): string
    {
        $guard = $guard ?? Auth::getDefaultDriver();
        $provider = config('auth.guards.'.$guard.'.provider');

        return config('auth.providers.'.$provider.'.model');
    }

    /**
     * Get the authentication provider.
     *
     * @param  string|null  $guard  The authentication guard
     * @return string The authentication provider
     */
    protected function getAuthenticationProvider(?string $guard = null): string
    {
        $guard = $guard ?? Auth::getDefaultDriver();

        return config('auth.guards.'.$guard.'.provider');
    }

    /**
     * Get the authentication driver.
     *
     * @param  string|null  $guard  The authentication guard
     * @return string The authentication driver
     */
    protected function getAuthenticationDriver(?string $guard = null): string
    {
        $guard = $guard ?? Auth::getDefaultDriver();

        return config('auth.guards.'.$guard.'.driver');
    }

    /**
     * Get the remember token cookie name.
     *
     * @return string The remember token cookie name
     */
    protected function getRememberTokenCookieName(): string
    {
        return Auth::getRecallerName();
    }

    /**
     * Get the session name used by the guard.
     *
     * @param  string|null  $guard  The authentication guard
     * @return string The session name
     */
    protected function getAuthSessionName(?string $guard = null): string
    {
        return Auth::guard($guard)->getName();
    }

    /**
     * Determine if the user was authenticated via "remember me" cookie.
     *
     * @param  string|null  $guard  The authentication guard
     * @return bool True if the user was authenticated via remember me cookie, false otherwise
     */
    protected function viaRemember(?string $guard = null): bool
    {
        return Auth::guard($guard)->viaRemember();
    }

    /**
     * Get the user provider used by the guard.
     *
     * @param  string|null  $name  The provider name
     * @return \Illuminate\Contracts\Auth\UserProvider The user provider
     */
    protected function getUserProvider(?string $name = null)
    {
        $name = $name ?? $this->getAuthenticationProvider();

        return Auth::createUserProvider($name);
    }

    /**
     * Create a token for a user.
     *
     * @param  Authenticatable  $user  The user
     * @param  string  $name  The token name
     * @param  array  $abilities  The token abilities
     * @return string The token
     */
    protected function createToken(Authenticatable $user, string $name, array $abilities = ['*']): string
    {
        if (method_exists($user, 'createToken')) {
            $token = $user->createToken($name, $abilities);

            return $token->plainTextToken;
        }

        return '';
    }

    /**
     * Revoke all tokens for a user.
     *
     * @param  Authenticatable  $user  The user
     */
    protected function revokeAllTokens(Authenticatable $user): void
    {
        if (method_exists($user, 'tokens')) {
            $user->tokens()->delete();
        }
    }

    /**
     * Revoke a specific token for a user.
     *
     * @param  Authenticatable  $user  The user
     * @param  string  $tokenId  The token ID
     */
    protected function revokeToken(Authenticatable $user, string $tokenId): void
    {
        if (method_exists($user, 'tokens')) {
            $user->tokens()->where('id', $tokenId)->delete();
        }
    }

    /**
     * Get all tokens for a user.
     *
     * @param  Authenticatable  $user  The user
     * @return \Illuminate\Database\Eloquent\Collection The tokens
     */
    protected function getAllTokens(Authenticatable $user)
    {
        if (method_exists($user, 'tokens')) {
            return $user->tokens;
        }

        return collect();
    }

    /**
     * Determine if a user has a specific token.
     *
     * @param  Authenticatable  $user  The user
     * @param  string  $tokenId  The token ID
     * @return bool True if the user has the token, false otherwise
     */
    protected function hasToken(Authenticatable $user, string $tokenId): bool
    {
        if (method_exists($user, 'tokens')) {
            return $user->tokens()->where('id', $tokenId)->exists();
        }

        return false;
    }

    /**
     * Get a specific token for a user.
     *
     * @param  Authenticatable  $user  The user
     * @param  string  $tokenId  The token ID
     * @return mixed The token
     */
    protected function getToken(Authenticatable $user, string $tokenId)
    {
        if (method_exists($user, 'tokens')) {
            return $user->tokens()->where('id', $tokenId)->first();
        }

        return null;
    }

    /**
     * Get the token abilities for a user.
     *
     * @param  Authenticatable  $user  The user
     * @param  string  $tokenId  The token ID
     * @return array The token abilities
     */
    protected function getTokenAbilities(Authenticatable $user, string $tokenId): array
    {
        $token = $this->getToken($user, $tokenId);

        if ($token && method_exists($token, 'abilities')) {
            return $token->abilities;
        }

        return [];
    }

    /**
     * Determine if a token has a specific ability.
     *
     * @param  Authenticatable  $user  The user
     * @param  string  $tokenId  The token ID
     * @param  string  $ability  The ability
     * @return bool True if the token has the ability, false otherwise
     */
    protected function tokenCan(Authenticatable $user, string $tokenId, string $ability): bool
    {
        $token = $this->getToken($user, $tokenId);

        if ($token && method_exists($token, 'can')) {
            return $token->can($ability);
        }

        return false;
    }

    /**
     * Get the current user's token.
     *
     * @param  string|null  $guard  The authentication guard
     * @return mixed The token
     */
    protected function getCurrentToken(?string $guard = null)
    {
        $user = $this->getAuthenticatedUser($guard);

        if ($user && method_exists($user, 'currentAccessToken')) {
            return $user->currentAccessToken();
        }

        return null;
    }

    /**
     * Determine if the current token has a specific ability.
     *
     * @param  string  $ability  The ability
     * @param  string|null  $guard  The authentication guard
     * @return bool True if the token has the ability, false otherwise
     */
    protected function currentTokenCan(string $ability, ?string $guard = null): bool
    {
        $user = $this->getAuthenticatedUser($guard);

        if ($user && method_exists($user, 'tokenCan')) {
            return $user->tokenCan($ability);
        }

        return false;
    }

    /**
     * Get the abilities of the current token.
     *
     * @param  string|null  $guard  The authentication guard
     * @return array The token abilities
     */
    protected function getCurrentTokenAbilities(?string $guard = null): array
    {
        $token = $this->getCurrentToken($guard);

        if ($token && method_exists($token, 'abilities')) {
            return $token->abilities;
        }

        return [];
    }

    /**
     * Revoke the current token.
     *
     * @param  string|null  $guard  The authentication guard
     */
    protected function revokeCurrentToken(?string $guard = null): void
    {
        $token = $this->getCurrentToken($guard);

        if ($token && method_exists($token, 'delete')) {
            $token->delete();
        }
    }
}
