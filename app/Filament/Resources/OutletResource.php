<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutletResource\Pages;
use App\Filament\Resources\OutletResource\RelationManagers;
use App\Models\Outlet;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OutletResource extends Resource
{
    protected static ?string $model = Outlet::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-storefront';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_outlet')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_outlet')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('alamat_outlet')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('nama_pemilik_outlet')
                    ->maxLength(255),
                Forms\Components\TextInput::make('nomer_tlp_outlet')
                    ->maxLength(255),
                Forms\Components\Select::make('badanusaha_id')
                    ->relationship('badanusaha', 'name')
                    ->required(),
                Forms\Components\Select::make('divisi_id')
                    ->relationship('divisi', 'name')
                    ->required(),
                Forms\Components\Select::make('region_id')
                    ->relationship('region', 'name')
                    ->required(),
                Forms\Components\Select::make('cluster_id')
                    ->relationship('cluster', 'name')
                    ->required(),
                Forms\Components\TextInput::make('distric')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('poto_shop_sign')
                    ->maxLength(255),
                Forms\Components\TextInput::make('poto_depan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('poto_kiri')
                    ->maxLength(255),
                Forms\Components\TextInput::make('poto_kanan')
                    ->maxLength(255),
                Forms\Components\TextInput::make('poto_ktp')
                    ->maxLength(255),
                Forms\Components\TextInput::make('video')
                    ->maxLength(255),
                Forms\Components\TextInput::make('limit')
                    ->numeric(),
                Forms\Components\TextInput::make('radius')
                    ->numeric(),
                Forms\Components\TextInput::make('latlong')
                    ->maxLength(255),
                Forms\Components\TextInput::make('status_outlet')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_outlet'),
                Tables\Columns\TextColumn::make('badanusaha.name'),
                Tables\Columns\TextColumn::make('divisi.name'),
                Tables\Columns\TextColumn::make('region.name'),
                Tables\Columns\TextColumn::make('cluster.name'),
                Tables\Columns\TextColumn::make('nama_outlet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_pemilik_outlet'),
                Tables\Columns\TextColumn::make('nomer_tlp_outlet'),
                Tables\Columns\TextColumn::make('distric'),
                Tables\Columns\TextColumn::make('poto_shop_sign'),
                Tables\Columns\TextColumn::make('poto_depan'),
                Tables\Columns\TextColumn::make('poto_kiri'),
                Tables\Columns\TextColumn::make('poto_kanan'),
                Tables\Columns\TextColumn::make('poto_ktp'),
                Tables\Columns\TextColumn::make('video'),
                Tables\Columns\TextColumn::make('limit'),
                Tables\Columns\TextColumn::make('radius'),
                Tables\Columns\TextColumn::make('latlong'),
                Tables\Columns\TextColumn::make('status_outlet'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('kode_outlet', 'asc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutlets::route('/'),
            'create' => Pages\CreateOutlet::route('/create'),
            'edit' => Pages\EditOutlet::route('/{record}/edit'),
        ];
    }
}
