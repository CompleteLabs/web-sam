<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Noo extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [
        'id'
    ];

    public function scopeFilter($query)
    {
        if (request('search')) {
            $query->where('nama_outlet', "like", '%' . request('search') . '%');
        }
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function badanusaha(): BelongsTo
    {
        return $this->belongsTo(BadanUsaha::class);
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function tm(): BelongsTo
    {
        return $this->belongsTo(User::class, 'tm_id');
    }

    public function getConfirmedAtAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->timestamp;
        }
    }

    public function getRejectedAtAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->timestamp;
        }
    }

    public function getApprovedAtAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->timestamp;
        }
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    protected static function booted()
    {
        static::updating(function ($model) {
            $fields = [
                'poto_shop_sign',
                'poto_depan',
                'poto_kiri',
                'poto_kanan',
                'poto_ktp',
                'video',
            ];

            foreach ($fields as $field) {
                if ($model->isDirty($field) && $model->getOriginal($field)) {
                    $oldFile = $model->getOriginal($field);
                    if (Storage::disk('public')->exists($oldFile)) {
                        Storage::disk('public')->delete($oldFile);
                    }
                }
            }

            // Memasukkan atau memperbarui data ke tabel outlets jika status berubah menjadi APPROVED
            if ($model->isDirty('status') && $model->status === 'APPROVED') {
                $kode_lead = 'LEAD' . $model->id;
                $outlet = Outlet::where('kode_outlet', $kode_lead)->first();
                if ($outlet) {
                    $outlet->update([
                        'kode_outlet' => $model->kode_outlet,
                        'limit' => $model->limit,
                        'is_member' => '1',
                    ]);
                } else {
                    Outlet::create([
                        'kode_outlet' => $model->kode_outlet,
                        'nama_outlet' => $model->nama_outlet,
                        'alamat_outlet' => $model->alamat_outlet,
                        'nama_pemilik_outlet' => $model->nama_pemilik_outlet,
                        'nomer_tlp_outlet' => $model->nomer_tlp_outlet,
                        'badanusaha_id' => $model->badanusaha_id,
                        'divisi_id' => $model->divisi_id,
                        'region_id' => $model->region_id,
                        'cluster_id' => $model->cluster_id,
                        'distric' => $model->distric,
                        'poto_shop_sign' => $model->poto_shop_sign,
                        'poto_depan' => $model->poto_depan,
                        'poto_kanan' => $model->poto_kanan,
                        'poto_kiri' => $model->poto_kiri,
                        'poto_ktp' => $model->poto_ktp,
                        'video' => $model->video,
                        'limit' => $model->limit,
                        'radius' => 100,
                        'latlong' => $model->latlong,
                        'status_outlet' => 'MAINTAIN',
                        'is_member' => '1',
                    ]);
                }
            }
        });
    }

    public function formatForAPI()
    {
        return [
            'id' => $this->id,
            'kode_outlet' => $this->kode_outlet,
            'nama_outlet' => $this->nama_outlet,
            'alamat_outlet' => $this->alamat_outlet,
            'nama_pemilik_outlet' => $this->nama_pemilik_outlet,
            'nomer_tlp_outlet' => $this->nomer_tlp_outlet,
            'nomer_wakil_outlet' => $this->nomer_wakil_outlet,
            'ktp_outlet' => $this->ktp_outlet,
            'distric' => $this->distric,
            'region' => $this->region ? $this->region->only(['id', 'name']) : null,
            'poto_shop_sign' => $this->poto_shop_sign,
            'poto_depan' => $this->poto_depan,
            'poto_kiri' => $this->poto_kiri,
            'poto_kanan' => $this->poto_kanan,
            'poto_ktp' => $this->poto_ktp,
            'video' => $this->video,
            'oppo' => $this->oppo,
            'vivo' => $this->vivo,
            'realme' => $this->realme,
            'samsung' => $this->samsung,
            'xiaomi' => $this->xiaomi,
            'fl' => $this->fl,
            'latlong' => $this->latlong,
            'limit' => $this->limit,
            'status' => $this->status, // Pastikan status sesuai enum NooStatus
            'rejected_at' => $this->rejected_at * 1000,
            'rejected_by' => $this->rejected_by,
            'confirmed_by' => $this->confirmed_by,
            'confirmed_at' => $this->confirmed_at * 1000,
            'approved_by' => $this->approved_by,
            'approved_at' => $this->approved_at * 1000,
            'deleted_at' => $this->deleted_at * 1000,
            'created_at' => $this->created_at * 1000,
            'updated_at' => $this->updated_at * 1000,
            'keterangan' => $this->keterangan,
            'cluster' => $this->cluster ? $this->cluster->only(['id', 'name']) : null,
            'badanusaha' => $this->badanusaha ? $this->badanusaha->only(['id', 'name']) : null,
            'divisi' => $this->divisi ? $this->divisi->only(['id', 'name']) : null,
            'created_by' => $this->created_by,
        ];
    }
}
