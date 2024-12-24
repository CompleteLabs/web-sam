<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutletResource\Pages;
use App\Filament\Resources\OutletResource\RelationManagers;
use App\Models\Cluster;
use App\Models\Division;
use App\Models\Outlet;
use App\Models\Region;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\HtmlString;

class OutletResource extends Resource
{
    protected static ?string $model = Outlet::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Outlet')
                    ->schema([
                        Forms\Components\TextInput::make('kode_outlet')
                            ->required()
                            ->regex('/^[\S]+$/', 'Kode outlet tidak boleh mengandung spasi')
                            ->helperText('Kode outlet tidak boleh mengandung spasi')
                            ->rule(function (callable $get) {
                                return function ($attribute, $value, $fail) use ($get) {
                                    $divisiId = $get('divisi_id'); // Retrieve badanusaha_id using $get
                                    $outletId = $get('id'); // Ambil id outlet untuk proses edit (pastikan field ini tersedia)
                                    // Cek apakah kode_outlet sudah digunakan di divisi yang sama, kecuali oleh outlet ini sendiri
                                    $exists = \DB::table('outlets')
                                        ->where('kode_outlet', $value)
                                        ->where('divisi_id', $divisiId)
                                        ->where('id', '!=', $outletId) // Abaikan data ini sendiri jika dalam mode edit
                                        ->where('deleted_at', null)
                                        ->exists();
                                    if ($exists) {
                                        $fail(__('Kode Outlet sudah digunakan untuk divisi ini.'));
                                    }
                                };
                            })
                            ->maxLength(255)
                            ->label('Kode Outlet')
                            ->placeholder('Masukkan kode outlet'),
                        Forms\Components\TextInput::make('nama_outlet')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Outlet')
                            ->placeholder('Masukkan nama outlet'),
                        Forms\Components\TextInput::make('distric')
                            ->required()
                            ->maxLength(255)
                            ->label('Distrik')
                            ->placeholder('Masukkan distrik outlet'),
                        Forms\Components\TextInput::make('latlong')
                            ->maxLength(255)
                            ->label('Latitude/Longitude')
                            ->placeholder('Masukkan koordinat latitude dan longitude outlet'),
                        Forms\Components\Textarea::make('alamat_outlet')
                            ->required()
                            ->columnSpanFull()
                            ->label('Alamat Outlet')
                            ->placeholder('Masukkan alamat lengkap outlet'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Kontak & Pemilik Outlet')
                    ->schema([
                        Forms\Components\TextInput::make('nama_pemilik_outlet')
                            ->maxLength(255)
                            ->label('Nama Pemilik Outlet')
                            ->placeholder('Masukkan nama pemilik outlet'),
                        Forms\Components\TextInput::make('nomer_tlp_outlet')
                            ->maxLength(255)
                            ->label('Nomor Telepon Outlet')
                            ->placeholder('Masukkan nomor telepon outlet'),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Foto & Video')
                    ->schema([
                        Forms\Components\FileUpload::make('poto_shop_sign')
                            ->image()
                            ->disk('public')
                            ->resize(30)
                            ->label('Foto Tanda Toko')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return $outletName . '-fotoshopsign-' . Carbon::now()->format('dmYHis') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('poto_depan')
                            ->image()
                            ->disk('public')
                            ->resize(30)
                            ->label('Foto Depan')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return $outletName . '-fotodepan-' . Carbon::now()->format('dmYHis') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('poto_kiri')
                            ->image()
                            ->disk('public')
                            ->resize(30)
                            ->label('Foto Kiri')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return $outletName . '-fotokiri-' . Carbon::now()->format('dmYHis') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('poto_kanan')
                            ->image()
                            ->disk('public')
                            ->resize(30)
                            ->label('Foto Kanan')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return $outletName . '-fotokanan-' . Carbon::now()->format('dmYHis') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('poto_ktp')
                            ->image()
                            ->disk('public')
                            ->resize(30)
                            ->label('Foto KTP Pemilik')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return $outletName . '-fotoktp-' . Carbon::now()->format('dmYHis') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('video')
                            ->disk('public')
                            ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mkv'])
                            ->label('Video Toko')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return $outletName . '-video-' . Carbon::now()->format('dmYHis') .  '.' . $file->getClientOriginalExtension();
                            }),
                    ])
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
                            ->options(function (callable $get) {
                                $regionId = $get('region_id');
                                if (!$regionId) {
                                    return [];
                                }
                                return Cluster::where('region_id', $regionId)
                                    ->pluck('name', 'id');
                            }),
                    ])
                    ->columns(2), // Menyusun dropdown dalam dua kolom

                // Grup Status & Limit Outlet
                Forms\Components\Section::make('Status & Limit Outlet')
                    ->schema([
                        Forms\Components\Select::make('status_outlet')
                            ->label('Status Outlet')
                            ->searchable()
                            ->options([
                                'MAINTAIN' => 'MAINTAIN',
                                'UNMAINTAIN' => 'UNMAINTAIN',
                                'UNPRODUCTIVE' => 'UNPRODUCTIVE',
                            ])
                            ->required(),

                        Forms\Components\TextInput::make('is_member')
                            ->label('Is Member')
                            ->default('1')
                            ->required()
                            ->numeric()
                            ->readonly(),

                        Forms\Components\TextInput::make('limit')
                            ->numeric()
                            ->label('Limit')
                            ->placeholder('Masukkan limit outlet'),

                        Forms\Components\TextInput::make('radius')
                            ->numeric()
                            ->label('Radius')
                            ->placeholder('Masukkan radius outlet'),
                    ])
                    ->columns(4), // Menyusun status dan limit dalam dua kolom
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_outlet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('badanusaha.name'),
                Tables\Columns\TextColumn::make('divisi.name'),
                Tables\Columns\TextColumn::make('region.name'),
                Tables\Columns\TextColumn::make('cluster.name'),
                Tables\Columns\TextColumn::make('nama_outlet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_pemilik_outlet'),
                Tables\Columns\TextColumn::make('nomer_tlp_outlet'),
                Tables\Columns\TextColumn::make('distric'),
                Tables\Columns\TextColumn::make('poto_shop_sign')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->url(fn($state): string => asset('storage/' . $state))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('poto_depan')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->url(fn($state): string => asset('storage/' . $state))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('poto_kiri')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->url(fn($state): string => asset('storage/' . $state))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('poto_kanan')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->url(fn($state): string => asset('storage/' . $state))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('poto_ktp')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO KTP'))
                    ->url(fn($state): string => asset('storage/' . $state))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('video')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('VIDEO'))
                    ->url(fn($state): string => asset('storage/' . $state))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('limit'),
                Tables\Columns\TextColumn::make('radius'),
                Tables\Columns\TextColumn::make('latlong')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('LOKASI'))
                    ->url(fn($state): string => 'https://www.google.com/maps/place/' . $state)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('status_outlet'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('kode_outlet', 'asc')
            ->deferLoading()
            ->filters([
                Tables\Filters\SelectFilter::make('region.name')
                    ->relationship('region', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Region'),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where(function ($query) {
                $user = auth()->user();
                // Display all tickets to Super Admin
                if ($user->role->name == 'SUPER ADMIN') {
                    return;
                } else {
                    $query->where('outlets.badanusaha_id', $user->badanusaha_id);
                }
            });
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
            'index' => Pages\ListOutlets::route('/'),
            'create' => Pages\CreateOutlet::route('/create'),
            'edit' => Pages\EditOutlet::route('/{record}/edit'),
        ];
    }
}
