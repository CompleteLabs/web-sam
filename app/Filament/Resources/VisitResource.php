<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitResource\Pages;
use App\Filament\Resources\VisitResource\RelationManagers;
use App\Models\Visit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;
    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\DateTimePicker::make('tanggal_visit')
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'id')
                    ->required(),
                Forms\Components\Select::make('outlet_id')
                    ->relationship('outlet', 'id')
                    ->required(),
                Forms\Components\TextInput::make('tipe_visit')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('latlong_in')
                    ->maxLength(255),
                Forms\Components\TextInput::make('latlong_out')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('check_in_time'),
                Forms\Components\DateTimePicker::make('check_out_time'),
                Forms\Components\Textarea::make('laporan_visit')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('transaksi'),
                Forms\Components\TextInput::make('durasi_visit')
                    ->numeric(),
                Forms\Components\Textarea::make('picture_visit_in')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('picture_visit_out')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_visit')
                    ->date('d F Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.nama_lengkap')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('outlet.nama_outlet')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tipe_visit')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latlong_in')
                    ->searchable(),
                Tables\Columns\TextColumn::make('latlong_out')
                    ->searchable(),
                Tables\Columns\TextColumn::make('check_in_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('check_out_time')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('transaksi'),
                Tables\Columns\TextColumn::make('durasi_visit')
                    ->numeric()
                    ->sortable(),
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
            ->defaultSort('created_at', 'desc')
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
            'index' => Pages\ListVisits::route('/'),
            'create' => Pages\CreateVisit::route('/create'),
            'edit' => Pages\EditVisit::route('/{record}/edit'),
        ];
    }
}
