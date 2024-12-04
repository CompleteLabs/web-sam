<?php

namespace App\Filament\Resources\NooResource\Pages;

use App\Filament\Resources\NooResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateNoo extends CreateRecord
{
    protected static string $resource = NooResource::class;
}
