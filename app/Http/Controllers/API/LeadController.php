<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Noo;
use App\Models\User;
use App\Models\Region;
use App\Models\Cluster;
use App\Models\Division;
use App\Helpers\SendNotif;
use App\Models\BadanUsaha;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class LeadController extends Controller
{
    public function create(Request $request)
    {
        try {
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
                case 1:
                    $badanusaha_id = BadanUsaha::where('name', $request->bu)->first()->id;
                    $divisi_id = Division::where('badanusaha_id', $badanusaha_id)->where('name', $request->div)->first()->id;
                    $region_id = Region::where('badanusaha_id', $badanusaha_id)->where('divisi_id', $divisi_id)->where('name', $request->reg)->first()->id;
                    $cluster_id = Cluster::where('badanusaha_id', $badanusaha_id)->where('divisi_id', $divisi_id)->where('region_id', $region_id)->where('name', $request->clus)->first()->id;
                    $data['badanusaha_id'] = $badanusaha_id;
                    $data['divisi_id'] = $divisi_id;
                    $data['region_id'] = $region_id;
                    $data['cluster_id'] = $cluster_id;
                    break;

                case 2:
                    $data['badanusaha_id'] = $user->badanusaha_id;
                    $data['divisi_id'] = $user->divisi_id;
                    $data['region_id'] = $user->region_id;
                    $data['cluster_id'] = Cluster::where('badanusaha_id', $user->badanusaha_id)->where('divisi_id', $user->divisi_id)->where('region_id', $user->region_id)->where('name', $request->clus)->first()->id;
                    error_log($data['cluster_id']);
                    break;

                default:
                    $data['badanusaha_id'] = $user->badanusaha_id;
                    $data['divisi_id'] = $user->divisi_id;
                    $data['region_id'] = $user->region_id;
                    $data['cluster_id'] = $user->cluster_id;
                    break;
            }

                for ($i = 0; $i <= 3; $i++) {
                        $namaFoto = $request->file('photo' . $i)->getClientOriginalName();
                        if (Str::contains($namaFoto, 'fotodepan')) {
                            $data['poto_depan'] = $namaFoto;
                        } else if (Str::contains($namaFoto, 'fotokanan')) {
                            $data['poto_kanan'] = $namaFoto;
                        } else if (Str::contains($namaFoto, 'fotokiri')) {
                            $data['poto_kiri'] = $namaFoto;
                        } else {
                            $data['poto_shop_sign'] = $namaFoto;
                        }
                        $request->file('photo' . $i)->move(storage_path('app/public/'), $namaFoto);
                    }

                if ($request->hasFile('video')) {
                    $name = $request->file('video')->getClientOriginalName();
                    $data['video'] = 'noo-' . time() . $name;
                    $request->file('video')->move(storage_path('app/public/'), 'noo-' . time() . $name);
                }

                $insert = Noo::create($data);
            return ResponseFormatter::success(null, 'berhasil menambahkan LEAD ' . $request->nama_outlet);
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), $e->getMessage());
        }
    }

    public function update(Request $request)
    {
        try{
            $request->validate([
                'id' => ['required'],
                'noktp' => ['required'],
                ]);

        $lead = Noo::find($request->id);
        $namaFoto = $request->file('photo')->getClientOriginalName();
        $request->file('photo')->move(storage_path('app/public/'), $namaFoto);
        $lead['poto_ktp'] = $namaFoto;
        $lead['ktp_outlet'] = $request->noktp;
        $lead['keterangan'] = NULL;
        $lead->update();
        SendNotif::sendMessage('Noo baru ' . $lead->nama_outlet . ' ditambahkan oleh ' . Auth::user()->nama_lengkap, array(User::where('role_id', 4)->first()->id_notif));
        return ResponseFormatter::success(null, 'berhasil menambahkan Lead ' . $request->nama_outlet);
        }catch (Exception $e){
            return ResponseFormatter::error($e->getMessage(), $e->getMessage());
        }
    }
}
