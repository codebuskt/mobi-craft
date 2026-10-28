<?php

namespace App\Filament\Resources\RepairDeviceResource\RelationManagers;

use App\Models\Tenant;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QualityChecksRelationManager extends RelationManager
{
    protected static string $relationship = 'qualityChecks';

    protected static ?string $title = 'Quality Checks';

    protected static ?string $modelLabel = 'Quality Check';

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

                    Select::make('checked_by')
                        ->label('Checked By')
                        ->relationship('checker', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->email})"),
                ]),

                Grid::make(2)->schema([
                    DatePicker::make('checked_date')
                        ->required()
                        ->default(now()),

                    Select::make('overall_result')
                        ->options([
                            'pass' => 'Pass',
                            'fail' => 'Fail',
                        ])
                        ->required()
                        ->default('pass')
                        ->native(false),
                ]),

                Toggle::make('is_recheck')
                    ->label('Re-check')
                    ->default(false),

                Textarea::make('checklist')
                    ->label('Checklist (JSON)')
                    ->rows(5)
                    ->columnSpanFull()
                    ->helperText('Format: [{"item": "Screen test", "description": "Check for dead pixels", "result": "pass", "notes": "All good"}]'),

                Textarea::make('notes')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\FileUpload::make('photos')
                    ->label('Photos')
                    ->multiple()
                    ->image()
                    ->directory('repairs/quality')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('repairDevice.display_name')
                    ->label('Device')
                    ->searchable(),

                TextColumn::make('checker.name')
                    ->label('Checked By')
                    ->searchable(),

                BadgeColumn::make('overall_result')
                    ->label('Result')
                    ->colors([
                        'success' => 'pass',
                        'danger' => 'fail',
                    ]),

                TextColumn::make('checked_date')
                    ->label('Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('passed_items_count')
                    ->label('Passed')
                    ->alignCenter(),

                TextColumn::make('failed_items_count')
                    ->label('Failed')
                    ->alignCenter()
                    ->color('danger'),

                TextColumn::make('total_items_count')
                    ->label('Total')
                    ->alignCenter(),

                IconColumn::make('is_recheck')
                    ->label('Re-check')
                    ->boolean()
                    ->trueIcon('heroicon-o-arrow-path')
                    ->falseIcon('heroicon-o-minus')
                    ->trueColor('warning')
                    ->falseColor('gray'),
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
