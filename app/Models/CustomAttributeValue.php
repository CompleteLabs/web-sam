<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'custom_attribute_id',
        'entity_id',
        'entity_type',
        'value',
    ];

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }

    public function attribute(): BelongsTo
    {
        return $this->belongsTo(CustomAttribute::class, 'custom_attribute_id');
    }
}
