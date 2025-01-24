<?php

namespace App\Filament\Exports;

use App\Models\Outlet;
use App\Models\User;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class OutletExporter extends Exporter
{
    protected static ?string $model = Outlet::class;

    public static function getColumns(): array
    {
        $baseUrl = 'https://grosir.mediaselularindonesia.com/storage/';

        $users = User::whereIn('divisi_id', Outlet::pluck('divisi_id')->unique())
            ->whereIn('region_id', Outlet::pluck('region_id')->unique())
            ->whereIn('cluster_id', Outlet::pluck('cluster_id')->unique())
            ->whereIn('role_id', [2, 3]) // Role TM, ASC, DSF
            ->get();

        $usersGrouped = $users->groupBy(function ($user) {
            return $user->divisi_id . '.' . $user->region_id . '.' . $user->cluster_id . '.' . $user->role_id;
        });

        return [
            ExportColumn::make('badanusaha.name')->label('Badan Usaha'),
            ExportColumn::make('divisi.name')->label('Divisi'),
            ExportColumn::make('region.name')->label('Region'),
            ExportColumn::make('cluster.name')->label('Cluster'),
            ExportColumn::make('kode_outlet')
                ->label('Kode Outlet')
                ->formatStateUsing(function ($state) {
                    if (is_numeric($state)) {
                        return "'$state";
                    }
                    return $state;
                }),
            ExportColumn::make('nama_outlet')->label('Nama Outlet'),
            ExportColumn::make('alamat_outlet')->label('Alamat Outlet'),
            ExportColumn::make('distric')->label('Distrik'),
            ExportColumn::make('status_outlet')->label('Status Outlet'),
            ExportColumn::make('radius')->label('Radius'),
            ExportColumn::make('limit')->label('Limit'),
            ExportColumn::make('latlong')->label('Latlong'),
            ExportColumn::make('nama_pemilik_outlet')->label('Nama Pemilik Outlet'),
            ExportColumn::make('nomer_tlp_outlet')->label('Nomor Telepon Outlet'),

            // For 'tm', 'asc', and 'dsf' columns, use the preloaded users
            ExportColumn::make('tm')
                ->label('TM')
                ->formatStateUsing(function ($state, $record) use ($usersGrouped) {
                    $key = $record->divisi_id . '.' . $record->region_id . '.' . $record->cluster_id . '.2'; // TM role_id is 2
                    $tm = $usersGrouped->get($key)?->first();
                    return $tm ? $tm->nama_lengkap : 'VACANT';
                }),

            ExportColumn::make('asc')
                ->label('ASC')
                ->formatStateUsing(function ($state, $record) use ($usersGrouped) {
                    $key = $record->divisi_id . '.' . $record->region_id . '.' . $record->cluster_id . '.2'; // ASC role_id is 2
                    $asc = $usersGrouped->get($key)?->first();
                    return $asc ? $asc->nama_lengkap : 'VACANT';
                }),

            ExportColumn::make('dsf')
                ->label('DSF')
                ->formatStateUsing(function ($state, $record) use ($usersGrouped) {
                    $key = $record->divisi_id . '.' . $record->region_id . '.' . $record->cluster_id . '.3'; // DSF role_id is 3
                    $dsf = $usersGrouped->get($key)?->first();
                    return $dsf ? $dsf->nama_lengkap : 'VACANT';
                }),

            ExportColumn::make('created_at')
                ->formatStateUsing(function ($state) {
                    return \Carbon\Carbon::parse($state)->format('d M Y');
                })
                ->label('Tanggal Registrasi'),

            ExportColumn::make('poto_shop_sign')
                ->formatStateUsing(function ($state) use ($baseUrl) {
                    return $state ? $baseUrl . $state : '-';
                })
                ->label('Foto Shop Sign'),

            ExportColumn::make('poto_depan')
                ->formatStateUsing(function ($state) use ($baseUrl) {
                    return $state ? $baseUrl . $state : '-';
                })
                ->label('Foto Depan'),

            ExportColumn::make('poto_kiri')
                ->formatStateUsing(function ($state) use ($baseUrl) {
                    return $state ? $baseUrl . $state : '-';
                })
                ->label('Foto Kiri'),

            ExportColumn::make('poto_kanan')
                ->formatStateUsing(function ($state) use ($baseUrl) {
                    return $state ? $baseUrl . $state : '-';
                })
                ->label('Foto Kanan'),

            ExportColumn::make('poto_ktp')
                ->formatStateUsing(function ($state) use ($baseUrl) {
                    return $state ? $baseUrl . $state : '-';
                })
                ->label('Foto KTP'),

            ExportColumn::make('video')
                ->formatStateUsing(function ($state) use ($baseUrl) {
                    return $state ? $baseUrl . $state : '-';
                })
                ->label('Video'),

            ExportColumn::make('is_member')
                ->formatStateUsing(function ($state) {
                    return $state == 1 ? 'MEMBER' : ($state == 0 ? 'LEAD' : '-');
                })
                ->label('Status Outlet'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Ekspor data outlet Anda telah selesai. Sebanyak ' . number_format($export->successful_rows) . ' ' . str('baris')->plural($export->successful_rows) . ' berhasil diekspor.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' Namun, ' . number_format($failedRowsCount) . ' ' . str('baris')->plural($failedRowsCount) . ' gagal diekspor.';
        }

        return $body;
    }
}
