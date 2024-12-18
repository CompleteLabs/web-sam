<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        if($value){
            return Carbon::parse($value)->timestamp;
        }else{
            return $value;
        }
    }

    protected static function booted()
    {
        static::saving(function ($visit) {
            $visit->calculateDurasiVisit();
        });
    }

    protected function calculateDurasiVisit(): void
    {
        // Pastikan kedua nilai check_in_time dan check_out_time valid
        if (!empty($this->check_in_time) && !empty($this->check_out_time)) {
            try {
                // Parsing check-in dan check-out time
                $checkIn = Carbon::parse($this->check_in_time);
                $checkOut = Carbon::parse($this->check_out_time);

                // Hitung durasi dalam menit
                $durationInMinutes = $checkIn->diffInMinutes($checkOut);

                // Set nilai durasi
                $this->durasi_visit = $durationInMinutes;
            } catch (\Exception $e) {
                // Jika terjadi kesalahan parsing, atur durasi menjadi null
                $this->durasi_visit = null;
            }
        } else {
            // Jika salah satu nilai tidak ada, atur durasi menjadi null
            $this->durasi_visit = null;
        }
    }
}
