<?php

namespace App\Filament\Resources\VisitResource\Pages;

use App\Filament\Resources\VisitResource;
use App\Models\Visit;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListVisits extends ListRecords
{
    protected static string $resource = VisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'EXTRACALL' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipe_visit', 'EXTRACALL'))
                ->badge(Visit::query()->where('tipe_visit', 'EXTRACALL')->count())
                ->badgeColor('warning'),
            'PLANNED' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('tipe_visit', 'PLANNED'))
                ->badge(Visit::query()->where('tipe_visit', 'PLANNED')->count())
                ->badgeColor('info'),
        ];
    }
}
