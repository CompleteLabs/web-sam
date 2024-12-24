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
        $baseUrl = 'http://grosir.mediaselularindonesia.com/storage/';

        return [
            ExportColumn::make('badanusaha.name')->label('Badan Usaha'),
            ExportColumn::make('divisi.name')->label('Divisi'),
            ExportColumn::make('region.name')->label('Region'),
            ExportColumn::make('cluster.name')->label('Cluster'),
            ExportColumn::make('kode_outlet')->label('Kode Outlet'),
            ExportColumn::make('nama_outlet')->label('Nama Outlet'),
            ExportColumn::make('alamat_outlet')->label('Alamat Outlet'),
            ExportColumn::make('distric')->label('Distrik'),
            ExportColumn::make('status_outlet')->label('Status Outlet'),
            ExportColumn::make('radius')->label('Radius'),
            ExportColumn::make('limit')->label('Limit'),
            ExportColumn::make('latlong')->label('Latlong'),
            ExportColumn::make('nama_pemilik_outlet')->label('Nama Pemilik Outlet'),
            ExportColumn::make('nomer_tlp_outlet')->label('Nomor Telepon Outlet'),
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


            // ExportColumn::make('tm', fn($record) => self::getUserByRole($record, 2, 'tm')),
            // ExportColumn::make('asc', fn($record) => self::getUserByRole($record, 2)),
            // ExportColumn::make('dsf', fn($record) => self::getUserByRole($record, 3, 'cluster')),
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

    // private static function getUserByRole($outlet, $roleId, $relation = null)
    // {
    //     $userQuery = User::where('divisi_id', $outlet->divisi_id)
    //                      ->where('region_id', $outlet->region_id)
    //                      ->where('role_id', $roleId);

    //     if ($roleId == 3) {
    //         $userQuery->where('cluster_id', $outlet->cluster_id);
    //     }

    //     $user = $userQuery->first();

    //     if ($user) {
    //         return $relation && isset($user->$relation) ? $user->$relation->nama_lengkap : $user->nama_lengkap;
    //     }

    //     return 'VACANT';
    // }
}
