<?php

namespace App\Notifications;

use App\Models\DiseaseReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

class DiseaseAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DiseaseReport $report) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->report->loadMissing('category');
        $severity = strtoupper($this->report->severity);
        $regionName = $this->report->region?->name ?? 'your area';
        $description = Str::limit($this->report->description, 200);

        return (new MailMessage)
            ->subject("⚠️ Disease Alert in {$regionName}: {$this->report->title}")
            ->greeting("Important Alert for {$notifiable->name}")
            ->line("A **{$severity}** severity disease case has been confirmed in **{$regionName}**.")
            ->line("**{$this->report->title}**")
            ->line($description)
            ->line('🐄 **Affected category:** '.(optional($this->report->category)->name ?? 'Unknown'))
            ->action('View Full Details', env('FRONTEND_URL', 'http://localhost:5173').'/alerts')
            ->line('Please take precautionary measures to protect your animals.')
            ->salutation('Stay vigilant, The ADAN Team');
    }

    public function toDatabase(object $notifiable): array
    {
        $this->report->loadMissing('category');

        return [
            'type' => 'disease_alert',
            'title' => "⚠️ Disease Alert: {$this->report->title}",
            'body' => 'Confirmed in '.($this->report->region?->name ?? 'unknown').' — '.$this->report->severity.' severity. '.(optional($this->report->category)->name ?? 'Unknown').' affected.',
            'report_id' => $this->report->id,
            'region_id' => $this->report->region_id,
            'severity' => $this->report->severity,
        ];
    }
}
