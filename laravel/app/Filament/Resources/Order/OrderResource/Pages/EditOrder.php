<?php

namespace App\Filament\Resources\Order\OrderResource\Pages;

use App\Filament\Resources\Order\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            /*
            Action::make('sendQuote')
            ->label('Створити ттн')
            ->icon('heroicon-o-paper-airplane')
            ->color('success')
            ->form([
                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required(),
                TextInput::make('price')
                    ->label('Ціна')
                    ->numeric()
                    ->required(),
                Select::make('currency')
                    ->options(['UAH' => 'UAH', 'USD' => 'USD']),
            ])
            ->action(function (array $data): void {
                \Mail::to($data['email'])->send(...);
            })
            ->modalHeading('Створити ттн')
            ->modalSubmitActionLabel('Створити ттн'),
            */
            Actions\DeleteAction::make(),
        ];
    }
}
