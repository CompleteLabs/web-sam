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
}
