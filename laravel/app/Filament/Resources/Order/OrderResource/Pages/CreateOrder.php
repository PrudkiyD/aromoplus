<?php

namespace App\Filament\Resources\Order\OrderResource\Pages;

use App\Filament\Resources\Order\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
