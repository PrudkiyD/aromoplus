<?php

namespace App\Filament\Resources\Page;

use App\Filament\Resources\Page\ElementResource\Pages;
use App\Models\Page\Element;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ElementResource extends Resource
{
    protected static ?string $model = Element::class;

    protected static ?string $navigationIcon = 'heroicon-o-puzzle-piece';

    protected static ?string $navigationGroup = 'Сторінки';

    protected static ?string $label = 'Елемент';

    protected static ?string $pluralLabel = 'Елементи';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Основне')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Назва')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ])->columns(2),

            Forms\Components\Section::make('Контент')->schema([
                Forms\Components\RichEditor::make('content')
                    ->label('Контент')
                    ->columnSpanFull(),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Назва')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Оновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListElements::route('/'),
            'create' => Pages\CreateElement::route('/create'),
            'edit' => Pages\EditElement::route('/{record}/edit'),
        ];
    }
}
