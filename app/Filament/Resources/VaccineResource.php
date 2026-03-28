<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VaccineResource\Pages;
use App\Models\Animal;
use App\Models\Vaccine;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class VaccineResource extends Resource
{
    protected static ?string $model = Vaccine::class;

    protected static ?string $navigationIcon = 'heroicon-o-beaker';

    protected static ?string $navigationGroup = 'Animals & Vaccines';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('animal_id')
                ->label('Animal')
                ->options(
                    Animal::with('category')
                        ->get()
                        ->mapWithKeys(fn ($a) => [$a->id => "{$a->name} ({$a->category->name})"])
                )
                ->searchable()
                ->required(),
            TextInput::make('name')->required()->maxLength(100)->label('Vaccine Name'),
            TextInput::make('doses_count')
                ->numeric()->required()->default(1)->minValue(1)->label('Number of Doses'),
            TextInput::make('interval_days')
                ->numeric()->nullable()->minValue(1)
                ->label('Interval Between Doses (days)')
                ->helperText('e.g. 180 for every 6 months. Leave empty if not recurring.'),
            Toggle::make('is_lifetime')
                ->label('Lifetime vaccine (given only once)')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $set('doses_count', 1);
                        $set('interval_days', null);
                    }
                }),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->width(60),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('animal.name')->label('Animal')->sortable()->badge()->color('info'),
                TextColumn::make('doses_count')->label('Doses'),
                TextColumn::make('interval_days')->label('Interval (days)')->placeholder('—'),
                IconColumn::make('is_lifetime')->label('Lifetime?')->boolean(),
                TextColumn::make('created_at')->dateTime()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('animal_id')
                    ->label('Animal')
                    ->options(Animal::all()->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVaccines::route('/'),
            'create' => Pages\CreateVaccine::route('/create'),
            'edit' => Pages\EditVaccine::route('/{record}/edit'),
        ];
    }
}
