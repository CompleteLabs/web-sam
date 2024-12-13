<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VisitResource\Pages;
use App\Filament\Resources\VisitResource\RelationManagers;
use App\Models\Visit;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use function Laravel\Prompts\search;

class VisitResource extends Resource
{
    protected static ?string $model = Visit::class;
    protected static ?string $navigationIcon = 'heroicon-o-camera';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Visit Information')
                    ->schema([
                        Forms\Components\DateTimePicker::make('tanggal_visit')
                            ->required()
                            ->label('Tanggal Visit'),
                        Forms\Components\Select::make('tipe_visit')
                            ->options([
                                'PLANNED' => 'PLANNED',
                                'EXTRACALL' => 'EXTRACALL',
                            ])
                            ->required()
                            ->label('Tipe Visit')
                            ->placeholder('Select a type')
                            ->searchable()
                            ->columnSpanFull(),
                    ]),
                Forms\Components\Section::make('User and Outlet')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->relationship('user', 'nama_lengkap')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Pilih User')
                            ->placeholder('Cari User berdasarkan nama lengkap'),
                        Forms\Components\Select::make('outlet_id')
                            ->relationship('outlet', 'nama_outlet')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->label('Pilih Outlet'),
                    ]),
                Forms\Components\Section::make('Location & Timing')
                    ->schema([
                        Forms\Components\TextInput::make('latlong_in')
                            ->maxLength(255)
                            ->label('LatLong In')
                            ->placeholder('Latitude and Longitude at the start'),
                        Forms\Components\TextInput::make('latlong_out')
                            ->maxLength(255)
                            ->label('LatLong Out')
                            ->placeholder('Latitude and Longitude at the end'),
                        Forms\Components\DateTimePicker::make('check_in_time')
                            ->label('Check-in Time'),
                        Forms\Components\DateTimePicker::make('check_out_time')
                            ->label('Check-out Time'),
                        Forms\Components\TextInput::make('durasi_visit')
                            ->numeric()
                            ->label('Durasi Visit (in minutes)'),
                    ]),
                Forms\Components\Section::make('Files')
                    ->schema([
                        Forms\Components\FileUpload::make('picture_visit_in')
                            ->image()
                            ->columnSpanFull()
                            ->required()
                            ->disk('public')
                            ->directory('pictures/visits')
                            ->nullable()
                            ->label('Picture at Start of Visit'),
                        Forms\Components\FileUpload::make('picture_visit_out')
                            ->image()
                            ->columnSpanFull()
                            ->required()
                            ->disk('public')
                            ->directory('pictures/visits')
                            ->nullable()
                            ->label('Picture at End of Visit'),
                    ]),
                Forms\Components\Section::make('Transaction Information')
                    ->schema([
                        Forms\Components\Select::make('transaksi')
                            ->label('Transaksi')
                            ->options([
                                'YES' => 'Yes',
                                'NO' => 'No',
                            ])
                            ->required()
                            ->placeholder('Select Yes or No')
                            ->searchable(),
                        Forms\Components\Textarea::make('laporan_visit')
                            ->columnSpanFull()
                            ->label('Laporan Visit'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_visit')
                    ->label('Tanggal Visit')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('user.nama_lengkap')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('outlet.nama_outlet')
                    ->label('Outlet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipe_visit'),
                Tables\Columns\TextColumn::make('latlong_in')
                    ->label('Lokasi CI'),
                Tables\Columns\TextColumn::make('latlong_out')
                    ->label('Lokasi CO'),
                Tables\Columns\TextColumn::make('check_in_time')
                    ->label('Jam CI')
                    ->time(),
                Tables\Columns\TextColumn::make('check_out_time')
                    ->label('Jam CO')
                    ->time(),
                Tables\Columns\TextColumn::make('transaksi'),
                Tables\Columns\TextColumn::make('durasi_visit'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->deferLoading()
            ->filters([
                Filter::make('created_at')
                    ->form([
                        DatePicker::make('tanggal_visit_from')
                            ->label('Tanggal Visit Mulai'),
                        DatePicker::make('tanggal_visit_until')
                            ->label('Tanggal Visit Akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_visit_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_visit', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_visit_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('tanggal_visit', '<=', $date),
                            );
                    })
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->join('users', 'visits.user_id', '=', 'users.id')
            ->where(function ($query) {
                $user = auth()->user();
                if ($user->role->name == 'SUPER ADMIN') {
                    return;
                }
                $query->where('users.badanusaha_id', $user->badanusaha_id);
            })
            ->select('visits.*', 'users.id as user_id')
            ->orderBy('visits.id', 'desc');
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
