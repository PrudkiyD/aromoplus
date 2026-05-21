<?php

namespace App\Filament\Resources\Catalog;

use App\Filament\Resources\Catalog\PriceListResource\Pages;
use App\Models\Catalog\PriceList;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PriceListResource extends Resource
{
    protected static ?string $model = PriceList::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Каталог товарів';
    protected static ?string $modelLabel = 'Група знижок';
    protected static ?string $pluralModelLabel = 'Група знижок';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Назва прайс-листа')
                            ->required()
                            ->placeholder('Наприклад: Оптовий поріг 1')
                            ->maxLength(255),

                        Forms\Components\Toggle::make('main')
                            ->label('Головний прайс-лист')
                            ->helperText('Тільки один прайс-лист може бути головним одночасно.')
                            ->default(false),
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

                Tables\Columns\IconColumn::make('main')
                    ->label('Статус головного')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Створено')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('main')
                    ->label('Тільки головний'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPriceLists::route('/'),
            'create' => Pages\CreatePriceList::route('/create'),
            'edit' => Pages\EditPriceList::route('/{record}/edit'),
        ];
    }
}