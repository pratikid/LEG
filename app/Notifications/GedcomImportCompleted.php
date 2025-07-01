<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Tree;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

final class GedcomImportCompleted extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @param  Tree  $tree  The tree that was imported into
     * @param  array  $parsedData  Array containing integer counts: ['individuals' => int, 'families' => int]
     * @param  string|null  $fileName  Original filename of the imported GEDCOM file
     * @param  string|null  $cleanedFilePath  Path to the cleaned GEDCOM file if user-defined tags were removed
     */
    public function __construct(
        protected Tree $tree,
        protected array $parsedData,
        protected ?string $fileName = null,
        protected ?string $cleanedFilePath = null
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
        $individualsCount = $this->parsedData['individuals'];
        $familiesCount = $this->parsedData['families'];

        $mailMessage = (new MailMessage)
            ->subject('GEDCOM Import Completed Successfully')
            ->greeting('Hello '.$notifiable->name.'!')
            ->line('Your GEDCOM file has been imported successfully.')
            ->line('File: '.($this->fileName ?? 'Unknown file'))
            ->line('Tree: '.$this->tree->name)
            ->line("Imported {$individualsCount} individuals and {$familiesCount} families.");

        // Add information about cleaned file if available
        if ($this->cleanedFilePath) {
            $mailMessage->line('Note: User-defined tags were removed during processing for compatibility.');
        }

        return $mailMessage
            ->action('View Tree Visualization', route('trees.visualization', $this->tree->id))
            ->line('You can now explore your family tree and view the relationships.')
            ->salutation('Best regards, LEG Team');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $data = [
            'type' => 'gedcom_import_completed',
            'title' => 'GEDCOM Import Completed',
            'message' => "Successfully imported {$this->fileName} into tree '{$this->tree->name}'",
            'tree_id' => $this->tree->id,
            'tree_name' => $this->tree->name,
            'file_name' => $this->fileName,
            'individuals_count' => $this->parsedData['individuals'],
            'families_count' => $this->parsedData['families'],
            'action_url' => route('trees.visualization', $this->tree->id),
            'action_text' => 'View Tree Visualization',
        ];

        // Add cleaned file path if available
        if ($this->cleanedFilePath) {
            $data['cleaned_file_path'] = $this->cleanedFilePath;
            $data['user_defined_tags_removed'] = true;
        }

        return $data;
    }

    /**
     * Get the notification's database type.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
