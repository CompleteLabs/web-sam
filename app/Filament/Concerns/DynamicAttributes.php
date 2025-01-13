<?php

namespace App\Filament\Concerns;

use App\Models\CustomAttribute;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Filament\Forms;
use Illuminate\Support\Facades\Log;

trait DynamicAttributes
{
    public static function dynamicAttributesSchema(string $entityType = null, $applyEntityType = null, $applyEntityId = null, $entityId = null)
    {
        if (!$entityType || !$applyEntityType || !$applyEntityId) {
            return [];
        }

        $attributeScopes = CustomAttribute::with(['values' => function ($query) use ($entityType, $entityId) {
            $query->where('entity_type', $entityType)
                ->where('entity_id', $entityId);
        }])
            ->where('entity_type', $entityType)
            ->where('apply_entity_type', $applyEntityType)
            ->where('apply_entity_id', $applyEntityId)
            ->get();

        return $attributeScopes->map(function ($attribute) {
            $existingValue = optional($attribute->values->first())->value;
            return self::createAttributeComponent($attribute, $existingValue);
        })->filter()->toArray();
    }

    public static function createAttributeComponent($attribute, $existingValue = null)
    {
        if (!$attribute || !isset($attribute->type)) {
            return null;
        }

        $existingValue = $existingValue ?? optional($attribute->values->first())->value;

        return match ($attribute->type) {
            'TEXT' => Forms\Components\TextInput::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'NUMBER' => Forms\Components\TextInput::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->numeric()
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'LINK' => Forms\Components\TextInput::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->url()
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'TEXTAREA' => Forms\Components\Textarea::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'CURRENCY' => Forms\Components\TextInput::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->numeric()
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'DATE' => Forms\Components\DatePicker::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'DATE_AND_TIME' => Forms\Components\DateTimePicker::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'TOGGLE' => Forms\Components\Toggle::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'TOGGLE_BUTTONS' => Forms\Components\ToggleButtons::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->options(collect($attribute->options)->pluck('option', 'option'))
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'SELECT' => Forms\Components\Select::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->searchable()
                ->preload()
                ->options(collect($attribute->options)->pluck('option', 'option'))
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'CHECKBOX' => Forms\Components\Checkbox::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'CHECKBOX_LIST' => Forms\Components\CheckboxList::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->options(collect($attribute->options)->pluck('option', 'option'))
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'RADIO' => Forms\Components\Radio::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->options(collect($attribute->options)->pluck('option', 'option'))
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($attribute->required),

            'UPLOAD' => Forms\Components\FileUpload::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->disk('public')
                ->required($attribute->required),

            'PHOTO' => Forms\Components\FileUpload::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->image()
                ->disk('public')
                ->resize(30)
                ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) use ($attribute) {
                    $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));
                    return $outletName . '-' . $attribute->key . '-' . Carbon::now()->format('dmYHis') . '.' . $file->getClientOriginalExtension();
                })
                ->required($attribute->required),

            default => Forms\Components\TextInput::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue)),
        };
    }
}
