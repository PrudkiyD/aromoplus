<?php

namespace App\Filament\Resources\Catalog\ProductResource\RelationManagers;

use App\Models\Order\Order;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'productItems';

    protected static ?string $title = 'Замовлення';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                Tables\Columns\TextColumn::make('order.number')
                    ->label('Номер замовлення')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('order.customer.name')
                    ->label('Клієнт')
                    ->searchable()
                    ->default('—'),

                Tables\Columns\TextColumn::make('order.customer.phone')
                    ->label('Телефон')
                    ->default('—'),

                Tables\Columns\BadgeColumn::make('order.status')
                    ->label('Статус')
                    ->colors([
                        'gray'    => Order::STATUS_NEW,
                        'warning' => Order::STATUS_PROCESSING,
                        'info'    => Order::STATUS_READY,
                        'primary' => Order::STATUS_SHIPPED,
                        'danger'  => Order::STATUS_CANCELED,
                        'success' => Order::STATUS_SUCCESSFUL,
                    ])
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Order::STATUS_NEW        => 'Нове',
                        Order::STATUS_PROCESSING => 'В обробці',
                        Order::STATUS_READY      => 'Готове',
                        Order::STATUS_SHIPPED    => 'Відправлено',
                        Order::STATUS_CANCELED   => 'Скасовано',
                        Order::STATUS_SUCCESSFUL => 'Виконано',
                        default                  => $state,
                    }),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Кількість')
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Ціна продажу')
                    ->money('UAH'),

                Tables\Columns\TextColumn::make('order.created_at')
                    ->label('Дата замовлення')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('order.created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('order.status')
                    ->label('Статус')
                    ->relationship('order', 'status')
                    ->options([
                        Order::STATUS_NEW        => 'Нове',
                        Order::STATUS_PROCESSING => 'В обробці',
                        Order::STATUS_READY      => 'Готове',
                        Order::STATUS_SHIPPED    => 'Відправлено',
                        Order::STATUS_CANCELED   => 'Скасовано',
                        Order::STATUS_SUCCESSFUL => 'Виконано',
                    ]),
            ])
            ->headerActions([])
            ->actions([
                Tables\Actions\Action::make('open_order')
                    ->label('Відкрити замовлення')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn ($record): string => route('filament.admin.resources.order.orders.edit', $record->order_id))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}