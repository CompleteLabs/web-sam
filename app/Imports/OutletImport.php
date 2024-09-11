<?php

namespace App\Imports;

use App\Models\BadanUsaha;
use App\Models\Cluster;
use App\Models\Division;
use App\Models\Outlet;
use App\Models\Region;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class OutletImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        try {
            $badanusaha_id = BadanUsaha::where('name', preg_replace('/\s+/', '', $row['badan_usaha']))->firstOrFail()->id;
            $divisi_id = Division::where('name', preg_replace('/\s+/', '', $row['divisi']))
                ->where('badanusaha_id', $badanusaha_id)->firstOrFail()->id;
            $region_id = Region::where('name', preg_replace('/\s+/', '', $row['region']))
                ->where('divisi_id', $divisi_id)->where('badanusaha_id', $badanusaha_id)->firstOrFail()->id;

            $cluster_name = preg_replace('/\s+/', '', $row['cluster']);
            Log::info('Searching for cluster: ' . $cluster_name);
            $cluster_id = Cluster::where('name', $cluster_name)->firstOrFail()->id;
            Log::info('Found cluster ID: ' . $cluster_id);

            $outlet = Outlet::where('kode_outlet', preg_replace('/\s+/', '', strtoupper($row['kode_outlet'])))
                ->where('divisi_id', $divisi_id);

            if ($outlet->exists()) {
                $outlet = $outlet->first();
                $outlet->update([
                    'badanusaha_id' => $badanusaha_id,
                    'divisi_id' => $divisi_id,
                    'region_id' => $region_id,
                    'cluster_id' => $cluster_id,
                    'kode_outlet' => preg_replace('/\s+/', '', strtoupper($row['kode_outlet'])),
                    'nama_outlet' => strtoupper($row['nama_outlet']),
                    'alamat_outlet' => strtoupper($row['alamat_outlet']),
                    'distric' => strtoupper($row['distric']),
                    'status_outlet' => strtoupper($row['status']),
                    'radius' => $row['radius'] ?? $outlet->radius,
                    'limit' => $row['limit'] ?? $outlet->limit,
                    'latlong' => $row['latlong'] ?? $outlet->latlong,
                ]);
                return null;
            } else {
                return new Outlet([
                    'badanusaha_id' => $badanusaha_id,
                    'divisi_id' => $divisi_id,
                    'region_id' => $region_id,
                    'cluster_id' => $cluster_id,
                    'kode_outlet' => strtoupper($row['kode_outlet']),
                    'nama_outlet' => strtoupper($row['nama_outlet']),
                    'alamat_outlet' => strtoupper($row['alamat_outlet']),
                    'distric' => strtoupper($row['distric']),
                    'status_outlet' => strtoupper($row['status']),
                    'radius' => $row['radius'] ?? 0,
                    'limit' => $row['limit'] ?? 0,
                    'latlong' => $row['latlong'],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error importing row: ' . json_encode($row) . ' - ' . $e->getMessage());
            Session::flash('error', 'Error importing row: ' . json_encode($row) . ' - ' . $e->getMessage());
            return null;
        }
    }
}
