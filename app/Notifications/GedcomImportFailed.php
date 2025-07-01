<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class GedcomImportFailed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        protected ?string $fileName = null,
        protected ?string $errorMessage = null
    ) {
        $this->onQueue('notifications');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('GEDCOM Import Failed')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Unfortunately, your GEDCOM file import has failed.')
            ->line('File: '.($this->fileName ?? 'Unknown file'))
            ->line('Error: '.($this->errorMessage ?? 'Unknown error'))
            ->action('Try Import Again', route('trees.import'))
            ->line('Please check your file format and try again. If the problem persists, contact support.')
            ->salutation('Best regards, LEG Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'gedcom_import_failed',
            'title' => 'GEDCOM Import Failed',
            'message' => "Failed to import {$this->fileName}: {$this->errorMessage}",
            'file_name' => $this->fileName,
            'error_message' => $this->errorMessage,
            'action_url' => route('trees.import'),
            'action_text' => 'Try Import Again',
        ];
    }

    /**
     * Get the notification's database type.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
