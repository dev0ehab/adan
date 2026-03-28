<?php

namespace App\Filament\Widgets;

use App\Models\DiseaseReport;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LatestReportsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Latest Disease Reports')
            ->query(
                DiseaseReport::query()
                    ->with(['reporter', 'animal', 'region'])
                    ->latest()
                    ->limit(8)
            )
            ->paginated(false)
            ->columns([
                TextColumn::make('title')->limit(40)->searchable(),
                TextColumn::make('reporter.name')->label('Reporter'),
                TextColumn::make('animal.name')->label('Animal')->badge()->color('info'),
                TextColumn::make('region.name')->label('Region')->placeholder('—'),
                TextColumn::make('severity')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'low' => 'success',
                        'moderate' => 'warning',
                        'high' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->since()->label('Submitted'),
            ]);
    }
}
