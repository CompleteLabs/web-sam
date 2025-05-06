<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitResource\Pages;
use App\Filament\Resources\VisitResource\RelationManagers;
use App\Models\Outlet;
use App\Models\User;
use App\Models\Visit;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\UploadedFile;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

use function Laravel\Prompts\search;

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;
    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Visit Information')
                    ->schema([
                        Forms\Components\DateTimePicker::make('tanggal_visit')
                        ->default(\Carbon\Carbon::parse(now())->startOfDay()) // Setel waktu ke 00:00:00
                        ->required()
                        ->label('Tanggal Visit'),                                      
                        Forms\Components\Select::make('tipe_visit')
                            ->options([
                                'PLANNED' => 'PLANNED',
                                'EXTRACALL' => 'EXTRACALL',
                            ])
                            ->required()
                            ->label('Tipe Visit')
                            ->placeholder('Select a type')
                            ->searchable(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('User and Outlet')
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
                    ->columns(2),
                Forms\Components\Section::make('Location & Timing')
                    ->schema([
                        Forms\Components\TextInput::make('latlong_in')
                            ->maxLength(255)
                            ->label('LatLong In')
                            ->placeholder('Latitude and Longitude at the start'),
                        Forms\Components\TextInput::make('latlong_out')
                            ->maxLength(255)
                            ->label('LatLong Out')
                            ->placeholder('Latitude and Longitude at the end')
                            ->visible(fn(string $context): bool => $context === 'edit'),
                        Forms\Components\DateTimePicker::make('check_in_time')
                            ->label('Check-in Time'),
                        Forms\Components\DateTimePicker::make('check_out_time')
                            ->label('Check-out Time')
                            ->visible(fn(string $context): bool => $context === 'edit'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Files')
                    ->schema([
                        Forms\Components\FileUpload::make('picture_visit_in')
                            ->image()
                            ->columnSpanFull()
                            ->required()
                            ->disk('public')
                            ->resize(30)
                            ->label('Picture at Start of Visit')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $user = User::find($get('user_id'));
                                $username = $user ? $user->username : 'vacant';
                                return Carbon::now()->format('Y-m-d') . '-' . $username . '-IN-' . Carbon::now()->getPreciseTimestamp(3) . '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('picture_visit_out')
                            ->image()
                            ->columnSpanFull()
                            // ->required()
                            ->disk('public')
                            ->resize(30)
                            ->label('Picture at End of Visit')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $user = User::find($get('user_id'));
                                $username = $user ? $user->username : 'vacant';
                                return Carbon::now()->format('Y-m-d') . '-' . $username . '-OUT-' . Carbon::now()->getPreciseTimestamp(3) . '.' . $file->getClientOriginalExtension();
                            })
                            ->visible(fn(string $context): bool => $context === 'edit'),
                    ]),
                Forms\Components\Section::make('Transaction Information')
                    ->schema([
                        Forms\Components\Select::make('transaksi')
                            ->label('Transaksi')
                            ->options([
                                'YES' => 'YES',
                                'NO' => 'NO',
                            ])
                            // ->required()
                            ->placeholder('Select Yes or No')
                            ->searchable(),
                        Forms\Components\Textarea::make('laporan_visit')
                            ->columnSpanFull()
                            ->label('Laporan Visit'),
                    ])
                    ->visible(fn(string $context): bool => $context === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_visit')
                    ->label('Tanggal Visit')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('user.nama_lengkap')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('outlet.nama_outlet')
                    ->label('Nama Outlet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipe_visit')
                    ->label('Tipe Visit'),
                Tables\Columns\TextColumn::make('latlong_in')
                    ->label('Lokasi Check-In')
                    ->color('primary')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('LOKASI'))
                    ->url(fn($state): string => 'https://www.google.com/maps/place/' . $state, shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make('latlong_out')
                    ->label('Lokasi Check-Out')
                    ->color('primary')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('LOKASI'))
                    ->url(fn($state): string => 'https://www.google.com/maps/place/' . $state, shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Jam Check-In')
                    ->time(),
                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('Jam Check-Out')
                    ->time(),
                Tables\Columns\TextColumn::make('picture_visit_in')
                    ->label('Foto Check-In')
                    ->color('primary')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->url(fn($state): string => asset('storage/' . $state), shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make('picture_visit_out')
                    ->label('Foto Check-Out')
                    ->color('primary')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->url(fn($state): string => asset('storage/' . $state), shouldOpenInNewTab: true),
                Tables\Columns\TextColumn::make('transaksi')
                    ->label('Transaksi'),
                Tables\Columns\TextColumn::make('durasi_visit')
                    ->label('Durasi Visit'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->deferLoading()
            ->filters([
                Tables\Filters\TrashedFilter::make()
                    ->hidden(fn() => !Gate::any(['restore_any_visit', 'force_delete_any_visit'], Visit::class)),
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('tanggal_visit_from')
                            ->label('Tanggal Visit Mulai'),
                        DatePicker::make('tanggal_visit_until')
                            ->label('Tanggal Visit Akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_visit_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_visit', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_visit_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_visit', '<=', $date),
                            );
                    })

            ])
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

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $role = $user->role;

        if ($role->filter_type === 'all') {
            return parent::getEloquentQuery();
        }

        $query = parent::getEloquentQuery()
            ->join('users', 'visits.user_id', '=', 'users.id')
            ->select('visits.*', 'users.id as user_id')
            ->when($role->filter_type === 'badanusaha', function ($query) use ($user) {
                $query->where('users.badanusaha_id', $user->badanusaha_id);
            })
            ->when($role->filter_type === 'divisi', function ($query) use ($role) {
                $query->whereIn('users.divisi_id', $role->filter_data ?? []);
            })
            ->when($role->filter_type === 'region', function ($query) use ($role) {
                $query->whereIn('users.region_id', $role->filter_data ?? []);
            })
            ->when($role->filter_type === 'cluster', function ($query) use ($role) {
                $query->whereIn('users.cluster_id', $role->filter_data ?? []);
            });

        return $query;
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
            'index' => Pages\ListVisits::route('/'),
            'create' => Pages\CreateVisit::route('/create'),
            'edit' => Pages\EditVisit::route('/{record}/edit'),
        ];
    }
}
