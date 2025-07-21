<?php

namespace App\Filament\Widgets;

use Filament\Widgets\LineChartWidget;
use App\Models\User;

class UserChart extends LineChartWidget
{
    protected static ?string $heading = 'Aylık Kullanıcı Kayıtları';

    protected function getData(): array
    {
        $data = User::query()
            ->selectRaw('MONTH(created_at) as ay, COUNT(*) as toplam')
            ->whereYear('created_at', now()->year)
            ->groupBy('ay')
            ->orderBy('ay')
            ->pluck('toplam', 'ay')
            ->all();

        return [
            'datasets' => [
                [
                    'label' => 'Kullanıcı',
                    'data' => array_values($data),
                ],
            ],
            'labels' => array_map(fn($ay) => $ay . '. Ay', array_keys($data)),
        ];
    }
}
