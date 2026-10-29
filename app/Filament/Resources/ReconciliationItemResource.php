<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReconciliationItemResource\Pages;
use App\Models\ReconciliationItem;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ReconciliationItemResource extends Resource
{
    protected static ?string $model = ReconciliationItem::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListReconciliationItems::route('/'),
            'create' => Pages\CreateReconciliationItem::route('/create'),
            'edit' => Pages\EditReconciliationItem::route('/{record}/edit'),
        ];
    }
}
