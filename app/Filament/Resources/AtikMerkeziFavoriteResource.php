<?php

namespace App\Filament\Resources;

use Filament\Tables\Filters\SelectFilter;
use App\Models\AtikMerkezi;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Resources\AtikMerkeziFavoriteResource\Pages;
use App\Filament\Resources\AtikMerkeziFavoriteResource\RelationManagers;
use App\Models\AtikMerkeziFavorite;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Models\User;

class AtikMerkeziFavoriteResource extends Resource
{
    protected static ?string $model = AtikMerkeziFavorite::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';
    
    protected static ?string $navigationLabel = 'Favoriler';
    
    protected static ?string $modelLabel = 'Favori';
    
    protected static ?string $pluralModelLabel = 'Favoriler';

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
                    ->limit(40)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    })
                    ->icon('heroicon-o-building-office'),
                \Filament\Tables\Columns\TextColumn::make('atikMerkezi.adres')
                    ->label('Adres')
                    ->limit(50)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    })
                    ->icon('heroicon-o-map-pin')
                    ->toggleable(isToggledHiddenByDefault: true),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Eklenme Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->icon('heroicon-o-heart'),
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
                ]),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Kullanıcı')
                    ->relationship('user', 'name')
                    ->searchable(),
                SelectFilter::make('atik_merkezi_id')
                    ->label('Atık Merkezi')
                    ->relationship('atikMerkezi', 'title')
                    ->searchable(),
                \Filament\Tables\Filters\Filter::make('recent')
                    ->label('Son 30 Gün')
                    ->query(fn (Builder $query): Builder => $query->where('created_at', '>=', now()->subDays(30))),
                \Filament\Tables\Filters\Filter::make('this_month')
                    ->label('Bu Ay')
                    ->query(fn (Builder $query): Builder => $query->whereMonth('created_at', now()->month)),
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
            'index' => Pages\ListAtikMerkeziFavorites::route('/'),
            'create' => Pages\CreateAtikMerkeziFavorite::route('/create'),
            'edit' => Pages\EditAtikMerkeziFavorite::route('/{record}/edit'),
        ];
    }
    
    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
