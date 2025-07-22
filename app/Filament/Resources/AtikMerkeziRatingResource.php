<?php

namespace App\Filament\Resources;
use Filament\Tables\Filters\SelectFilter;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\AtikMerkeziRatingResource\Pages;
use App\Filament\Resources\AtikMerkeziRatingResource\RelationManagers;
use App\Models\AtikMerkeziRating;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\Filter;

class AtikMerkeziRatingResource extends Resource
{
    protected static ?string $model = AtikMerkeziRating::class;

    protected static ?string $navigationIcon = 'heroicon-o-star';
    
    protected static ?string $navigationLabel = 'Değerlendirmeler';
    
    protected static ?string $modelLabel = 'Değerlendirme';
    
    protected static ?string $pluralModelLabel = 'Değerlendirmeler';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('user_id')
                    ->label('Kullanıcı')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->required(),
                \Filament\Forms\Components\Select::make('atik_merkezi_id')
                    ->label('Atık Merkezi')
                    ->relationship('atikMerkezi', 'title')
                    ->searchable()
                    ->required(),
                \Filament\Forms\Components\Select::make('rating')
                    ->label('Puan')
                    ->options([
                        1 => '⭐ (1 Yıldız)',
                        2 => '⭐⭐ (2 Yıldız)',
                        3 => '⭐⭐⭐ (3 Yıldız)',
                        4 => '⭐⭐⭐⭐ (4 Yıldız)',
                        5 => '⭐⭐⭐⭐⭐ (5 Yıldız)',
                    ])
                    ->required(),
                \Filament\Forms\Components\Textarea::make('comment')
                    ->label('Yorum')
                    ->rows(3)
                    ->maxLength(1000),
                \Filament\Forms\Components\Toggle::make('is_approved')
                    ->label('Onaylı mı?')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('user.name')
                    ->label('Kullanıcı')
                    ->searchable()
                    ->sortable()
                    ->icon('heroicon-o-user'),
                \Filament\Tables\Columns\TextColumn::make('atikMerkezi.title')
                    ->label('Atık Merkezi')
                    ->searchable()
                    ->sortable()
                    ->limit(30)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 30) {
                            return null;
                        }
                        return $state;
                    })
                    ->icon('heroicon-o-building-office'),
                \Filament\Tables\Columns\TextColumn::make('rating')
                    ->label('Puan')
                    ->sortable()
                    ->formatStateUsing(fn (string $state): string => str_repeat('⭐', (int) $state) . " ({$state}/5)")
                    ->color(fn (string $state): string => match ((int) $state) {
                        1, 2 => 'danger',
                        3 => 'warning',
                        4, 5 => 'success',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('comment')
                    ->label('Yorum')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->searchable(),
                \Filament\Tables\Columns\IconColumn::make('is_approved')
                    ->label('Onay Durumu')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('updated_at')
                    ->label('Güncellenme')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                \Filament\Tables\Actions\EditAction::make(),
                \Filament\Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                \Filament\Tables\Actions\BulkActionGroup::make([
                    \Filament\Tables\Actions\DeleteBulkAction::make(),
                    \Filament\Tables\Actions\BulkAction::make('approve')
                        ->label('Seçilileri Onayla')
                        ->action(fn (\Illuminate\Support\Collection $records) => $records->each->update(['is_approved' => true]))
                        ->icon('heroicon-o-check')
                        ->color('success')
                        ->requiresConfirmation(),
                    \Filament\Tables\Actions\BulkAction::make('reject')
                        ->label('Seçilileri Reddet')
                        ->action(fn (\Illuminate\Support\Collection $records) => $records->each->update(['is_approved' => false]))
                        ->icon('heroicon-o-x-mark')
                        ->color('danger')
                        ->requiresConfirmation(),
                ]),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('is_approved')
                    ->label('Onay Durumu')
                    ->options([
                        1 => 'Onaylı',
                        0 => 'Onaysız',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('rating')
                    ->label('Puan')
                    ->options([
                        1 => '⭐ (1 Yıldız)',
                        2 => '⭐⭐ (2 Yıldız)',
                        3 => '⭐⭐⭐ (3 Yıldız)',
                        4 => '⭐⭐⭐⭐ (4 Yıldız)',
                        5 => '⭐⭐⭐⭐⭐ (5 Yıldız)',
                    ]),
                \Filament\Tables\Filters\SelectFilter::make('user_id')
                    ->label('Kullanıcı')
                    ->relationship('user', 'name')
                    ->searchable(),
                \Filament\Tables\Filters\SelectFilter::make('atik_merkezi_id')
                    ->label('Atık Merkezi')
                    ->relationship('atikMerkezi', 'title')
                    ->searchable(),
            ]);
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
            'index' => Pages\ListAtikMerkeziRatings::route('/'),
            'create' => Pages\CreateAtikMerkeziRating::route('/create'),
            'edit' => Pages\EditAtikMerkeziRating::route('/{record}/edit'),
        ];
    }
}
