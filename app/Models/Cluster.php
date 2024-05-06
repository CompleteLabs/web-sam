<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cluster extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    protected $hidden = [
        "created_at", "updated_at"
    ];

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function outlet()
    {
        return $this->hasMany(Outlet::class);
    }

    public function noo()
    {
        return $this->hasMany(Noo::class);
    }

    public function badanusaha()
    {
        return $this->belongsTo(BadanUsaha::class);
    }

    public function divisi()
    {
        return $this->belongsTo(Division::class);
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }
}
