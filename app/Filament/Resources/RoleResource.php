<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use App\Models\Permission;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Section::make('Permissions')
                    ->schema(static::getPermissionSchema())
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    protected static function getPermissionSchema(): array
    {
        $permissions = Permission::all()
            ->groupBy(function ($permission) {
                $lastUnderscorePosition = strrpos($permission->name, '_');
                return $lastUnderscorePosition !== false
                    ? substr($permission->name, $lastUnderscorePosition + 1)
                    : $permission->name;
            });

        return [
            Forms\Components\Grid::make(3)
                ->schema(
                    $permissions->map(function ($permissions, $resource) {
                        $operations = $permissions->pluck('name')->toArray();

                        return Card::make(self::formatHeadline($resource))
                            ->schema([
                                Toggle::make("select_all_{$resource}")
                                    ->label('Select All')
                                    ->reactive()
                                    ->afterStateHydrated(function ($component, $state) use ($operations, $resource) {
                                        $record = $component->getRecord();
                                        if ($record) {
                                            $existingPermissions = $record->permissions()
                                                ->whereIn('name', $operations)
                                                ->pluck('name')
                                                ->toArray();
                                            $component->state(count($existingPermissions) === count($operations));
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, $get, $set) use ($operations, $resource) {
                                        if ($state) {
                                            $set("permissions.{$resource}", $operations);
                                        } else {
                                            $set("permissions.{$resource}", []);
                                        }
                                    }),

                                CheckboxList::make("permissions.{$resource}")
                                    ->label('')
                                    ->options(self::formatOptions($operations))
                                    ->dehydrated(true)
                                    ->reactive()
                                    ->afterStateHydrated(function ($component, $state) use ($operations, $resource) {
                                        $record = $component->getRecord();
                                        if ($record) {
                                            $existingPermissions = $record->permissions()
                                                ->whereIn('name', $operations)
                                                ->pluck('name')
                                                ->toArray();

                                            $component->state($existingPermissions);
                                        }
                                    })
                                    ->afterStateUpdated(function ($state, $get, $set) use ($operations, $resource) {
                                        if (count($state) === count($operations)) {
                                            $set("select_all_{$resource}", true);
                                        } else {
                                            $set("select_all_{$resource}", false);
                                        }
                                    })
                                    ->columns(2),
                            ])
                            ->collapsible()
                            ->columnSpan(1);
                    })->values()->toArray()
                )
                ->columnSpanFull(),
        ];
    }
    protected static function formatHeadline(string $resource): string
    {
        return Str::headline(str_replace('::', ' ', $resource));
    }

    protected static function formatOptions(array $operations): array
    {
        return collect($operations)
            ->mapWithKeys(function ($operation) {
                $lastUnderscorePosition = strrpos($operation, '_');
                $baseOperation = $lastUnderscorePosition !== false
                    ? substr($operation, 0, $lastUnderscorePosition)
                    : $operation;
                $label = Str::headline(str_replace('_', ' ', $baseOperation));
                return [$operation => $label];
            })
            ->toArray();
    }


    public static function getRelations(): array
    {
        return [];
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
