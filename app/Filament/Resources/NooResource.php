<?php

namespace App\Filament\Resources;

use App\Filament\Resources\NooResource\Pages;
use App\Filament\Resources\NooResource\RelationManagers;
use App\Models\Noo;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Gate;

use function Laravel\Prompts\form;

class NooResource extends Resource
{
    protected static ?string $model = Noo::class;
    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?int $navigationSort = 2;
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('kode_outlet')
                    ->maxLength(255),
                Forms\Components\Select::make('badanusaha_id')
                    ->relationship('badanusaha', 'name')
                    ->required(),
                Forms\Components\Select::make('divisi_id')
                    ->relationship('divisi', 'name')
                    ->required(),
                Forms\Components\TextInput::make('nama_outlet')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('alamat_outlet')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('nama_pemilik_outlet')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nomer_tlp_outlet')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nomer_wakil_outlet')
                    ->maxLength(255),
                Forms\Components\TextInput::make('ktp_outlet')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('distric')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('region_id')
                    ->relationship('region', 'name')
                    ->required(),
                Forms\Components\Select::make('cluster_id')
                    ->relationship('cluster', 'name')
                    ->required(),
                Forms\Components\TextInput::make('poto_shop_sign')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('poto_depan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('poto_kiri')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('poto_kanan')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('poto_ktp')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('video')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('oppo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('vivo')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('realme')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('samsung')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('xiaomi')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('fl')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('latlong')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('limit')
                    ->numeric(),
                Forms\Components\TextInput::make('status')
                    ->required(),
                Forms\Components\TextInput::make('created_by')
                    ->required()
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('rejected_at'),
                Forms\Components\TextInput::make('rejected_by')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('confirmed_at'),
                Forms\Components\TextInput::make('confirmed_by')
                    ->maxLength(255),
                Forms\Components\TextInput::make('approved_by')
                    ->maxLength(255),
                Forms\Components\DateTimePicker::make('approved_at'),
                Forms\Components\TextInput::make('keterangan')
                    ->maxLength(255),
                Forms\Components\Select::make('tm_id')
                    ->relationship('tm', 'nama_lengkap')
                    ->preload()
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->date('d M Y'),
                Tables\Columns\TextColumn::make('created_by')
                    ->searchable(),
                Tables\Columns\TextColumn::make('kode_outlet'),
                Tables\Columns\TextColumn::make('divisi.name'),
                Tables\Columns\TextColumn::make('badanusaha.name'),
                Tables\Columns\TextColumn::make('nama_outlet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('alamat_outlet'),
                Tables\Columns\TextColumn::make('nama_pemilik_outlet'),
                Tables\Columns\TextColumn::make('ktp_outlet'),
                Tables\Columns\TextColumn::make('nomer_tlp_outlet'),
                Tables\Columns\TextColumn::make('nomer_wakil_outlet'),
                Tables\Columns\TextColumn::make('distric'),
                Tables\Columns\TextColumn::make('region.name'),
                Tables\Columns\TextColumn::make('cluster.name'),
                Tables\Columns\TextColumn::make('poto_ktp')
                    ->formatStateUsing(fn($state) => '<a href="' . asset('storage/' . $state) . '" class="" target="_blank">Lihat Foto</a>')
                    ->html(),
                Tables\Columns\TextColumn::make('poto_shop_sign')
                    ->formatStateUsing(fn($state) => '<a href="' . asset('storage/' . $state) . '" class="" target="_blank">Lihat Foto</a>')
                    ->html(),
                Tables\Columns\TextColumn::make('poto_depan')
                    ->formatStateUsing(fn($state) => '<a href="' . asset('storage/' . $state) . '" class="" target="_blank">Lihat Foto</a>')
                    ->html(),
                Tables\Columns\TextColumn::make('poto_kanan')
                    ->formatStateUsing(fn($state) => '<a href="' . asset('storage/' . $state) . '" class="" target="_blank">Lihat Foto</a>')
                    ->html(),
                Tables\Columns\TextColumn::make('poto_kiri')
                    ->formatStateUsing(fn($state) => '<a href="' . asset('storage/' . $state) . '" class="" target="_blank">Lihat Foto</a>')
                    ->html(),
                Tables\Columns\TextColumn::make('video')
                    ->formatStateUsing(fn($state) => '<a href="' . asset('storage/' . $state) . '" class="" target="_blank">Lihat Video</a>')
                    ->html(),
                Tables\Columns\TextColumn::make('oppo'),
                Tables\Columns\TextColumn::make('vivo'),
                Tables\Columns\TextColumn::make('realme'),
                Tables\Columns\TextColumn::make('samsung'),
                Tables\Columns\TextColumn::make('xiaomi'),
                Tables\Columns\TextColumn::make('fl'),
                Tables\Columns\TextColumn::make('latlong')
                    ->formatStateUsing(fn($state) => '<a href="https://www.google.com/maps/place/' . $state . '" target="_blank">Lihat Lokasi</a>')
                    ->html(),
                Tables\Columns\TextColumn::make('limit'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->deferLoading()
            ->filters([
                Tables\Filters\SelectFilter::make('divisi.name')
                    ->relationship('divisi', 'name')
                    ->searchable()
                    ->preload()
                    ->label('Divisi'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status !== 'CONFIRMED' && $record->status !== 'REJECTED' && $record->status !== 'APPROVED' && Gate::allows('approve', $record))
                    ->form([
                        TextInput::make('kode_outlet')
                            ->required(),
                        TextInput::make('limit')
                            ->numeric()
                            ->required(),
                    ])
                    ->action(function ($record, $data) {
                        $record->update([
                            'kode_outlet' => $data['kode_outlet'],
                            'limit' => $data['limit'],
                            'confirmed_at' => Carbon::now(),
                            'confirmed_by' => auth()->user()->name,
                            'status' => 'CONFIRMED',
                            Notification::make()
                                ->title($record->nama_outlet . ' Confirm')
                                ->success()
                                ->send(),
                        ]);
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn($record) => $record->status !== 'CONFIRMED' && $record->status !== 'REJECTED' && $record->status !== 'APPROVED' && Gate::allows('reject', $record))
                    ->action(function ($record, $data) {
                        $record->update([
                            'confirmed_at' => Carbon::now(),
                            'confirmed_by' => auth()->user()->name,
                            'status' => 'REJECTED',
                        ]);
                        Notification::make()
                            ->title($record->nama_outlet . ' Rejected')
                            ->success()
                            ->send();
                    }),
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
            ->where(function ($query) {
                $user = auth()->user();
                // Display all tickets to Super Admin
                if ($user->role->name == 'Super Admin') {
                    return;
                } else {
                    $query->where('noos.badanusaha_id', $user->badanusaha_id);
                }
            });
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListNoos::route('/'),
            'create' => Pages\CreateNoo::route('/create'),
            // 'edit' => Pages\EditNoo::route('/{record}/edit'),
        ];
    }
}
