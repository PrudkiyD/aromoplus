<?php

namespace App\Filament\Resources\Catalog\ProductResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CustomersRelationManager extends RelationManager
{
    protected static string $relationship = 'productItems';

    protected static ?string $title = 'Клієнти';

    protected function getTableQuery(): Builder
    {
        $productId = $this->getOwnerRecord()->getKey();

        return \App\Models\User\Customer::query()
            ->whereHas('orders.productItems', function (Builder $q) use ($productId) {
                $q->where('product_id', $productId);
            })
            ->withCount([
                'orders as orders_count' => function (Builder $q) use ($productId) {
                    $q->whereHas('productItems', fn (Builder $q2) => $q2->where('product_id', $productId));
                },
            ])
            ->withSum([
                'orders as total_spent' => function (Builder $q) use ($productId) {
                    $q->whereHas('productItems', fn (Builder $q2) => $q2->where('product_id', $productId));
                },
            ], 'total');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Клієнт')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Скопійовано'),
            ])
            ->defaultSort('orders_count', 'desc')
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('open_customer')
                    ->label('Відкрити клієнта')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record): string => route('filament.admin.resources.user.customers.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}