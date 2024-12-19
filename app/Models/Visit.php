<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Visit extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [
        'id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }


    public function outlet(): BelongsTo
    {
        return $this->belongsTo(Outlet::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getTanggalVisitAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getCheckInTimeAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getCheckOutTimeAttribute($value)
    {
        if ($value) {
            return Carbon::parse($value)->timestamp;
        } else {
            return $value;
        }
    }

    protected static function booted()
    {
        static::saving(function ($visit) {
            $visit->calculateDurasiVisit();
        });

        static::updating(function ($model) {
            // Jika field picture_visit_in berubah, hapus gambar lama
            if ($model->isDirty('picture_visit_in') && $model->getOriginal('picture_visit_in')) {
                $oldFile = $model->getOriginal('picture_visit_in');
                if (Storage::disk('public')->exists($oldFile)) {
                    Storage::disk('public')->delete($oldFile);
                }
            }

            // Jika field picture_visit_out berubah, hapus gambar lama
            if ($model->isDirty('picture_visit_out') && $model->getOriginal('picture_visit_out')) {
                $oldFileOut = $model->getOriginal('picture_visit_out');
                if (Storage::disk('public')->exists($oldFileOut)) {
                    Storage::disk('public')->delete($oldFileOut);
                }
            }
        });
    }

    protected function calculateDurasiVisit(): void
    {
        if (!empty($this->check_in_time) && !empty($this->check_out_time)) {
            try {
                $checkIn = Carbon::parse($this->check_in_time);
                $checkOut = Carbon::parse($this->check_out_time);

                $durationInMinutes = $checkIn->diffInMinutes($checkOut);

                $this->durasi_visit = $durationInMinutes;
            } catch (\Exception $e) {
                $this->durasi_visit = null;
            }
        } else {
            $this->durasi_visit = null;
        }
    }
}
