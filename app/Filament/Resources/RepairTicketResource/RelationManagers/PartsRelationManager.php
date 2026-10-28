<?php

namespace App\Filament\Resources\RepairTicketResource\RelationManagers;

use App\Models\RepairPart;
use App\Models\Tenant;
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

class PartsRelationManager extends RelationManager
{
    protected static string $relationship = 'repairParts';

    protected static ?string $title = 'Parts Used';

    protected static ?string $modelLabel = 'Part';

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

                    Select::make('product_id')
                        ->label('Product')
                        ->relationship('product', 'name')
                        ->required()
                        ->searchable()
                        ->preload()
                        ->getOptionLabelFromRecordUsing(fn ($record) => "{$record->name} ({$record->sku}) - Stock: {$record->current_stock}"),
                ]),

                Grid::make(3)->schema([
                    TextInput::make('quantity')
                        ->required()
                        ->numeric()
                        ->default(1)
                        ->minValue(1),

                    TextInput::make('unit_price')
                        ->label('Unit Price')
                        ->numeric()
                        ->prefix('BDT')
                        ->required()
                        ->step(0.01),

                    Select::make('source')
                        ->options([
                            'inventory' => 'Inventory',
                            'special_order' => 'Special Order',
                        ])
                        ->default('inventory')
                        ->native(false)
                        ->required(),
                ]),

                Textarea::make('notes')
                    ->rows(2)
                    ->columnSpanFull(),

                Toggle::make('is_installed')
                    ->label('Installed')
                    ->default(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->weight('medium'),

                TextColumn::make('repairDevice.display_name')
                    ->label('Device')
                    ->searchable(),

                TextColumn::make('quantity')
                    ->label('Qty')
                    ->alignCenter(),

                TextColumn::make('unit_price')
                    ->label('Unit Price')
                    ->money('BDT'),

                TextColumn::make('total_price')
                    ->label('Total')
                    ->money('BDT')
                    ->weight('medium'),

                BadgeColumn::make('source')
                    ->colors([
                        'success' => 'inventory',
                        'info' => 'special_order',
                    ]),

                IconColumn::make('is_installed')
                    ->label('Installed')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['tenant_id'] = auth()->user()->tenant_id ?? Tenant::getCurrentTenant()?->id;
                        $data['total_price'] = $data['quantity'] * $data['unit_price'];

                        return $data;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('mark_installed')
                    ->label('Mark Installed')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->action(function (RepairPart $record) {
                        $record->markAsInstalled(auth()->id());
                    })
                    ->visible(fn (RepairPart $record) => ! $record->is_installed),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
