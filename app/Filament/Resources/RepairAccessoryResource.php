<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepairAccessoryResource\Pages;
use App\Models\RepairAccessory;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RepairAccessoryResource extends Resource
{
    protected static ?string $model = RepairAccessory::class;

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
            'index' => Pages\ListRepairAccessories::route('/'),
            'create' => Pages\CreateRepairAccessory::route('/create'),
            'edit' => Pages\EditRepairAccessory::route('/{record}/edit'),
        ];
    }
}
