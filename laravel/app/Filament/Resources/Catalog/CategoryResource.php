<?php

namespace App\Filament\Resources\Catalog;

use App\Filament\Resources\Catalog\CategoryResource\Pages;
use App\Models\Catalog\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationGroup = 'Каталог товарів';
    protected static ?string $modelLabel = 'Категорії';
    protected static ?string $pluralModelLabel = 'Категорії';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основна інформація')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => 
                                $operation === 'create' ? $set('slug', Str::slug($state)) : null),

                        Forms\Components\TextInput::make('slug')
                            ->disabled() // Автогенерація працює в моделі, тут тільки для перегляду
                            ->dehydrated(false)
                            ->unique(Category::class, 'slug', ignoreRecord: true),

                        Forms\Components\TextInput::make('title'),
                        
                        Forms\Components\Select::make('parent_id')
                            ->label('Батьківська категорія')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->placeholder('Виберіть категорію'),

                        Forms\Components\Toggle::make('is_published')
                            ->label('Опубліковано')
                            ->default(true),
                    ])->columns(2),

                Forms\Components\Section::make('Медіа та опис')
                    ->schema([
                        Forms\Components\FileUpload::make('image')
                            ->image()
                            ->directory('categories'), // Папка в storage

                        Forms\Components\TextInput::make('label')
                            ->placeholder('Короткий маркер'),

                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image'),
                
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Батько')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_published')
                    ->boolean()
                    ->label('Статус'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Тільки опубліковані'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}