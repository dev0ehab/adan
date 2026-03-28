<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiseaseReportResource\Pages;
use App\Jobs\SendDiseaseAlertJob;
use App\Models\DiseaseReport;
use App\Models\Region;
use App\Notifications\ReportStatusNotification;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DiseaseReportResource extends Resource
{
    protected static ?string $model = DiseaseReport::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';

    protected static ?string $navigationLabel = 'Disease Reports';

    protected static ?string $navigationGroup = 'Reports';

    protected static ?string $navigationBadge = null;

    public static function getNavigationBadge(): ?string
    {
        return (string) DiseaseReport::pending()->count() ?: null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Report')
                    ->schema([
                        TextEntry::make('title'),
                        TextEntry::make('description')->columnSpanFull(),
                        TextEntry::make('severity')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'low' => 'success',
                                'moderate' => 'warning',
                                'high' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('status')
                            ->badge()
                            ->color(fn (string $state) => match ($state) {
                                'pending' => 'warning',
                                'approved' => 'success',
                                'rejected' => 'danger',
                                default => 'gray',
                            }),
                        TextEntry::make('rejection_reason')
                            ->label('Rejection reason')
                            ->placeholder('—')
                            ->columnSpanFull()
                            ->visible(fn (DiseaseReport $record) => $record->isRejected()),
                        TextEntry::make('latitude')
                            ->label('Latitude')
                            ->placeholder('—'),
                        TextEntry::make('longitude')
                            ->label('Longitude')
                            ->placeholder('—'),
                        TextEntry::make('created_at')->dateTime()->label('Submitted'),
                        TextEntry::make('reviewed_at')->dateTime()->label('Reviewed')->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make('Context')
                    ->schema([
                        TextEntry::make('reporter.name')->label('Reporter'),
                        TextEntry::make('reporter.email')->label('Reporter email'),
                        TextEntry::make('animal.name')->label('Animal'),
                        TextEntry::make('region.name')->label('Region')->placeholder('—'),
                    ])
                    ->columns(2),
                Section::make('Submitted images')
                    ->schema([
                        ImageEntry::make('images')
                            ->label('')
                            ->getStateUsing(fn (DiseaseReport $record): array => $record->getMedia('images')
                                ->map(fn ($media) => $media->getUrl())
                                ->filter()
                                ->values()
                                ->all())
                            ->height('10rem')
                            ->columnSpanFull()
                            ->defaultImageUrl(null),
                    ])
                    ->visible(fn (DiseaseReport $record) => $record->getMedia('images')->isNotEmpty()),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->disabled()->columnSpanFull(),
            Textarea::make('description')->disabled()->rows(4)->columnSpanFull(),
            TextInput::make('reporter.name')->disabled()->label('Submitted By'),
            TextInput::make('animal.name')->disabled()->label('Animal'),
            TextInput::make('region.name')->disabled()->label('Region'),
            Select::make('severity')
                ->options(['low' => 'Low', 'moderate' => 'Moderate', 'high' => 'High'])
                ->disabled(),
            Select::make('status')
                ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])
                ->required()
                ->live(),
            Textarea::make('rejection_reason')
                ->nullable()
                ->rows(3)
                ->label('Rejection Reason')
                ->helperText('Required when rejecting a report')
                ->visible(fn (Get $get) => $get('status') === 'rejected'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->width(60),
                TextColumn::make('title')->searchable()->limit(40)->sortable(),
                TextColumn::make('reporter.name')->label('Reporter')->searchable(),
                TextColumn::make('animal.name')->label('Animal')->sortable(),
                TextColumn::make('region.name')->label('Region')->sortable(),
                TextColumn::make('severity')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'low' => 'success',
                        'moderate' => 'warning',
                        'high' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->dateTime()->sortable()->label('Submitted'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']),
                SelectFilter::make('severity')
                    ->options(['low' => 'Low', 'moderate' => 'Moderate', 'high' => 'High']),
                SelectFilter::make('region_id')
                    ->label('Region')
                    ->options(Region::all()->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([
                ViewAction::make(),
                Action::make('approve')
                    ->label('Approve')
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Disease Report')
                    ->modalDescription('This will approve the report and send disease alert notifications to all users in the affected region.')
                    ->visible(fn (DiseaseReport $record) => $record->isPending())
                    ->action(function (DiseaseReport $record) {
                        $record->update([
                            'status' => 'approved',
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        $fresh = $record->fresh();
                        SendDiseaseAlertJob::dispatch($fresh);
                        $regionLabel = $fresh->region?->name ?? 'the affected region';
                        Notification::make()
                            ->title('Report Approved')
                            ->body("Alerts have been dispatched to users in {$regionLabel}.")
                            ->success()
                            ->send();
                    }),
                Action::make('reject')
                    ->label('Reject')
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->visible(fn (DiseaseReport $record) => $record->isPending())
                    ->form([
                        Textarea::make('rejection_reason')
                            ->label('Reason for rejection')
                            ->required()
                            ->rows(4),
                    ])
                    ->action(function (DiseaseReport $record, array $data) {
                        $record->update([
                            'status' => 'rejected',
                            'rejection_reason' => $data['rejection_reason'],
                            'reviewed_by' => auth()->id(),
                            'reviewed_at' => now(),
                        ]);
                        $record->reporter?->notify(
                            new ReportStatusNotification($record->fresh())
                        );
                        Notification::make()
                            ->title('Report Rejected')
                            ->warning()
                            ->send();
                    }),
            ])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDiseaseReports::route('/'),
            'view' => Pages\ViewDiseaseReport::route('/{record}'),
        ];
    }
}
