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
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required(),

                Forms\Components\Placeholder::make('plate_readiness_info')
                    ->label('Status Kesiapan Plate Pra Cetak & Lokasi Rak')
                    ->visible(fn (Forms\Get $get) => filled($get('spk_id')))
                    ->content(function (Forms\Get $get) {
                        $spkId = $get('spk_id');
                        if (! $spkId) {
                            return '-';
                        }
                        $pracetak = \App\Models\PracetakProcess::with('plateArchive')
                            ->where('spk_id', $spkId)
                            ->latest('id')
                            ->first();

                        if (! $pracetak) {
                            return new \Illuminate\Support\HtmlString(
                                '<div style="padding: 8px 12px; border-radius: 8px; border: 1px solid rgba(245,158,11,0.35); background: rgba(245,158,11,0.08); font-size: 12px; color: #f59e0b; font-weight: 600;">'
                                . 'Belum ada catatan proses Pra Cetak untuk SPK ini. Pastikan plate sudah siap sebelum naik cetak.'
                                . '</div>'
                            );
                        }

                        $isReady = in_array($pracetak->status, ['selesai', 'handed_over'], true);
                        $color = $isReady ? '#10b981' : '#3b82f6';
                        $bg = $isReady ? 'rgba(16,185,129,0.08)' : 'rgba(59,130,246,0.08)';
                        $border = $isReady ? 'rgba(16,185,129,0.35)' : 'rgba(59,130,246,0.35)';
                        $rak = $pracetak->lokasi_simpan_plate ?: ($pracetak->plateArchive?->lokasi_rak ?: 'Belum ditentukan');
                        $arsip = $pracetak->plateArchive?->kode_arsip ? " · Arsip: {$pracetak->plateArchive->kode_arsip}" : '';
                        $handover = $pracetak->handed_over_to ? " · Penerima: {$pracetak->handed_over_to}" : '';

                        return new \Illuminate\Support\HtmlString(
                            '<div style="padding: 8px 12px; border-radius: 8px; border: 1px solid ' . $border . '; background: ' . $bg . '; font-size: 12px; color: ' . $color . '; font-weight: 600;">'
                            . 'Status Plate: <strong>' . e($pracetak->status_label) . '</strong>'
                            . ' · Lokasi Rak: <strong>' . e($rak) . '</strong>'
                            . ' · ' . (int) ($pracetak->jumlah_warna_plate ?: $pracetak->qty) . ' Warna'
                            . e($arsip . $handover)
                            . '</div>'
                        );
                    })
                    ->columnSpanFull(),

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
                        'color-printing' => 'Color Printing',
                        'cold-foil' => 'Cold Foil',
                        'spot-uv' => 'Spot UV',
                        'furnish-uv' => 'Furnish UV',
                        'drip-off' => 'Drip Off',
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
    ->label('Hasil Baik Satuan (Meter)')
    ->numeric()
    ->suffix('m')
    ->default(0)
    ->required(),

Forms\Components\TextInput::make('hasil_rusak')
    ->label('Hasil Rusak Satuan (Meter)')
    ->numeric()
    ->suffix('m')
    ->default(0)
    ->required(),

Forms\Components\TextInput::make('semi_waste')
    ->label('Semi Waste Satuan (Meter)')
    ->numeric()
    ->suffix('m')
    ->default(0)
    ->required(),

              Forms\Components\Select::make('error_production_id')
    ->label('Note Waste')
    ->relationship(
        name: 'errorProduction',
        titleAttribute: 'nama_error'
    )
    ->searchable()
    ->preload(),

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