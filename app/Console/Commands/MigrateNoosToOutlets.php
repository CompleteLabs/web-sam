<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MigrateNoosToOutlets extends Command
{
    protected $signature = 'crm:migrate-noos-to-outlets';
    protected $description = 'Migrasi data dari tabel noos ke tabel outlets dengan mapping status';

    public function handle()
    {
        $noos = DB::table('noos')->get();
        $count = 0;
        foreach ($noos as $noo) {
            // Mapping status
            $status = match ($noo->status) {
                'PENDING' => 'NOO',
                'CONFIRMED' => 'NOO',
                'APPROVED' => 'MEMBER',
                'REJECTED' => 'REJECTED',
                default => 'LEAD',
            };
            // Cek jika sudah ada outlet dengan kode_outlet sama, skip
            $exists = DB::table('outlets')->where('kode_outlet', $noo->kode_outlet)->exists();
            if ($exists) continue;
            DB::table('outlets')->insert([
                'kode_outlet' => $noo->kode_outlet,
                'nama_outlet' => $noo->nama_outlet,
                'alamat_outlet' => $noo->alamat_outlet,
                'nama_pemilik_outlet' => $noo->nama_pemilik_outlet,
                'nomer_tlp_outlet' => $noo->nomer_tlp_outlet,
                'badanusaha_id' => $noo->badanusaha_id,
                'divisi_id' => $noo->divisi_id,
                'region_id' => $noo->region_id,
                'cluster_id' => $noo->cluster_id,
                'distric' => $noo->distric,
                'poto_shop_sign' => $noo->poto_shop_sign ?? null,
                'poto_depan' => $noo->poto_depan ?? null,
                'poto_kiri' => $noo->poto_kiri ?? null,
                'poto_kanan' => $noo->poto_kanan ?? null,
                'poto_ktp' => $noo->poto_ktp ?? null,
                'video' => $noo->video ?? null,
                'limit' => $noo->limit ?? null,
                'latlong' => $noo->latlong ?? null,
                'status_outlet' => 'UNMAINTAIN', // default, bisa diubah sesuai kebutuhan
                'status' => $status,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);
            $count++;
        }
        $this->info("Migrasi selesai. Total data yang dimigrasi: $count");
    }
}
