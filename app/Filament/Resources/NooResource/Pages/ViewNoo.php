<?php

namespace App\Filament\Resources\NooResource\Pages;

use App\Filament\Resources\NooResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Gate;

class ViewNoo extends ViewRecord
{
    protected static string $resource = NooResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make('confirm')
                ->label('Confirm')
                ->icon('heroicon-o-check-circle')
                ->color('primary')
                ->visible(fn () => $this->record
                    && $this->record->status === 'PENDING'
                    && Gate::allows('confirm', $this->record))
                ->form([
                    TextInput::make('kode_outlet')
                        ->regex('/^[\S]+$/', 'Kode outlet tidak boleh mengandung spasi')
                        ->helperText('Kode outlet tidak boleh mengandung spasi')
                        ->required(),
                    TextInput::make('limit')
                        ->numeric()
                        ->required(),
                ])
                ->action(function ($record, $data) {
                    $this->record->update([
                        'kode_outlet' => $data['kode_outlet'],
                        'limit' => $data['limit'],
                        'confirmed_at' => Carbon::now(),
                        'confirmed_by' => auth()->user()->nama_lengkap,
                        'status' => 'CONFIRMED',
                    ]);

                    Notification::make()
                        ->title($this->record->nama_outlet.' Confirm')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Persetujuan')
                ->modalSubheading('Apakah Anda yakin ingin mengapprove record ini?')
                ->visible(fn () => $this->record
                    && $this->record->status === 'CONFIRMED'
                    && Gate::allows('approve', $this->record))
                ->action(function () {
                    $this->record->update([
                        'approved_at' => Carbon::now(),
                        'approved_by' => auth()->user()->nama_lengkap,
                        'status' => 'APPROVED',
                    ]);

                    Notification::make()
                        ->title($this->record->nama_outlet.' Approved')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () => $this->record
                    && $this->record->status !== 'REJECTED'
                    && $this->record->status !== 'APPROVED'
                    && Gate::allows('reject', $this->record))
                ->form([
                    Textarea::make('alasan')
                        ->required(),
                ])
                ->action(function ($record, $data) {
                    $this->record->update([
                        'confirmed_at' => Carbon::now(),
                        'confirmed_by' => auth()->user()->name,
                        'status' => 'REJECTED',
                        'keterangan' => $data['alasan'],
                    ]);

                    Notification::make()
                        ->title($this->record->nama_outlet.' Rejected')
                        ->success()
                        ->send();
                }),
        ];
    }
}
