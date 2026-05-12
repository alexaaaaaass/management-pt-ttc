<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MesinFinisResource\Pages;
use App\Models\MesinFinis;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MesinFinisResource extends Resource
{
    protected static ?string $model = MesinFinis::class;

    protected static ?string $navigationGroup = 'Finishing';

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'Mesin Finish';

    protected static ?string $modelLabel = 'Mesin Finish';
    protected static ?int $navigationSort = 4;

    protected static ?string $pluralModelLabel = 'Mesin Finish';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama_mesin')
                    ->label('Nama Mesin')
                    ->required()
                    ->maxLength(255),

                Forms\Components\Select::make('jenis_mesin')
                    ->label('Jenis Mesin')
                    ->options([
                        'manual' => 'Manual',
                        'semi_otomatis' => 'Semi Otomatis',
                        'otomatis' => 'Otomatis',
                    ])
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('kapasitas')
                    ->label('Kapasitas')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('proses')
                    ->label('Proses')
                    ->required()
                    ->maxLength(255),

             Forms\Components\Select::make('status')
    ->label('Status')
    ->options([
        '1' => 'Aktif',        // '1' akan disimpan sebagai integer 1
        '2' => 'Maintenance',  // '2' akan disimpan sebagai integer 2
        '0' => 'Non Aktif',    // '0' akan disimpan sebagai integer 0
    ])
    ->default('1')
    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama_mesin')
                    ->label('Nama Mesin')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis_mesin')
                    ->label('Jenis Mesin')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kapasitas')
                    ->label('Kapasitas')
                    ->sortable(),

                Tables\Columns\TextColumn::make('proses')
                    ->label('Proses')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'aktif',
                        'warning' => 'maintenance',
                        'danger' => 'nonaktif',
                    ]),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMesinFinis::route('/'),
            'create' => Pages\CreateMesinFinis::route('/create'),
            'edit' => Pages\EditMesinFinis::route('/{record}/edit'),
        ];
    }
}