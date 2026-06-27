<?php

namespace App\Filament\Resources\User;

use App\Filament\Resources\User\CustomerResource\Pages;
use App\Filament\Resources\User\CustomerResource\RelationManagers\OrdersRelationManager;
use App\Filament\Resources\User\CustomerResource\RelationManagers\PurchasedProductsRelationManager;
use App\Models\User\Customer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationLabel = 'Клієнти';
    protected static ?string $modelLabel = 'Клієнт';
    protected static ?string $pluralModelLabel = 'Клієнти';
    protected static ?string $navigationGroup = 'Клієнти та Користувачі';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Основна інформація')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('ПІБ')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Телефон')
                            ->tel()
                            ->required(),

                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('address')
                            ->label('Адреса')
                            ->maxLength(500),

                        Forms\Components\Textarea::make('notes')
                            ->label('Примітки')
                            ->columnSpanFull()
                            ->rows(3),
                            
                        Forms\Components\Placeholder::make('orders_count')
                            ->label('Кількість замовлень')
                            ->content(fn (Customer $record): string => $record->orders()->count() . ' шт.')
                            ->visibleOn('edit'),

                        Forms\Components\Placeholder::make('orders_sum')
                            ->label('Загальна сума')
                            ->content(fn (Customer $record): string => '₴ ' . number_format($record->orders()->sum('total'), 2, '.', ' '))
                            ->visibleOn('edit'),

                        Forms\Components\Hidden::make('user_id')
                            ->default(auth()->id()),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('ПІБ')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Телефон')
                    ->searchable(),

                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Останнє оновлення')
                    ->dateTime('d.m.Y H:i')
                    ->sortable(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('Замовлень')
                    ->counts('orders')
                    ->sortable()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('orders_sum_total')
                    ->label('Загальна сума')
                    ->money('UAH')
                    ->sum('orders', 'total')
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([])
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
        return [
            OrdersRelationManager::class,
            PurchasedProductsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}