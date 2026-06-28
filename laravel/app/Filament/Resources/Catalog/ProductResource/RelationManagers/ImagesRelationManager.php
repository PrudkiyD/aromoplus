<?php

namespace App\Filament\Resources\Catalog\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class ImagesRelationManager extends RelationManager
{
    protected static string $relationship = 'images';

    protected static ?string $title = 'Зображення';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Placeholder::make('current_image')
                    ->label('Поточне фото')
                    ->content(fn ($record) => $record && $record->image 
                        ? new \Illuminate\Support\HtmlString("
                                                                <a href='/catalog/product/{$record->id}' title='Переглянути сторінку товара'>
                                                                    <img src='/storage/{$record->image}' id='preview{$record->id}' style='max-height: 200px; border-radius: 8px;'>
                                                                </a><br>
                                                                <input type='file' name='import-main-img' data-preview='preview{$record->id}' data-model-id='{$record->id}' data-path='product-images' data-model='Catalog/Product' data-col='main_image'>
                                                            ")
                        : 'Фото відсутнє'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('image')
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Фото')
                    ->size(75)
                    ->getStateUsing(fn ($record) => asset('/storage/' . $record->image)),
                    
                Tables\Columns\TextColumn::make('image')
                    ->label('Шлях')
                    ->limit(50),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Додати зображення'),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}