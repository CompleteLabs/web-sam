<?php

namespace App\Filament\Resources\NooResource\Pages;

use App\Filament\Resources\NooResource;
use App\Models\Noo;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListNoos extends ListRecords
{
    protected static string $resource = NooResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'pending' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'PENDING'))
                ->badge(Noo::query()->where('status', 'PENDING')->count())
                ->badgeColor('warning'),
            'confirmed' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'CONFIRMED'))
                ->badge(Noo::query()->where('status', 'CONFIRMED')->count())
                ->badgeColor('info'),
            'approved' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'APPROVED'))
                ->badge(Noo::query()->where('status', 'APPROVED')->count())
                ->badgeColor('success'),
            'rejected' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('status', 'REJECTED'))
                ->badge(Noo::query()->where('status', 'REJECTED')->count())
                ->badgeColor('danger'),
        ];
    }
}
