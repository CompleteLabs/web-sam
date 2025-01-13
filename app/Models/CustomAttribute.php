<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomAttribute extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'label', 'type', 'options', 'required', 'active', 'system_defined', 'entity_type', 'apply_entity_type', 'apply_entity_id'];
    protected $casts = ['options' => 'array'];

    public function getOptionsAttribute($value)
    {
        if (is_array($value)) {
            return array_map(function ($item) {
                return ['option' => $item]; // Mengubah menjadi array objek
            }, $value);
        }
        return json_decode($value, true);
    }
    public function applyEntity(): MorphTo
    {
        return $this->morphTo();
    }

    public function values(): HasMany
    {
        return $this->hasMany(CustomAttributeValue::class, 'custom_attribute_id');
    }
}
