<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Gate;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [
            Actions\CreateAction::make(),
        ];

        // Check if the user is authorized to export
        if (Gate::allows('export', User::class)) {
            $actions[] = Actions\Action::make('export')
                ->color('success')
                ->icon('heroicon-o-arrow-up-tray')
                ->action(function () {
                    return redirect()->route('user.export');
                });
        }

        return $actions;
    }
}
