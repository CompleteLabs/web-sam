<?php

namespace App\Filament\Resources\OutletResource\Pages;

use App\Filament\Resources\OutletResource;
use App\Models\AttributeDefinition;
use App\Models\CustomAttribute;
use App\Models\CustomAttributeValue;
use App\Models\EntityAttribute;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOutlet extends CreateRecord
{
    protected static string $resource = OutletResource::class;

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
        if (!empty($this->customAttributes)) {
            foreach ($this->customAttributes as $attributeKey => $attributeValue) {
                $attributeDefinition = CustomAttribute::where('key', $attributeKey)->first();
                if (!$attributeDefinition) {
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
