<?php

namespace App\Filament\Resources\Page\PageResource\Pages;

use App\Filament\Resources\Page\PageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePage extends CreateRecord
{
    protected static string $resource = PageResource::class;
}
