<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Noo;
use App\Models\User;
use App\Models\Region;
use App\Models\Cluster;
use App\Models\Division;
use App\Models\BadanUsaha;
use Illuminate\Http\Request;
use App\Helpers\SendNotif;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class LeadController extends Controller
{
    public function create(Request $request)
    {
        try {
            $request->validate([
                'nama_outlet' => 'required|string',
                'alamat_outlet' => 'required|string',
                'nama_pemilik' => 'required|string',
                'nomer_pemilik' => 'required|string',
                'nomer_perwakilan' => 'required|string',
                'distric' => 'required|string',
                'oppo' => 'required|integer',
                'vivo' => 'required|integer',
                'samsung' => 'required|integer',
                'xiaomi' => 'required|integer',
                'realme' => 'required|integer',
                'fl' => 'required|string',
                'latlong' => 'required|string',
            ]);

            $user = Auth::user();
            $data = [
                'nama_outlet' => $request->nama_outlet,
                'alamat_outlet' => $request->alamat_outlet,
                'nama_pemilik_outlet' => $request->nama_pemilik,
                'nomer_tlp_outlet' => $request->nomer_pemilik,
                'nomer_wakil_outlet' => $request->nomer_perwakilan,
                'ktp_outlet' => '-',
                'distric' => $request->distric,
                'oppo' => $request->oppo,
                'vivo' => $request->vivo,
                'samsung' => $request->samsung,
                'xiaomi' => $request->xiaomi,
                'realme' => $request->realme,
                'fl' => $request->fl,
                'latlong' => $request->latlong,
                'created_by' => $user->nama_lengkap,
                'tm_id' => $user->tm->id,
                'keterangan' => "LEAD",
                'poto_ktp' => "-",
            ];

            switch ($user->role_id) {
                case 1: // ASM
                    $badanusaha_id = BadanUsaha::where('name', $request->bu)->value('id');
                    $divisi_id = Division::where('badanusaha_id', $badanusaha_id)->where('name', $request->div)->value('id');
                    $region_id = Region::where('badanusaha_id', $badanusaha_id)
                        ->where('divisi_id', $divisi_id)
                        ->where('name', $request->reg)
                        ->value('id');
                    $cluster_id = Cluster::where('badanusaha_id', $badanusaha_id)
                        ->where('divisi_id', $divisi_id)
                        ->where('region_id', $region_id)
                        ->where('name', $request->clus)
                        ->value('id');
                    $data['badanusaha_id'] = $badanusaha_id;
                    $data['divisi_id'] = $divisi_id;
                    $data['region_id'] = $region_id;
                    $data['cluster_id'] = $cluster_id;
                    break;

                case 2: // ASC
                    $data['badanusaha_id'] = $user->badanusaha_id;
                    $data['divisi_id'] = $user->divisi_id;
                    $data['region_id'] = $user->region_id;
                    $data['cluster_id'] = Cluster::where('badanusaha_id', $user->badanusaha_id)
                        ->where('divisi_id', $user->divisi_id)
                        ->where('region_id', $user->region_id)
                        ->where('name', $request->clus)
                        ->value('id');
                    break;

                default:
                    $data['badanusaha_id'] = $user->badanusaha_id;
                    $data['divisi_id'] = $user->divisi_id;
                    $data['region_id'] = $user->region_id;
                    $data['cluster_id'] = $user->cluster_id;
                    break;
            }

            // Handle file upload for photos (Multiple files)
            $uploadedPhotos = collect();
            for ($i = 0; $i <= 3; $i++) {
                if ($request->hasFile('photo' . $i)) {
                    $photo = $request->file('photo' . $i);
                    $photoName = $photo->getClientOriginalName();
                    $photo->move(storage_path('app/public/'), $photoName);
                    $uploadedPhotos->put('photo' . $i, $photoName);

                    // Determine the photo field to store based on name
                    if (Str::contains($photoName, 'fotodepan')) {
                        $data['poto_depan'] = $photoName;
                    } elseif (Str::contains($photoName, 'fotokanan')) {
                        $data['poto_kanan'] = $photoName;
                    } elseif (Str::contains($photoName, 'fotokiri')) {
                        $data['poto_kiri'] = $photoName;
                    } else {
                        $data['poto_shop_sign'] = $photoName;
                    }
                }
            }

            if ($request->hasFile('video')) {
                $video = $request->file('video');
                $videoName = 'noo-' . time() . $video->getClientOriginalName();
                $video->move(storage_path('app/public/'), $videoName);
                $data['video'] = $videoName;
            }

            $noo = Noo::create($data);

            $outletData = [
                'kode_outlet' => 'LEAD' . $noo->id,
                'limit' => 0,
                'radius' => 100,
                'is_member' => 0,
            ];

            Outlet::create(array_merge($data, $outletData));

            return ResponseFormatter::success(null, 'Berhasil menambahkan LEAD ' . $request->nama_outlet);
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required',
                'noktp' => 'required|string',
            ]);

            $lead = Noo::findOrFail($request->id);
            if ($request->hasFile('photo')) {
                $namaFoto = $request->file('photo')->getClientOriginalName();
                $request->file('photo')->move(storage_path('app/public/'), $namaFoto);
                $lead->poto_ktp = $namaFoto;
            }

            $lead->ktp_outlet = $request->noktp;
            $lead->keterangan = NULL;
            $lead->save();

            SendNotif::sendMessage(
                'Noo baru ' . $lead->nama_outlet . ' ditambahkan oleh ' . Auth::user()->nama_lengkap,
                [User::where('role_id', 4)->first()->id_notif]
            );

            return ResponseFormatter::success(null, 'Berhasil menambahkan Lead ' . $request->nama_outlet);
        } catch (Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Terjadi kesalahan pada server.'
            ], $error->getMessage(), 500);
        }
    }
}
