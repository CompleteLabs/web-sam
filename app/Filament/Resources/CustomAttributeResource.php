<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CustomAttributeResource\Pages;
use App\Filament\Resources\CustomAttributeResource\RelationManagers;
use App\Models\BadanUsaha;
use App\Models\CustomAttribute;
use App\Models\Division;
use App\Services\OrganizationalStructureService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomAttributeResource extends Resource
{
    protected static ?string $model = CustomAttribute::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('tabs')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('Informasi Custom Attribute')
                            ->schema([
                                Forms\Components\TextInput::make('label')
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        $key = strtolower(str_replace(' ', '_', $state));
                                        $set('key', $key);
                                    }),
                                Forms\Components\TextInput::make('key')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\ToggleButtons::make('entity_type')
                                    ->label('Terapkan pada data')
                                    ->required()
                                    ->options([
                                        'App\Models\Noo' => 'NOO',
                                        'App\Models\Outlet' => 'Outlet'
                                    ])
                                    ->icons([
                                        'App\Models\Noo' => 'heroicon-o-building-office',
                                        'App\Models\Outlet' => 'heroicon-o-building-storefront',
                                    ])
                                    ->inline(),
                                Forms\Components\ToggleButtons::make('apply_entity_type')
                                    ->label('Terapkan untuk')
                                    ->required()
                                    ->options(function () {
                                        $role = auth()->user()->role;
                                        if ($role->filter_type === 'App\Models\BadanUsaha' || $role->filter_type === 'all') {
                                            return [
                                                'App\Models\BadanUsaha' => 'Badan Usaha',
                                                'App\Models\Division' => 'Divisi',
                                            ];
                                        }

                                        if ($role->filter_type === 'App\Models\Division') {
                                            return [
                                                'App\Models\Division' => 'Divisi',
                                            ];
                                        }

                                        return [];
                                    })
                                    ->icons([
                                        'App\Models\BadanUsaha' => 'heroicon-o-building-office-2',
                                        'App\Models\Division' => 'heroicon-o-briefcase',
                                    ])
                                    ->inline()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        if ($state == 'App\Models\BadanUsaha') {
                                            $set('apply_entity_id', null);
                                        } elseif ($state == 'App\Models\Division') {
                                            $set('apply_entity_id', null);
                                        }
                                    }),
                                Forms\Components\Select::make('apply_entity_id')
                                    ->label('Badan Usaha')
                                    ->required()
                                    ->options(function () {
                                        $organizationalStructureService = new OrganizationalStructureService();
                                        return $organizationalStructureService->getBadanUsahaOptions();
                                    })
                                    ->searchable()
                                    ->visible(fn(Get $get) => $get('apply_entity_type') === 'App\Models\BadanUsaha')
                                    ->columnSpanFull()
                                    ->placeholder('Pilih Badan Usaha'),
                                Forms\Components\Select::make('apply_entity_id')
                                    ->label('Divisi')
                                    ->required()
                                    ->preload()
                                    ->searchable()
                                    ->options(function () {
                                        $user = auth()->user();
                                        $role = $user->role;

                                        if ($role->filter_type !== 'all' && $role->filter_type !== null) {
                                            $organizationalStructureService = new OrganizationalStructureService();
                                            return $organizationalStructureService->getDivisiOptions(null);
                                        }

                                        $divisions = Division::with('badanusaha')
                                            ->leftJoin('badan_usahas', 'divisions.badanusaha_id', '=', 'badan_usahas.id')
                                            ->orderBy('badan_usahas.name')
                                            ->select('divisions.*')
                                            ->get();

                                        return $divisions->mapWithKeys(function ($division) {
                                            $badanusahaName = $division->badanusaha ? $division->badanusaha->name : 'Tidak ada badan usaha';
                                            return [$division->id => "{$badanusahaName} / {$division->name}"];
                                        });
                                    })
                                    ->visible(fn(Get $get) => $get('apply_entity_type') === 'App\Models\Division')
                                    ->columnSpanFull()
                                    ->placeholder('Pilih Divisi'),
                                Forms\Components\Select::make('type')
                                    ->required()
                                    ->searchable()
                                    ->options([
                                        'TEXT' => 'Single-line text',
                                        'NUMBER' => 'Number',
                                        'LINK' => 'Link (URL)',
                                        'TEXTAREA' => 'Text Area',
                                        'CURRENCY' => 'Currency',
                                        'DATE' => 'Date',
                                        'DATE_AND_TIME' => 'Date and Time',
                                        'TOGGLE' => 'Toggle',
                                        'TOGGLE_BUTTONS' => 'Toggle Buttons',
                                        'SELECT' => 'Select',
                                        'CHECKBOX' => 'Checkbox',
                                        'CHECKBOX_LIST' => 'Checkbox List',
                                        'RADIO' => 'Radio',
                                        'UPLOAD' => 'Upload',
                                        'PHOTO' => 'Photo',
                                    ])
                                    ->reactive()
                                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                                        if (!in_array($state, ['SELECT'])) {
                                            $set('attribute_options', []);
                                        }
                                    })
                                    ->columnSpanFull(),
                                Forms\Components\Repeater::make('options')
                                    ->label('Attribute Options')
                                    ->schema([
                                        Forms\Components\TextInput::make('option')
                                            ->label('Option')
                                            ->required(),
                                    ])
                                    ->visible(fn(Get $get) => $get('type') === 'SELECT')
                                    ->columnSpanFull(),
                            ]),
                        Forms\Components\Tabs\Tab::make('Validation Rules')
                            ->schema([
                                Forms\Components\Section::make('General Options')
                                    ->schema([
                                        // Forms\Components\Toggle::make('validation_rules.general_options.is_multiple_select')
                                        //     ->label('Izinkan Pilihan Multiple')
                                        //     ->helperText('Izinkan memilih lebih dari satu pilihan (untuk SELECT)')
                                        //     ->visible(fn(Get $get) => $get('type') === 'SELECT')
                                        //     ->columnSpanFull(),
                                        Forms\Components\Toggle::make('validation_rules.general_options.is_required')
                                            ->label('Field Ini Wajib Diisi')
                                            ->helperText('Tandai apakah field ini wajib diisi')
                                            ->columnSpanFull(),
                                        // Forms\Components\Toggle::make('validation_rules.general_options.is_hidden_label')
                                        //     ->label('Sembunyikan Label')
                                        //     ->helperText('Tandai apakah label field harus disembunyikan')
                                        //     ->columnSpanFull(),
                                    ])
                                    ->collapsible(),

                                Forms\Components\Section::make('Hint Options')
                                    ->schema([
                                        Forms\Components\TextInput::make('validation_rules.hint_options.hint_text')
                                            ->label('Text Hint')
                                            ->helperText('Text yang ditampilkan sebagai hint'),
                                        Forms\Components\TextInput::make('validation_rules.hint_options.hint_icon')
                                            ->label('Ikon Hint')
                                            ->helperText('Ikon yang ditampilkan sebagai hint'),
                                        Forms\Components\TextInput::make('validation_rules.hint_options.hint_color')
                                            ->label('Warna Hint')
                                            ->helperText('Warna teks untuk hint'),
                                        Forms\Components\TextInput::make('validation_rules.hint_options.tooltip')
                                            ->label('Tooltip')
                                            ->helperText('Tooltip yang ditampilkan saat hover'),
                                    ])
                                    ->columns(2)
                                    ->collapsed(),

                                Forms\Components\Section::make('Conditional Visibility')
                                    ->schema([
                                        Forms\Components\Toggle::make('validation_rules.conditional_visibility.enable')
                                            ->label('Enable Conditional Visibility')
                                            ->helperText('Aktifkan untuk menyembunyikan/memunculkan field berdasarkan kondisi')
                                            ->reactive()
                                            ->columnSpanFull(),
                                        Forms\Components\Select::make('validation_rules.conditional_visibility.field')
                                            ->label('Show when the field:')
                                            ->required()
                                            ->reactive()
                                            ->searchable()
                                            ->preload()
                                            ->options(function (Get $get) {
                                                $currentField = $get('validation_rules.conditional_visibility.field');

                                                return \App\Models\CustomAttribute::where('type', 'SELECT')
                                                    // ->where('id', '!=', $currentField)
                                                    ->pluck('label', 'key')
                                                    ->toArray();
                                            })
                                            ->columnSpanFull()
                                            ->visible(fn(Get $get) => $get('validation_rules.conditional_visibility.enable') === true),
                                        Forms\Components\Select::make('validation_rules.conditional_visibility.value')
                                            ->label('Has the value:')
                                            ->required()
                                            ->searchable()
                                            ->preload()
                                            ->options(function (Get $get) {
                                                $selectedFieldKey = $get('validation_rules.conditional_visibility.field');
                                                if ($selectedFieldKey) {
                                                    $attribute = \App\Models\CustomAttribute::where('key', $selectedFieldKey)->first();
                                                    $options = $attribute?->options ?? [];
                                                    return collect($options)
                                                        ->mapWithKeys(fn($item) => [$item['option'] => $item['option']])
                                                        ->toArray();
                                                }
                                                return [];
                                            })
                                            ->columnSpanFull()
                                            ->visible(fn(Get $get) => $get('validation_rules.conditional_visibility.field')
                                                && $get('validation_rules.conditional_visibility.enable') === true),
                                    ])
                                    ->collapsed(),
                            ]),
                    ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->slideOver()
                    ->visible(function ($record) {
                        return !$record->system_defined;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(function ($record) {
                        return !$record->system_defined;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $role = $user->role;
        $divisionIds = [];

        // Jika filter_type adalah 'all' atau null, kembalikan query tanpa filter.
        if ($role->filter_type === 'all' || $role->filter_type === null) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()->where(function ($query) use ($role, $divisionIds) {
            switch ($role->filter_type) {
                case 'App\Models\BadanUsaha':
                    $query->where(function ($q) use ($role) {
                        $q->where('custom_attributes.apply_entity_type', 'App\Models\BadanUsaha')
                            ->unless(empty($role->filter_data), function ($query) use ($role) {
                                $query->whereIn('custom_attributes.apply_entity_id', (array) $role->filter_data);
                            });
                    })
                        ->orWhere(function ($q2) use ($divisionIds) {
                            $q2->where('custom_attributes.apply_entity_type', 'App\Models\Division')
                                ->unless(empty($divisionIds), function ($query) use ($divisionIds) {
                                    $query->whereIn('custom_attributes.apply_entity_id', $divisionIds);
                                });
                        });
                    break;
                case 'App\Models\Division':
                    $query->where('custom_attributes.apply_entity_type', 'App\Models\Division')
                        ->unless(empty($role->filter_data), function ($query) use ($role) {
                            $query->whereIn('custom_attributes.apply_entity_id', (array) $role->filter_data);
                        });
                    break;
                default:
                    $query->whereRaw('0 = 1');
                    break;
            }
        });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCustomAttributes::route('/'),
        ];
    }
}
