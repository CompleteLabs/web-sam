<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Cluster;
use App\Models\User;
use App\Services\OrganizationalStructureService;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 0;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('username')
                            ->required()
                            ->maxLength(255)
                            ->label('Username')
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn($state) => strtolower($state))
                            ->placeholder('Masukkan username yang unik')
                            ->regex('/^[\S]+$/', 'Username tidak boleh mengandung spasi')
                            ->helperText('Username tidak boleh mengandung spasi'),
                        Forms\Components\TextInput::make('nama_lengkap')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Lengkap')
                            ->placeholder('Masukkan nama lengkap')
                            ->dehydrateStateUsing(fn($state) => strtoupper($state)),
                        Forms\Components\TextInput::make('phone')
                            ->label('Nomor Handphone')
                            ->placeholder('08xxxxxxxxxx')
                            ->maxLength(20)
                            ->unique(ignoreRecord: true)
                            ->tel()
                            ->required()
                            ->helperText('Nomor handphone harus unik dan aktif (untuk login WhatsApp OTP)'),
                    ])
                    ->collapsible()
                    ->columns(2),
                Forms\Components\Section::make('Badan Usaha & Divisi')
                    ->schema([
                        Forms\Components\Select::make('badanusaha_id')
                            ->label('Badan Usaha')
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->placeholder('Pilih badan usaha')
                            ->options(function (callable $get) {
                                $badanUsahaService = new OrganizationalStructureService();
                                return $badanUsahaService->getBadanUsahaOptions();
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('divisi_id', null);
                                $set('region_id', null);
                                $set('cluster_id', null);
                                $set('cluster_id2', null);
                            }),
                        Forms\Components\Select::make('divisi_id')
                            ->label('Divisi')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->placeholder('Pilih divisi')
                            ->options(function (callable $get) {
                                $badanusahaId = $get('badanusaha_id');
                                if (!$badanusahaId) {
                                    return [];
                                }
                                $badanUsahaService = new OrganizationalStructureService();
                                return $badanUsahaService->getDivisiOptions($badanusahaId);
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('region_id', null);
                                $set('cluster_id', null);
                                $set('cluster_id2', null);
                            }),
                        Forms\Components\Select::make('region_id')
                            ->label('Region')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->placeholder('Pilih region')
                            ->options(function (callable $get) {
                                $divisiId = $get('divisi_id');
                                if (!$divisiId) {
                                    return [];
                                }
                                $badanUsahaService = new OrganizationalStructureService();
                                return $badanUsahaService->getRegionOptions($divisiId);
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('cluster_id', null);
                                $set('cluster_id2', null);
                            }),
                        Forms\Components\Select::make('cluster_id')
                            ->label('Cluster')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->placeholder('Pilih cluster')
                            ->options(function (callable $get) {
                                $regionId = $get('region_id');
                                if (!$regionId) {
                                    return [];
                                }
                                $badanUsahaService = new OrganizationalStructureService();
                                return $badanUsahaService->getClusterOptions($regionId);
                            }),
                        Forms\Components\Select::make('cluster_id2')
                            ->label('Cluster 2')
                            ->searchable()
                            ->preload()
                            ->reactive()
                            ->placeholder('Pilih cluster 2')
                            ->options(function (callable $get) {
                                $clusterId = $get('region_id');
                                if (!$clusterId) {
                                    return [];
                                }
                                return Cluster::where('region_id', $clusterId)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            }),
                    ])
                    ->collapsible()
                    ->columns(2),
                Forms\Components\Section::make('Role & TM')
                    ->schema([
                        Forms\Components\Select::make('role_id')
                            ->relationship('role', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Role')
                            ->placeholder('Pilih role')
                            ->options(function (callable $get) {
                                $user = auth()->user();
                                if ($user->role->name !== 'SUPER ADMIN') {
                                    return \App\Models\Role::whereIn('name', ['ASC', 'ASM', 'DSF/DM'])
                                        ->pluck('name', 'id')->toArray();
                                }
                                return \App\Models\Role::pluck('name', 'id')->toArray();
                            }),
                        Forms\Components\Select::make('tm_id')
                            ->label('TM')
                            ->relationship('tm', 'nama_lengkap')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Pilih TM'),
                    ])
                    ->collapsible()
                    ->columns(2),
                Forms\Components\Section::make('Password')
                    ->schema([
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->maxLength(255)
                            ->label('Password')
                            ->placeholder('Masukkan password')
                            ->required(fn(string $context): bool => $context === 'create')
                            ->revealable(),
                    ])
                    ->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('role.name'),
                Tables\Columns\TextColumn::make('badanusaha.name'),
                Tables\Columns\TextColumn::make('divisi.name'),
                Tables\Columns\TextColumn::make('region.name'),
                Tables\Columns\TextColumn::make('cluster.name'),
                Tables\Columns\TextColumn::make('tm.nama_lengkap')
                    ->label('TM'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('nama_lengkap', 'asc')
            ->filters([
                Filter::make('region')
                    ->form([
                        Select::make('businessEntity')
                            ->label('Badan Usaha')
                            ->options(function (callable $get) {
                                $badanUsahaService = new OrganizationalStructureService();
                                return $badanUsahaService->getBadanUsahaOptions();
                            })
                            ->reactive()
                            ->searchable()
                            ->placeholder('Pilih Business Entity')
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $set('division', null);
                                $set('region', null);
                            }),
                        Select::make('division')
                            ->label('Divisi')
                            ->options(function (callable $get) {
                                $businessEntityId = $get('businessEntity');
                                if ($businessEntityId) {
                                    $badanUsahaService = new OrganizationalStructureService();
                                    return $badanUsahaService->getDivisiOptions($businessEntityId);
                                }
                                return [];
                            })
                            ->reactive()
                            ->searchable()
                            ->placeholder('Pilih Division')
                            ->afterStateUpdated(function ($state, callable $get, callable $set) {
                                $set('region', null);
                            }),
                        Select::make('region')
                            ->label('Region')
                            ->searchable()
                            ->placeholder('Pilih Region')
                            ->options(function (callable $get) {
                                $divisionId = $get('division');
                                if ($divisionId) {
                                    $badanUsahaService = new OrganizationalStructureService();
                                    return $badanUsahaService->getRegionOptions($divisionId);
                                }
                                return [];
                            })
                            ->reactive(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if ($data['businessEntity'] ?? null) {
                            $query->where('badanusaha_id', $data['businessEntity']);
                        }
                        if ($data['division'] ?? null) {
                            $query->where('divisi_id', $data['division']);
                        }
                        if ($data['region'] ?? null) {
                            $query->where('region_id', $data['region']);
                        }
                        return $query;
                    }),

                Tables\Filters\TrashedFilter::make()
                    ->hidden(fn() => !Gate::any(['restore_any_visit', 'force_delete_any_visit'], User::class)),
            ], layout: FiltersLayout::Modal)
            ->filtersFormWidth(MaxWidth::Large)
            ->deferLoading()
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();
        $role  = $user->role;
        $filterData = $role->filter_data ?? [];

        if ($role->filter_type === 'App\Models\BadanUsaha') {
            $query->whereIn('users.badanusaha_id', $filterData);
        } elseif ($role->filter_type === 'App\Models\Division') {
            $query->whereIn('users.divisi_id', $filterData);
        } elseif ($role->filter_type === 'App\Models\Region') {
            $query->whereIn('users.region_id', $filterData);
        } elseif ($role->filter_type === 'App\Models\Cluster') {
            $query->whereIn('users.cluster_id', $filterData);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
