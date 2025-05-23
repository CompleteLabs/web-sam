<?php

namespace App\Filament\Resources\ClusterResource\Pages;

use App\Filament\Exports\ClusterExporter;
use App\Filament\Resources\ClusterResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageClusters extends ManageRecords
{
    protected static string $resource = ClusterResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            \Filament\Actions\ExportAction::make()
                ->exporter(ClusterExporter::class)
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
                ->label('Export'),
        ];
    }
}
