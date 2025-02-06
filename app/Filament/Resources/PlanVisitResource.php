<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanVisitResource\Pages;
use App\Filament\Resources\PlanVisitResource\RelationManagers;
use App\Models\Outlet;
use App\Models\PlanVisit;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Routing\Route;

class PlanVisitResource extends Resource
{
    protected static ?string $model = PlanVisit::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('User Information')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->label('Pilih User')
                            ->placeholder('Cari User berdasarkan nama lengkap')
                            ->options(function () {
                                $users = User::with(['badanusaha', 'divisi'])->get();

                                return $users->mapWithKeys(function ($user) {
                                    $badanusahaName = $user->badanusaha ? $user->badanusaha->name : 'Tidak ada badan usaha';
                                    $divisiName = $user->divisi ? $user->divisi->name : 'Tidak ada divisi';
                                    return [$user->id => "{$user->nama_lengkap} - {$badanusahaName} / {$divisiName}"];
                                });
                            }),
                        Forms\Components\Select::make('outlet_id')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Pilih Outlet')
                            ->options(function () {
                                // Eager load badanusaha dan divisi
                                $outlets = Outlet::with(['badanusaha', 'divisi'])->get();

                                return $outlets->mapWithKeys(function ($outlet) {
                                    // Menggabungkan nama outlet, badan usaha, dan divisi untuk label
                                    $badanusahaName = $outlet->badanusaha ? $outlet->badanusaha->name : 'Tidak ada badan usaha';
                                    $divisiName = $outlet->divisi ? $outlet->divisi->name : 'Tidak ada divisi';
                                    return [$outlet->id => "[{$outlet->kode_outlet}] {$outlet->nama_outlet} - {$badanusahaName} / {$divisiName}"];
                                });
                            }),
                    ])
                    ->collapsible()
                    ->columns(2),
                Forms\Components\Section::make('Visit Details')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_visit')
                            ->native(false)
                            ->required()
                            ->label('Tanggal Visit')
                            ->placeholder('Pilih tanggal dan waktu visit')
                            ->helperText('Tanggal dan waktu kunjungan akan dicatat di sini'),
                    ])
                    ->collapsible()
                    ->columns(1),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.nama_lengkap')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('outlet.nama_outlet')
                    ->label('Outlet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('outlet.kode_outlet'),
                Tables\Columns\TextColumn::make('tanggal_visit')
                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::createFromTimestamp($state / 1000)->format('d M Y') : '-'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_visit', 'desc')
            ->deferLoading()
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
        $user = auth()->user();
        $role = $user->role;

        if ($role->filter_type === null) {
            return parent::getEloquentQuery();
        }

        $query = parent::getEloquentQuery()
            ->leftJoin('users', 'plan_visits.user_id', '=', 'users.id')
            ->select('plan_visits.*', 'users.id as user_id')
            ->when($role->filter_type === 'App\Models\BadanUsaha', function ($query) use ($user) {
                $query->where('users.badanusaha_id', $user->badanusaha_id);
            })
            ->when($role->filter_type === 'App\Models\Division', function ($query) use ($role) {
                $query->whereIn('users.divisi_id', $role->filter_data ?? []);
            })
            ->when($role->filter_type === 'App\Models\Region', function ($query) use ($role) {
                $query->whereIn('users.region_id', $role->filter_data ?? []);
            })
            ->when($role->filter_type === 'App\Models\Cluster', function ($query) use ($role) {
                $query->whereIn('users.cluster_id', $role->filter_data ?? []);
            });

        return $query;
    }

    public static function getRecordId(): null|string
    {
        return Route::current()->parameter('record');
    }

    public static function resolveRecordRouteBinding(int|string $key): ?PlanVisit
    {
        return self::getEloquentQuery()->where('plan_visits.id', $key)->first();
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlanVisits::route('/'),
            'create' => Pages\CreatePlanVisit::route('/create'),
            'edit' => Pages\EditPlanVisit::route('/{record}/edit'),
        ];
    }
}
