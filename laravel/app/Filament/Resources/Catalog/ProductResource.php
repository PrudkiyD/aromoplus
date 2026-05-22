<?php

namespace App\Filament\Resources\Catalog;

use App\Filament\Resources\Catalog\ProductResource\RelationManagers\DiscountsRelationManager;
use App\Filament\Resources\Catalog\ProductResource\Pages;
use App\Models\Catalog\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\ImageColumn;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $navigationGroup = 'Каталог товарів';
    protected static ?string $modelLabel = 'Товари';
    protected static ?string $pluralModelLabel = 'Товари';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Основна інформація')
                            ->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\RichEditor::make('description')
                                    ->columnSpanFull(),
                            ]),

                        Forms\Components\Section::make('Ціни та склад')
                            ->schema([
                                Forms\Components\TextInput::make('internal_sku')->label('АПН'),

                                Forms\Components\TextInput::make('manufacturer_sku')->label('Артикул'),

                                Forms\Components\TextInput::make('price')
                                    ->label('Ціна')
                                    ->numeric()
                                    ->prefix('₴')
                                    ->required(),
                                
                                Forms\Components\TextInput::make('quantity')
                                    ->label('Кількість')
                                    ->numeric()
                                    ->default(0),

                                
                            ])->columns(2),
                        
                        Forms\Components\Section::make('Пошук')
                            ->schema([
                                Forms\Components\Textarea::make('search_words')
                                    ->rows(3)
                                    ->required(),
                            ])->columns(1),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Статус та Категорії')
                            ->schema([
                                Forms\Components\Toggle::make('is_published')
                                    ->label('Опубліковано')
                                    ->default(true),

                                Forms\Components\Select::make('availability')
                                    ->options([
                                        Product::AVAILABILITY_IN_STOCK => 'В наявності',
                                        Product::AVAILABILITY_OUT_OF_STOCK => 'Немає в наявності',
                                        Product::AVAILABILITY_ON_ORDER => 'Під замовлення',
                                    ])
                                ->required()
                                ->native(false),

                                Forms\Components\Select::make('categories')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable(),
                            ]),

                        Forms\Components\Section::make('Зображення')
                            ->schema([
                                /*
                                Forms\Components\FileUpload::make('main_image')
                                    ->label('Змінити')
                                    ->image()
                                    ->saveUploadedFileUsing(function ($file) {
                                        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
                                        $targetFolder = '/var/www/aromoplus1/data/www/aromoplus.com.ua/storage/product-images/';

                                        // 1. Перевіряємо, чи існує папка, якщо ні — створюємо її
                                        if (!file_exists($targetFolder)) {
                                            mkdir($targetFolder, 0775, true);
                                        }

                                        // 2. Використовуємо getRealPath(), щоб отримати шлях до тимчасового файлу
                                        $tempPath = $file->getRealPath();
                                        $targetPath = $targetFolder . $filename;

                                        // 3. Копіюємо файл замість move, щоб уникнути конфліктів прав між директоріями
                                        if (copy($tempPath, $targetPath)) {
                                            chmod($targetPath, 0664); // Надаємо права на читання файлу
                                            return 'product-images/' . $filename;
                                        }

                                        throw new \Exception("Не вдалося скопіювати файл у $targetPath");
                                    }),
                                */
                                Forms\Components\Placeholder::make('current_image')
                                    ->label('Поточне фото')
                                    ->content(fn ($record) => $record && $record->main_image 
                                        ? new \Illuminate\Support\HtmlString("<img src='/storage/{$record->main_image}' style='max-height: 200px; border-radius: 8px;'><a href='/catalog/product/{$record->id}'>Переглянути сторінку товара</a>")
                                        : 'Фото відсутнє'),
                            ]),

                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([25]) 
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\IconColumn::make('is_published')
                    ->label('👁')
                    ->boolean(),

                ImageColumn::make('main_image')
                    ->label('Фото')
                    ->size(75)
                    ->getStateUsing(fn ($record) => asset('/storage/' . $record->main_image)),                

                Tables\Columns\TextColumn::make('name')
                    ->label('Назва')
                    ->searchable(query: function ($query, $search) {
                        $query->where('name', 'like', "%{$search}%")
                            ->orWhere('search_words', 'like', "%{$search}%");
                    })
                    ->sortable()
                    ->lineClamp(2)
                    ->wrap()
                    ->tooltip(fn ($state) => $state),

                Tables\Columns\TextColumn::make('manufacturer_sku')
                    ->label('Артикул')
                    ->searchable()
                    ->sortable()
                    ->lineClamp(2)
                    ->wrap()
                    ->copyable()
                    ->copyMessage('Скопійовано')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('internal_sku')
                    ->label('АПН')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Скопійовано')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('quantity') 
                    ->label('Кількість')
                    ->numeric()
                    ->sortable(),
                    
                Tables\Columns\SelectColumn::make('availability')
                    ->label('Наявність')
                    ->options([
                        Product::AVAILABILITY_IN_STOCK => 'В наявності',
                        Product::AVAILABILITY_ON_ORDER => 'Під замовлення',
                        Product::AVAILABILITY_OUT_OF_STOCK => 'Немає в наявності',
                    ])
                    ,

                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна')
                    ->money('UAH')
                    ->sortable(),    
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_published')
                    ->label('Опубліковано')
                    ->placeholder('Всі товари')
                    ->trueLabel('Опубліковані')
                    ->falseLabel('Не опубліковані'),

                Tables\Filters\SelectFilter::make('availability')
                    ->label('Наявність')
                    ->options([
                        Product::AVAILABILITY_IN_STOCK => 'В наявності',
                        Product::AVAILABILITY_OUT_OF_STOCK => 'Немає',
                        Product::AVAILABILITY_ON_ORDER => 'Під замовлення',
                    ]),

                Tables\Filters\SelectFilter::make('categories')
                    ->label('Категорія')
                    ->relationship('categories', 'name')
                    ->preload()
                    ->multiple()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('is_published', 'desc')
            ->orderBy('availability', 'asc') 
            ->orderBy('quantity', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            DiscountsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}