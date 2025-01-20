<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Outlet;
use App\Models\Region;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Carbon\Carbon;

class OutletController extends Controller
{
    // Fungsi bantu untuk mengurangi duplikasi di fetch()
    private function getOutletsByUserRole(Request $request, $roleId, $user)
    {
        $query = Outlet::with(['badanusaha', 'cluster', 'region', 'divisi']);
        switch ($roleId) {
            case 1: // ASM
            case 6: // COO
            case 8: // CSO
            case 9: // RKAM
                $divisi = Division::where('name', $request->divisi)->first()->id;
                $region = Region::where('name', $request->region)->where('divisi_id', $divisi)->first()->id;
                return $query->where('divisi_id', $divisi)
                    ->where('region_id', $region)
                    ->orderBy('nama_outlet')
                    ->get();

            case 2: // ASC
                return $query->where('badanusaha_id', $user->badanusaha_id)
                    ->where('divisi_id', $user->divisi_id)
                    ->where('region_id', $user->region_id)
                    ->whereIn('cluster_id', [$user->cluster_id, $user->cluster_id2])
                    ->orderBy('nama_outlet')
                    ->get();

            case 3: // DSF/DM
                return $query->where('badanusaha_id', $user->badanusaha_id)
                    ->where('divisi_id', $user->divisi_id)
                    ->where('region_id', $user->region_id)
                    ->where('cluster_id', $user->cluster_id)
                    ->orderBy('nama_outlet')
                    ->get();

            case 10: // KAM
                return $query->where('badanusaha_id', $user->badanusaha_id)
                    ->where('divisi_id', $user->divisi_id)
                    ->where('region_id', $user->region_id)
                    ->orderBy('nama_outlet')
                    ->get();

            case 11: // CSO FAST EV
                $divisi = Division::where('name', $request->divisi)->first()->id;
                $region = Region::where('name', $request->region)->where('divisi_id', $divisi)->first()->id;
                return $query->where('divisi_id', $divisi)
                    ->where('region_id', $region)
                    ->orderBy('nama_outlet')
                    ->get();

            default:
                return $query->get();
        }
    }

    public function all()
    {
        try {
            $outlet = Outlet::with(['badanusaha', 'cluster', 'region', 'divisi'])->get();
            return ResponseFormatter::success(
                $outlet->map->formatForAPI(),
                'berhasil'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }

    public function fetch(Request $request)
    {
        try {
            $user = Auth::user();
            $outlet = $this->getOutletsByUserRole($request, $user->role_id, $user);

            return ResponseFormatter::success(
                $outlet->map->formatForAPI(),
                count($outlet)
            );
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }

    public function singleOutlet(Request $request, $nama)
    {
        try {
            $outlet = Outlet::with(['badanusaha', 'cluster', 'region', 'divisi'])
                ->where('kode_outlet', $nama)
                ->get();
            return ResponseFormatter::success($outlet->map->formatForAPI(), 'berhasil');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }

    public function updatefoto(Request $request)
    {
        try {
            $request->validate([
                'kode_outlet' => ['required'],
                'nama_pemilik_outlet' => ['required'],
                'nomer_tlp_outlet' => ['required'],
                'latlong' => ['required'],
            ]);

            $data = Outlet::where('kode_outlet', $request->kode_outlet)->first();

            $photoFields = ['poto_depan', 'poto_kanan', 'poto_kiri', 'poto_shop_sign', 'poto_ktp'];
            foreach ($photoFields as $index => $field) {
                if ($request->hasFile('photo' . $index)) {
                    $photo = $request->file('photo' . $index);
                    $name = $photo->getClientOriginalName();
                    $photo->move(storage_path('app/public/'), $name);
                    $data[$field] = $name;
                }
            }

            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $videoName = 'update-' . now()->timestamp . '-' . $video->getClientOriginalName();
                $video->move(storage_path('app/public/'), $videoName);
                $data['video'] = $videoName;
            }

            $data->nama_pemilik_outlet = strtoupper($request->nama_pemilik_outlet);
            $data->nomer_tlp_outlet = $request->nomer_tlp_outlet;
            $data->latlong = $request->latlong;
            $data->save();

            return ResponseFormatter::success(null, 'Berhasil Update');
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }
}
