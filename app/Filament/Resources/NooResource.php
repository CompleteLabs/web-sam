<?php

namespace App\Filament\Resources;

use App\Filament\Concerns\DynamicAttributes;
use App\Filament\Resources\NooResource\Pages;
use App\Filament\Resources\NooResource\RelationManagers;
use App\Models\Noo;
use App\Models\Outlet;
use App\Services\OrganizationalStructureService;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Tabs\Tab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;

class NooResource extends Resource
{

    use DynamicAttributes;

    protected static ?string $model = Noo::class;
    protected static ?string $navigationLabel = 'NOO';
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Outlet')
                    ->schema([
                        Forms\Components\TextInput::make('kode_outlet')
                            ->required()
                            ->regex('/^[\S]+$/', 'Kode outlet tidak boleh mengandung spasi')
                            ->helperText('Kode outlet tidak boleh mengandung spasi')
                            ->rule(function (callable $get) {
                                return function ($attribute, $value, $fail) use ($get) {
                                    $divisiId = $get('divisi_id');
                                    $outletId = $get('id');
                                    $exists = DB::table('outlets')
                                        ->where('kode_outlet', $value)
                                        ->where('divisi_id', $divisiId)
                                        ->where('id', '!=', $outletId)
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
                                $organizationalStructureService = new OrganizationalStructureService();
                                return $organizationalStructureService->getBadanUsahaOptions();
                            })
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('divisi_id', null);
                                $set('region_id', null);
                                $set('cluster_id', null);
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
                                $organizationalStructureService = new OrganizationalStructureService();
                                return $organizationalStructureService->getDivisiOptions($badanusahaId);
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
                                $organizationalStructureService = new OrganizationalStructureService();
                                return $organizationalStructureService->getRegionOptions($divisiId);
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
                                $organizationalStructureService = new OrganizationalStructureService();
                                return $organizationalStructureService->getClusterOptions($regionId);
                            }),
                    ])
                    ->collapsible()
                    ->columns(2),

                Forms\Components\Section::make('Custom Attributes')
                    ->schema(function (callable $get, $record) {
                        $badanusahaId = $get('badanusaha_id');
                        $divisiId = $get('divisi_id');
                        $entityId = $record?->id;

                        if ($badanusahaId && $divisiId) {
                            $attributesBadanUsaha = static::dynamicAttributesSchema(
                                'App\Models\Noo',
                                'App\Models\BadanUsaha',
                                $badanusahaId,
                                $entityId
                            );
                            $attributesDivisi = static::dynamicAttributesSchema(
                                'App\Models\Noo',
                                'App\Models\Division',
                                $divisiId,
                                $entityId
                            );
                            return array_merge($attributesBadanUsaha, $attributesDivisi);
                        } elseif ($badanusahaId) {
                            return static::dynamicAttributesSchema(
                                'App\Models\Noo',
                                'App\Models\BadanUsaha',
                                $badanusahaId,
                                $entityId
                            );
                        } elseif ($divisiId) {
                            return static::dynamicAttributesSchema(
                                'App\Models\Noo',
                                'App\Models\Division',
                                $divisiId,
                                $entityId
                            );
                        }
                        return [];
                    })
                    ->collapsible()
                    ->columns(2),

                Forms\Components\Section::make('Dokumentasi')
                    ->schema([
                        Forms\Components\FileUpload::make('poto_shop_sign')
                            ->required()
                            ->image()
                            ->disk('public')
                            ->resize(30)
                            ->label('Foto Tanda Toko')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return 'noo-' . $outletName . '-fotoshopsign-' . Carbon::now()->format('dmYHis') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('poto_depan')
                            ->required()
                            ->image()
                            ->disk('public')
                            ->resize(30)
                            ->label('Foto Depan')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return 'noo-' . $outletName . '-fotodepan-' . Carbon::now()->format('dmYHis') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('poto_kiri')
                            ->required()
                            ->image()
                            ->disk('public')
                            ->resize(30)
                            ->label('Foto Kiri')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return 'noo-' . $outletName . '-fotokiri-' . Carbon::now()->format('dmYHis') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('poto_kanan')
                            ->required()
                            ->image()
                            ->disk('public')
                            ->resize(30)
                            ->label('Foto Kanan')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return 'noo-' . $outletName . '-fotokanan-' . Carbon::now()->format('dmYHis') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('poto_ktp')
                            ->required()
                            ->image()
                            ->resize(30)
                            ->disk('public')
                            ->label('Foto KTP Pemilik')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return 'noo-' . $outletName . '-fotoktp-' . Carbon::now()->format('dmYHis') .  '.' . $file->getClientOriginalExtension();
                            }),
                        Forms\Components\FileUpload::make('video')
                            ->required()
                            ->disk('public')
                            ->label('Video Toko')
                            ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) {
                                $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                                return 'noo-' . $outletName . '-video-' . Carbon::now()->format('dmYHis') .  '.' . $file->getClientOriginalExtension();
                            }),
                    ])
                    ->collapsible()
                    ->columns(2),

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
                    ->collapsible()
                    ->columns(2),

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
                    ->collapsible()
                    ->columns(2),

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
                    ->collapsible()
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
                    ->label('Tanggal Dibuat')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_by')
                    ->label('Dibuat Oleh')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('kode_outlet')
                    ->label('Kode Outlet')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('divisi.name')
                    ->label('Divisi')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('badanusaha.name')
                    ->label('Badan Usaha')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('nama_outlet')
                    ->label('Nama Outlet')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('alamat_outlet')
                    ->label('Alamat Outlet')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('nama_pemilik_outlet')
                    ->label('Nama Pemilik Outlet')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('ktp_outlet')
                    ->label('Nomor KTP Outlet')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('nomer_tlp_outlet')
                    ->label('Nomor Telepon Outlet')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('nomer_wakil_outlet')
                    ->label('Nomor Wakil Outlet')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('distric')
                    ->label('Distrik')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('region.name')
                    ->label('Region')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('cluster.name')
                    ->label('Cluster')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('poto_ktp')
                    ->label('Foto KTP')
                    ->color('primary')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('KTP'))
                    ->url(fn($state): string => asset('storage/' . $state), shouldOpenInNewTab: true)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('poto_shop_sign')
                    ->label('Foto Tanda Outlet')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->color('primary')
                    ->url(fn($state): string => asset('storage/' . $state), shouldOpenInNewTab: true)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('poto_depan')
                    ->label('Foto Depan')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->color('primary')
                    ->url(fn($state): string => asset('storage/' . $state), shouldOpenInNewTab: true)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('poto_kanan')
                    ->label('Foto Kanan')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->color('primary')
                    ->url(fn($state): string => asset('storage/' . $state), shouldOpenInNewTab: true)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('poto_kiri')
                    ->label('Foto Kiri')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('FOTO'))
                    ->color('primary')
                    ->url(fn($state): string => asset('storage/' . $state), shouldOpenInNewTab: true)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('video')
                    ->label('Video Outlet')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('VIDEO'))
                    ->color('primary')
                    ->url(fn($state): string => asset('storage/' . $state), shouldOpenInNewTab: true)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('oppo')
                    ->label('Oppo')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('vivo')
                    ->label('Vivo')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('realme')
                    ->label('Realme')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('samsung')
                    ->label('Samsung')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('xiaomi')
                    ->label('Xiaomi')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('fl')
                    ->label('Frontliner')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('latlong')
                    ->label('Lokasi (LatLong)')
                    ->formatStateUsing(fn(string $state): HtmlString => new HtmlString('LOKASI'))
                    ->color('primary')
                    ->url(fn($state): string => 'https://www.google.com/maps/place/' . $state, shouldOpenInNewTab: true)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('limit')
                    ->label('Limit')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->deferLoading()
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
                    ->hidden(fn() => !Gate::any(['restore_any_visit', 'force_delete_any_visit'], Noo::class)),
            ], layout: FiltersLayout::Modal)
            ->filtersFormWidth(MaxWidth::Large)
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('confirm')
                    ->label('Confirm')
                    ->icon('heroicon-o-check-circle')
                    ->color('primary')
                    ->visible(fn($record) => $record->status === 'PENDING' && Gate::allows('confirm', $record))
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
                            'confirmed_by' => auth()->user()->nama_lengkap,
                            'status' => 'CONFIRMED',
                            Notification::make()
                                ->title($record->nama_outlet . ' Confirm')
                                ->success()
                                ->send(),
                        ]);
                    }),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation() // Menampilkan modal konfirmasi
                    ->modalHeading('Konfirmasi Persetujuan')
                    ->modalSubheading('Apakah Anda yakin ingin mengapprove record ini?')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'CONFIRMED' && Gate::allows('approve', $record))
                    ->action(function ($record, $data) {
                        $record->update([
                            'approved_at' => Carbon::now(),
                            'approved_by' => auth()->user()->nama_lengkap,
                            'status' => 'APPROVED',
                            Notification::make()
                                ->title($record->nama_outlet . ' Approved')
                                ->success()
                                ->send(),
                        ]);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status !== 'REJECTED' && $record->status !== 'APPROVED' && Gate::allows('reject', $record))
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
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('createOutlets')
                        ->label('Create Outlets')
                        ->icon('heroicon-o-plus-circle')
                        ->visible(fn() => Auth::user()->role->name === 'SUPER ADMIN')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $data = [
                                    'kode_outlet' => 'LEAD' . $record->id,
                                    'nama_outlet' => $record->nama_outlet,
                                    'alamat_outlet' => $record->alamat_outlet,
                                    'nama_pemilik_outlet' => $record->nama_pemilik_outlet,
                                    'nomer_tlp_outlet' => $record->nomer_tlp_outlet,
                                    'badanusaha_id' => $record->badanusaha_id,
                                    'divisi_id' => $record->divisi_id,
                                    'region_id' => $record->region_id,
                                    'cluster_id' => $record->cluster_id,
                                    'distric' => $record->distric,
                                    'poto_shop_sign' => $record->poto_shop_sign,
                                    'poto_depan' => $record->poto_depan,
                                    'poto_kanan' => $record->poto_kanan,
                                    'poto_kiri' => $record->poto_kiri,
                                    'poto_ktp' => $record->poto_ktp,
                                    'video' => $record->video,
                                    'limit' => $record->limit ?? 0,
                                    'radius' => 100,
                                    'latlong' => $record->latlong,
                                    'status_outlet' => 'MAINTAIN',
                                    'is_member' => '0',
                                ];
                                Outlet::create($data);
                            }
                            Notification::make()
                                ->title('Outlets created successfully!')
                                ->success()
                                ->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                // Section Informasi Outlet
                Tabs::make('Informasi')
                    ->tabs([
                        Tab::make('Informasi Outlet')
                            ->schema([
                                TextEntry::make('kode_outlet')->label('Kode Outlet'),
                                TextEntry::make('nama_outlet')->label('Nama Outlet'),
                                TextEntry::make('distric')->label('Distrik'),
                                TextEntry::make('alamat_outlet')->label('Alamat Outlet'),
                                TextEntry::make('nama_pemilik_outlet')->label('Nama Pemilik Outlet'),
                                TextEntry::make('nomer_tlp_outlet')->label('Nomor Telepon Outlet'),
                                TextEntry::make('nomer_wakil_outlet')->label('Nomor Wakil Outlet'),
                                TextEntry::make('ktp_outlet')->label('KTP Pemilik Outlet'),
                                TextEntry::make('tm.nama_lengkap')->label('Nama TM'),
                            ])
                            ->columns(2),
                        Tab::make('Promotor dan Frontliner')
                            ->schema([
                                TextEntry::make('oppo')->label('Oppo'),
                                TextEntry::make('vivo')->label('Vivo'),
                                TextEntry::make('realme')->label('Realme'),
                                TextEntry::make('samsung')->label('Samsung'),
                                TextEntry::make('xiaomi')->label('Xiaomi'),
                                TextEntry::make('fl')->label('FL'),
                            ])
                            ->columns(3),
                        Tab::make('Dokumentasi Outlet')
                            ->schema([
                                ImageEntry::make('poto_shop_sign')
                                    ->label('Foto Tanda Toko')
                                    ->height(40)
                                    ->width(100)
                                    ->url(fn($state): string => asset('storage' . $state), shouldOpenInNewTab: true),
                                ImageEntry::make('poto_depan')
                                    ->label('Foto Depan')
                                    ->height(40)
                                    ->width(100)
                                    ->url(fn($state): string => asset('storage' . $state), shouldOpenInNewTab: true),
                                ImageEntry::make('poto_kiri')
                                    ->label('Foto Kiri')
                                    ->height(40)
                                    ->width(100)
                                    ->url(fn($state): string => asset('storage' . $state), shouldOpenInNewTab: true),
                                ImageEntry::make('poto_kanan')
                                    ->label('Foto Kanan')
                                    ->height(40)
                                    ->width(100)
                                    ->url(fn($state): string => asset('storage' . $state), shouldOpenInNewTab: true),
                                ImageEntry::make('poto_ktp')
                                    ->label('Foto KTP Pemilik')
                                    ->height(40)
                                    ->width(100)
                                    ->url(fn($state): string => asset('storage' . $state), shouldOpenInNewTab: true),
                                ImageEntry::make('video')
                                    ->label('Video Outlet')
                                    ->height(40)
                                    ->width(100)
                                    ->url(fn($state): string => asset('storage' . $state), shouldOpenInNewTab: true),
                            ])
                            ->columns(3),
                        Tab::make('Persetujuan & Tanggal')
                            ->schema([
                                TextEntry::make('rejected_at')
                                    ->label('Tanggal Ditolak')
                                    ->hidden(fn($record) => empty($record->rejected_at)),
                                TextEntry::make('rejected_by')
                                    ->label('Ditolak Oleh')
                                    ->hidden(fn($record) => empty($record->rejected_by)),
                                TextEntry::make('confirmed_at')
                                    ->label('Tanggal Dikonfirmasi')
                                    ->hidden(fn($record) => empty($record->confirmed_at)),
                                TextEntry::make('confirmed_by')
                                    ->label('Dikonfirmasi Oleh')
                                    ->hidden(fn($record) => empty($record->confirmed_by)),
                                TextEntry::make('approved_at')
                                    ->label('Tanggal Disetujui')
                                    ->hidden(fn($record) => empty($record->approved_at)),
                                TextEntry::make('approved_by')
                                    ->label('Disetujui Oleh')
                                    ->hidden(fn($record) => empty($record->approved_by)),
                            ])
                            ->columns(2),
                    ])
                    ->columns(2)
                    ->columnSpan(2),
                Section::make('Badan Usaha & Divisi')
                    ->schema([
                        TextEntry::make('badanusaha.name')->label('Badan Usaha'),
                        TextEntry::make('divisi.name')->label('Divisi'),
                        TextEntry::make('region.name')->label('Region'),
                        TextEntry::make('cluster.name')->label('Cluster'),
                    ])
                    ->collapsible()
                    ->columns(1)
                    ->columnSpan(1),
                Section::make('Custom Attribute Values')
                    ->schema([
                        Grid::make(2)
                            ->schema(function ($record) {
                                return $record->customAttributeValues->map(function ($attributeValue) {
                                    $value = $attributeValue->value;
                                    $label = optional($attributeValue->attribute)->label ?? 'Unknown Attribute';
                                    $imageExtensions = ['png', 'jpg', 'jpeg', 'gif', 'bmp', 'svg'];
                                    $extension = strtolower(pathinfo($value, PATHINFO_EXTENSION));
                                    if (in_array($extension, $imageExtensions)) {
                                        $linkHtml = "<a href='/{$value}' target='_blank'>Lihat Gambar</a>";
                                        return TextEntry::make("custom_attribute_{$attributeValue->custom_attribute_id}")
                                            ->label($label)
                                            ->state($linkHtml)
                                            ->html();
                                    }
                                    return TextEntry::make("custom_attribute_{$attributeValue->custom_attribute_id}")
                                        ->label($label)
                                        ->state($value);
                                })->toArray();
                            }),
                    ])
                    ->collapsible()
                    ->columns(3)
                    ->hidden(fn($record) => $record->customAttributeValues->isEmpty())
                    ->columnSpan(2),
                Section::make('Status & Keterangan')
                    ->schema([
                        TextEntry::make('status')->label('Status'),
                        TextEntry::make('created_by')->label('Dibuat Oleh'),
                        TextEntry::make('keterangan')->label('Keterangan'),
                    ])
                    ->collapsible()
                    ->columns(3)
                    ->columnSpan(2),
            ])->columns(3);
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

        if ($role->filter_type === 'badanusaha') {
            $query->whereIn('noos.badanusaha_id', $filterData);
        } elseif ($role->filter_type === 'divisi') {
            $query->whereIn('noos.divisi_id', $filterData);
        } elseif ($role->filter_type === 'region') {
            $query->whereIn('noos.region_id', $filterData);
        } elseif ($role->filter_type === 'cluster') {
            $query->whereIn('noos.cluster_id', $filterData);
        }

        $query->where(function ($q) {
            $q->whereNull('keterangan')
                ->orWhere('keterangan', '!=', 'LEAD');
        });

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNoos::route('/'),
            'create' => Pages\CreateNoo::route('/create'),
            'view' => Pages\ViewNoo::route('/{record}'),
            'edit' => Pages\EditNoo::route('/{record}/edit'),
        ];
    }
}
