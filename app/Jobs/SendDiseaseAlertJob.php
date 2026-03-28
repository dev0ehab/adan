<?php

namespace App\Jobs;

use App\Models\DiseaseReport;
use App\Models\User;
use App\Notifications\DiseaseAlertNotification;
use App\Notifications\ReportStatusNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDiseaseAlertJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 120;

    public function __construct(public DiseaseReport $report) {}

    public function handle(): void
    {
        if ($this->report->region_id === null) {
            $this->report->load('reporter');
            $this->report->reporter?->notify(new ReportStatusNotification($this->report->fresh()));

            return;
        }

        User::where('region_id', $this->report->region_id)
            ->where('role', 'customer')
            ->where('id', '!=', $this->report->user_id)
            ->chunk(50, function ($users) {
                foreach ($users as $user) {
                    $user->notify(new DiseaseAlertNotification($this->report));
                }
            });

        $this->report->load('reporter');
        $this->report->reporter?->notify(
            new ReportStatusNotification($this->report->fresh())
        );
    }
}
