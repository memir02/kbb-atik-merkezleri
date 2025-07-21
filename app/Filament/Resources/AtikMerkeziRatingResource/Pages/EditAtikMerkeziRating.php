<?php

namespace App\Filament\Resources\AtikMerkeziRatingResource\Pages;

use App\Filament\Resources\AtikMerkeziRatingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAtikMerkeziRating extends EditRecord
{
    protected static string $resource = AtikMerkeziRatingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
