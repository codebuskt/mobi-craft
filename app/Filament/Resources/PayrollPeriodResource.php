<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PayrollPeriodResource\Pages;
use App\Models\PayrollPeriod;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PayrollPeriodResource extends Resource
{
    protected static ?string $model = PayrollPeriod::class;

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
            'index' => Pages\ListPayrollPeriods::route('/'),
            'create' => Pages\CreatePayrollPeriod::route('/create'),
            'edit' => Pages\EditPayrollPeriod::route('/{record}/edit'),
        ];
    }
}
