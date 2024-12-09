<?php

namespace App\Filament\Resources\PlanVisitResource\Pages;

use App\Filament\Resources\PlanVisitResource;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;

class ListPlanVisits extends ListRecords
{
    protected static string $resource = PlanVisitResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('export')
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
                }),
        ];
    }
}
