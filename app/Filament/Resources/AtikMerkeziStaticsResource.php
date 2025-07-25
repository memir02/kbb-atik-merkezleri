<?php

namespace App\Filament\Resources;

use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use App\Filament\Resources\AtikMerkeziStaticsResource\Pages;
use App\Models\AtikMerkezi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AtikMerkeziStaticsResource extends Resource
{
    protected static ?string $model = AtikMerkezi::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationLabel = 'İstatistikler';
    
    protected static ?string $modelLabel = 'İstatistik';
    
    protected static ?string $pluralModelLabel = 'İstatistikler';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('📍 Merkez Bilgileri')
                    ->schema([
                        TextInput::make('title')
                            ->label('Merkez Adı')
                            ->disabled(),
                        TextInput::make('adres')
                            ->label('Adres')
                            ->disabled(),
                        Forms\Components\Textarea::make('content')
                            ->label('Açıklama')
                            ->disabled()
                            ->rows(3),
                    ])
                    ->columns(1),
                
                Forms\Components\Section::make('📊 İstatistikler')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                Forms\Components\Placeholder::make('average_rating')
                                    ->label('Ortalama Puan')
                                    ->content(fn ($record) => $record->average_rating ? 
                                        '⭐ ' . number_format($record->average_rating, 1) . '/5' : 
                                        '⚪ Henüz puanlanmamış'),
                                
                                Forms\Components\Placeholder::make('total_ratings')
                                    ->label('Toplam Değerlendirme')
                                    ->content(fn ($record) => '⭐ ' . $record->ratings()->count() . ' değerlendirme'),
                                
                                Forms\Components\Placeholder::make('total_favorites')
                                    ->label('Toplam Favori')
                                    ->content(fn ($record) => '❤️ ' . $record->favorites()->count() . ' kişi'),
                            ]),
                    ]),

                Forms\Components\Section::make('💬 Son Yorumlar')
                    ->schema([
                        Forms\Components\Placeholder::make('recent_comments')
                            ->label('')
                            ->content(function ($record) {
                                $recentRatings = $record->ratings()
                                    ->with('user')
                                    ->latest()
                                    ->limit(5)
                                    ->get();
                                
                                if ($recentRatings->isEmpty()) {
                                    return 'Henüz değerlendirme yapılmamış.';
                                }

                                $html = '<div class="space-y-3">';
                                foreach ($recentRatings as $rating) {
                                    $stars = str_repeat('⭐', $rating->rating);
                                    $date = $rating->created_at->format('d.m.Y H:i');
                                    $comment = $rating->comment ? $rating->comment : 'Yorum yapılmamış...';

                                    $html .= "
                                        <div class='p-4 border-2 rounded-lg bg-slate-800 border-slate-600 hover:bg-slate-700 transition-colors'>
                                            <div class='flex justify-between items-start mb-3'>
                                                <span class='font-bold text-sm text-blue-400 flex items-center'>
                                                    <svg class='w-4 h-4 mr-1' fill='currentColor' viewBox='0 0 20 20'>
                                                        <path fill-rule='evenodd' d='M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z' clip-rule='evenodd'></path>
                                                    </svg>
                                                    {$rating->user->name}
                                                </span>
                                                <span class='text-xs text-slate-400 italic'>
                                                    <svg class='w-3 h-3 inline mr-1' fill='currentColor' viewBox='0 0 20 20'>
                                                        <path fill-rule='evenodd' d='M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z' clip-rule='evenodd'></path>
                                                    </svg>
                                                    {$date}
                                                </span>
                                            </div>
                                            <div class='mb-2 text-lg'>{$stars}</div>
                                            <div class='bg-slate-900 p-3 rounded border-l-4 border-blue-500'>
                                                <p class='text-sm text-white font-medium'>{$comment}</p>
                                            </div>
                                        </div>
                                    ";
                                }
                                $html .= '</div>';
                                
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ]),

                Forms\Components\Section::make('❤️ Favorileyenler')
                    ->schema([
                        Forms\Components\Placeholder::make('favorites_list')
                            ->label('')
                            ->content(function ($record) {
                                $favorites = $record->favorites()
                                    ->with('user')
                                    ->latest()
                                    ->limit(10)
                                    ->get();
                                
                                if ($favorites->isEmpty()) {
                                    return 'Henüz kimse favorilerine eklememiş.';
                                }

                                $html = '<div class="grid grid-cols-2 gap-2">';
                                foreach ($favorites as $favorite) {
                                    $date = $favorite->created_at->format('d.m.Y');
                                    $html .= "
                                        <div class='flex items-center justify-between p-2 border rounded bg-red-50'>
                                            <span class='font-medium text-sm'>{$favorite->user->name}</span>
                                            <span class='text-xs text-gray-500'>{$date}</span>
                                        </div>
                                    ";
                                }
                                
                                if ($record->favorites()->count() > 10) {
                                    $remaining = $record->favorites()->count() - 10;
                                    $html .= "
                                        <div class='col-span-2 text-center p-2 text-sm text-gray-500'>
                                            ... ve {$remaining} kişi daha
                                        </div>
                                    ";
                                }
                                
                                $html .= '</div>';
                                
                                return new \Illuminate\Support\HtmlString($html);
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withAvg('ratings', 'rating')->withCount(['ratings', 'favorites']))
            ->columns([
                TextColumn::make('title')
                    ->label('Merkez Adı')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('adres')
                    ->label('Adres')
                    ->searchable()
                    ->limit(50),
                TextColumn::make('content')
                    ->label('Merkez Türü')
                    ->formatStateUsing(function ($state) {
                        $content = mb_strtoupper($state, 'UTF-8');
                        if (str_contains($content, '1. SINIF ATIK GETİRME MERKEZİ')) return '🟢 1. Sınıf Merkez';
                        if (str_contains($content, 'MOBİL ATIK GETİRME MERKEZİ')) return '🚛 Mobil Merkez';
                        if (str_contains($content, 'BİTKİSEL ATIK YAĞ')) return '🛢️ Atık Yağ';
                        if (str_contains($content, 'ATIK CAM')) return '🟫 Atık Cam';
                        if (str_contains($content, 'TEKSTİL KUMBARASI')) return '👕 Tekstil';
                        if (str_contains($content, 'ATIK İLAÇ')) return '💊 Atık İlaç';
                        if (str_contains($content, 'İNERT ATIK')) return '🧱 İnert Atık';
                        if (str_contains($content, 'HAFRİYAT')) return '🏗️ Hafriyat';
                        return '📦 Diğer';
                    })
                    ->sortable(),
                TextColumn::make('ratings_avg_rating')
                    ->label('Ortalama Puan')
                    ->formatStateUsing(fn ($state) => $state ? '⭐ ' . number_format($state, 1) : '⚪ Puansız')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('ratings_count')
                    ->label('Değerlendirme')
                    ->formatStateUsing(fn ($state) => $state . ' değerlendirme')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('favorites_count')
                    ->label('Favori')
                    ->formatStateUsing(fn ($state) => '❤️ ' . $state)
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('content')
                    ->label('🏢 Merkez Türü Seç')
                    ->placeholder('Merkez türü seçin...')
                    ->options([
                        '1. SINIF ATIK GETİRME MERKEZİ' => '🟢 1. Sınıf Atık Getirme Merkezi',
                        'MOBİL ATIK GETİRME MERKEZİ' => '🚛 Mobil Atık Getirme Merkezi',
                        'BİTKİSEL ATIK YAĞ' => '🛢️ Bitkisel Atık Yağ',
                        'ATIK CAM' => '🟫 Atık Cam',
                        'TEKSTİL KUMBARASI' => '👕 Tekstil Kumbarası',
                        'ATIK İLAÇ' => '💊 Atık İlaç',
                        'İNERT ATIK' => '🧱 İnert Atık',
                        'HAFRİYAT' => '🏗️ Hafriyat',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->where('content', 'like', "%{$value}%"),
                        );
                    }),
                
                SelectFilter::make('ratings_filter')
                    ->label('⭐ Puan Durumu')
                    ->placeholder('Puan durumu seçin...')
                    ->options([
                        'rated' => '✅ Puanlanmış',
                        'unrated' => '⚪ Puanlanmamış',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($query, $value) {
                            return match ($value) {
                                'rated' => $query->has('ratings'),
                                'unrated' => $query->doesntHave('ratings'),
                                default => $query,
                            };
                        });
                    }),

                SelectFilter::make('favorites_filter')
                    ->label('❤️ Favori Durumu')
                    ->placeholder('Favori durumu seçin...')
                    ->options([
                        'favorited' => '❤️ Favorilenmiş',
                        'not_favorited' => '🤍 Favorilenmemiş',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when($data['value'], function ($query, $value) {
                            return match ($value) {
                                'favorited' => $query->has('favorites'),
                                'not_favorited' => $query->doesntHave('favorites'),
                                default => $query,
                            };
                        });
                    }),
            ])
            ->filtersFormColumns(3)
            ->filtersTriggerAction(
                fn (Tables\Actions\Action $action) => $action
                    ->button()
                    ->label('🔍 Filtrele')
            )
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detay')
                    ->icon('heroicon-o-eye'),
            ])
            ->emptyStateHeading('Sonuç Bulunamadı')
            ->emptyStateDescription('Seçtiğiniz filtrelere uygun atık merkezi bulunamadı. Lütfen farklı filtreler deneyin.')
            ->emptyStateIcon('heroicon-o-magnifying-glass')
            ->defaultSort('ratings_avg_rating', 'desc')
            ->paginated([10, 25, 50]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAtikMerkeziStatics::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }
}