<?php

namespace App\Filament\Resources\AtikMerkeziResource\Pages;

use App\Filament\Resources\AtikMerkeziResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAtikMerkezis extends ListRecords
{
    protected static string $resource = AtikMerkeziResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
