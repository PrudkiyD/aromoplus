<?php

namespace App\Filament\Resources\Catalog\ProductResource\Pages;

use App\Filament\Resources\Catalog\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;
}
