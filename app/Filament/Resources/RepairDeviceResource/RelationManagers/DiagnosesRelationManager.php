<?php

namespace App\Filament\Resources\RepairDeviceResource\RelationManagers;

use App\Models\Tenant;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DiagnosesRelationManager extends RelationManager
{
    protected static string $relationship = 'diagnoses';

    protected static ?string $title = 'Diagnoses';

    protected static ?string $modelLabel = 'Diagnosis';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(2)->schema([
                    Select::make('repair_device_id')
                        ->label('Device')
                        ->relationship('repairDevice', 'display_name')
                        ->required()
                        ->searchable()
                        ->preload(),

                    Select::make('technician_id')
                        ->label('Technician')
                        ->relationship('technician', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->email})"),
                ]),

                Grid::make(2)->schema([
                    DatePicker::make('diagnosis_date')
                        ->required()
                        ->default(now()),

                    Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'completed' => 'Completed',
                            'requires_approval' => 'Requires Approval',
                        ])
                        ->required()
                        ->default('pending')
                        ->native(false),
                ]),

                Textarea::make('findings')
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),

                Textarea::make('recommended_repair')
                    ->label('Recommended Repair')
                    ->rows(2)
                    ->columnSpanFull(),

                Textarea::make('recommended_parts')
                    ->label('Recommended Parts (JSON)')
                    ->rows(3)
                    ->columnSpanFull()
                    ->helperText('Format: [{"product_id": 1, "quantity": 1, "estimated_price": 500}]'),

                Grid::make(3)->schema([
                    TextInput::make('labor_charge')
                        ->label('Labor Charge')
                        ->numeric()
                        ->prefix('BDT')
                        ->default(0)
                        ->step(0.01),

                    TextInput::make('parts_estimated_cost')
                        ->label('Parts Est. Cost')
                        ->numeric()
                        ->prefix('BDT')
                        ->default(0)
                        ->step(0.01),

                    TextInput::make('estimated_total')
                        ->label('Estimated Total')
                        ->numeric()
                        ->prefix('BDT')
                        ->default(0)
                        ->step(0.01)
                        ->readonly(),
                ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('repairDevice.display_name')
                    ->label('Device')
                    ->searchable(),

                TextColumn::make('technician.name')
                    ->label('Technician')
                    ->searchable(),

                BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'completed',
                        'info' => 'requires_approval',
                    ])
                    ->formatStateUsing(fn ($state) => ucfirst(str_replace('_', ' ', $state))),

                TextColumn::make('diagnosis_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('labor_charge')
                    ->label('Labor')
                    ->money('BDT'),

                TextColumn::make('parts_estimated_cost')
                    ->label('Parts Est.')
                    ->money('BDT'),

                TextColumn::make('estimated_total')
                    ->label('Total Est.')
                    ->money('BDT')
                    ->weight('medium'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['tenant_id'] = auth()->user()->tenant_id ?? Tenant::getCurrentTenant()?->id;

                        return $data;
                    }),
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
