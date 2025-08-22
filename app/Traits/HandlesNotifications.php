<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\User;
use App\Notifications\TreeSharedNotification;
use App\Notifications\TreeUpdatedNotification;
use App\Notifications\CommentAddedNotification;
use App\Notifications\MentionNotification;
use App\Notifications\NewMessageNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

/**
 * Trait for handling notifications
 */
trait HandlesNotifications
{
    /**
     * Send a notification when a tree is shared with a user.
     *
     * @param User $recipient The user receiving the share
     * @param Model $tree The tree being shared
     * @param User|null $sharedBy The user sharing the tree (defaults to authenticated user)
     * @param string $accessLevel The access level granted (read, write, admin)
     * @return void
     */
    protected function sendTreeSharedNotification(
        User $recipient,
        Model $tree,
        ?User $sharedBy = null,
        string $accessLevel = 'read'
    ): void {
        $sharedBy = $sharedBy ?? auth()?->user();
        
        // Don't send notification if recipient has disabled this notification type
        if (!$recipient->shouldReceiveNotification('tree_shared')) {
            return;
        }
        
        $recipient->notify(new TreeSharedNotification($tree, $sharedBy, $accessLevel));
    }
    
    /**
     * Send a notification when a tree is updated.
     *
     * @param Model $tree The tree that was updated
     * @param string $updateType The type of update (e.g., 'individual_added', 'family_updated')
     * @param Model|null $updatedModel The specific model that was updated
     * @param User|null $updatedBy The user who made the update (defaults to authenticated user)
     * @return void
     */
    protected function sendTreeUpdatedNotification(
        Model $tree,
        string $updateType,
        ?Model $updatedModel = null,
        ?User $updatedBy = null
    ): void {
        $updatedBy = $updatedBy ?? auth()->user();
        
        // Get all users who should be notified (tree owner and users with whom the tree is shared)
        $usersToNotify = $this->getUsersToNotifyForTree($tree);
        
        // Filter out users who have disabled this notification type
        $usersToNotify = $usersToNotify->filter(function ($user) {
            return $user->shouldReceiveNotification('tree_updated');
        });
        
        // Don't notify the user who made the update
        $usersToNotify = $usersToNotify->filter(function ($user) use ($updatedBy) {
            return $updatedBy === null || $user->id !== $updatedBy->id;
        });
        
        Notification::send($usersToNotify, new TreeUpdatedNotification(
            $tree,
            $updateType,
            $updatedModel,
            $updatedBy
        ));
    }
    
    /**
     * Send a notification when a comment is added.
     *
     * @param Model $comment The comment that was added
     * @param Model $commentable The model the comment was added to
     * @param User|null $commentedBy The user who added the comment (defaults to authenticated user)
     * @return void
     */
    protected function sendCommentAddedNotification(
        Model $comment,
        Model $commentable,
        ?User $commentedBy = null
    ): void {
        $commentedBy = $commentedBy ?? auth()->user();
        
        // Determine which users to notify based on the commentable type
        $usersToNotify = $this->getUsersToNotifyForComment($comment, $commentable);
        
        // Filter out users who have disabled this notification type
        $usersToNotify = $usersToNotify->filter(function ($user) {
            return $user->shouldReceiveNotification('comment_added');
        });
        
        // Don't notify the user who made the comment
        $usersToNotify = $usersToNotify->filter(function ($user) use ($commentedBy) {
            return $commentedBy === null || $user->id !== $commentedBy->id;
        });
        
        Notification::send($usersToNotify, new CommentAddedNotification(
            $comment,
            $commentable,
            $commentedBy
        ));
    }
    
    /**
     * Send a notification when a user is mentioned.
     *
     * @param User $mentionedUser The user who was mentioned
     * @param Model $mentionableModel The model where the mention occurred
     * @param User|null $mentionedBy The user who mentioned (defaults to authenticated user)
     * @return void
     */
    protected function sendMentionNotification(
        User $mentionedUser,
        Model $mentionableModel,
        ?User $mentionedBy = null
    ): void {
        $mentionedBy = $mentionedBy ?? auth()->user();
        
        // Don't send notification if recipient has disabled this notification type
        if (!$mentionedUser->shouldReceiveNotification('mention')) {
            return;
        }
        
        // Don't notify if the user mentioned themselves
        if ($mentionedBy !== null && $mentionedUser->id === $mentionedBy->id) {
            return;
        }
        
        $mentionedUser->notify(new MentionNotification(
            $mentionableModel,
            $mentionedBy
        ));
    }
    
    /**
     * Send a notification when a new message is received.
     *
     * @param User $recipient The user receiving the message
     * @param Model $message The message that was sent
     * @param User|null $sentBy The user who sent the message (defaults to authenticated user)
     * @return void
     */
    protected function sendNewMessageNotification(
        User $recipient,
        Model $message,
        ?User $sentBy = null
    ): void {
        $sentBy = $sentBy ?? auth()->user();
        
        // Don't send notification if recipient has disabled this notification type
        if (!$recipient->shouldReceiveNotification('new_message')) {
            return;
        }
        
        // Don't notify if the user messaged themselves
        if ($sentBy !== null && $recipient->id === $sentBy->id) {
            return;
        }
        
        $recipient->notify(new NewMessageNotification(
            $message,
            $sentBy
        ));
    }
    
    /**
     * Get users who should be notified about updates to a tree.
     *
     * @param Model $tree The tree
     * @return Collection Users to notify
     */
    protected function getUsersToNotifyForTree(Model $tree): Collection
    {
        $users = new Collection();
        
        // Add the tree owner
        if ($tree->user) {
            $users->push($tree->user);
        }
        
        // Add users with whom the tree is shared
        if (method_exists($tree, 'sharedUsers')) {
            $users = $users->merge($tree->sharedUsers);
        }
        
        return $users->unique('id');
    }
    
    /**
     * Get users who should be notified about a new comment.
     *
     * @param Model $comment The comment
     * @param Model $commentable The model the comment was added to
     * @return Collection Users to notify
     */
    protected function getUsersToNotifyForComment(Model $comment, Model $commentable): Collection
    {
        $users = new Collection();
        
        // Add the owner of the commentable model
        if (method_exists($commentable, 'user') && $commentable->user) {
            $users->push($commentable->user);
        }
        
        // Add users who have also commented on this model
        if (method_exists($commentable, 'comments')) {
            $commenters = $commentable->comments()
                ->with('user')
                ->get()
                ->pluck('user');
                
            $users = $users->merge($commenters);
        }
        
        // If the commentable is related to a tree, add tree owner and shared users
        if (method_exists($commentable, 'tree') && $commentable->tree) {
            $users = $users->merge($this->getUsersToNotifyForTree($commentable->tree));
        }
        
        return $users->unique('id');
    }
    
    /**
     * Extract mentioned users from content.
     *
     * @param string $content The content to check for mentions
     * @return Collection Users who were mentioned
     */
    protected function extractMentionedUsers(string $content): Collection
    {
        $mentionedUsers = new Collection();
        
        // Extract @username mentions
        preg_match_all('/@([\w.]+)/', $content, $matches);
        
        if (!empty($matches[1])) {
            $usernames = $matches[1];
            
            // Find users by username
            $users = User::whereIn('username', $usernames)->get();
            $mentionedUsers = $mentionedUsers->merge($users);
        }
        
        return $mentionedUsers->unique('id');
    }
    
    /**
     * Process mentions in content and send notifications.
     *
     * @param string $content The content to check for mentions
     * @param Model $mentionableModel The model where the mention occurred
     * @param User|null $mentionedBy The user who mentioned (defaults to authenticated user)
     * @return void
     */
    protected function processMentions(
        string $content,
        Model $mentionableModel,
        ?User $mentionedBy = null
    ): void {
        $mentionedUsers = $this->extractMentionedUsers($content);
        
        foreach ($mentionedUsers as $mentionedUser) {
            $this->sendMentionNotification(
                $mentionedUser,
                $mentionableModel,
                $mentionedBy
            );
        }
    }
}