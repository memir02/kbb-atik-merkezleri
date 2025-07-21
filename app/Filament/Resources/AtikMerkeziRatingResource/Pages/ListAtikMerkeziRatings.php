<?php

namespace App\Filament\Resources\AtikMerkeziRatingResource\Pages;

use App\Filament\Resources\AtikMerkeziRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAtikMerkeziRatings extends ListRecords
{
    protected static string $resource = AtikMerkeziRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
