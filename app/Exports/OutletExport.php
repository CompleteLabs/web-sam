<?php

namespace App\Exports;

use App\Models\Outlet;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OutletExport implements FromCollection, WithMapping, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Outlet::with(['cluster', 'region', 'badanusaha'])->orderBy('nama_outlet')->get();
    }

    public function headings(): array
    {
        return [
            'badan_usaha',
            'divisi',
            'region',
            'cluster',
            'kode_outlet',
            'nama_outlet',
            'alamat_outlet',
            'distric',
            'status',
            'radius',
            'limit',
            'latlong',
            'nama_pemilik_outlet',
            'telepon_outlet',
            'TM',
            'ASC',
            'DSF',
            'tanggal regitrasi',
            'foto_shop_sign',
            'foto_depan',
            'foto_kiri',
            'foto_kanan',
            'foto_ktp',
            'video',
        ];
    }

    public function map($outlet): array
    {
        $foto_shop_sign = $outlet->poto_shop_sign ? 'http://grosir.mediaselularindonesia.com/storage/' . $outlet->poto_shop_sign : '-';
        $foto_depan = $outlet->poto_depan ? 'http://grosir.mediaselularindonesia.com/storage/' . $outlet->poto_depan : '-';
        $foto_kiri = $outlet->poto_kiri ? 'http://grosir.mediaselularindonesia.com/storage/' . $outlet->poto_kiri : '-';
        $foto_kanan = $outlet->poto_kanan ? 'http://grosir.mediaselularindonesia.com/storage/' . $outlet->poto_kanan : '-';
        $foto_ktp = $outlet->poto_ktp ? 'http://grosir.mediaselularindonesia.com/storage/' . $outlet->poto_ktp : '-';
        $video = $outlet->video ? 'http://grosir.mediaselularindonesia.com/storage/' . $outlet->video : '-';

        return [
            $outlet->badanusaha->name,
            $outlet->divisi->name,
            $outlet->region->name,
            $outlet->cluster->name,
            $outlet->kode_outlet,
            $outlet->nama_outlet,
            $outlet->alamat_outlet,
            $outlet->distric,
            $outlet->status_outlet,
            $outlet->radius . ' Meter',
            'Rp ' . number_format($outlet->limit, 0, ',', '.'),
            $outlet->latlong,
            $outlet->nama_pemilik_outlet,
            $outlet->nomer_tlp_outlet,
            User::where('divisi_id', $outlet->divisi_id)->where('region_id', $outlet->region_id)->where('role_id', 2)->first()->tm->nama_lengkap  ?? 'VACANT',
            User::where('divisi_id', $outlet->divisi_id)->where('region_id', $outlet->region_id)->where('role_id', 2)->first()->nama_lengkap ?? 'VACANT',
            User::where('divisi_id', $outlet->divisi_id)->where('region_id', $outlet->region_id)->where('cluster_id', $outlet->cluster_id)->where('role_id', 3)->first()->nama_lengkap ?? 'VACANT',
            date('d M Y', $outlet->created_at / 1000),
            $foto_shop_sign,
            $foto_depan,
            $foto_kiri,
            $foto_kanan,
            $foto_ktp,
            $video,
        ];
    }
}
