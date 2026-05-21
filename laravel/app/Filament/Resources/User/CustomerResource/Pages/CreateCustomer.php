<?php

namespace App\Filament\Resources\User\CustomerResource\Pages;

use App\Filament\Resources\User\CustomerResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;
}
