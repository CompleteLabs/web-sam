<?php

namespace App\Filament\Resources\NooResource\Pages;

use App\Filament\Resources\NooResource;
use App\Models\CustomAttribute;
use App\Models\CustomAttributeValue;
use Filament\Resources\Pages\CreateRecord;

class CreateNoo extends CreateRecord
{
    protected static string $resource = NooResource::class;

    private array $customAttributes = [];

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->customAttributes = $data['custom_attributes'] ?? [];
        unset($data['custom_attributes']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        if (! empty($this->customAttributes)) {
            foreach ($this->customAttributes as $attributeKey => $attributeValue) {
                $attributeDefinition = CustomAttribute::where('key', $attributeKey)->first();
                if (! $attributeDefinition) {
                    continue;
                }
                if ($attributeDefinition) {
                    CustomAttributeValue::create([
                        'entity_type' => get_class($record),
                        'entity_id' => $record->id,
                        'custom_attribute_id' => $attributeDefinition->id,
                        'value' => $attributeValue,
                    ]);
                }
            }
        }
    }
}
