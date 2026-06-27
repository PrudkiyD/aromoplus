<?php

namespace App\Filament\Resources\User\CustomerResource\RelationManagers;

use App\Models\Order\Order;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';

    protected static ?string $title = 'Замовлення';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('number')
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('Номер')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
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

                Tables\Columns\TextColumn::make('total')
                    ->label('Сума')
                    ->money('UAH')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
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
                Tables\Actions\Action::make('view')
                    ->label('Відкрити')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Order $record): string => route('filament.admin.resources.order.orders.edit', $record))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([]);
    }
}