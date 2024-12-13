<?php

namespace App\Filament\Resources\PlanVisitResource\Pages;

use App\Filament\Resources\PlanVisitResource;
use App\Models\User;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Gate;

class ListPlanVisits extends ListRecords
{
    protected static string $resource = PlanVisitResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\CreateAction::make(),
        ];

        // Check if the user is authorized to export
        if (Gate::allows('export', User::class)) {
            $actions[] = Actions\Action::make('export')
                ->color("success")
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    DatePicker::make('tanggal1')
                        ->label('Dari')
                        ->maxDate(now())
                        ->required(),
                    DatePicker::make('tanggal2')
                        ->label('Sampai')
                        ->maxDate(now())
                        ->required(),
                ])
                ->modalWidth('md')
                ->modalHeading('Export Data')
                ->modalSubheading('Pilih periode untuk export data')
                ->modalButton('Export')
                ->action(function (array $data) {
                    // After form is submitted, redirect to the export route
                    return redirect()->route('planvisit.export', [
                        'tanggal1' => $data['tanggal1'],
                        'tanggal2' => $data['tanggal2'],
                    ]);
                });
        }

        return $actions;
    }
}
