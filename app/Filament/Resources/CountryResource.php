<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CountryResource\Pages;
use App\Models\Country;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CountryResource extends Resource
{
    protected static ?string $model = Country::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    protected static ?string $navigationGroup = 'Locations';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')
                ->required()
                ->maxLength(100)
                ->label('Country Name'),
            TextInput::make('code')
                ->required()
                ->maxLength(5)
                ->label('ISO Code')
                ->helperText('e.g. EG for Egypt, US for United States')
                ->validationAttribute('ISO code')
                ->extraInputAttributes(['class' => 'uppercase'])
                ->afterStateUpdated(function (Set $set, $state): void {
                    if (! is_string($state) || $state === '') {
                        return;
                    }
                    $normalized = strtoupper(trim($state));
                    if ($normalized !== $state) {
                        $set('code', $normalized);
                    }
                })
                ->dehydrateStateUsing(fn (?string $state): ?string => $state === null || $state === '' ? $state : strtoupper(trim($state)))
                ->unique(Country::class, 'code', ignoreRecord: true)
                ->validationMessages([
                    'unique' => 'This ISO code is already in use.',
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->width(60),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('code')->badge()->color('success'),
                TextColumn::make('governorates_count')
                    ->counts('governorates')
                    ->label('Governorates')
                    ->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCountries::route('/'),
            'create' => Pages\CreateCountry::route('/create'),
            'edit' => Pages\EditCountry::route('/{record}/edit'),
        ];
    }
}
