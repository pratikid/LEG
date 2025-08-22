<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;

/**
 * Trait for handling notification operations
 */
trait HandlesNotificationOperations
{
    /**
     * Send a notification to a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @param \Illuminate\Notifications\Notification $notification The notification to send
     * @return void
     */
    protected function sendNotification($notifiable, Notification $notification): void
    {
        $notifiable->notify($notification);
    }
    
    /**
     * Send a notification to multiple notifiable entities.
     *
     * @param array|\Illuminate\Support\Collection $notifiables The notifiable entities
     * @param \Illuminate\Notifications\Notification $notification The notification to send
     * @return void
     */
    protected function sendNotificationToMultiple($notifiables, Notification $notification): void
    {
        NotificationFacade::send($notifiables, $notification);
    }
    
    /**
     * Send a notification later (queued).
     *
     * @param mixed $notifiable The notifiable entity
     * @param \Illuminate\Notifications\Notification $notification The notification to send
     * @param \DateTimeInterface|\DateInterval|int|null $delay The delay before sending
     * @return void
     */
    protected function sendNotificationLater($notifiable, Notification $notification, $delay = null): void
    {
        $notifiable->notifyNow($notification);
    }
    
    /**
     * Send a notification immediately (not queued).
     *
     * @param mixed $notifiable The notifiable entity
     * @param \Illuminate\Notifications\Notification $notification The notification to send
     * @return void
     */
    protected function sendNotificationNow($notifiable, Notification $notification): void
    {
        $notifiable->notifyNow($notification);
    }
    
    /**
     * Send a notification to multiple notifiable entities immediately (not queued).
     *
     * @param array|\Illuminate\Support\Collection $notifiables The notifiable entities
     * @param \Illuminate\Notifications\Notification $notification The notification to send
     * @return void
     */
    protected function sendNotificationToMultipleNow($notifiables, Notification $notification): void
    {
        NotificationFacade::sendNow($notifiables, $notification);
    }
    
    /**
     * Get all notifications for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @return \Illuminate\Database\Eloquent\Collection The notifications
     */
    protected function getAllNotifications($notifiable)
    {
        return $notifiable->notifications;
    }
    
    /**
     * Get all unread notifications for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @return \Illuminate\Database\Eloquent\Collection The unread notifications
     */
    protected function getUnreadNotifications($notifiable)
    {
        return $notifiable->unreadNotifications;
    }
    
    /**
     * Mark a notification as read.
     *
     * @param mixed $notifiable The notifiable entity
     * @param string $notificationId The notification ID
     * @return void
     */
    protected function markNotificationAsRead($notifiable, string $notificationId): void
    {
        $notification = $notifiable->notifications()->where('id', $notificationId)->first();
        
        if ($notification) {
            $notification->markAsRead();
        }
    }
    
    /**
     * Mark all notifications as read for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @return void
     */
    protected function markAllNotificationsAsRead($notifiable): void
    {
        $notifiable->unreadNotifications->markAsRead();
    }
    
    /**
     * Delete a notification.
     *
     * @param mixed $notifiable The notifiable entity
     * @param string $notificationId The notification ID
     * @return void
     */
    protected function deleteNotification($notifiable, string $notificationId): void
    {
        $notifiable->notifications()->where('id', $notificationId)->delete();
    }
    
    /**
     * Delete all notifications for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @return void
     */
    protected function deleteAllNotifications($notifiable): void
    {
        $notifiable->notifications()->delete();
    }
    
    /**
     * Get the notification count for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @param bool $onlyUnread Whether to count only unread notifications
     * @return int The notification count
     */
    protected function getNotificationCount($notifiable, bool $onlyUnread = true): int
    {
        return $onlyUnread
            ? $notifiable->unreadNotifications()->count()
            : $notifiable->notifications()->count();
    }
    
    /**
     * Get notifications by type for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @param string $type The notification type
     * @param bool $onlyUnread Whether to get only unread notifications
     * @return \Illuminate\Database\Eloquent\Collection The notifications
     */
    protected function getNotificationsByType($notifiable, string $type, bool $onlyUnread = false)
    {
        $query = $onlyUnread
            ? $notifiable->unreadNotifications()
            : $notifiable->notifications();
            
        return $query->where('type', $type)->get();
    }
    
    /**
     * Get notifications created after a specific date for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @param string|\DateTimeInterface $date The date
     * @param bool $onlyUnread Whether to get only unread notifications
     * @return \Illuminate\Database\Eloquent\Collection The notifications
     */
    protected function getNotificationsAfterDate($notifiable, $date, bool $onlyUnread = false)
    {
        $query = $onlyUnread
            ? $notifiable->unreadNotifications()
            : $notifiable->notifications();
            
        return $query->where('created_at', '>=', $date)->get();
    }
    
    /**
     * Get notifications created before a specific date for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @param string|\DateTimeInterface $date The date
     * @param bool $onlyUnread Whether to get only unread notifications
     * @return \Illuminate\Database\Eloquent\Collection The notifications
     */
    protected function getNotificationsBeforeDate($notifiable, $date, bool $onlyUnread = false)
    {
        $query = $onlyUnread
            ? $notifiable->unreadNotifications()
            : $notifiable->notifications();
            
        return $query->where('created_at', '<=', $date)->get();
    }
    
    /**
     * Get notifications with pagination for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @param int $perPage The number of items per page
     * @param bool $onlyUnread Whether to get only unread notifications
     * @return \Illuminate\Pagination\LengthAwarePaginator The paginated notifications
     */
    protected function getPaginatedNotifications($notifiable, int $perPage = 15, bool $onlyUnread = false)
    {
        $query = $onlyUnread
            ? $notifiable->unreadNotifications()
            : $notifiable->notifications();
            
        return $query->paginate($perPage);
    }
    
    /**
     * Check if a notifiable entity has unread notifications.
     *
     * @param mixed $notifiable The notifiable entity
     * @return bool True if the notifiable entity has unread notifications, false otherwise
     */
    protected function hasUnreadNotifications($notifiable): bool
    {
        return $notifiable->unreadNotifications()->exists();
    }
    
    /**
     * Check if a notifiable entity has a specific notification type.
     *
     * @param mixed $notifiable The notifiable entity
     * @param string $type The notification type
     * @param bool $onlyUnread Whether to check only unread notifications
     * @return bool True if the notifiable entity has the notification type, false otherwise
     */
    protected function hasNotificationType($notifiable, string $type, bool $onlyUnread = false): bool
    {
        $query = $onlyUnread
            ? $notifiable->unreadNotifications()
            : $notifiable->notifications();
            
        return $query->where('type', $type)->exists();
    }
    
    /**
     * Get the latest notification for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @param bool $onlyUnread Whether to get only unread notifications
     * @return mixed The latest notification
     */
    protected function getLatestNotification($notifiable, bool $onlyUnread = false)
    {
        $query = $onlyUnread
            ? $notifiable->unreadNotifications()
            : $notifiable->notifications();
            
        return $query->latest()->first();
    }
    
    /**
     * Get the oldest notification for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @param bool $onlyUnread Whether to get only unread notifications
     * @return mixed The oldest notification
     */
    protected function getOldestNotification($notifiable, bool $onlyUnread = false)
    {
        $query = $onlyUnread
            ? $notifiable->unreadNotifications()
            : $notifiable->notifications();
            
        return $query->oldest()->first();
    }
    
    /**
     * Get notifications with data containing a specific key for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @param string $key The data key
     * @param mixed $value The data value
     * @param bool $onlyUnread Whether to get only unread notifications
     * @return \Illuminate\Database\Eloquent\Collection The notifications
     */
    protected function getNotificationsWithData($notifiable, string $key, $value = null, bool $onlyUnread = false)
    {
        $query = $onlyUnread
            ? $notifiable->unreadNotifications()
            : $notifiable->notifications();
            
        return $query->where("data->{$key}", $value)->get();
    }
    
    /**
     * Route notifications for a specific channel.
     *
     * @param mixed $notifiable The notifiable entity
     * @param string $channel The notification channel
     * @return mixed The routing information
     */
    protected function routeNotificationsFor($notifiable, string $channel)
    {
        return $notifiable->routeNotificationFor($channel);
    }
    
    /**
     * Get the notification channels for a notification.
     *
     * @param \Illuminate\Notifications\Notification $notification The notification
     * @param mixed $notifiable The notifiable entity
     * @return array The notification channels
     */
    protected function getNotificationChannels(Notification $notification, $notifiable): array
    {
        return method_exists($notification, 'via')
            ? (array) $notification->via($notifiable)
            : ['mail'];
    }
    
    /**
     * Check if a notification uses a specific channel.
     *
     * @param \Illuminate\Notifications\Notification $notification The notification
     * @param mixed $notifiable The notifiable entity
     * @param string $channel The notification channel
     * @return bool True if the notification uses the channel, false otherwise
     */
    protected function notificationUsesChannel(Notification $notification, $notifiable, string $channel): bool
    {
        return in_array($channel, $this->getNotificationChannels($notification, $notifiable));
    }
    
    /**
     * Get the notification data.
     *
     * @param mixed $notification The notification
     * @return array The notification data
     */
    protected function getNotificationData($notification): array
    {
        return $notification->data ?? [];
    }
    
    /**
     * Get a specific value from the notification data.
     *
     * @param mixed $notification The notification
     * @param string $key The data key
     * @param mixed $default The default value if the key doesn't exist
     * @return mixed The data value
     */
    protected function getNotificationDataValue($notification, string $key, $default = null)
    {
        $data = $this->getNotificationData($notification);
        
        return $data[$key] ?? $default;
    }
    
    /**
     * Check if a notification has a specific data key.
     *
     * @param mixed $notification The notification
     * @param string $key The data key
     * @return bool True if the notification has the data key, false otherwise
     */
    protected function notificationHasDataKey($notification, string $key): bool
    {
        $data = $this->getNotificationData($notification);
        
        return array_key_exists($key, $data);
    }
    
    /**
     * Get the notification created date.
     *
     * @param mixed $notification The notification
     * @return \Carbon\Carbon|null The notification created date
     */
    protected function getNotificationCreatedDate($notification)
    {
        return $notification->created_at ?? null;
    }
    
    /**
     * Get the notification read date.
     *
     * @param mixed $notification The notification
     * @return \Carbon\Carbon|null The notification read date
     */
    protected function getNotificationReadDate($notification)
    {
        return $notification->read_at ?? null;
    }
    
    /**
     * Check if a notification is read.
     *
     * @param mixed $notification The notification
     * @return bool True if the notification is read, false otherwise
     */
    protected function isNotificationRead($notification): bool
    {
        return $notification->read_at !== null;
    }
    
    /**
     * Get the notification type.
     *
     * @param mixed $notification The notification
     * @return string|null The notification type
     */
    protected function getNotificationType($notification): ?string
    {
        return $notification->type ?? null;
    }
    
    /**
     * Get the notification ID.
     *
     * @param mixed $notification The notification
     * @return string|null The notification ID
     */
    protected function getNotificationId($notification): ?string
    {
        return $notification->id ?? null;
    }
    
    /**
     * Group notifications by type for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @param bool $onlyUnread Whether to group only unread notifications
     * @return \Illuminate\Support\Collection The grouped notifications
     */
    protected function groupNotificationsByType($notifiable, bool $onlyUnread = false): Collection
    {
        $notifications = $onlyUnread
            ? $notifiable->unreadNotifications
            : $notifiable->notifications;
            
        return $notifications->groupBy('type');
    }
    
    /**
     * Group notifications by date for a notifiable entity.
     *
     * @param mixed $notifiable The notifiable entity
     * @param string $dateFormat The date format
     * @param bool $onlyUnread Whether to group only unread notifications
     * @return \Illuminate\Support\Collection The grouped notifications
     */
    protected function groupNotificationsByDate($notifiable, string $dateFormat = 'Y-m-d', bool $onlyUnread = false): Collection
    {
        $notifications = $onlyUnread
            ? $notifiable->unreadNotifications
            : $notifiable->notifications;
            
        return $notifications->groupBy(function ($notification) use ($dateFormat) {
            return $notification->created_at->format($dateFormat);
        });
    }
    
    /**
     * Get the notification database connection.
     *
     * @return string|null The notification database connection
     */
    protected function getNotificationDatabaseConnection(): ?string
    {
        return config('database.notifications.connection');
    }
    
    /**
     * Get the notification database table.
     *
     * @return string The notification database table
     */
    protected function getNotificationDatabaseTable(): string
    {
        return config('database.notifications.table', 'notifications');
    }
}