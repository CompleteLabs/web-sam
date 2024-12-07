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
}
