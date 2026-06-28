<?php

namespace App\Filament\Resources\Page;

use App\Filament\Resources\Page\PageResource\Pages;
use App\Models\Page\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PageResource extends Resource
{
    protected static ?string $model = Page::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Сторінки';

    protected static ?string $label = 'Сторінка';

    protected static ?string $pluralLabel = 'Сторінки';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Основне')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Назва')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('type')
                    ->label('Тип')
                    ->options(Page::getTypes())
                    ->required(),

                Forms\Components\TextInput::make('title')
                    ->label('Заголовок')
                    ->maxLength(255),

                Forms\Components\Toggle::make('is_published')
                    ->label('Опубліковано')
                    ->default(false),

                Forms\Components\Toggle::make('out_slug')
                    ->label('Без slug')
                    ->default(false),

                Forms\Components\TextInput::make('slug')
                    ->label('Slug')
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
            ])->columns(2),

            Forms\Components\Section::make('Контент')->schema([
                Forms\Components\Section::make('Зображення')
                            ->schema([
                                Forms\Components\Placeholder::make('current_image')
                                    ->label('Поточне фото')
                                    ->content(fn ($record) => $record && $record->image 
                                        ? new \Illuminate\Support\HtmlString("
                                                                                
                                                                                <img src='/storage/{$record->image}' id='preview{$record->id}' style='max-height: 200px; border-radius: 8px;'>
                                                                                <br>
                                                                                <input type='file' name='import-main-img' data-preview='preview{$record->id}' data-model-id='{$record->id}' data-path='page_images' data-model='Page/Page' data-col='image'>
                                                                            ")
                                        : 'Фото відсутнє'),
                            ]),

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

                Tables\Columns\TextColumn::make('type')
                    ->label('Тип')
                    ->formatStateUsing(fn ($state) => Page::getTypes()[$state] ?? $state)
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->label('Опубліковано')
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Оновлено')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Тип')
                    ->options(Page::getTypes()),

                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Опубліковано'),
            ])
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
            'index' => Pages\ListPages::route('/'),
            'create' => Pages\CreatePage::route('/create'),
            'edit' => Pages\EditPage::route('/{record}/edit'),
        ];
    }
}
