<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AnimalCategoryResource\Pages;
use App\Models\AnimalCategory;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AnimalCategoryResource extends Resource
{
    protected static ?string $model = AnimalCategory::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationLabel = 'Animal Categories';

    protected static ?string $navigationGroup = 'Animals & Vaccines';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name')->required()->maxLength(100),
            Textarea::make('description')->nullable()->rows(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable()->width(60),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('description')->limit(60)->toggleable(),
                TextColumn::make('animals_count')->counts('animals')->label('Animals')->sortable(),
                TextColumn::make('created_at')->dateTime()->toggleable(),
            ])
            ->actions([EditAction::make(), DeleteAction::make()])
            ->bulkActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAnimalCategories::route('/'),
            'create' => Pages\CreateAnimalCategory::route('/create'),
            'edit' => Pages\EditAnimalCategory::route('/{record}/edit'),
        ];
    }
}
