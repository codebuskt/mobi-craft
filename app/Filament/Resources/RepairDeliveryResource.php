<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepairDeliveryResource\Pages;
use App\Models\RepairDelivery;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RepairDeliveryResource extends Resource
{
    protected static ?string $model = RepairDelivery::class;

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
            'index' => Pages\ListRepairDeliveries::route('/'),
            'create' => Pages\CreateRepairDelivery::route('/create'),
            'edit' => Pages\EditRepairDelivery::route('/{record}/edit'),
        ];
    }
}
