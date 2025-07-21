<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Card;
use App\Models\User;
use App\Models\AtikMerkezi;
use App\Models\AtikMerkeziRating;
use App\Models\AtikMerkeziFavorite;

class StatsOverview extends BaseWidget
{
    protected function getCards(): array
    {
        return [
            Card::make('Toplam Kullanıcı', User::count()),
            Card::make('Toplam Atık Merkezi', AtikMerkezi::count()),
            Card::make('Toplam Puan', AtikMerkeziRating::count()),
            Card::make('Toplam Favori', AtikMerkeziFavorite::count()),
            Card::make('Toplam Yorum', AtikMerkeziRating::count()),
        ];
    }
}
