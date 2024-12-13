<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PlanVisitResource\Pages;
use App\Filament\Resources\PlanVisitResource\RelationManagers;
use App\Models\PlanVisit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PlanVisitResource extends Resource
{
    protected static ?string $model = PlanVisit::class;
    protected static ?string $navigationIcon = 'heroicon-o-calendar-date-range';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Grup User Information
                Forms\Components\Section::make('User Information')
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
                            ->label('Pilih Outlet')
                            ->placeholder('Cari Outlet berdasarkan nama'),
                    ])
                    ->collapsible() // Membuat bagian ini dapat diperluas atau disembunyikan
                    ->columns(2),  // Membuat dua kolom, jika space memungkinkan

                // Grup Visit Details
                Forms\Components\Section::make('Visit Details')
                    ->schema([
                        Forms\Components\DatePicker::make('tanggal_visit')
                            ->native(false)
                            ->required()
                            ->label('Tanggal Visit')
                            ->placeholder('Pilih tanggal dan waktu visit')
                            ->helperText('Tanggal dan waktu kunjungan akan dicatat di sini'),
                    ])
                    ->collapsible()
                    ->columns(1), // Menggunakan satu kolom karena hanya ada satu input
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.nama_lengkap')
                    ->label('Nama')
                    ->searchable(),
                Tables\Columns\TextColumn::make('outlet.nama_outlet')
                    ->label('Outlet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('outlet.kode_outlet'),
                Tables\Columns\TextColumn::make('tanggal_visit')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->date('d M Y')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('tanggal_visit', 'desc')
            ->deferLoading()
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->join('users', 'plan_visits.user_id', '=', 'users.id') // Join dengan tabel users berdasarkan user_id
            ->where(function ($query) {
                $user = auth()->user();
                // Jika Super Admin, tampilkan semua data
                if ($user->role->name == 'Super Admin') {
                    return;
                }

                // Jika bukan Super Admin, filter berdasarkan badanusaha_id
                $query->where('users.badanusaha_id', $user->badanusaha_id);
            })
            ->select('plan_visits.*', 'users.id as user_id') // Menentukan kolom yang ingin diambil dan memberi alias untuk users.id
            ->orderBy('plan_visits.id', 'desc'); // Mengurutkan berdasarkan visits.id
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPlanVisits::route('/'),
            'create' => Pages\CreatePlanVisit::route('/create'),
            'edit' => Pages\EditPlanVisit::route('/{record}/edit'),
        ];
    }
}
