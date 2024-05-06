<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanVisitNoo extends Model
{
    use HasFactory;

    protected $table = 'plan_visit_noo';

    public function scopeFilter($query)
    {
        if(request('search')){
            $query->where('nama_lengkap',"like",'%'.request('search').'%');
        }
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Noo::class, 'noo_id');
    }

    public function getTanggalVisitAttribute($value)
    {
        return Carbon::parse($value)->getPreciseTimestamp(3);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->getPreciseTimestamp(3);
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->getPreciseTimestamp(3);
    }
}
