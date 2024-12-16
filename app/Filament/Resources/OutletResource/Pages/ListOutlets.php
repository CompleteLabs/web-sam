<?php

namespace App\Filament\Resources\OutletResource\Pages;

use App\Filament\Resources\OutletResource;
use App\Models\Outlet;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Gate;

class ListOutlets extends ListRecords
{
    protected static string $resource = OutletResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\CreateAction::make(),
        ];

        // Check if the user is authorized to export
        if (Gate::allows('export', Outlet::class)) {
            $actions[] = Actions\Action::make('export')
                ->color("success")
                ->icon('heroicon-o-arrow-up-tray')
                ->action(function () {
                    return redirect()->route('outlet.export');
                });
        }

        return $actions;
    }
}
