<?php

namespace App\Filament\Widgets;

use App\Models\DiseaseReport;
use App\Models\User;
use App\Models\UserAnimal;
use App\Models\VaccineSchedule;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Total Breeders', User::where('role', 'customer')->count())
                ->description('Registered customer accounts')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Animals Tracked', UserAnimal::count())
                ->description('Total registered animals')
                ->descriptionIcon('heroicon-m-heart')
                ->color('info'),

            Stat::make('Pending Reports', DiseaseReport::pending()->count())
                ->description('Awaiting vet review')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('Vaccines Due This Week',
                VaccineSchedule::upcoming(7)->count()
            )
                ->description('Schedules in next 7 days')
                ->descriptionIcon('heroicon-m-beaker')
                ->color('primary'),

            Stat::make('Approved Reports (Month)',
                DiseaseReport::approved()
                    ->whereMonth('reviewed_at', now()->month)
                    ->whereYear('reviewed_at', now()->year)
                    ->count()
            )
                ->description('Confirmed this month')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Overdue Vaccines', VaccineSchedule::overdue()->count())
                ->description('Past due, not administered')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('danger'),
        ];
    }
}
