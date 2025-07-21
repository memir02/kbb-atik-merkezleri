<?php

namespace App\Filament\Resources;

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

class AtikMerkeziFavoriteResource extends Resource
{
    protected static ?string $model = AtikMerkeziFavorite::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('user_id')->label('Kullanıcı ID'),
                TextInput::make('atikMerkezi_id')->label('Merkez ID'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Kullanıcı Adı'),
                TextColumn::make('atikMerkezi.title')->label('Merkez Adı'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
}
