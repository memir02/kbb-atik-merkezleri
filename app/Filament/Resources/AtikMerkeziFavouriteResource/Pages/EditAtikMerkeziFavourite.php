<?php

namespace App\Filament\Resources\AtikMerkeziFavoriteResource\Pages;

use App\Filament\Resources\AtikMerkeziFavoriteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAtikMerkeziFavorite extends EditRecord
{
    protected static string $resource = AtikMerkeziFavoriteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
