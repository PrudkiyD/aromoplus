<?php

namespace App\Filament\Resources\Page\SliderResource\Pages;

use App\Filament\Resources\Page\SliderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSlider extends EditRecord
{
    protected static string $resource = SliderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
