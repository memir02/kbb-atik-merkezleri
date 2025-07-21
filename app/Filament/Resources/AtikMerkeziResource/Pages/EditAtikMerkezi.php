<?php

namespace App\Filament\Resources\AtikMerkeziResource\Pages;

use App\Filament\Resources\AtikMerkeziResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAtikMerkezi extends EditRecord
{
    protected static string $resource = AtikMerkeziResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
