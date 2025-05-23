<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BadanUsahaResource\Pages;
use App\Models\BadanUsaha;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BadanUsahaResource extends Resource
{
    protected static ?string $model = BadanUsaha::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user = auth()->user();
        $role = $user->role;
        $filterData = $role->filter_data ?? [];

        if ($role->filter_type === 'App\Models\BadanUsaha') {
            $query->whereIn('badanusahas.id', $filterData);
        } elseif ($role->filter_type === 'App\Models\Division') {
            $query->whereHas('divisi', function ($q) use ($filterData) {
                $q->whereIn('divisions.id', $filterData);
            });
        } elseif ($role->filter_type === 'App\Models\Region') {
            $query->whereHas('region', function ($q) use ($filterData) {
                $q->whereIn('regions.id', $filterData);
            });
        } elseif ($role->filter_type === 'App\Models\Cluster') {
            $query->whereHas('cluster', function ($q) use ($filterData) {
                $q->whereIn('clusters.id', $filterData);
            });
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageBadanUsahas::route('/'),
        ];
    }
}
