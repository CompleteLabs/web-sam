<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Division extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    protected $hidden = [
        "created_at", "updated_at"
    ];

    public function user(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function outlet(): HasMany
    {
        return $this->hasMany(Outlet::class);
    }

    public function noo(): HasMany
    {
        return $this->hasMany(Noo::class);
    }

    public function badanusaha(): BelongsTo
    {
        return $this->belongsTo(BadanUsaha::class, 'badanusaha_id');
    }

    public function region(): HasMany
    {
        return $this->hasMany(Region::class, 'divisi_id');
    }

    public function cluster(): HasMany
    {
        return $this->hasMany(Cluster::class);
    }

    public function customAttributes(): MorphMany
    {
        return $this->morphMany(CustomAttribute::class, 'apply_entity');
    }
}
