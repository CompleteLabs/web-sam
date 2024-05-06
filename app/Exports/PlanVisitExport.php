<?php

namespace App\Exports;

use App\Models\PlanVisit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PlanVisitExport implements FromCollection, WithHeadings, WithMapping
{
    protected $tanggal1;
    protected $tanggal2;

    function __construct($tanggal1, $tanggal2)
    {
        $this->tanggal1 = $tanggal1;
        $this->tanggal2 = $tanggal2;
    }
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return PlanVisit::with(['user', 'outlet'])->whereBetween('tanggal_visit', [$this->tanggal1, $this->tanggal2])->get();
    }

    public function headings(): array
    {
        return [
            'nama',
            'divisi',
            'region',
            'cluster',
            'outlet',
            'tanggal',
        ];
    }

    public function map($plan): array
    {
        return [
            $plan->user->nama_lengkap ?? '-',
            $plan->outlet->divisi->name ?? '-',
            $plan->outlet->region->name ?? '-',
            $plan->outlet->cluster->name ?? '-',
            $plan->outlet->nama_outlet ?? '-',
            date('d M Y',$plan->tanggal_visit/1000),
        ];
    }
}
