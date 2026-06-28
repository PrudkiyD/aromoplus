<?php

namespace App\Filament\Resources\Page\ElementResource\Pages;

use App\Filament\Resources\Page\ElementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditElement extends EditRecord
{
    protected static string $resource = ElementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
