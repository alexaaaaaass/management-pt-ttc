<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MesinDieMakingResource\Pages;
use App\Models\MesinDieMaking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MesinDieMakingResource extends Resource
{
    protected static ?string $model = MesinDieMaking::class;

    protected static ?string $navigationGroup = 'Diemaking';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

     public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_mesin')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('jenis_mesin')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('kapasitas')
                ->numeric()
                ->required(),

            Forms\Components\TextInput::make('proses')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('status')
                ->options([
                    'aktif' => 'Aktif',
                    'maintenance' => 'Maintenance',
                    'nonaktif' => 'Nonaktif',
                ])
                ->default('aktif')
                ->required(),
        ]);
    }

   public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_mesin')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jenis_mesin')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kapasitas'),

                Tables\Columns\TextColumn::make('proses'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'aktif',
                        'warning' => 'maintenance',
                        'danger' => 'nonaktif',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListMesinDieMakings::route('/'),
            'create' => Pages\CreateMesinDieMaking::route('/create'),
            'edit' => Pages\EditMesinDieMaking::route('/{record}/edit'),
        ];
    }
}