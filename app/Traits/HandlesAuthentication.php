<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Trait for handling authentication operations
 */
trait HandlesAuthentication
{
    /**
     * Authenticate a user with email and password.
     *
     * @param string $email User email
     * @param string $password User password
     * @param bool $remember Whether to remember the user
     * @return User The authenticated user
     * @throws AuthenticationException If authentication fails
     */
    protected function authenticateUser(string $email, string $password, bool $remember = false): User
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password], $remember)) {
            throw new AuthenticationException('Invalid credentials');
        }
        
        return Auth::user();
    }
    
    /**
     * Generate an API token for a user.
     *
     * @param User $user The user to generate a token for
     * @param string $tokenName Name of the token
     * @param array $abilities Token abilities/permissions
     * @return string The generated token
     */
    protected function generateApiToken(User $user, string $tokenName, array $abilities = ['*']): string
    {
        // Revoke any existing tokens with the same name
        $user->tokens()->where('name', $tokenName)->delete();
        
        // Create a new token
        $token = $user->createToken($tokenName, $abilities);
        
        return $token->plainTextToken;
    }
    
    /**
     * Revoke all tokens for a user.
     *
     * @param User $user The user to revoke tokens for
     * @return void
     */
    protected function revokeAllTokens(User $user): void
    {
        $user->tokens()->delete();
    }
    
    /**
     * Revoke a specific token by ID.
     *
     * @param User $user The user who owns the token
     * @param int $tokenId The ID of the token to revoke
     * @return bool Whether the token was successfully revoked
     */
    protected function revokeToken(User $user, int $tokenId): bool
    {
        return $user->tokens()->where('id', $tokenId)->delete() > 0;
    }
    
    /**
     * Register a new user.
     *
     * @param array $userData User data including name, email, and password
     * @return User The newly created user
     * @throws ValidationException If validation fails
     */
    protected function registerUser(array $userData): User
    {
        $user = new User();
        $user->name = $userData['name'];
        $user->email = $userData['email'];
        $user->password = Hash::make($userData['password']);
        $user->email_verified_at = $userData['email_verified'] ?? null;
        $user->save();
        
        return $user;
    }
    
    /**
     * Send a password reset link to the user.
     *
     * @param string $email User email
     * @return bool Whether the reset link was sent
     */
    protected function sendPasswordResetLink(string $email): bool
    {
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false;
        }
        
        $token = Str::random(60);
        
        // Store the token in the password_resets table
        $passwordReset = \DB::table('password_resets')->updateOrInsert(
            ['email' => $email],
            [
                'token' => Hash::make($token),
                'created_at' => now(),
            ]
        );
        
        // Send the reset link email
        try {
            $user->sendPasswordResetNotification($token);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send password reset email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reset a user's password.
     *
     * @param string $email User email
     * @param string $token Reset token
     * @param string $password New password
     * @return bool Whether the password was reset
     */
    protected function resetPassword(string $email, string $token, string $password): bool
    {
        // Verify the token
        $resetRecord = \DB::table('password_resets')
            ->where('email', $email)
            ->first();
            
        if (!$resetRecord || !Hash::check($token, $resetRecord->token)) {
            return false;
        }
        
        // Check if the token is expired (typically 60 minutes)
        if (now()->diffInMinutes($resetRecord->created_at) > 60) {
            return false;
        }
        
        // Update the user's password
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            return false;
        }
        
        $user->password = Hash::make($password);
        $user->save();
        
        // Delete the reset record
        \DB::table('password_resets')->where('email', $email)->delete();
        
        return true;
    }
    
    /**
     * Verify a user's email address.
     *
     * @param string $userId User ID
     * @param string $hash Verification hash
     * @return bool Whether the email was verified
     */
    protected function verifyEmail(string $userId, string $hash): bool
    {
        $user = User::find($userId);
        
        if (!$user || !hash_equals(sha1($user->getEmailForVerification()), $hash)) {
            return false;
        }
        
        if ($user->hasVerifiedEmail()) {
            return true; // Already verified
        }
        
        $user->markEmailAsVerified();
        
        return true;
    }
    
    /**
     * Resend the email verification notification.
     *
     * @param User $user The user to send the verification to
     * @return bool Whether the verification was sent
     */
    protected function resendEmailVerification(User $user): bool
    {
        if ($user->hasVerifiedEmail()) {
            return false; // Already verified
        }
        
        try {
            $user->sendEmailVerificationNotification();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send verification email: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Get the authenticated user from the request.
     *
     * @param Request $request The HTTP request
     * @return User|null The authenticated user or null
     */
    protected function getAuthenticatedUser(Request $request): ?User
    {
        return $request->user();
    }
    
    /**
     * Check if a user has a specific permission.
     *
     * @param User $user The user to check
     * @param string $permission The permission to check for
     * @return bool Whether the user has the permission
     */
    protected function userHasPermission(User $user, string $permission): bool
    {
        // If using a permission package like Spatie's laravel-permission
        if (method_exists($user, 'hasPermissionTo')) {
            return $user->hasPermissionTo($permission);
        }
        
        // Basic implementation for custom permission system
        // This should be adapted to your specific permission system
        $userPermissions = $this->getUserPermissions($user);
        return in_array($permission, $userPermissions);
    }
    
    /**
     * Get all permissions for a user.
     *
     * @param User $user The user to get permissions for
     * @return array The user's permissions
     */
    protected function getUserPermissions(User $user): array
    {
        // This is a placeholder implementation
        // Replace with your actual permission retrieval logic
        
        // Example implementation for a custom permission system
        // return DB::table('user_permissions')
        //     ->where('user_id', $user->id)
        //     ->pluck('permission_name')
        //     ->toArray();
        
        // For now, return an empty array
        return [];
    }
    
    /**
     * Check if the current user can access a specific resource.
     *
     * @param string $ability The ability to check (view, create, update, delete)
     * @param mixed $resource The resource to check access for
     * @return bool Whether the user can access the resource
     */
    protected function userCan(string $ability, $resource): bool
    {
        return Auth::user()->can($ability, $resource);
    }
    
    /**
     * Update a user's last login timestamp.
     *
     * @param User $user The user to update
     * @param string|null $ipAddress The IP address of the login
     * @return void
     */
    protected function updateLastLogin(User $user, ?string $ipAddress = null): void
    {
        $user->last_login_at = now();
        
        if ($ipAddress) {
            $user->last_login_ip = $ipAddress;
        }
        
        $user->save();
    }
}