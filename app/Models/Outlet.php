<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function planvisit(): HasMany
    {
        return $this->hasMany(PlanVisit::class);
    }
    public function visit(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function user(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function badanusaha(): BelongsTo
    {
        return $this->belongsTo(BadanUsaha::class);
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->timestamp;
    }
}
