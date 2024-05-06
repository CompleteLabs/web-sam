<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
use App\Models\Region;
use App\Models\Cluster;
use App\Models\Division;
use App\Models\BadanUsaha;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UserImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $user = new User();
        $user = $user->where('username', strtolower($row['username']));
        if ($user->first()) {
            error_log($row['nama_lengkap'] . $row['role']);
            $badanusaha_id = BadanUsaha::where('name', preg_replace('/\s+/', '', $row['badan_usaha']))->first()->id;
            $divisi_id = Division::where('name', preg_replace('/\s+/', '', $row['divisi']))->where('badanusaha_id', $badanusaha_id)->first()->id;
            $region_id = strtoupper($row['role']) === 'TM' ? null : Region::where('name', preg_replace('/\s+/', '', $row['region']))->where('divisi_id', $divisi_id)->where('badanusaha_id', $badanusaha_id)->first()->id;
            $user->update([
                'nama_lengkap' => strtoupper($row['nama_lengkap']),
                'username' => strtolower($row['username']),
                'role_id' => Role::where('name', preg_replace('/\s+/', '', $row['role']))->first()->id,
                'badanusaha_id' => $badanusaha_id,
                'divisi_id' => $divisi_id,
                'region_id' => strtoupper($row['role']) === 'TM' ? Region::where('name', preg_replace('/\s+/', '', $row['region']))->first()->id : $region_id,
                'cluster_id' => Cluster::where('name', preg_replace('/\s+/', '', $row['cluster']))->first()->id,
                'tm_id' => User::where('nama_lengkap', $row['tm'])->first()->id,
            ]);
        } else {
            $badanusaha_id = BadanUsaha::where('name', preg_replace('/\s+/', '', $row['badan_usaha']))->first()->id;
            $divisi_id = Division::where('name', preg_replace('/\s+/', '', $row['divisi']))->where('badanusaha_id', $badanusaha_id)->first()->id;
            $region_id = strtoupper($row['role']) === 'TM' ? null : Region::where('name', preg_replace('/\s+/', '', $row['region']))->where('divisi_id', $divisi_id)->where('badanusaha_id', $badanusaha_id)->first()->id;
            return new User([
                'nama_lengkap' => strtoupper($row['nama_lengkap']),
                'username' => strtolower($row['username']),
                'role_id' => Role::where('name', preg_replace('/\s+/', '', $row['role']))->first()->id,
                'badanusaha_id' => $badanusaha_id,
                'divisi_id' => $divisi_id,
                'region_id' => strtoupper($row['role']) === 'TM' ? Region::where('name', preg_replace('/\s+/', '', $row['region']))->first()->id : $region_id,
                'cluster_id' => Cluster::where('name', preg_replace('/\s+/', '', $row['cluster']))->first()->id,
                'tm_id' => User::where('nama_lengkap', $row['tm'])->first()->id,
                'password' => $row['password'] ? bcrypt($row['password']) : bcrypt('complete123'),
            ]);
        }
    }
}
