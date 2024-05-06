<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $hidden = [
        "created_at", "updated_at"
    ];

    protected $guarded = [
        'id'
    ];

    public function user()
    {
        return $this->hasMany(User::class);
    }
}
