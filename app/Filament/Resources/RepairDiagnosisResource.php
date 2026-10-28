<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RepairDiagnosisResource\Pages;
use App\Models\RepairDiagnosis;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RepairDiagnosisResource extends Resource
{
    protected static ?string $model = RepairDiagnosis::class;

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
            'index' => Pages\ListRepairDiagnoses::route('/'),
            'create' => Pages\CreateRepairDiagnosis::route('/create'),
            'edit' => Pages\EditRepairDiagnosis::route('/{record}/edit'),
        ];
    }
}
