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

    protected static ?string $navigationGroup = 'Die Making';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Mesin Die Making';

    protected static ?string $modelLabel = 'Mesin Die Making';

    protected static ?string $pluralModelLabel = 'Mesin Die Making';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nama_mesin')
                ->label('Nama Mesin')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('jenis_mesin')
                ->label('Jenis Mesin')
                ->placeholder('Contoh: Auto Die-Cutting / Foil Stamping')
                ->required()
                ->maxLength(255),

            Forms\Components\TextInput::make('kapasitas')
                ->label('Kapasitas (Lembar / Jam)')
                ->numeric()
                ->default(0)
                ->required(),

            Forms\Components\TextInput::make('proses')
                ->label('Cakupan Proses')
                ->placeholder('Contoh: Pond / Die-Cutting, Emboss, Hot Print')
                ->required()
                ->maxLength(255),

            Forms\Components\Select::make('status')
                ->label('Status')
                ->options([
                    'aktif'       => 'Aktif',
                    'maintenance' => 'Maintenance',
                    'nonaktif'    => 'Non Aktif',
                ])
                ->default('aktif')
                ->required(),
        ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_mesin')
                    ->label('Nama Mesin')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('jenis_mesin')
                    ->label('Jenis Mesin')
                    ->badge()
                    ->color('info')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('proses')
                    ->label('Cakupan Proses')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'aktif'       => 'success',
                        'maintenance' => 'warning',
                        default       => 'danger',
                    }),
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

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMesinDieMakings::route('/'),
            'create' => Pages\CreateMesinDieMaking::route('/create'),
            'edit'   => Pages\EditMesinDieMaking::route('/{record}/edit'),
        ];
    }
}
