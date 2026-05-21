<?php

namespace App\Filament\Resources\Remont\BlanckResource\Pages;

use App\Filament\Resources\Remont\BlanckResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlanck extends EditRecord
{
    protected static string $resource = BlanckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
