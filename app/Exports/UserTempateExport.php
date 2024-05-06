<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithHeadings;

class UserTempateExport implements WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function headings(): array
    {
        return [
            'nama_lengkap',
            'username',
            'role',
            'badan_usaha',
            'divisi',
            'region',
            'cluster',
            'tm',
            'password',
        ];
    }
}
