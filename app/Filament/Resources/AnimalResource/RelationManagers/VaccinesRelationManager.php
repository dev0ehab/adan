<?php

namespace App\Filament\Resources\AnimalResource\RelationManagers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VaccinesRelationManager extends RelationManager
{
    protected static string $relationship = 'vaccines';

    protected static ?string $title = 'Vaccines for this Animal';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(100)->label('Vaccine Name')->columnSpanFull(),
            TextInput::make('doses_count')
                ->numeric()->required()->default(1)->minValue(1)
                ->label('Number of Doses'),
            TextInput::make('interval_days')
                ->numeric()->nullable()->minValue(1)
                ->label('Interval (days)')
                ->helperText('Days between doses or annual repeat. Leave empty if one-time only.'),
            Toggle::make('is_lifetime')
                ->label('Given once in lifetime?')
                ->helperText('Enable if this vaccine is administered only once in the animal\'s life.')
                ->reactive()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $set('doses_count', 1);
                        $set('interval_days', null);
                    }
                }),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('doses_count')->label('Doses')->sortable(),
                TextColumn::make('interval_days')->label('Interval (days)')->placeholder('—'),
                IconColumn::make('is_lifetime')->label('Lifetime?')->boolean(),
            ])
            ->headerActions([CreateAction::make()])
            ->actions([EditAction::make(), DeleteAction::make()]);
    }
}
