<?php

namespace App\Filament\Resources;

use App\Filament\Clusters\CustomField;
use App\Filament\Resources\CustomAttributeResource\Pages;
use App\Filament\Resources\CustomAttributeResource\RelationManagers;
use App\Models\BadanUsaha;
use App\Models\CustomAttribute;
use App\Models\Division;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomAttributeResource extends Resource
{
    protected static ?string $model = CustomAttribute::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $navigationGroup = 'Settings';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('label')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $key = strtolower(str_replace(' ', '_', $state));
                        $set('key', $key);
                    }),
                Forms\Components\TextInput::make('key')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Toggle::make('required')
                    ->columnSpanFull(),
                Forms\Components\ToggleButtons::make('entity_type')
                    ->label('Terapkan pada data')
                    ->required()
                    ->options([
                        'App\Models\Noo' => 'NOO',
                        'App\Models\Outlet' => 'Outlet'
                    ])
                    ->icons([
                        'App\Models\Noo' => 'heroicon-o-building-office',
                        'App\Models\Outlet' => 'heroicon-o-building-storefront',
                    ])
                    ->inline(),

                Forms\Components\ToggleButtons::make('apply_entity_type')
                    ->label('Terapkan untuk')
                    ->required()
                    ->options([
                        'App\Models\BadanUsaha' => 'Badan Usaha',
                        'App\Models\Division' => 'Divisi'
                    ])
                    ->icons([
                        'App\Models\BadanUsaha' => 'heroicon-o-building-office-2',
                        'App\Models\Division' => 'heroicon-o-briefcase',
                    ])
                    ->inline()
                    ->reactive()  // Membuat field ini reaktif agar bisa memperbarui field lain
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Reset field lainnya berdasarkan pilihan 'apply_to'
                        if ($state == 'App\Models\BadanUsaha') {
                            $set('apply_entity_id', null);
                        } elseif ($state == 'App\Models\Division') {
                            $set('apply_entity_id', null);
                        }
                    }),

                Forms\Components\Select::make('apply_entity_id')
                    ->label('Badan Usaha')
                    ->required()
                    ->options(BadanUsaha::orderBy('name', 'asc')->pluck('name', 'id')) // Ambil semua Badan Usaha dan tampilkan berdasarkan name dan id
                    ->searchable()
                    ->visible(fn(Get $get) => $get('apply_entity_type') === 'App\Models\BadanUsaha')
                    ->columnSpanFull()
                    ->placeholder('Pilih Badan Usaha'),

                Forms\Components\Select::make('apply_entity_id')
                    ->label('Divisi')
                    ->required()
                    ->options(Division::orderBy('name', 'asc')->pluck('name', 'id')) // Ambil semua Badan Usaha dan tampilkan berdasarkan name dan id
                    ->searchable()
                    ->visible(fn(Get $get) => $get('apply_entity_type') === 'App\Models\Division')
                    ->columnSpanFull()
                    ->placeholder('Pilih Divisi'),

                Forms\Components\Select::make('type')
                    ->required()
                    ->searchable()
                    ->options([
                        'TEXT' => 'Single-line text',
                        'NUMBER' => 'Number',
                        'LINK' => 'Link (URL)',
                        'TEXTAREA' => 'Text Area',
                        'CURRENCY' => 'Currency',
                        'DATE' => 'Date',
                        'DATE_AND_TIME' => 'Date and Time',
                        'TOGGLE' => 'Toggle',
                        'TOGGLE_BUTTONS' => 'Toggle Buttons',
                        'SELECT' => 'Select',
                        'CHECKBOX' => 'Checkbox',
                        'CHECKBOX_LIST' => 'Checkbox List',
                        'RADIO' => 'Radio',
                        'UPLOAD' => 'Upload',
                        'PHOTO' => 'Photo',
                    ])
                    ->reactive()
                    ->afterStateUpdated(function (Forms\Set $set, $state) {
                        if (!in_array($state, ['DROPDOWN_SELECT', 'MULTIPLE_SELECT'])) {
                            $set('attribute_options', []);
                        }
                    })
                    ->columnSpanFull(),
                Forms\Components\Repeater::make('options')
                    ->label('Attribute Options')
                    ->schema([
                        Forms\Components\TextInput::make('option')
                            ->label('Option')
                            ->required(),
                    ])
                    ->visible(fn(Get $get) => $get('type') === 'DROPDOWN_SELECT' || $get('type') === 'MULTIPLE_SELECT') // Tipe parameter disesuaikan
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable(),
                Tables\Columns\IconColumn::make('active')
                    ->boolean(),
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
                Tables\Actions\EditAction::make()
                    ->visible(function ($record) {
                        return !$record->system_defined;
                    }),
                Tables\Actions\DeleteAction::make()
                    ->visible(function ($record) {
                        return !$record->system_defined;
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCustomAttributes::route('/'),
        ];
    }
}
