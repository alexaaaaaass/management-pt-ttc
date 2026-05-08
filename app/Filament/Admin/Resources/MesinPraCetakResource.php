<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MesinPraCetakResource\Pages;
use App\Models\MesinPraCetak;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MesinPraCetakResource extends Resource
{
    protected static ?string $model = MesinPraCetak::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?string $navigationLabel = 'Mesin Pra Cetak';
    protected static ?int $navigationSort = 2;
    protected static ?string $pluralLabel = 'Mesin Pra Cetak';

    protected static ?string $navigationGroup = 'Pra Cetak';

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
                ->maxLength(100),

            Forms\Components\Select::make('proses')
                ->options([
                    'ctp' => 'CTP',
                    'setting_film' => 'Setting Film',
                    'plate_making' => 'Plate Making',
                ])
                ->required(),

            Forms\Components\Select::make('status')
                ->options([
                    'aktif' => 'Aktif',
                    'nonaktif' => 'Non Aktif',
                ])
                ->default('aktif')
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_mesin')->searchable(),
                Tables\Columns\TextColumn::make('jenis_mesin')->searchable(),
                Tables\Columns\TextColumn::make('kapasitas'),
                Tables\Columns\TextColumn::make('proses'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'aktif',
                        'danger' => 'nonaktif',
                    ]),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMesinPraCetaks::route('/'),
            'create' => Pages\CreateMesinPraCetak::route('/create'),
            'edit' => Pages\EditMesinPraCetak::route('/{record}/edit'),
        ];
    }
}