<?php

namespace App\Filament\Resources;

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

class AtikMerkeziRatingResource extends Resource
{
    protected static ?string $model = AtikMerkeziRating::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('user_id')->label('Kullanıcı ID'),
                TextInput::make('atik_merkezi_id')->label('Merkez ID'),
                TextInput::make('rating')->label('Puan'),
                TextInput::make('comment')->label('Yorum'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Kullanıcı Adı'),
                TextColumn::make('atikMerkezi.title')->label('Merkez Adı'),
                TextColumn::make('rating')->label('Puan'),
                TextColumn::make('comment')->label('Yorum'),
                TextColumn::make('created_at')->label('Oluşturulma Tarihi')->dateTime('d.m.Y H:i:s '),
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
            'index' => Pages\ListAtikMerkeziRatings::route('/'),
            'create' => Pages\CreateAtikMerkeziRating::route('/create'),
            'edit' => Pages\EditAtikMerkeziRating::route('/{record}/edit'),
        ];
    }
}
