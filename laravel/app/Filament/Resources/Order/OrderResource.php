<?php

namespace App\Filament\Resources\Order;

use App\Filament\Resources\Order\OrderResource\RelationManagers\ProductItemRelationManager;
use App\Filament\Resources\Order\OrderResource\Pages;
use App\Models\Order\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';
    protected static ?string $navigationGroup = 'Замовлення';
    protected static ?string $modelLabel = 'Замовлення';
    protected static ?string $pluralModelLabel = 'Замовлення';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('Деталі замовлення')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Інфо.')
                            ->schema([
                                Forms\Components\TextInput::make('number')
                                    ->label('Номер замовлення')
                                    ->disabled()
                                    ->dehydrated(true),
                                
                                Forms\Components\Select::make('status')
                                    ->label('Статус')
                                    ->options([
                                        Order::STATUS_NEW => 'Новий',
                                        Order::STATUS_PROCESSING => 'В роботі',
                                        Order::STATUS_READY => 'Склад',
                                        Order::STATUS_SHIPPED => 'Відправлено',
                                        Order::STATUS_SUCCESSFUL => 'Виконано',
                                        Order::STATUS_CANCELED => 'Скасовано',
                                    ])
                                    ->required()
                                    ->native(false),
                                Forms\Components\TextInput::make('created_at')
                                    ->label('Дата створення')
                                    ->disabled()
                                    ->formatStateUsing(fn ($state) => $state ? \Illuminate\Support\Carbon::parse($state)->format('d.m.Y H:i') : '-'),    
                                Forms\Components\TextInput::make('updated_at')
                                    ->label('Дата оновлення')
                                    ->disabled()
                                    ->formatStateUsing(fn ($state) => $state ? \Illuminate\Support\Carbon::parse($state)->format('d.m.Y H:i') : '-'),
                                Forms\Components\Textarea::make('comment')
                                    ->label('Коментар клієнта'),

                                Forms\Components\TextInput::make('key')
                                    ->label('Трекер')
                                    ->disabled()
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('open_tracker')
                                            ->label('Відкрити')
                                            ->icon('heroicon-m-arrow-top-right-on-square')
                                            ->color('primary')
                                            ->url(fn ($state) => $state ? "https://aromoplus.com.ua/order/tracker/{$state}" : null)
                                            ->openUrlInNewTab() // Щоб не закривати адмінку
                                    )
                                ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Дані покупця')
                            ->schema([
                                Select::make('user_id')
                                    ->label('Користувач')
                                    ->relationship('user', 'name')
                                    ->searchable(['name', 'phone']) 
                                    ->preload() 
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->phone})"),
                                Select::make('customer_id')
                                    ->label('Покупець')
                                    ->relationship('customer', 'name')
                                    ->searchable(['name', 'phone']) 
                                    ->preload() 
                                    ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->phone})"),
                                Forms\Components\TextInput::make('last_name')->label('Прізвище')->required(),
                                Forms\Components\TextInput::make('first_name')->label('Ім’я')->required(),
                                Forms\Components\TextInput::make('middle_name')->label('По батькові'),
                                Forms\Components\TextInput::make('phone_number')->label('Телефон')->tel()->required(),
                                Forms\Components\TextInput::make('organization')->label('Організація'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Оплата')
                            ->schema([
                                Forms\Components\Select::make('payment_type')
                                    ->label('Оплата')
                                    ->options([
                                        Order::PAYMENT_COD => 'Післяплата',
                                        Order::PAYMENT_FOP => 'Реквізити ФОП',
                                        Order::PAYMENT_LLC => 'Рахунок ТОВ',
                                        Order::PAYMENT_KASA => 'Каса',
                                    ])->native(false),
                                    

                                Select::make('price_list_id')
                                    ->label('Прайс лист')
                                    ->relationship('priceList', 'name'),

                                Forms\Components\TextInput::make('total')
                                    ->label('Загальна сума')
                                    ->numeric()
                                    ->prefix('₴'),
                            ])->columns(2),

                        Forms\Components\Tabs\Tab::make('Доставка')
                            ->schema([
                                Forms\Components\Select::make('delivery')
                                    ->label('Спосіб доставки')
                                    ->options([
                                        Order::DELIVERY_NP_BRANCH => 'Нова Пошта (Відділення)',
                                        Order::DELIVERY_NP_ADDRESS => 'Нова Пошта (Адресна)',
                                        Order::DELIVERY_PICKUP => 'Самовивіз',
                                        Order::DELIVERY_UKR => 'Укрпошта',
                                    ])->native(false),

                                Forms\Components\TextInput::make('city')->label('Місто'),
                                Forms\Components\TextInput::make('department')->label('Відділення'),
                                Forms\Components\TextInput::make('street')->label('Вулиця'),
                                Forms\Components\TextInput::make('addresses')->label('Номер будинку'),
                                Forms\Components\TextInput::make('ttn')
                                    ->label('ТТН / Трек-номер')
                                    ->default('Не створено'),
                            ])->columns(2),
                    
                    
                            ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->paginated([25]) 
            ->defaultPaginationPageOption(25)
            ->columns([
                Tables\Columns\SelectColumn::make('status')
                    ->label('Статус')
                    ->options([
                        Order::STATUS_NEW => 'Новий',
                        Order::STATUS_PROCESSING => 'В роботі',
                        Order::STATUS_READY => 'Склад',
                        Order::STATUS_SHIPPED => 'Відправлено',
                        Order::STATUS_SUCCESSFUL => 'Виконано',
                        Order::STATUS_CANCELED => 'Скасовано',
                    ]),

                Tables\Columns\TextColumn::make('number')
                    ->label('№')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Скопійовано')
                    ->copyMessageDuration(1500),

                Tables\Columns\TextColumn::make('full_name')
                    ->label('Клієнт')
                    ->state(fn ($record) => "{$record->last_name} {$record->first_name}")
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search): \Illuminate\Database\Eloquent\Builder {
                            return $query->where(function ($q) use ($search) {
                                $q->where('last_name', 'like', "%{$search}%")
                                ->orWhere('first_name', 'like', "%{$search}%")
                                ->orWhere('middle_name', 'like', "%{$search}%")
                                ->orWhere('organization', 'like', "%{$search}%");
                            });
                        })
                    ->lineClamp(2)
                    ->wrap()
                    ->tooltip(fn ($record) => "{$record->last_name} {$record->first_name} {$record->middle_name} {$record->organization}"),

                Tables\Columns\TextColumn::make('phone_number')
                    ->label('Номер телефону')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Скопійовано')
                    ->copyMessageDuration(1500),
                
                Tables\Columns\TextColumn::make('ttn')
                    ->label('Експрес-накладна')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Скопійовано')
                    ->copyMessageDuration(1500),
                    
                Tables\Columns\TextColumn::make('payment_type')
                    ->label('Оплата')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        Order::PAYMENT_COD => 'Післяплата',
                        Order::PAYMENT_FOP => 'Реквізити ФОП',
                        Order::PAYMENT_LLC => 'Рахунок ТОВ',
                        Order::PAYMENT_KASA => 'Каса',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('total')
                    ->label('Сума')
                    ->money('UAH')
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Дата')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->lineClamp(2)
                    ->wrap(),
                    
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Статус')
                    ->options([
                        Order::STATUS_NEW => 'Новий',
                        Order::STATUS_PROCESSING => 'В роботі',
                        Order::STATUS_READY => 'Склад',
                        Order::STATUS_SHIPPED => 'Відправлено',
                        Order::STATUS_SUCCESSFUL => 'Виконано',
                        Order::STATUS_CANCELED => 'Скасовано',
                    ]),

                Tables\Filters\SelectFilter::make('payment_type')
                    ->label('Тип оплати')
                    ->options([
                        Order::PAYMENT_COD => 'Післяплата',
                        Order::PAYMENT_FOP => 'Реквізити ФОП',
                        Order::PAYMENT_LLC => 'Рахунок ТОВ',
                        Order::PAYMENT_KASA => 'Каса',
                    ]),

                Filter::make('created_at')
                    ->label('Період замовлення')
                    ->form([
                        DatePicker::make('from')
                            ->label('Дата з'),
                        DatePicker::make('until')
                            ->label('Дата по'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = 'З ' . \Carbon\Carbon::parse($data['from'])->format('d.m.Y');
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = 'По ' . \Carbon\Carbon::parse($data['until'])->format('d.m.Y');
                        }

                        return $indicators;
                    })
            ])

            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProductItemRelationManager::class,
        ];
    }

    

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}