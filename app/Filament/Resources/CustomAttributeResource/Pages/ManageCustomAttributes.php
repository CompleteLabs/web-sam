<?php

namespace App\Filament\Resources\CustomAttributeResource\Pages;

use App\Filament\Resources\CustomAttributeResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Database\Eloquent\Builder;

class ManageCustomAttributes extends ManageRecords
{
    protected static string $resource = CustomAttributeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->slideOver(),
        ];
    }

    // public function getTabs(): array
    // {
    //     // Ambil query yang sudah difilter berdasarkan role
    //     $query = CustomAttributeResource::getEloquentQuery(); // Panggil getEloquentQuery() dari Resource

    //     return [
    //         'NOO' => Tab::make()
    //             ->modifyQueryUsing(fn(Builder $query) => $query
    //                 ->where('entity_type', 'App\Models\Noo')
    //                 ->orWhereNull('entity_type')
    //             ),

    //         'Outlet' => Tab::make()
    //             ->modifyQueryUsing(fn(Builder $query) => $query
    //                 ->where('entity_type', 'App\Models\Outlet')
    //                 ->orWhereNull('entity_type')
    //             ),
    //     ];

    // }
}
