<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutletResource\Pages;
use App\Filament\Resources\OutletResource\RelationManagers;
use App\Models\Cluster;
use App\Models\Division;
use App\Models\Outlet;
use App\Models\Region;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OutletResource extends Resource
{
    protected static ?string $model = Outlet::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            // Grup Informasi Outlet
            Forms\Components\Section::make('Informasi Outlet')
                ->schema([
                    Forms\Components\TextInput::make('kode_outlet')
                        ->required()
                        ->maxLength(255)
                        ->label('Kode Outlet')
                        ->placeholder('Masukkan kode outlet'),
                    Forms\Components\TextInput::make('nama_outlet')
                        ->required()
                        ->maxLength(255)
                        ->label('Nama Outlet')
                        ->placeholder('Masukkan nama outlet'),
                    Forms\Components\Textarea::make('alamat_outlet')
                        ->required()
                        ->columnSpanFull()
                        ->label('Alamat Outlet')
                        ->placeholder('Masukkan alamat lengkap outlet')
                ])
                ->columns(2),  // Dua kolom untuk informasi outlet

            // Grup Kontak Pemilik Outlet
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
                    Forms\Components\TextInput::make('distric')
                        ->required()
                        ->maxLength(255)
                        ->label('Distrik')
                        ->placeholder('Masukkan distrik outlet'),
                ])
                ->columns(2), // Menyusun informasi kontak dalam dua kolom

            // Grup Lokasi dan Foto
            Forms\Components\Section::make('Lokasi & Foto')
                ->schema([
                    Forms\Components\TextInput::make('latlong')
                        ->maxLength(255)
                        ->label('Latitude/Longitude')
                        ->placeholder('Masukkan koordinat latitude dan longitude outlet'),
                    Forms\Components\FileUpload::make('poto_shop_sign')
                        ->label('Foto Tanda Outlet')
                        ->maxSize(10240)
                        ->image()
                        ->disk('public')
                        ->directory('uploads/shop_signs')
                        ->required(),
                    Forms\Components\FileUpload::make('poto_depan')
                        ->label('Foto Depan')
                        ->maxSize(10240)
                        ->image()
                        ->disk('public')
                        ->directory('uploads/front_photos')
                        ->required(),
                    Forms\Components\FileUpload::make('poto_kiri')
                        ->label('Foto Kiri')
                        ->maxSize(10240)
                        ->image()
                        ->disk('public')
                        ->directory('uploads/left_photos')
                        ->required(),
                    Forms\Components\FileUpload::make('poto_kanan')
                        ->label('Foto Kanan')
                        ->maxSize(10240)
                        ->image()
                        ->disk('public')
                        ->directory('uploads/right_photos')
                        ->required(),
                    Forms\Components\FileUpload::make('poto_ktp')
                        ->label('Foto KTP')
                        ->maxSize(10240)
                        ->image()
                        ->disk('public')
                        ->directory('uploads/ktp_photos')
                        ->required(),
                    Forms\Components\FileUpload::make('video')
                        ->label('Video')
                        ->maxSize(10240)
                        ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mkv'])
                        ->disk('public')
                        ->directory('uploads/videos')
                        ->required(),
                ])
                ->columns(2), // Menyusun foto dalam dua kolom

            // Grup Badan Usaha & Divisi
            Forms\Components\Section::make('Badan Usaha & Divisi')
                ->schema([
                    Forms\Components\Select::make('badanusaha_id')
                        ->label('Badan Usaha')
                        ->relationship('badanusaha', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->reactive()
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

                    Forms\Components\TextInput::make('limit')
                        ->numeric()
                        ->label('Limit')
                        ->placeholder('Masukkan limit outlet'),

                    Forms\Components\TextInput::make('radius')
                        ->numeric()
                        ->label('Radius')
                        ->placeholder('Masukkan radius outlet'),
                ])
                ->columns(2), // Menyusun status dan limit dalam dua kolom
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_outlet'),
                Tables\Columns\TextColumn::make('badanusaha.name'),
                Tables\Columns\TextColumn::make('divisi.name'),
                Tables\Columns\TextColumn::make('region.name'),
                Tables\Columns\TextColumn::make('cluster.name'),
                Tables\Columns\TextColumn::make('nama_outlet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_pemilik_outlet'),
                Tables\Columns\TextColumn::make('nomer_tlp_outlet'),
                Tables\Columns\TextColumn::make('distric'),
                Tables\Columns\TextColumn::make('poto_shop_sign'),
                Tables\Columns\TextColumn::make('poto_depan'),
                Tables\Columns\TextColumn::make('poto_kiri'),
                Tables\Columns\TextColumn::make('poto_kanan'),
                Tables\Columns\TextColumn::make('poto_ktp'),
                Tables\Columns\TextColumn::make('video'),
                Tables\Columns\TextColumn::make('limit'),
                Tables\Columns\TextColumn::make('radius'),
                Tables\Columns\TextColumn::make('latlong'),
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

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutlets::route('/'),
            'create' => Pages\CreateOutlet::route('/create'),
            'edit' => Pages\EditOutlet::route('/{record}/edit'),
        ];
    }
}
