<?php
namespace App\Filament\Resources\Catalog\ProductResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Model;

class DiscountsRelationManager extends RelationManager
{
    protected static string $relationship = 'discounts';

    // Заголовок для всієї секції
    protected static ?string $title = 'Знижки';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('price_list_id')
                    ->label('Група цін / Прайс-лист')
                    ->relationship('priceList', 'name')
                    ->searchable(['name', 'phone'])
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->phone})")
                    ->required()
                    ->columnSpanFull(),

                TextInput::make('quantity')
                    ->label('Мінімальна кількість')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->required(),

                TextInput::make('discount')
                    ->label('Знижка')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->required(),

                Toggle::make('vat_included')
                    ->label('ПДВ включено')
                    ->default(false)
                    ->inline(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('quantity')
            ->columns([
                TextColumn::make('priceList.name')
                    ->label('Група')
                    ->description(fn ($record) => $record->priceList?->phone)
                    ->sortable(),

                TextColumn::make('quantity')
                    ->label('К-сть від')
                    ->suffix(' шт.')
                    ->sortable()
                    ->alignCenter(),

                TextColumn::make('discount')
                    ->label('Знижка')
                    ->numeric(decimalPlaces: 2)
                    ->badge()
                    ->color('success')
                    ->sortable()
                    ->alignCenter(),
                
                TextColumn::make('calculated_price')
                    ->label('Ціна зі знижкою')
                    ->money('UAH') // або інша валюта
                    ->state(function (Model $record): float {
                        // Отримуємо базову ціну з головної моделі Product
                        $basePrice = $this->getOwnerRecord()->price ?? 0;
                        
                        // 1. Рахуємо ціну після знижки відсотка
                        // Формула: Ціна * (1 - знижка/100)
                        $priceAfterDiscount = $basePrice * $record->discount;

                        // 2. Якщо вказано ПДВ, множимо на 1.2
                        if ($record->vat_included) {
                            $priceAfterDiscount = $priceAfterDiscount * 1.2;
                        }

                        $priceAfterDiscount = ceil($priceAfterDiscount * 10) / 10;

                        return $priceAfterDiscount;
                    })
                    ->description(fn($record) => $record->vat_included ? 'з ПДВ (x1.2)' : 'без ПДВ')
                    ->color('primary')
                    ->weight('bold'),

            ])
            ->defaultSort('quantity', 'asc') // Сортуємо за кількістю за замовчуванням
            ->filters([
                /*
                SelectFilter::make('price_list_id')
                    ->label('Фільтр за групою')
                    ->relationship('priceList', 'name')
                    ->searchable()
                    ->preload(),
                */
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Додати знижку'),
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
}