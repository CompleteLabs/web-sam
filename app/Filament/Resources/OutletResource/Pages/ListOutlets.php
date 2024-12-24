<?php

namespace App\Filament\Resources\OutletResource\Pages;

use App\Exports\OutletExport;
use App\Filament\Exports\OutletExporter;
use App\Filament\Imports\OutletImporter;
use App\Filament\Resources\OutletResource;
use App\Models\Outlet;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Gate;
use Filament\Actions\ExportAction;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\ImportAction;

class ListOutlets extends ListRecords
{
    protected static string $resource = OutletResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\CreateAction::make(),
        ];

        // Check if the user is authorized to export
        if (Gate::allows('exportAll', Outlet::class)) {
            $actions[] = ExportAction::make()
                ->exporter(OutletExporter::class)
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
                ->label('Export');
        }

        return $actions;
    }

    public function getTabs(): array
    {
        $query = OutletResource::getEloquentQuery();

        return [
            'all' => Tab::make(),

            'MEMBER' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_member', '1'))
                ->badge($this->getStatusBadgeCount($query, 1))
                ->badgeColor('success'),

            'LEAD' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_member', '0'))
                ->badge($this->getStatusBadgeCount($query, 0))
                ->badgeColor('info'),
        ];
    }

    private function getStatusBadgeCount(Builder $query, ?string $status): int
    {
        if ($status === null) {
            return $query->clone()->count();
        }

        return $query->clone()->where('is_member', $status)->count();
    }
}
