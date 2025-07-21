<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
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
        // En çok puan alan merkezi bul
        $topRated = \App\Models\AtikMerkeziRating::select('atik_merkezi_id', DB::raw('AVG(rating) as avg_rating'))
            ->groupBy('atik_merkezi_id')
            ->orderByDesc('avg_rating')
            ->first();

        $topMerkez = null;
        $topPuan = null;
        if ($topRated) {
            $merkez = \App\Models\AtikMerkezi::find($topRated->atik_merkezi_id);
            $topMerkez = $merkez ? ($merkez->ad ?? $merkez->isim ?? $merkez->title) : 'Bilinmiyor';
            $topPuan = round($topRated->avg_rating, 2);
        }

        return [
            Card::make('Toplam Kullanıcı', User::count()),
            Card::make('Toplam Atık Merkezi', AtikMerkezi::count()),
            Card::make('Toplam Puan', AtikMerkeziRating::count()),
            Card::make('Toplam Favori', AtikMerkeziFavorite::count()),
            Card::make('Toplam Yorum', AtikMerkeziRating::whereNotNull('comment')->where('comment', '!=', '')->count()),
            Card::make('En Yüksek Puanlı Merkez', $topMerkez ? ($topMerkez . ' (' . $topPuan . ')') : 'Yok'),
        ];
    }
}
