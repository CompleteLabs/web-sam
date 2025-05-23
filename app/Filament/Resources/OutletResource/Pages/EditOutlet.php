<?php

namespace App\Filament\Resources\OutletResource\Pages;

use App\Filament\Resources\OutletResource;
use App\Models\CustomAttribute;
use App\Models\CustomAttributeValue;
use App\Models\Outlet;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutlet extends EditRecord
{
    protected static string $resource = OutletResource::class;

    private array $customAttributes = [];

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $entityId = $this->record->id; // Ambil ID dari outlet yang sedang diedit
        $entityType = get_class($this->record);

        // Ambil nilai custom_attributes dari database
        $customAttributes = CustomAttributeValue::where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->get()
            ->pluck('value', 'customAttribute.key')
            ->toArray();

        $data['custom_attributes'] = $customAttributes;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->customAttributes = $data['custom_attributes'] ?? [];
        unset($data['custom_attributes']);

        return $data;
    }

    protected function afterSave(): void
    {
        $record = $this->record;
        if (! empty($this->customAttributes)) {
            foreach ($this->customAttributes as $attributeKey => $attributeValue) {
                $attributeDefinition = CustomAttribute::where('key', $attributeKey)->first();
                if (! $attributeDefinition) {
                    continue;
                }
                CustomAttributeValue::updateOrCreate(
                    [
                        'entity_type' => get_class($record),
                        'entity_id' => $record->id,
                        'custom_attribute_id' => $attributeDefinition->id,
                    ],
                    [
                        'value' => $attributeValue,
                    ]
                );
            }
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
