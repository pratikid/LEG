<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Tree;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Trait for handling tree-specific operations
 */
trait HandlesTreeOperations
{
    /**
     * Get trees owned by a specific user.
     *
     * @param int|null $userId User ID (defaults to current authenticated user)
     * @return Collection Trees owned by the user
     */
    protected function getTreesOwnedByUser(?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();
        
        return Tree::where('user_id', $userId)
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Get trees shared with a specific user.
     *
     * @param int|null $userId User ID (defaults to current authenticated user)
     * @return Collection Trees shared with the user
     */
    protected function getTreesSharedWithUser(?int $userId = null): Collection
    {
        $userId = $userId ?? Auth::id();
        
        return Tree::whereHas('sharedUsers', function ($query) use ($userId) {
                $query->where('users.id', $userId);
            })
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Get all public trees.
     *
     * @return Collection Public trees
     */
    protected function getPublicTrees(): Collection
    {
        return Tree::where('is_public', true)
            ->orderBy('name')
            ->get();
    }
    
    /**
     * Check if a user has access to a specific tree.
     *
     * @param int $treeId Tree ID
     * @param int|null $userId User ID (defaults to current authenticated user)
     * @return bool Whether the user has access
     */
    protected function userHasTreeAccess(int $treeId, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        
        if ($userId === null) {
            // Guest users can only access public trees
            return Tree::where('id', $treeId)
                ->where('is_public', true)
                ->exists();
        }
        
        // Check if user owns the tree
        $isOwner = Tree::where('id', $treeId)
            ->where('user_id', $userId)
            ->exists();
            
        if ($isOwner) {
            return true;
        }
        
        // Check if tree is shared with user
        $isShared = DB::table('tree_user')
            ->where('tree_id', $treeId)
            ->where('user_id', $userId)
            ->exists();
            
        if ($isShared) {
            return true;
        }
        
        // Check if tree is public
        return Tree::where('id', $treeId)
            ->where('is_public', true)
            ->exists();
    }
    
    /**
     * Check if a user is the owner of a specific tree.
     *
     * @param int $treeId Tree ID
     * @param int|null $userId User ID (defaults to current authenticated user)
     * @return bool Whether the user is the owner
     */
    protected function userOwnsTree(int $treeId, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();
        
        if ($userId === null) {
            return false;
        }
        
        return Tree::where('id', $treeId)
            ->where('user_id', $userId)
            ->exists();
    }
    
    /**
     * Share a tree with a user.
     *
     * @param int $treeId Tree ID
     * @param int $targetUserId User ID to share with
     * @param string $accessLevel Access level (read, write, admin)
     * @return bool Whether the operation was successful
     */
    protected function shareTreeWithUser(int $treeId, int $targetUserId, string $accessLevel = 'read'): bool
    {
        // Verify the tree exists
        $tree = Tree::find($treeId);
        if (!$tree) {
            return false;
        }
        
        // Verify the current user is the owner
        if (!$this->userOwnsTree($treeId)) {
            return false;
        }
        
        // Verify the target user exists
        $targetUser = User::find($targetUserId);
        if (!$targetUser) {
            return false;
        }
        
        // Don't share with the owner
        if ($tree->user_id === $targetUserId) {
            return false;
        }
        
        // Check if already shared
        $existingShare = DB::table('tree_user')
            ->where('tree_id', $treeId)
            ->where('user_id', $targetUserId)
            ->first();
            
        if ($existingShare) {
            // Update access level if different
            if ($existingShare->access_level !== $accessLevel) {
                DB::table('tree_user')
                    ->where('tree_id', $treeId)
                    ->where('user_id', $targetUserId)
                    ->update(['access_level' => $accessLevel]);
            }
        } else {
            // Create new share
            DB::table('tree_user')->insert([
                'tree_id' => $treeId,
                'user_id' => $targetUserId,
                'access_level' => $accessLevel,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        return true;
    }
    
    /**
     * Remove tree sharing for a user.
     *
     * @param int $treeId Tree ID
     * @param int $targetUserId User ID to remove sharing for
     * @return bool Whether the operation was successful
     */
    protected function unshareTreeWithUser(int $treeId, int $targetUserId): bool
    {
        // Verify the tree exists
        $tree = Tree::find($treeId);
        if (!$tree) {
            return false;
        }
        
        // Verify the current user is the owner
        if (!$this->userOwnsTree($treeId)) {
            return false;
        }
        
        // Remove sharing
        DB::table('tree_user')
            ->where('tree_id', $treeId)
            ->where('user_id', $targetUserId)
            ->delete();
            
        return true;
    }
    
    /**
     * Get users with whom a tree is shared.
     *
     * @param int $treeId Tree ID
     * @return Collection Users with whom the tree is shared
     */
    protected function getUsersWithTreeAccess(int $treeId): Collection
    {
        // Verify the tree exists
        $tree = Tree::find($treeId);
        if (!$tree) {
            return new Collection();
        }
        
        // Verify the current user is the owner
        if (!$this->userOwnsTree($treeId)) {
            return new Collection();
        }
        
        return User::whereHas('sharedTrees', function ($query) use ($treeId) {
                $query->where('trees.id', $treeId);
            })
            ->get();
    }
    
    /**
     * Get the access level a user has for a tree.
     *
     * @param int $treeId Tree ID
     * @param int|null $userId User ID (defaults to current authenticated user)
     * @return string|null Access level (read, write, admin) or null if no access
     */
    protected function getUserTreeAccessLevel(int $treeId, ?int $userId = null): ?string
    {
        $userId = $userId ?? Auth::id();
        
        if ($userId === null) {
            // Guest users have read access to public trees
            $isPublic = Tree::where('id', $treeId)
                ->where('is_public', true)
                ->exists();
                
            return $isPublic ? 'read' : null;
        }
        
        // Check if user owns the tree
        $isOwner = Tree::where('id', $treeId)
            ->where('user_id', $userId)
            ->exists();
            
        if ($isOwner) {
            return 'owner';
        }
        
        // Check if tree is shared with user
        $share = DB::table('tree_user')
            ->where('tree_id', $treeId)
            ->where('user_id', $userId)
            ->first();
            
        if ($share) {
            return $share->access_level;
        }
        
        // Check if tree is public
        $isPublic = Tree::where('id', $treeId)
            ->where('is_public', true)
            ->exists();
            
        return $isPublic ? 'read' : null;
    }
}