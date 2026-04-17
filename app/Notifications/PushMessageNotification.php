<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Persists title/body + payload for GET /api/notifications (database channel).
 * FCM is sent separately by PushNotificationService.
 */
class PushMessageNotification extends Notification
{
    use Queueable;

    /**
     * @param  array<string, mixed>  $payload  Extra keys (e.g. type, report_id); must be JSON-serializable.
     */
    public function __construct(
        public string $title,
        public string $body,
        public array $payload = [],
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toDatabase(object $notifiable): array
    {
        $row = [
            'title' => $this->title,
            'body' => $this->body,
        ];

        foreach ($this->payload as $key => $value) {
            if ($key === 'title' || $key === 'body') {
                continue;
            }
            $row[$key] = $value;
        }

        if (! array_key_exists('type', $row)) {
            $row['type'] = 'push';
        }

        return $row;
    }
}
