<?php

namespace App\Filament\Resources\Page;

use App\Filament\Resources\Page\SliderResource\Pages;
use App\Models\Page\Slider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SliderResource extends Resource
{
    protected static ?string $model = Slider::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'Сторінки';

    protected static ?string $label = 'Слайд';

    protected static ?string $pluralLabel = 'Слайдер';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Основне')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Назва')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('url')
                    ->label('URL посилання')
                    ->url()
                    ->maxLength(255),
            ])->columns(2),

            Forms\Components\Section::make('Зображення')->schema([
                Forms\Components\Section::make('Зображення')
                            ->schema([
                                Forms\Components\Placeholder::make('current_image')
                                    ->label('Поточне фото')
                                    ->content(fn ($record) => $record && $record->image 
                                        ? new \Illuminate\Support\HtmlString("
                                                                                
                                                                                <img src='/storage/{$record->image}' id='preview{$record->id}' style='max-height: 200px; border-radius: 8px;'>
                                                                                <br>
                                                                                <input type='file' name='import-main-img' data-preview='preview{$record->id}' data-model-id='{$record->id}' data-path='slider_images' data-model='Page/Slider' data-col='image'>
                                                                            ")
                                        : 'Фото відсутнє'),
                            ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('Фото')
                    ->size(75)
                    ->getStateUsing(fn ($record) => asset('/storage/' . $record->image)),

                Tables\Columns\TextColumn::make('name')
                    ->label('Назва')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('url')
                    ->label('URL')
                    ->limit(40),

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
            'index' => Pages\ListSliders::route('/'),
            'create' => Pages\CreateSlider::route('/create'),
            'edit' => Pages\EditSlider::route('/{record}/edit'),
        ];
    }
}
