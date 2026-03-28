<?php

namespace App\Notifications;

use App\Models\DiseaseReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DiseaseReportSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DiseaseReport $report) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('✅ Your Disease Report Has Been Received')
            ->greeting("Hello {$notifiable->name},")
            ->line("We have received your disease report: **\"{$this->report->title}\"**.")
            ->line('Our veterinary team will review it shortly. You will be notified once a decision has been made.')
            ->line('📍 **Location:** '.($this->report->region?->name ?? '—'))
            ->line("🐄 **Animal:** {$this->report->animal->name}")
            ->line("⚠️ **Severity:** {$this->report->severity}")
            ->action('View Your Reports', env('FRONTEND_URL', 'http://localhost:5173').'/reports')
            ->salutation('Thank you for helping keep animals safe, The ADAN Team');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'report_submitted',
            'title' => 'Report Received & Under Review',
            'body' => "Your report \"{$this->report->title}\" has been submitted and is awaiting veterinary review.",
            'report_id' => $this->report->id,
        ];
    }
}
