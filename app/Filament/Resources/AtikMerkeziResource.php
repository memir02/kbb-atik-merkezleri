<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\TextInput;
use App\Filament\Resources\AtikMerkeziResource\Pages;
use App\Filament\Resources\AtikMerkeziResource\RelationManagers;
use App\Models\AtikMerkezi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AtikMerkeziResource extends Resource
{
    protected static ?string $model = AtikMerkezi::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    
    protected static ?string $navigationLabel = 'Atık Merkezleri';
    
    protected static ?string $modelLabel = 'Atık Merkezi';
    
    protected static ?string $pluralModelLabel = 'Atık Merkezleri';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')->label('Merkez Adı'),
                TextInput::make('adres')->label('Adres'),
                TextInput::make('content')->label('Açıklama'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Merkez Adı'),
                TextColumn::make('lat')->label('Enlem'),
                TextColumn::make('lon')->label('Boylam'),
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
            'index' => Pages\ListAtikMerkezis::route('/'),
            'create' => Pages\CreateAtikMerkezi::route('/create'),
            'edit' => Pages\EditAtikMerkezi::route('/{record}/edit'),
        ];
    }
}
