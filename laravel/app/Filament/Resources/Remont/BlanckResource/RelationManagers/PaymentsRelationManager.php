<?php
namespace App\Filament\Resources\Remont\BlanckResource\RelationManagers;

use App\Models\Remont\PayBlanck;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments'; // назва методу в моделі Blanck

    protected static ?string $title = 'Платежі за замовленням';
    protected static ?string $modelLabel = 'платіж';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('type')
                    ->label('Вид послуги')
                    ->options(PayBlanck::TYPE_CHOICES)
                    ->required(),
                
                Forms\Components\Select::make('status')
                    ->label('Статус оплати')
                    ->options(PayBlanck::STATUS_CHOICES)
                    ->default('N')
                    ->required(),

                Forms\Components\Select::make('pay')
                    ->label('Спосіб оплати')
                    ->options(PayBlanck::PAY_CHOICES),

                Forms\Components\TextInput::make('total')
                    ->label('Сума')
                    ->numeric()
                    ->prefix('₴')
                    ->required(),

                Forms\Components\DatePicker::make('date_get')
                    ->label('Дата фактичного отримання'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Послуга')
                    ->formatStateUsing(fn ($state) => PayBlanck::TYPE_CHOICES[$state] ?? $state),
                
                Tables\Columns\TextColumn::make('total')
                    ->label('Сума')
                    ->money('UAH'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Статус')
                    ->formatStateUsing(fn ($state) => PayBlanck::STATUS_CHOICES[$state] ?? $state)
                    ->colors([
                        'danger' => 'N',
                        'success' => 'O',
                        'warning' => 'D',
                        'secondary' => 'S',
                    ]),

                Tables\Columns\TextColumn::make('date_get')
                    ->label('Дата отримання')
                    ->date('d.m.Y'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('view_product')
                    ->label('Переглянути')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn (PayBlanck $record): string => "/admin/print-blanck/{$record->blanck_id}/{$record->id}")
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}