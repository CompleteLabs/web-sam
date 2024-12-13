<?php

namespace App\Filament\Resources\VisitResource\Pages;

use App\Filament\Resources\VisitResource;
use App\Models\User;
use App\Models\Visit;
use Filament\Actions;
use Filament\Forms\Components\DatePicker;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class ListVisits extends ListRecords
{
    protected static string $resource = VisitResource::class;

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
                    return redirect()->route('visit.export', [
                        'tanggal1' => $data['tanggal1'],
                        'tanggal2' => $data['tanggal2'],
                    ]);
                });
        }

        return $actions;
    }

    public function getTabs(): array
    {
        return [
            'SEMUA' => Tab::make(),
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
