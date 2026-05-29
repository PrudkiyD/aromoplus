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
                                    ->label('Назва')
                                    ->required()
                                    ->maxLength(255),
                                
                                Forms\Components\RichEditor::make('description')
                                    ->label('Опис')
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
                                
                                Forms\Components\TextInput::make('one_c_path')
                                    ->label('Шлях в 1с')
                                    ->columnSpanFull(),

                                
                            ])->columns(2),
                        
                        Forms\Components\Section::make('Пошук')
                            ->schema([
                                Forms\Components\Textarea::make('search_words')
                                    ->rows(3)
                                    ->required(),
                            ])->columns(1),
                        Forms\Components\Section::make('Аналітика (365 днів)')
                        ->schema([
                            Forms\Components\Placeholder::make('popularity_status')
                                ->label('Популярність')
                                ->content(function (Product $record): \Illuminate\Support\HtmlString {
                                    $score = $record->popularity ?? 0.0;
                                    
                                    // Визначаємо текстовий статус
                                    $status = 'Низька активність';
                                    $color = '#9ca3af'; // сірий
                                    if ($score >= 4.0) {
                                        $status = '🔥 Висока активність';
                                        $color = '#ef4444'; // червоний
                                    } elseif ($score >= 2.5) {
                                        $status = '📈 Середня активність';
                                        $color = '#f59e0b'; // помаранчевий
                                    }

                                    // Рендеримо красивий HTML-індикатор прогресу всередині картки Filament
                                    return new \Illuminate\Support\HtmlString("
                                        <div style='display: flex; align-items: center; gap: 12px; margin-top: 4px;'>
                                            <span style='font-size: 1.5rem; font-weight: bold; color: ${color};'>${score} / 5.0</span>
                                            <span style='font-size: 0.9rem; background: ${color}22; color: ${color}; padding: 2px 8px; border-radius: 6px; font-weight: 600;'>${status}</span>
                                        </div>
                                        <div style='width: 100%; background: #e5e7eb; height: 8px; border-radius: 4px; margin-top: 8px; overflow: hidden;'>
                                            <div style='width: " . ($score * 20) . "%; background: ${color}; height: 100%; transition: width 0.5s ease;'></div>
                                        </div>
                                    ");
                                }),

                            Forms\Components\Placeholder::make('abc_xyz_class')
                                ->label('ABC / XYZ Клас')
                                ->content(function ($record): \Illuminate\Support\HtmlString {
                                    if (!$record || !$record->abc_class) {
                                        return new \Illuminate\Support\HtmlString('<span class="text-gray-500">Немає даних</span>');
                                    }
                                    
                                    $class = $record->abc_class . $record->xyz_class;
                                    
                                    // Задаємо колір плашки залежно від літери А, В чи С
                                    $colorClass = match ($record->abc_class) {
                                        'A' => 'bg-success-500/10 text-success-700 dark:text-success-400',
                                        'B' => 'bg-warning-500/10 text-warning-700 dark:text-warning-400',
                                        default => 'bg-gray-500/10 text-gray-700 dark:text-gray-400',
                                    };

                                    return new \Illuminate\Support\HtmlString("
                                        <div class='flex items-center gap-2'>
                                            <span class='px-2.5 py-1 text-sm font-bold rounded-md {$colorClass}'>
                                                {$class}
                                            </span>
                                        </div>
                                    ");
                                }),
                            // 2. Страховий запас
                            Forms\Components\Placeholder::make('display_safety_stock')
                                ->label('Страховий запас')
                                ->content(function ($record): \Illuminate\Support\HtmlString {
                                    if (!$record) return new \Illuminate\Support\HtmlString('0 шт.');
                                    return new \Illuminate\Support\HtmlString("<strong>{$record->safety_stock}</strong> шт.");
                                }),

                            Forms\Components\Placeholder::make('purchase_status')
                                ->label('Статус складу')
                                ->content(function ($record): \Illuminate\Support\HtmlString {
                                    if (!$record) return new \Illuminate\Support\HtmlString('-');
                                    
                                    // Логіка визначення дефіциту
                                    if ($record->quantity <= $record->safety_stock) {
                                        return new \Illuminate\Support\HtmlString('<span class="text-danger-600 dark:text-danger-400 font-bold">🔴 КРИТИЧНО (Час замовляти)</span>');
                                    }
                                    
                                    if ($record->quantity <= ($record->safety_stock * 1.2)) {
                                        return new \Illuminate\Support\HtmlString('<span class="text-warning-600 dark:text-warning-400 font-bold">🟡 УВАГА (Запас закінчується)</span>');
                                    }
                                    
                                    return new \Illuminate\Support\HtmlString('<span class="text-success-600 dark:text-success-400 font-bold">🟢 ДОСТАТНЬО (Запас в нормі)</span>');
                                }),
                                        
                        ])
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Зображення')
                            ->schema([
                                Forms\Components\Placeholder::make('current_image')
                                    ->label('Поточне фото')
                                    ->content(fn ($record) => $record && $record->main_image 
                                        ? new \Illuminate\Support\HtmlString("<img src='/storage/{$record->main_image}' style='max-height: 200px; border-radius: 8px;'><a href='/catalog/product/{$record->id}'>Переглянути сторінку товара</a>")
                                        : 'Фото відсутнє'),
                            ]),
                            
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

                Tables\Columns\TextColumn::make('popularity')
                    ->label('Поп.')
                    ->numeric(1) // Округлює до 1 знака після коми (наприклад, 4.3)
                    ->badge()    // Робить поле у вигляді красивого бейджа
                    ->icon(fn ($state) => $state >= 4.0 ? 'heroicon-m-fire' : 'heroicon-m-chart-bar')
                    ->color(fn ($state): string => match (true) {
                        $state >= 4.0 => 'danger',  // Червоний бейдж для топу
                        $state >= 2.5 => 'warning', // Помаранчевий для середнього попиту
                        default => 'gray',          // Сірий для низької активності
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('abc_class')
                    ->label('ABC')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                Tables\Columns\TextColumn::make('xyz_class')
                    ->label('XYZ')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

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
            ->defaultSort('popularity', 'desc')
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
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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