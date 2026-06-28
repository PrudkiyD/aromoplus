<?php

namespace App\Filament\Resources\Page\ElementResource\Pages;

use App\Filament\Resources\Page\ElementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListElements extends ListRecords
{
    protected static string $resource = ElementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
