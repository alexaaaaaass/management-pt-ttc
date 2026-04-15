<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DieMakingResource\Pages;
use App\Models\DieMaking;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DieMakingResource extends Resource
{
    protected static ?string $model = DieMaking::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?string $navigationGroup = 'Diemaking';
    protected static ?string $navigationLabel = 'Data Die Making';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)
                ->schema([

                    Forms\Components\Select::make('spk_id')
                        ->label('Surat Perintah Kerja')
                        ->relationship('spk', 'no_spk')
                        ->required(),

                    Forms\Components\Select::make('mesin_id')
                        ->label('Mesin')
                        ->relationship('mesin', 'nama_mesin')
                        ->required(),

                    Forms\Components\Select::make('operator_id')
                        ->label('Operator')
                        ->relationship('operator', 'nama_operator')
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal_entri')
                        ->required(),

                    Forms\Components\Select::make('proses_diemaking')
                        ->options([
                            'UV Varnish' => 'UV Varnish',
                            'Hot Print' => 'Hot Print',
                            'UV Spot' => 'UV Spot',
                            'UV Holo' => 'UV Holo',
                            'Embos' => 'Embos',
                            'Cutting' => 'Cutting',
                        ])
                        ->required(),

                    Forms\Components\Select::make('tahap_diemaking')
                        ->options([
                            'Proses Die Making 1' => 'Proses Die Making 1',
                            'Proses Die Making 2' => 'Proses Die Making 2',
                        ])
                        ->required(),

                    Forms\Components\TextInput::make('hasil_baik')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\TextInput::make('hasil_rusak')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\TextInput::make('semi_waste')
                        ->numeric()
                        ->default(0)
                        ->required(),

                    Forms\Components\Select::make('note_waste')
                        ->options([
                            'CETAK KOTOR' => 'CETAK KOTOR',
                            'CETAK LUNTUR' => 'CETAK LUNTUR',
                            'CETAK BINTIK' => 'CETAK BINTIK',
                        ]),

                    Forms\Components\Select::make('keterangan_spk')
                        ->options([
                            'Reguler' => 'Reguler',
                            'Subcount' => 'Subcount',
                        ])
                        ->required(),
                ])
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('spk.no_spk')
                    ->label('SPK')
                    ->searchable(),

                Tables\Columns\TextColumn::make('mesin.nama_mesin')
                    ->label('Mesin'),

                Tables\Columns\TextColumn::make('operator.nama_operator')
                    ->label('Operator'),

                Tables\Columns\TextColumn::make('proses_diemaking'),

                Tables\Columns\TextColumn::make('tahap_diemaking'),

                Tables\Columns\TextColumn::make('hasil_baik'),

                Tables\Columns\TextColumn::make('tanggal_entri')
                    ->date('d-m-Y'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDieMakings::route('/'),
            'create' => Pages\CreateDieMaking::route('/create'),
            'edit' => Pages\EditDieMaking::route('/{record}/edit'),
        ];
    }
}