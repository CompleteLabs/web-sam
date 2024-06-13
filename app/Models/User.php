<?php

namespace App\Models;

use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Jetstream\HasProfilePhoto;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use SoftDeletes;

    public function scopeFilter($query)
    {
        if(request('search')){
            $query->where('nama_lengkap',"like",'%'.request('search').'%');
        }
    }

    public function outlet()
    {
        return $this->hasMany(Outlet::class);
    }

    public function nootm()
    {
        return $this->hasMany(Noo::class,'tm_id');
    }

    public function visit()
    {
        return $this->hasMany(Visit::class);
    }

    public function planvisit()
    {
        return $this->hasMany(Planvisit::class);
    }

    public function cluster()
    {
        return $this->belongsTo(Cluster::class);
    }

    public function cluster2()
    {
        return $this->belongsTo(Cluster::class, 'cluster_id2');
    }

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function divisi()
    {
        return $this->belongsTo(Division::class);
    }

    public function badanusaha()
    {
        return $this->belongsTo(BadanUsaha::class);
    }

    public function tm()
    {
        return $this->belongsTo(User::class,'tm_id');
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [
        'id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
        "created_at",
        "updated_at",
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'profile_photo_url',
    ];

    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->getPreciseTimestamp(3);
    }

    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->getPreciseTimestamp(3);
    }
}
