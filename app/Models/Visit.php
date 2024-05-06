<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Visit extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [
        'id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }


    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->getPreciseTimestamp(3);
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->getPreciseTimestamp(3);
    }

    public function getTanggalVisitAttribute($value)
    {
        return Carbon::parse($value)->getPreciseTimestamp(3);
    }

    public function getCheckInTimeAttribute($value)
    {
        return Carbon::parse($value)->getPreciseTimestamp(3);
    }

    public function getCheckOutTimeAttribute($value)
    {
        if($value){
            return Carbon::parse($value)->getPreciseTimestamp(3);
        }else{
            return $value;
        }
    }
}
