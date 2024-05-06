<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Outlet extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [
        'id'
    ];

    public function scopeFilter($query)
    {
        if(request('search')){
            $query->where('nama_outlet',"like",'%'.request('search').'%')
            ->orWhere('kode_outlet', "like",'%'.request('search').'%');
        }
    }

    public function planvisit()
    {
        return $this->hasMany(PlanVisit::class);
    }
    public function visit()
    {
        return $this->hasMany(Visit::class);
    }

    public function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function badanusaha()
    {
        return $this->belongsTo(BadanUsaha::class);
    }

    public function divisi()
    {
        return $this->belongsTo(Division::class);
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
