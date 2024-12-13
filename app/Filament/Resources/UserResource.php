<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Cluster;
use App\Models\Division;
use App\Models\Region;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
                            ->dehydrateStateUsing(fn($state) => strtoupper($state))
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Organization Information')
                    ->schema([
                        Forms\Components\Select::make('badanusaha_id')
                            ->label('Badan Usaha')
                            ->searchable()
                            ->required()
                            ->reactive()
                            ->placeholder('Pilih badan usaha')
                            ->options(function (callable $get) {
                                $user = auth()->user();
                                if ($user->role->name !== 'SUPER ADMIN') {
                                    return \App\Models\BadanUsaha::where('id', $user->badanusaha_id)
                                        ->pluck('name', 'id');
                                }
                                return \App\Models\BadanUsaha::pluck('name', 'id');
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
                                return Division::where('badanusaha_id', $badanusahaId)
                                    ->pluck('name', 'id');
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
                                return Region::where('divisi_id', $divisiId)
                                    ->pluck('name', 'id');
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
                                return Cluster::where('region_id', $regionId)
                                    ->pluck('name', 'id');
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
                                    ->pluck('name', 'id');
                            }),
                    ])
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
                                    return \App\Models\Role::whereIn('name', ['AR', 'ASC', 'ASM', 'DSF/DM'])
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
                // Tables\Columns\TextColumn::make('username')
                //     ->searchable(),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $user = auth()->user();
                // Display all tickets to Super Admin
                if ($user->role->name == 'SUPER ADMIN') {
                    return;
                } else {
                    $query->where('users.badanusaha_id', $user->badanusaha_id);
                }
            });
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
