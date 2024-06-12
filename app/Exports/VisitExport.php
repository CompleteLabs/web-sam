<?php

namespace App\Exports;

use App\Models\Visit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class VisitExport implements FromCollection,WithMapping,WithHeadings
{

    protected $tanggal1;
    protected $tanggal2;

    function __construct($tanggal1, $tanggal2) {
        $this->tanggal1 = $tanggal1;
        $this->tanggal2 = $tanggal2;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Visit::with(['user.role','outlet.cluster','outlet.region','outlet.divisi','user'])->whereBetween('tanggal_visit',[$this->tanggal1,$this->tanggal2])->get();
    }

    public function headings(): array
    {
        return [
            'tanggal',
            'nama',
            'role',
            'kode_outlet',
            'outlet',
            'divisi',
            'region',
            'cluster',
            'tipe',
            'Foto CI',
            'Foto CO',
            'lokasi CI',
            'lokasi CO',
            'jam CI',
            'jam CO',
            'durasi',
            'transaksi',
            'laporan'
        ];
    }

    public function map($visit) : array
    {
        return [
            date('d M Y',$visit->tanggal_visit/1000),
            $visit->user->nama_lengkap ?? '-',
            $visit->user->role->name ?? '-',
            $visit->outlet->kode_outlet ?? '-',
            $visit->outlet->nama_outlet ?? '-',
            $visit->outlet->divisi->name ?? '-',
            $visit->outlet->region->name ?? '-',
            $visit->outlet->cluster->name ?? '-',
            $visit->tipe_visit,
	    "http://grosir.mediaselularindonesia.com/storage/".$visit->picture_visit_in ?? '-',
	    $visit->picture_visit_out ? "http://grosir.mediaselularindonesia.com/storage/".$visit->picture_visit_out ?? '-' : '-',
            "https://www.google.com/maps/place/".$visit->latlong_in,
            $visit->latlong_out ? "https://www.google.com/maps/place/".$visit->latlong_out : '-',
            date('H:i',$visit->check_in_time/1000),
            $visit->check_out_time ? date('H:i',$visit->check_out_time/1000) : '-',
            $visit->durasi_visit ? $visit->durasi_visit . ' Menit': '-',
            $visit->transaksi ?? '-',
            $visit->laporan_visit ?? '-',
        ];
    }
}
