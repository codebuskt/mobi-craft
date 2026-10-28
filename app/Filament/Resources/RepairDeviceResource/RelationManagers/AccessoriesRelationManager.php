<?php

namespace App\Filament\Resources\RepairDeviceResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AccessoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'accessories';

    protected static ?string $title = 'Accessories';

    protected static ?string $modelLabel = 'Accessory';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    TextInput::make('name')
                        ->required(),

                    Select::make('type')
                        ->options([
                            'charger' => 'Charger',
                            'cable' => 'Cable',
                            'case' => 'Case',
                            'screen_protector' => 'Screen Protector',
                            'earphones' => 'Earphones',
                            'box' => 'Box',
                            'manual' => 'Manual',
                            'other' => 'Other',
                        ])
                        ->default('other')
                        ->native(false),
                ]),

                Textarea::make('description')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('condition_notes')
                    ->label('Condition Notes')
                    ->rows(2)
                    ->columnSpanFull(),

                Grid::make(2)->schema([
                    Toggle::make('is_received')
                        ->label('Received')
                        ->default(false),

                    Toggle::make('is_returned')
                        ->label('Returned')
                        ->default(false),
                ]),

                Forms\Components\FileUpload::make('photos')
                    ->label('Photos')
                    ->multiple()
                    ->image()
                    ->directory('repairs/accessories')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->weight('medium'),

                BadgeColumn::make('type')
                    ->colors([
                        'primary' => 'charger',
                        'info' => 'cable',
                        'success' => 'case',
                        'warning' => 'screen_protector',
                        'purple' => 'earphones',
                        'gray' => 'box',
                        'gray' => 'manual',
                        'gray' => 'other',
                    ]),

                IconColumn::make('is_received')
                    ->label('Received')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                IconColumn::make('is_returned')
                    ->label('Returned')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),

                TextColumn::make('condition_notes')
                    ->label('Condition')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
