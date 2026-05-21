<?php

namespace App\Filament\Resources\Remont\BlanckResource\Pages;

use App\Filament\Resources\Remont\BlanckResource;
use App\Models\Remont\Blanck;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use ZipArchive;

class ListBlancks extends ListRecords
{
    protected static string $resource = BlanckResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}