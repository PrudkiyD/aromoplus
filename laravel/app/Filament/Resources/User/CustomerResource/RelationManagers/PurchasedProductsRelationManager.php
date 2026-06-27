<?php

namespace App\Filament\Resources\User\CustomerResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PurchasedProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Куплені товари';

    /**
     * Перевизначаємо запит: беремо всі ProductItem через замовлення,
     * групуємо по product_id щоб не було дублікатів.
     */
    protected function getTableQuery(): Builder
    {
        $customerId = $this->getOwnerRecord()->getKey();

        return \App\Models\Catalog\Product::query()
            ->whereHas('productItems', function (Builder $q) use ($customerId) {
                $q->whereHas('order', function (Builder $q2) use ($customerId) {
                    $q2->where('customer_id', $customerId);
                });
            })
            ->withCount([
                'productItems as times_ordered' => function (Builder $q) use ($customerId) {
                    $q->whereHas('order', fn (Builder $q2) => $q2->where('customer_id', $customerId));
                },
            ])
            ->withSum([
                'productItems as total_quantity' => function (Builder $q) use ($customerId) {
                    $q->whereHas('order', fn (Builder $q2) => $q2->where('customer_id', $customerId));
                },
            ], 'quantity');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\ImageColumn::make('main_image')
                    ->label('')
                    ->width(48)
                    ->height(48)
                    ->defaultImageUrl(fn () => null),

                Tables\Columns\TextColumn::make('name')
                    ->label('Назва товару')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('internal_sku')
                    ->label('SKU')
                    ->searchable()
                    ->copyable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Поточна ціна')
                    ->money('UAH')
                    ->sortable(),

                Tables\Columns\TextColumn::make('times_ordered')
                    ->label('Замовлень')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Куплено (шт)')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\IconColumn::make('availability')
                    ->label('В наявності')
                    ->boolean()
                    ->getStateUsing(fn ($record) => $record->availability === \App\Models\Catalog\Product::AVAILABILITY_IN_STOCK),
            ])
            ->defaultSort('times_ordered', 'desc')
            ->filters([])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('view_product')
                    ->label('Відкрити товар')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record): string => route('filament.admin.resources.catalog.products.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    // Вимикаємо стандартний заголовок "Create" — тут тільки перегляд
    public function isReadOnly(): bool
    {
        return true;
    }
}