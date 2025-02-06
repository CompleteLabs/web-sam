<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClusterResource\Pages;
use App\Filament\Resources\ClusterResource\RelationManagers;
use App\Models\Cluster;
use App\Services\OrganizationalStructureService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ClusterResource extends Resource
{
    protected static ?string $model = Cluster::class;
    protected static ?string $navigationIcon = 'heroicon-o-map-pin';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('badanusaha_id')
                    ->label('Badan Usaha')
                    ->searchable()
                    ->required()
                    ->reactive()
                    ->placeholder('Pilih badan usaha')
                    ->options(function (callable $get) {
                        $organizationalStructureService = new OrganizationalStructureService();
                        return $organizationalStructureService->getBadanUsahaOptions();
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('divisi_id', null);
                        $set('region_id', null);
                    }),

                Forms\Components\Select::make('divisi_id')
                    ->label('Divisi')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->options(function (callable $get) {
                        $badanusahaId = $get('badanusaha_id');
                        if (!$badanusahaId) {
                            return [];
                        }
                        $organizationalStructureService = new OrganizationalStructureService();
                        return $organizationalStructureService->getDivisiOptions($badanusahaId);
                    })
                    ->afterStateUpdated(function ($state, callable $set) {
                        $set('region_id', null);
                    }),
                Forms\Components\Select::make('region_id')
                    ->label('Region')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->reactive()
                    ->options(function (callable $get) {
                        $divisiId = $get('divisi_id');
                        if (!$divisiId) {
                            return [];
                        }
                        $organizationalStructureService = new OrganizationalStructureService();
                        return $organizationalStructureService->getRegionOptions($divisiId);
                    }),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('badanusaha.name'),
                Tables\Columns\TextColumn::make('divisi.name'),
                Tables\Columns\TextColumn::make('region.name'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name', 'asc')
            ->groups([
                Group::make('region.name')
                    ->label('Region')
                    ->collapsible(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('divisi.name')
                    ->relationship('divisi', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Divisi'),
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
            $query->whereIn('clusters.badanusaha_id', $filterData);
        } elseif ($role->filter_type === 'App\Models\Division') {
            $query->whereIn('clusters.divisi_id', $filterData);
        } elseif ($role->filter_type === 'App\Models\Region') {
            $query->whereIn('clusters.region_id', $filterData);
        } elseif ($role->filter_type === 'App\Models\Cluster') {
            $query->whereIn('clusters.id', $filterData);
        }

        return $query;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageClusters::route('/'),
        ];
    }
}
