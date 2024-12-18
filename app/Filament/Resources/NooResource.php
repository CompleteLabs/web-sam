<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NooResource\Pages;
use App\Filament\Resources\NooResource\RelationManagers;
use App\Models\Cluster;
use App\Models\Division;
use App\Models\Noo;
use App\Models\Region;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\HtmlString;

class NooResource extends Resource
{
    protected static ?string $model = Noo::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Data Outlet
                Forms\Components\Section::make('Data Outlet')
                    ->schema([
                        Forms\Components\TextInput::make('kode_outlet')
                            ->required()
                            ->regex('/^[\S]+$/', 'Kode outlet tidak boleh mengandung spasi')
                            ->helperText('Kode outlet tidak boleh mengandung spasi')
                            ->rule(function (callable $get) {
                                return function ($attribute, $value, $fail) use ($get) {
                                    $divisiId = $get('divisi_id'); // Retrieve badanusaha_id using $get
                                    $exists = \DB::table('outlets')
                                        ->where('kode_outlet', $value)
                                        ->where('divisi_id', $divisiId)
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
                            ->reactive()
                            ->label('Nama Outlet'),
                        Forms\Components\TextInput::make('distric')
                            ->required()
                            ->maxLength(255)
                            ->label('Distrik'),
                        Forms\Components\Textarea::make('alamat_outlet')
                            ->required()
                            ->columnSpanFull()
                            ->label('Alamat Outlet'),
                        Forms\Components\TextInput::make('nama_pemilik_outlet')
                            ->required()
                            ->maxLength(255)
                            ->label('Nama Pemilik Outlet'),
                        Forms\Components\TextInput::make('nomer_tlp_outlet')
                            ->required()
                            ->maxLength(255)
                            ->label('Nomor Telepon Outlet'),
                        Forms\Components\TextInput::make('nomer_wakil_outlet')
                            ->maxLength(255)
                            ->label('Nomor Wakil Outlet'),
                        Forms\Components\TextInput::make('ktp_outlet')
                            ->required()
                            ->maxLength(255)
                            ->label('KTP Pemilik Outlet'),

                    ])
                    ->columns(2), // Menggunakan dua kolom untuk tampilan lebih kompak

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

                // Foto dan Video
                Forms\Components\Section::make('Dokumentasi')
                    ->schema([
                        Forms\Components\FileUpload::make('poto_shop_sign')
                            ->required()
                            ->image()
                            ->disk('public')
                            ->label('Foto Tanda Toko')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return 'noo-' . $outletName . '-fotoshopsign-' . Carbon::now()->format('d-m-Y') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('poto_depan')
                            ->required()
                            ->image()
                            ->disk('public')
                            ->label('Foto Depan')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return 'noo-' . $outletName . '-fotodepan-' . Carbon::now()->format('d-m-Y') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('poto_kiri')
                            ->required()
                            ->image()
                            ->disk('public')
                            ->label('Foto Kiri')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return 'noo-' . $outletName . '-fotokiri-' . Carbon::now()->format('d-m-Y') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('poto_kanan')
                            ->required()
                            ->image()
                            ->disk('public')
                            ->label('Foto Kanan')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return 'noo-' . $outletName . '-fotokanan-' . Carbon::now()->format('d-m-Y') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('poto_ktp')
                            ->required()
                            ->image()
                            ->disk('public')
                            ->label('Foto KTP Pemilik')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return 'noo-' . $outletName . '-fotoktp-' . Carbon::now()->format('d-m-Y') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('video')
                            ->required()
                            ->disk('public')
                            ->label('Video Toko')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return 'noo-' . $outletName . '-video-' . Carbon::now()->format('d-m-Y') .  '.' . $file->getClientOriginalExtension();
                            }),
                    ])
                    ->columns(2),

                // Produk dan Merk
                Forms\Components\Section::make('Promotor dan Frontliner')
                    ->schema([
                        Forms\Components\TextInput::make('oppo')
                            ->required()
                            ->numeric()
                            ->label('Oppo'),
                        Forms\Components\TextInput::make('vivo')
                            ->required()
                            ->numeric()
                            ->label('Vivo'),
                        Forms\Components\TextInput::make('realme')
                            ->required()
                            ->numeric()
                            ->label('Realme'),
                        Forms\Components\TextInput::make('samsung')
                            ->required()
                            ->numeric()
                            ->label('Samsung'),
                        Forms\Components\TextInput::make('xiaomi')
                            ->required()
                            ->numeric()
                            ->label('Xiaomi'),
                        Forms\Components\TextInput::make('fl')
                            ->required()
                            ->numeric()
                            ->label('FL'),
                    ])
                    ->columns(2),

                // Informasi Tambahan
                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\TextInput::make('latlong')
                            ->required()
                            ->maxLength(255)
                            ->label('Koordinat Lat/Long'),
                        Forms\Components\TextInput::make('limit')
                            ->numeric()
                            ->label('Limit'),
                        Forms\Components\Select::make('status')
                            ->required()
                            ->label('Status')
                            ->options([
                                'PENDING' => 'PENDING',
                                'CONFIRMED' => 'CONFIRMED',
                                'APPROVED' => 'APPROVED',
                                'REJECTED' => 'REJECTED',
                            ])
                            ->placeholder('Pilih Status'),
                        Forms\Components\TextInput::make('created_by')
                            ->required()
                            ->maxLength(255)
                            ->label('Dibuat Oleh'),
                        Forms\Components\TextInput::make('keterangan')
                            ->maxLength(255)
                            ->label('Keterangan'),
                    ])
                    ->columns(2),

                // Tanggal dan Persetujuan
                Forms\Components\Section::make('Tanggal dan Persetujuan')
                    ->schema([
                        Forms\Components\DateTimePicker::make('rejected_at')
                            ->label('Tanggal Ditolak'),
                        Forms\Components\TextInput::make('rejected_by')
                            ->maxLength(255)
                            ->label('Ditolak Oleh'),
                        Forms\Components\DateTimePicker::make('confirmed_at')
                            ->label('Tanggal Dikonfirmasi'),
                        Forms\Components\TextInput::make('confirmed_by')
                            ->maxLength(255)
                            ->label('Dikonfirmasi Oleh'),
                        Forms\Components\DateTimePicker::make('approved_at')
                            ->label('Tanggal Disetujui'),
                        Forms\Components\TextInput::make('approved_by')
                            ->maxLength(255)
                            ->label('Disetujui Oleh'),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('TM')
                    ->schema([
                        Forms\Components\Select::make('tm_id')
                            ->relationship('tm', 'nama_lengkap')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->label('Nama TM'),
                    ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('created_by')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kode_outlet'),
                Tables\Columns\TextColumn::make('divisi.name'),
                Tables\Columns\TextColumn::make('badanusaha.name'),
                Tables\Columns\TextColumn::make('nama_outlet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat_outlet'),
                Tables\Columns\TextColumn::make('nama_pemilik_outlet'),
                Tables\Columns\TextColumn::make('ktp_outlet'),
                Tables\Columns\TextColumn::make('nomer_tlp_outlet'),
                Tables\Columns\TextColumn::make('nomer_wakil_outlet'),
                Tables\Columns\TextColumn::make('distric'),
                Tables\Columns\TextColumn::make('region.name'),
                Tables\Columns\TextColumn::make('cluster.name'),
                Tables\Columns\TextColumn::make('poto_ktp')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO KTP'))
                    ->url(fn($state): string => asset('storage/' . $state))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('poto_shop_sign')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->url(fn($state): string => asset('storage/' . $state))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('poto_depan')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->url(fn($state): string => asset('storage/' . $state))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('poto_kanan')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->url(fn($state): string => asset('storage/' . $state))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('poto_kiri')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->url(fn($state): string => asset('storage/' . $state))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('video')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('VIDEO'))
                    ->url(fn($state): string => asset('storage/' . $state))
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('oppo'),
                Tables\Columns\TextColumn::make('vivo'),
                Tables\Columns\TextColumn::make('realme'),
                Tables\Columns\TextColumn::make('samsung'),
                Tables\Columns\TextColumn::make('xiaomi'),
                Tables\Columns\TextColumn::make('fl'),
                Tables\Columns\TextColumn::make('latlong')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('LOKASI'))
                    ->url(fn($state): string => 'https://www.google.com/maps/place/' . $state)
                    ->openUrlInNewTab(),
                Tables\Columns\TextColumn::make('limit'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->deferLoading()
            ->filters([
                Tables\Filters\SelectFilter::make('divisi.name')
                    ->relationship('divisi', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Divisi'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'CONFIRMED' && $record->status !== 'REJECTED' && $record->status !== 'APPROVED' && Gate::allows('approve', $record))
                    ->form([
                        TextInput::make('kode_outlet')
                            ->regex('/^[\S]+$/', 'Kode outlet tidak boleh mengandung spasi')
                            ->helperText('Kode outlet tidak boleh mengandung spasi')
                            ->required(),
                        TextInput::make('limit')
                            ->numeric()
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $record->update([
                            'kode_outlet' => $data['kode_outlet'],
                            'limit' => $data['limit'],
                            'confirmed_at' => Carbon::now(),
                            'confirmed_by' => auth()->user()->name,
                            'status' => 'CONFIRMED',
                            Notification::make()
                                ->title($record->nama_outlet . ' Confirm')
                                ->success()
                                ->send(),
                        ]);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status !== 'CONFIRMED' && $record->status !== 'REJECTED' && $record->status !== 'APPROVED' && Gate::allows('reject', $record))
                    ->form([
                        Textarea::make('alasan')
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $record->update([
                            'confirmed_at' => Carbon::now(),
                            'confirmed_by' => auth()->user()->name,
                            'status' => 'REJECTED',
                            'keterangan' => $data['alasan'],
                        ]);
                        Notification::make()
                            ->title($record->nama_outlet . ' Rejected')
                            ->success()
                            ->send();
                    }),
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
                if ($user->role->name == 'SUPER ADMIN') {
                    return;
                } else {
                    $query->where('noos.badanusaha_id', $user->badanusaha_id);
                }
            })
            ->where(function ($query) {
                $query->whereNull('keterangan')
                    ->orWhere('keterangan', '!=', 'LEAD');
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNoos::route('/'),
            'create' => Pages\CreateNoo::route('/create'),
            'edit' => Pages\EditNoo::route('/{record}/edit'),
        ];
    }
}
