<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\Access\Response;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Support\Facades\Gate as GateFacade;

/**
 * Trait for handling authorization operations
 */
trait HandlesAuthorizationOperations
{
    /**
     * Authorize a given action for the current user.
     *
     * @param string $ability The ability to check
     * @param array|mixed $arguments The arguments to pass to the gate
     * @return \Illuminate\Auth\Access\Response The authorization response
     * @throws \Illuminate\Auth\Access\AuthorizationException If the action is not authorized
     */
    protected function authorize(string $ability, $arguments = []): Response
    {
        return GateFacade::authorize($ability, $arguments);
    }
    
    /**
     * Determine if the current user can perform a given action.
     *
     * @param string $ability The ability to check
     * @param array|mixed $arguments The arguments to pass to the gate
     * @return bool True if the user can perform the action, false otherwise
     */
    protected function can(string $ability, $arguments = []): bool
    {
        return GateFacade::allows($ability, $arguments);
    }
    
    /**
     * Determine if the current user cannot perform a given action.
     *
     * @param string $ability The ability to check
     * @param array|mixed $arguments The arguments to pass to the gate
     * @return bool True if the user cannot perform the action, false otherwise
     */
    protected function cannot(string $ability, $arguments = []): bool
    {
        return GateFacade::denies($ability, $arguments);
    }
    
    /**
     * Determine if any of the given abilities are allowed for the current user.
     *
     * @param array|string $abilities The abilities to check
     * @param array|mixed $arguments The arguments to pass to the gate
     * @return bool True if any of the abilities are allowed, false otherwise
     */
    protected function canAny($abilities, $arguments = []): bool
    {
        return GateFacade::any($abilities, $arguments);
    }
    
    /**
     * Determine if all of the given abilities are allowed for the current user.
     *
     * @param array|string $abilities The abilities to check
     * @param array|mixed $arguments The arguments to pass to the gate
     * @return bool True if all of the abilities are allowed, false otherwise
     */
    protected function canAll($abilities, $arguments = []): bool
    {
        if (is_string($abilities)) {
            $abilities = [$abilities];
        }
        
        foreach ($abilities as $ability) {
            if (GateFacade::denies($ability, $arguments)) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Authorize a given action for a user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param string $ability The ability to check
     * @param array|mixed $arguments The arguments to pass to the gate
     * @return \Illuminate\Auth\Access\Response The authorization response
     * @throws \Illuminate\Auth\Access\AuthorizationException If the action is not authorized
     */
    protected function authorizeForUser($user, string $ability, $arguments = []): Response
    {
        return GateFacade::forUser($user)->authorize($ability, $arguments);
    }
    
    /**
     * Determine if a user can perform a given action.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param string $ability The ability to check
     * @param array|mixed $arguments The arguments to pass to the gate
     * @return bool True if the user can perform the action, false otherwise
     */
    protected function userCan($user, string $ability, $arguments = []): bool
    {
        return GateFacade::forUser($user)->allows($ability, $arguments);
    }
    
    /**
     * Determine if a user cannot perform a given action.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param string $ability The ability to check
     * @param array|mixed $arguments The arguments to pass to the gate
     * @return bool True if the user cannot perform the action, false otherwise
     */
    protected function userCannot($user, string $ability, $arguments = []): bool
    {
        return GateFacade::forUser($user)->denies($ability, $arguments);
    }
    
    /**
     * Get all of the defined abilities.
     *
     * @return array The defined abilities
     */
    protected function getDefinedAbilities(): array
    {
        return GateFacade::abilities();
    }
    
    /**
     * Define a new ability.
     *
     * @param string $ability The ability name
     * @param callable|string $callback The callback
     * @return \Illuminate\Auth\Access\Gate The gate instance
     */
    protected function defineAbility(string $ability, $callback): Gate
    {
        return GateFacade::define($ability, $callback);
    }
    
    /**
     * Define multiple abilities at once.
     *
     * @param array $abilities The abilities
     * @return \Illuminate\Auth\Access\Gate The gate instance
     */
    protected function defineAbilities(array $abilities): Gate
    {
        foreach ($abilities as $ability => $callback) {
            GateFacade::define($ability, $callback);
        }
        
        return GateFacade::getFacadeRoot();
    }
    
    /**
     * Define a policy class for a given class type.
     *
     * @param string $class The class
     * @param string $policy The policy class
     * @return \Illuminate\Auth\Access\Gate The gate instance
     */
    protected function definePolicy(string $class, string $policy): Gate
    {
        return GateFacade::policy($class, $policy);
    }
    
    /**
     * Define policies for multiple class types at once.
     *
     * @param array $policies The policies
     * @return \Illuminate\Auth\Access\Gate The gate instance
     */
    protected function definePolicies(array $policies): Gate
    {
        foreach ($policies as $class => $policy) {
            GateFacade::policy($class, $policy);
        }
        
        return GateFacade::getFacadeRoot();
    }
    
    /**
     * Get a policy instance for a given class.
     *
     * @param string|object $class The class
     * @return mixed The policy instance
     */
    protected function getPolicyFor($class)
    {
        return GateFacade::getPolicyFor($class);
    }
    
    /**
     * Get the raw result from the authorization gate.
     *
     * @param string $ability The ability to check
     * @param array|mixed $arguments The arguments to pass to the gate
     * @return mixed The raw result
     */
    protected function getRawAuthorizationResult(string $ability, $arguments = [])
    {
        return GateFacade::raw($ability, $arguments);
    }
    
    /**
     * Check if a given ability has been defined.
     *
     * @param string $ability The ability
     * @return bool True if the ability has been defined, false otherwise
     */
    protected function hasAbility(string $ability): bool
    {
        return GateFacade::has($ability);
    }
    
    /**
     * Create an authorization response.
     *
     * @param bool $allowed Whether the action is allowed
     * @param string $message The response message
     * @param mixed $code The response code
     * @return \Illuminate\Auth\Access\Response The authorization response
     */
    protected function createAuthorizationResponse(bool $allowed, string $message = '', $code = null): Response
    {
        return $allowed
            ? Response::allow($message, $code)
            : Response::deny($message, $code);
    }
    
    /**
     * Create an allowed authorization response.
     *
     * @param string $message The response message
     * @param mixed $code The response code
     * @return \Illuminate\Auth\Access\Response The authorization response
     */
    protected function allowResponse(string $message = '', $code = null): Response
    {
        return Response::allow($message, $code);
    }
    
    /**
     * Create a denied authorization response.
     *
     * @param string $message The response message
     * @param mixed $code The response code
     * @return \Illuminate\Auth\Access\Response The authorization response
     */
    protected function denyResponse(string $message = '', $code = null): Response
    {
        return Response::deny($message, $code);
    }
    
    /**
     * Throw an authorization exception.
     *
     * @param string $message The exception message
     * @param mixed $code The exception code
     * @return void
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    protected function throwAuthorizationException(string $message = 'This action is unauthorized.', $code = null): void
    {
        throw new AuthorizationException($message, $code);
    }
    
    /**
     * Get the gate instance.
     *
     * @return \Illuminate\Contracts\Auth\Access\Gate The gate instance
     */
    protected function getGate(): Gate
    {
        return GateFacade::getFacadeRoot();
    }
    
    /**
     * Determine if the current user has a role.
     *
     * @param string|array $roles The role(s) to check
     * @return bool True if the user has the role, false otherwise
     */
    protected function hasRole($roles): bool
    {
        $user = auth()->user();
        
        if (!$user || !method_exists($user, 'hasRole')) {
            return false;
        }
        
        return $user->hasRole($roles);
    }
    
    /**
     * Determine if the current user has a permission.
     *
     * @param string|array $permissions The permission(s) to check
     * @return bool True if the user has the permission, false otherwise
     */
    protected function hasPermission($permissions): bool
    {
        $user = auth()->user();
        
        if (!$user || !method_exists($user, 'hasPermissionTo')) {
            return false;
        }
        
        return $user->hasPermissionTo($permissions);
    }
    
    /**
     * Determine if the current user has any of the given permissions.
     *
     * @param array $permissions The permissions to check
     * @return bool True if the user has any of the permissions, false otherwise
     */
    protected function hasAnyPermission(array $permissions): bool
    {
        $user = auth()->user();
        
        if (!$user || !method_exists($user, 'hasAnyPermission')) {
            return false;
        }
        
        return $user->hasAnyPermission($permissions);
    }
    
    /**
     * Determine if the current user has all of the given permissions.
     *
     * @param array $permissions The permissions to check
     * @return bool True if the user has all of the permissions, false otherwise
     */
    protected function hasAllPermissions(array $permissions): bool
    {
        $user = auth()->user();
        
        if (!$user || !method_exists($user, 'hasAllPermissions')) {
            return false;
        }
        
        return $user->hasAllPermissions($permissions);
    }
    
    /**
     * Determine if a user has a role.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param string|array $roles The role(s) to check
     * @return bool True if the user has the role, false otherwise
     */
    protected function userHasRole($user, $roles): bool
    {
        if (!$user || !method_exists($user, 'hasRole')) {
            return false;
        }
        
        return $user->hasRole($roles);
    }
    
    /**
     * Determine if a user has a permission.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param string|array $permissions The permission(s) to check
     * @return bool True if the user has the permission, false otherwise
     */
    protected function userHasPermission($user, $permissions): bool
    {
        if (!$user || !method_exists($user, 'hasPermissionTo')) {
            return false;
        }
        
        return $user->hasPermissionTo($permissions);
    }
    
    /**
     * Determine if a user has any of the given permissions.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param array $permissions The permissions to check
     * @return bool True if the user has any of the permissions, false otherwise
     */
    protected function userHasAnyPermission($user, array $permissions): bool
    {
        if (!$user || !method_exists($user, 'hasAnyPermission')) {
            return false;
        }
        
        return $user->hasAnyPermission($permissions);
    }
    
    /**
     * Determine if a user has all of the given permissions.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param array $permissions The permissions to check
     * @return bool True if the user has all of the permissions, false otherwise
     */
    protected function userHasAllPermissions($user, array $permissions): bool
    {
        if (!$user || !method_exists($user, 'hasAllPermissions')) {
            return false;
        }
        
        return $user->hasAllPermissions($permissions);
    }
    
    /**
     * Get all roles for the current user.
     *
     * @return \Illuminate\Database\Eloquent\Collection|array The roles
     */
    protected function getUserRoles()
    {
        $user = auth()->user();
        
        if (!$user || !method_exists($user, 'roles')) {
            return collect();
        }
        
        return $user->roles;
    }
    
    /**
     * Get all permissions for the current user.
     *
     * @return \Illuminate\Database\Eloquent\Collection|array The permissions
     */
    protected function getUserPermissions()
    {
        $user = auth()->user();
        
        if (!$user || !method_exists($user, 'permissions')) {
            return collect();
        }
        
        return $user->permissions;
    }
    
    /**
     * Get all roles for a user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @return \Illuminate\Database\Eloquent\Collection|array The roles
     */
    protected function getRolesForUser($user)
    {
        if (!$user || !method_exists($user, 'roles')) {
            return collect();
        }
        
        return $user->roles;
    }
    
    /**
     * Get all permissions for a user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @return \Illuminate\Database\Eloquent\Collection|array The permissions
     */
    protected function getPermissionsForUser($user)
    {
        if (!$user || !method_exists($user, 'permissions')) {
            return collect();
        }
        
        return $user->permissions;
    }
    
    /**
     * Get all permissions for a role.
     *
     * @param string|\Spatie\Permission\Models\Role $role The role
     * @return \Illuminate\Database\Eloquent\Collection|array The permissions
     */
    protected function getPermissionsForRole($role)
    {
        if (is_string($role)) {
            $roleClass = config('permission.models.role');
            $role = $roleClass::findByName($role);
        }
        
        if (!$role || !method_exists($role, 'permissions')) {
            return collect();
        }
        
        return $role->permissions;
    }
    
    /**
     * Assign a role to a user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param string|array $roles The role(s) to assign
     * @return mixed The user instance
     */
    protected function assignRoleToUser($user, $roles)
    {
        if (!$user || !method_exists($user, 'assignRole')) {
            return $user;
        }
        
        return $user->assignRole($roles);
    }
    
    /**
     * Remove a role from a user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param string|array $roles The role(s) to remove
     * @return mixed The user instance
     */
    protected function removeRoleFromUser($user, $roles)
    {
        if (!$user || !method_exists($user, 'removeRole')) {
            return $user;
        }
        
        return $user->removeRole($roles);
    }
    
    /**
     * Sync roles for a user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param string|array $roles The role(s) to sync
     * @return mixed The user instance
     */
    protected function syncRolesForUser($user, $roles)
    {
        if (!$user || !method_exists($user, 'syncRoles')) {
            return $user;
        }
        
        return $user->syncRoles($roles);
    }
    
    /**
     * Give permission to a user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param string|array $permissions The permission(s) to give
     * @return mixed The user instance
     */
    protected function givePermissionToUser($user, $permissions)
    {
        if (!$user || !method_exists($user, 'givePermissionTo')) {
            return $user;
        }
        
        return $user->givePermissionTo($permissions);
    }
    
    /**
     * Revoke permission from a user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param string|array $permissions The permission(s) to revoke
     * @return mixed The user instance
     */
    protected function revokePermissionFromUser($user, $permissions)
    {
        if (!$user || !method_exists($user, 'revokePermissionTo')) {
            return $user;
        }
        
        return $user->revokePermissionTo($permissions);
    }
    
    /**
     * Sync permissions for a user.
     *
     * @param \Illuminate\Contracts\Auth\Authenticatable|mixed $user The user
     * @param string|array $permissions The permission(s) to sync
     * @return mixed The user instance
     */
    protected function syncPermissionsForUser($user, $permissions)
    {
        if (!$user || !method_exists($user, 'syncPermissions')) {
            return $user;
        }
        
        return $user->syncPermissions($permissions);
    }
    
    /**
     * Give permission to a role.
     *
     * @param string|\Spatie\Permission\Models\Role $role The role
     * @param string|array $permissions The permission(s) to give
     * @return mixed The role instance
     */
    protected function givePermissionToRole($role, $permissions)
    {
        if (is_string($role)) {
            $roleClass = config('permission.models.role');
            $role = $roleClass::findByName($role);
        }
        
        if (!$role || !method_exists($role, 'givePermissionTo')) {
            return $role;
        }
        
        return $role->givePermissionTo($permissions);
    }
    
    /**
     * Revoke permission from a role.
     *
     * @param string|\Spatie\Permission\Models\Role $role The role
     * @param string|array $permissions The permission(s) to revoke
     * @return mixed The role instance
     */
    protected function revokePermissionFromRole($role, $permissions)
    {
        if (is_string($role)) {
            $roleClass = config('permission.models.role');
            $role = $roleClass::findByName($role);
        }
        
        if (!$role || !method_exists($role, 'revokePermissionTo')) {
            return $role;
        }
        
        return $role->revokePermissionTo($permissions);
    }
    
    /**
     * Sync permissions for a role.
     *
     * @param string|\Spatie\Permission\Models\Role $role The role
     * @param string|array $permissions The permission(s) to sync
     * @return mixed The role instance
     */
    protected function syncPermissionsForRole($role, $permissions)
    {
        if (is_string($role)) {
            $roleClass = config('permission.models.role');
            $role = $roleClass::findByName($role);
        }
        
        if (!$role || !method_exists($role, 'syncPermissions')) {
            return $role;
        }
        
        return $role->syncPermissions($permissions);
    }
}