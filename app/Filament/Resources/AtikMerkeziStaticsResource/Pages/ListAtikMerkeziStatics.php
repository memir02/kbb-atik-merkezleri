<?php

namespace App\Filament\Resources\AtikMerkeziStaticsResource\Pages;

use App\Filament\Resources\AtikMerkeziStaticsResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAtikMerkeziStatics extends ListRecords
{
    protected static string $resource = AtikMerkeziStaticsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // İstatistik sayfası olduğu için create action'ı dahil etmiyoruz
        ];
    }

    public function getTitle(): string
    {
        return 'Atık Merkezi İstatistikleri';
    }
} 