<?php

namespace App\Filament\Concerns;

use App\Models\CustomAttribute;
use Carbon\Carbon;
use Filament\Forms;
use Illuminate\Http\UploadedFile;

trait DynamicAttributes
{
    public static function dynamicAttributesSchema(?string $entityType = null, $applyEntityType = null, $applyEntityId = null, $entityId = null)
    {
        if (! $entityType || ! $applyEntityType || ! $applyEntityId) {
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
        if (! $attribute || ! isset($attribute->type)) {
            return null;
        }

        $existingValue = $existingValue ?? optional($attribute->values->first())->value;

        // Mengambil opsi validasi dari general_options
        $isRequired = $attribute->validation_rules['general_options']['is_required'] ?? false;
        $isHiddenLabel = $attribute->validation_rules['general_options']['is_hidden_label'] ?? false;

        // Aturan validasi tambahan berdasarkan conditional_visibility
        $validationRules = [];
        if (isset($attribute->conditional_visibility) && $attribute->conditional_visibility['enable']) {
            // Misalnya: Field 2 dan nilai PKP
            $validationRules[] = "required_if:custom_attributes.{$attribute->conditional_visibility['field']},{$attribute->conditional_visibility['value']}";
        }

        // Menyesuaikan komponen form dengan aturan validasi yang telah ditentukan
        return match ($attribute->type) {
            'TEXT' => Forms\Components\TextInput::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'NUMBER' => Forms\Components\TextInput::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->numeric()
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'LINK' => Forms\Components\TextInput::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->url()
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'TEXTAREA' => Forms\Components\Textarea::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'CURRENCY' => Forms\Components\TextInput::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->numeric()
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'DATE' => Forms\Components\DatePicker::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'DATE_AND_TIME' => Forms\Components\DateTimePicker::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'TOGGLE' => Forms\Components\Toggle::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'TOGGLE_BUTTONS' => Forms\Components\ToggleButtons::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->options(collect($attribute->options)->pluck('option', 'option'))
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'SELECT' => Forms\Components\Select::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->searchable()
                ->preload()
                ->options(collect($attribute->options)->pluck('option', 'option')->toArray())
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->reactive(),

            'CHECKBOX' => Forms\Components\Checkbox::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'CHECKBOX_LIST' => Forms\Components\CheckboxList::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->options(collect($attribute->options)->pluck('option', 'option'))
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'RADIO' => Forms\Components\Radio::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->options(collect($attribute->options)->pluck('option', 'option'))
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue))
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'UPLOAD' => Forms\Components\FileUpload::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->disk('public')
                ->required($isRequired)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            'PHOTO' => Forms\Components\FileUpload::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->image()
                ->disk('public')
                ->resize(30)
                ->hint($attribute->validation_rules['hint_options']['hint_text'] ?? '')
                ->hintColor($attribute->validation_rules['hint_options']['hint_color'] ?? '')
                ->hintIcon($attribute->validation_rules['hint_options']['hint_icon'] ?? '', tooltip: $attribute->validation_rules['hint_options']['tooltip'] ?? '')
                ->getUploadedFileNameForStorageUsing(function (UploadedFile $file, $get) use ($attribute) {
                    $outletName = strtolower(str_replace(' ', '_', $get('nama_outlet')));

                    return $outletName.'-'.$attribute->key.'-'.Carbon::now()->format('dmYHis').'.'.$file->getClientOriginalExtension();
                })
                ->required($isRequired)
                ->visible(function ($get) use ($attribute) {
                    $conditionalVisibility = $attribute->validation_rules['conditional_visibility'] ?? null;
                    if (! $conditionalVisibility || ! $conditionalVisibility['enable']) {
                        return true;
                    }
                    $fieldValue = $get("custom_attributes.{$conditionalVisibility['field']}");

                    return $fieldValue == $conditionalVisibility['value'];
                }),

            default => Forms\Components\TextInput::make("custom_attributes.{$attribute->key}")
                ->label($attribute->label)
                ->afterStateHydrated(fn ($state, callable $set) => $set("custom_attributes.{$attribute->key}", $existingValue)),
        };
    }
}
