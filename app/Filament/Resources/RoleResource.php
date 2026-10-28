<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Permission;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'Users & Permissions';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Role Details')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->alphaDash(),
                        Forms\Components\Textarea::make('description')
                            ->columnSpanFull()
                            ->rows(3),
                    ])->columns(2),

                Forms\Components\Section::make('Settings')
                    ->schema([
                        Forms\Components\Toggle::make('is_system')
                            ->label('System Role')
                            ->default(false)
                            ->helperText('System roles cannot be deleted by tenants'),
                        Forms\Components\Toggle::make('is_tenant_scoped')
                            ->label('Tenant Scoped')
                            ->default(true)
                            ->helperText('If disabled, this role applies to super admin only'),
                    ])->columns(2),

                Forms\Components\Section::make('Permissions')
                    ->schema([
                        Forms\Components\CheckboxList::make('permissions')
                            ->relationship('permissions', 'slug')
                            ->label('Permissions')
                            ->columns(3)
                            ->searchable()
                            ->getOptionLabelFromRecordUsing(fn (Permission $permission) => "{$permission->slug} ({$permission->group})")
                            ->helperText('Select permissions for this role'),
                    ])->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('is_system')
                    ->label('System')
                    ->colors([
                        'success' => true,
                        'gray' => false,
                    ])
                    ->formatStateUsing(fn (bool $state) => $state ? 'Yes' : 'No')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('is_tenant_scoped')
                    ->label('Tenant Scoped')
                    ->colors([
                        'info' => true,
                        'warning' => false,
                    ])
                    ->formatStateUsing(fn (bool $state) => $state ? 'Yes' : 'No')
                    ->sortable(),
                Tables\Columns\TextColumn::make('permissions_count')
                    ->label('Permissions')
                    ->counts('permissions')
                    ->badge()
                    ->color('primary'),
                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->badge()
                    ->color('gray'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_system')
                    ->label('Type')
                    ->trueLabel('System Roles')
                    ->falseLabel('Tenant Roles')
                    ->native(false),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->visible(fn (Role $record) => ! $record->is_system),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn () => auth()->user()?->hasPermission('roles.manage')),
                ]),
            ])
            ->defaultSort('is_system', 'desc')
            ->defaultSort('name');
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
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}