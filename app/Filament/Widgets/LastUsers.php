<?php

namespace App\Filament\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use App\Models\User;

class LastUsers extends BaseWidget
{
    protected static ?int $sort = 2; // Dashboard'da sıralama için

    protected static ?string $heading = 'Son Kullanıcılar'; // ← Başlık

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return User::query()->latest()->limit(5);
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')->label('Ad Soyad'),
            Tables\Columns\TextColumn::make('email')->label('E-posta'),
            Tables\Columns\TextColumn::make('created_at')->label('Kayıt Tarihi')->dateTime('d.m.Y H:i'),
        ];
    }
}
