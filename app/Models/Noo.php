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
}
