<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PrintingResource\Pages;
use App\Models\Printing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class PrintingResource extends Resource
{
    protected static ?string $model = Printing::class;
    protected static ?string $navigationGroup = 'Printing';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
{
    return $form->schema([
        Forms\Components\Grid::make(2)
            ->schema([

                Forms\Components\Select::make('spk_id')
                    ->label('Surat Perintah Kerja')
                    ->relationship('spk', 'no_spk')
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('mesin_id')
                    ->label('Mesin')
                    ->relationship('mesin', 'nama_mesin')
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('operator_id')
                    ->label('Operator')
                    ->relationship('operator', 'nama_operator')
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_entri')
                    ->required(),

                Forms\Components\Select::make('proses_printing')
                    ->options([
                        'potong' => 'Potong',
                        'printing' => 'Printing',
                    ])
                    ->required(),

                Forms\Components\Select::make('tahap_printing')
                    ->options([
                        'potong' => 'Potong',
                        'proses_cetak' => 'Proses Cetak',
                        'proses_cetak_2' => 'Proses Cetak 2',
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
                        'CETAK_LUNTUR' => 'Cetak Luntur',
                        'CETAK_BOCOR_BANJIR' => 'Cetak Bocor / Banjir',
                    ]),

                Forms\Components\TextInput::make('keterangan_spk')
                    ->columnSpanFull()
                    ->required(),
            ])
    ]);
}

  public static function table(Table $table): Table
{
    return $table->columns([
        Tables\Columns\TextColumn::make('spk.no_spk')
            ->label('No SPK')
            ->searchable(),

        Tables\Columns\TextColumn::make('mesin.nama_mesin')
            ->label('Mesin'),

        Tables\Columns\TextColumn::make('operator.nama_operator')
            ->label('Operator'),

        Tables\Columns\TextColumn::make('tanggal_entri')
            ->date(),

        Tables\Columns\TextColumn::make('proses_printing'),

        Tables\Columns\TextColumn::make('tahap_printing'),

        Tables\Columns\TextColumn::make('hasil_baik'),

        Tables\Columns\TextColumn::make('hasil_rusak'),

        Tables\Columns\TextColumn::make('semi_waste'),
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
            'index' => Pages\ListPrintings::route('/'),
            'create' => Pages\CreatePrinting::route('/create'),
            'edit' => Pages\EditPrinting::route('/{record}/edit'),
        ];
    }
}