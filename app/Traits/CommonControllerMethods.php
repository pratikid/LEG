<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Tree;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Trait for common controller methods
 */
trait CommonControllerMethods
{
    /**
     * Get trees available to the current user.
     *
     * @param  bool  $includeShared  Whether to include trees shared with the user
     * @return \Illuminate\Database\Eloquent\Collection Trees available to the user
     */
    protected function getAvailableTrees(bool $includeShared = true): \Illuminate\Database\Eloquent\Collection
    {
        $query = Tree::query();

        if (Auth::check()) {
            $userId = Auth::id();

            if ($includeShared) {
                // Include trees owned by the user and trees shared with the user
                $query->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId)
                        ->orWhereHas('sharedUsers', function ($sq) use ($userId) {
                            $sq->where('users.id', $userId);
                        });
                });
            } else {
                // Only include trees owned by the user
                $query->where('user_id', $userId);
            }
        } else {
            // For guests, only include public trees
            $query->where('is_public', true);
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Check if the current user has access to a specific tree.
     *
     * @param  Tree|int  $tree  The tree or tree ID to check
     * @param  bool  $requireOwnership  Whether to require ownership (not just access)
     * @return bool Whether the user has access
     */
    protected function userHasTreeAccess($tree, bool $requireOwnership = false): bool
    {
        if (! Auth::check()) {
            return false;
        }

        $treeId = $tree instanceof Tree ? $tree->id : $tree;
        $userId = Auth::id();

        // Check ownership
        $isOwner = Tree::where('id', $treeId)
            ->where('user_id', $userId)
            ->exists();

        if ($requireOwnership) {
            return $isOwner;
        }

        // Check if shared with user
        if (! $isOwner) {
            return DB::table('tree_user')
                ->where('tree_id', $treeId)
                ->where('user_id', $userId)
                ->exists();
        }

        return true;
    }

    /**
     * Log an action performed by a user.
     *
     * @param  string  $action  The action performed
     * @param  Model|null  $model  The model affected
     * @param  array  $oldValues  Old values (for updates)
     * @param  array  $newValues  New values (for updates)
     */
    protected function logUserAction(string $action, ?Model $model = null, array $oldValues = [], array $newValues = []): void
    {
        $data = [
            'user_id' => Auth::id(),
            'action' => $action,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];

        if ($model) {
            $data['model_type'] = get_class($model);
            $data['model_id'] = $model->getKey();
        }

        if (! empty($oldValues)) {
            $data['old_values'] = $oldValues;
        }

        if (! empty($newValues)) {
            $data['new_values'] = $newValues;
        }

        try {
            // Use activity log model if available, otherwise just log to file
            if (class_exists('\App\Models\ActivityLog')) {
                \App\Models\ActivityLog::create($data);
            } else {
                Log::info('User action: '.$action, $data);
            }
        } catch (Exception $e) {
            Log::error('Failed to log user action', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Validate request data with custom error messages.
     *
     * @param  Request  $request  The request to validate
     * @param  array  $rules  Validation rules
     * @param  array  $messages  Custom error messages
     * @param  array  $attributes  Custom attribute names
     * @return array The validated data
     *
     * @throws ValidationException If validation fails
     */
    protected function validateWithCustomMessages(
        Request $request,
        array $rules,
        array $messages = [],
        array $attributes = []
    ): array {
        $validator = Validator::make(
            $request->all(),
            $rules,
            $messages,
            $attributes
        );

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
