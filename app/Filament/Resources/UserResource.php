<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('username')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('nama_lengkap')
                    ->required()
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
                Forms\Components\TextInput::make('cluster_id2')
                    ->numeric(),
                Forms\Components\Select::make('role_id')
                    ->relationship('role', 'name')
                    ->required(),
                Forms\Components\Select::make('tm_id')
                    ->relationship('tm', 'id')
                    ->required(),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('two_factor_secret')
                    ->columnSpanFull(),
                Forms\Components\Textarea::make('two_factor_recovery_codes')
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('id_notif')
                    ->maxLength(255),
                Forms\Components\TextInput::make('profile_photo_path')
                    ->maxLength(255),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('nama_lengkap')
                    ->searchable(),
                Tables\Columns\TextColumn::make('badanusaha.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('divisi.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('region.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cluster.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cluster_id2')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tm.id')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_notif')
                    ->searchable(),
                Tables\Columns\TextColumn::make('profile_photo_path')
                    ->searchable(),
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
