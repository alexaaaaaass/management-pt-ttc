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
                    ->relationship(
                        'spk',
                        'no_spk',
                        modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => $query->orderByDesc('id')
                    )
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
                    ->default(now())
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
                    ->label('Hasil Rusak / Total Waste (Meter)')
                    ->numeric()
                    ->suffix('m')
                    ->default(0)
                    ->helperText('Otomatis terisi dari total Qty Waste di bawah bila rincian Note Waste diisi')
                    ->required(),

                Forms\Components\TextInput::make('semi_waste')
                    ->label('Semi Waste Satuan (Meter)')
                    ->numeric()
                    ->suffix('m')
                    ->default(0)
                    ->required(),

                Forms\Components\Section::make('Rincian Note Waste & Qty Waste')
                    ->description('Pilih jenis waste dari Master Note Waste lalu masukkan jumlah qty waste (meter). Bisa lebih dari 1 penyebab waste.')
                    ->columnSpanFull()
                    ->schema([
                        Forms\Components\Repeater::make('wastes')
                            ->label('Daftar Note Waste')
                            ->relationship('wastes')
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Note Waste')
                            ->reorderable(false)
                            ->collapsible()
                            ->itemLabel(function (array $state): string {
                                $errId = $state['error_production_id'] ?? null;
                                $name = $errId ? (\App\Models\ErrorProduction::whereKey($errId)->value('nama_error') ?? 'Note Waste') : 'Pilih Note Waste';
                                $qty = (float) ($state['qty_waste'] ?? 0);

                                return $qty > 0 ? "{$name} — " . rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',') . ' m' : $name;
                            })
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action->after(function (Forms\Get $get, Forms\Set $set) {
                                    $wastes = $get('wastes') ?? [];
                                    $sum = collect($wastes)->sum(fn ($row) => (float) ($row['qty_waste'] ?? 0));
                                    $set('hasil_rusak', (int) round($sum));
                                })
                            )
                            ->schema([
                                Forms\Components\Select::make('error_production_id')
                                    ->label('Note Waste')
                                    ->relationship(
                                        name: 'errorProduction',
                                        titleAttribute: 'nama_error',
                                        modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => $query->orderBy('kode_error')
                                    )
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        Forms\Components\TextInput::make('kode_error')
                                            ->label('Kode Waste')
                                            ->default(fn () => ErrorProductionResource::suggestKode())
                                            ->required()
                                            ->unique('error_productions', 'kode_error'),
                                        Forms\Components\TextInput::make('nama_error')
                                            ->label('Nama Note Waste')
                                            ->placeholder('Contoh: CETAK MIRING')
                                            ->required(),
                                    ]),

                                Forms\Components\TextInput::make('qty_waste')
                                    ->label('Qty Waste (Meter)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->suffix('m')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                        $wastes = $get('../../wastes') ?? [];
                                        $sum = collect($wastes)->sum(fn ($row) => (float) ($row['qty_waste'] ?? 0));
                                        $set('../../hasil_rusak', (int) round($sum));
                                    }),

                                Forms\Components\TextInput::make('catatan')
                                    ->label('Catatan (Opsional)')
                                    ->placeholder('Contoh: Awal setting roll / warna Cyan geser'),
                            ])
                            ->columns(3),

                        Forms\Components\Placeholder::make('total_waste_summary')
                            ->label('Ringkasan Total Waste')
                            ->content(function (Forms\Get $get): \Illuminate\Support\HtmlString {
                                $wastes = $get('wastes') ?? [];
                                if (empty($wastes)) {
                                    return new \Illuminate\Support\HtmlString(
                                        '<span style="font-size:12px;opacity:0.7;">Belum ada rincian Note Waste. Klik <strong>Tambah Note Waste</strong> untuk mencatat penyebab &amp; qty waste.</span>'
                                    );
                                }

                                $total = collect($wastes)->sum(fn ($row) => (float) ($row['qty_waste'] ?? 0));
                                $fmt = rtrim(rtrim(number_format($total, 2, ',', '.'), '0'), ',');

                                return new \Illuminate\Support\HtmlString(
                                    '<div style="border-radius:0.5rem;border:1px solid rgba(239,68,68,0.35);background:rgba(239,68,68,0.1);padding:0.5rem 0.75rem;font-size:0.8125rem;">'
                                    . 'Total dari ' . count($wastes) . ' jenis waste: <strong style="color:#f87171;">' . $fmt . ' m</strong> (otomatis disinkronkan ke Hasil Rusak).'
                                    . '</div>'
                                );
                            }),
                    ]),

                Forms\Components\TextInput::make('keterangan_spk')
                    ->columnSpanFull()
                    ->required(),
            ])
    ]);
}

  public static function table(Table $table): Table
{
    return $table
        ->defaultSort('id', 'desc')
        ->searchPlaceholder('Cari No SPK, Mesin, Operator, Keterangan, Waste...')
        ->columns([
            Tables\Columns\TextColumn::make('spk.no_spk')
                ->label('No SPK')
                ->searchable()
                ->sortable()
                ->weight('bold'),

            Tables\Columns\TextColumn::make('mesin.nama_mesin')
                ->label('Mesin')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('operator.nama_operator')
                ->label('Operator')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('tanggal_entri')
                ->label('Tanggal Entri')
                ->date('d M Y')
                ->sortable(),

            Tables\Columns\TextColumn::make('proses_printing')
                ->label('Proses Printing')
                ->badge()
                ->color('info')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'color-printing' => 'Color Printing',
                    'cold-foil'      => 'Cold Foil',
                    'spot-uv'        => 'Spot UV',
                    'furnish-uv'     => 'Furnish UV',
                    'drip-off'       => 'Drip Off',
                    default          => (string) $state,
                })
                ->sortable(),

            Tables\Columns\TextColumn::make('tahap_printing')
                ->label('Tahap Printing')
                ->badge()
                ->color('gray')
                ->formatStateUsing(fn (?string $state): string => match ($state) {
                    'potong'         => 'Potong',
                    'proses_cetak'   => 'Proses Cetak',
                    'proses_cetak_2' => 'Proses Cetak 2',
                    default          => (string) $state,
                })
                ->sortable(),

            Tables\Columns\TextColumn::make('hasil_baik')
                ->label('Hasil Baik')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.') . ' m')
                ->color('success')
                ->weight('bold')
                ->sortable(),

            Tables\Columns\TextColumn::make('hasil_rusak')
                ->label('Hasil Rusak')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.') . ' m')
                ->color(fn ($state) => (float) $state > 0 ? 'danger' : 'gray')
                ->sortable(),

            Tables\Columns\TextColumn::make('semi_waste')
                ->label('Semi Waste')
                ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.') . ' m')
                ->color(fn ($state) => (float) $state > 0 ? 'warning' : 'gray')
                ->sortable(),

            Tables\Columns\TextColumn::make('waste_details')
                ->label('Note & Qty Waste')
                ->getStateUsing(function (Printing $record): string {
                    if ($record->wastes->isNotEmpty()) {
                        return $record->wastes->map(function ($w) {
                            $name = $w->errorProduction?->nama_error ?? '-';
                            $qty = rtrim(rtrim(number_format((float) $w->qty_waste, 2, ',', '.'), '0'), ',');

                            return "{$name} ({$qty} m)";
                        })->implode(', ');
                    }

                    return $record->errorProduction?->nama_error ?? '-';
                })
                ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search): \Illuminate\Database\Eloquent\Builder {
                    return $query->where(function ($q) use ($search) {
                        $q->whereHas('wastes.errorProduction', fn ($wq) => $wq->where('nama_error', 'like', "%{$search}%"))
                            ->orWhereHas('errorProduction', fn ($eq) => $eq->where('nama_error', 'like', "%{$search}%"))
                            ->orWhere('keterangan_spk', 'like', "%{$search}%");
                    });
                })
                ->wrap()
                ->placeholder('-'),

            Tables\Columns\TextColumn::make('keterangan_spk')
                ->label('Keterangan')
                ->limit(30)
                ->toggleable(isToggledHiddenByDefault: true),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('spk_id')
                ->label('No SPK')
                ->relationship('spk', 'no_spk')
                ->searchable()
                ->preload(),

            Tables\Filters\SelectFilter::make('mesin_id')
                ->label('Mesin')
                ->relationship('mesin', 'nama_mesin')
                ->searchable()
                ->preload(),

            Tables\Filters\SelectFilter::make('operator_id')
                ->label('Operator')
                ->relationship('operator', 'nama_operator')
                ->searchable()
                ->preload(),

            Tables\Filters\SelectFilter::make('proses_printing')
                ->label('Proses Printing')
                ->options([
                    'color-printing' => 'Color Printing',
                    'cold-foil'      => 'Cold Foil',
                    'spot-uv'        => 'Spot UV',
                    'furnish-uv'     => 'Furnish UV',
                    'drip-off'       => 'Drip Off',
                ]),

            Tables\Filters\SelectFilter::make('tahap_printing')
                ->label('Tahap Printing')
                ->options([
                    'potong'         => 'Potong',
                    'proses_cetak'   => 'Proses Cetak',
                    'proses_cetak_2' => 'Proses Cetak 2',
                ]),

            Tables\Filters\SelectFilter::make('note_waste')
                ->label('Note Waste')
                ->options(fn () => \App\Models\ErrorProduction::orderBy('kode_error')->pluck('nama_error', 'id'))
                ->searchable()
                ->preload()
                ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                    $val = $data['value'] ?? null;
                    if (! $val) {
                        return $query;
                    }

                    return $query->where(function ($q) use ($val) {
                        $q->where('error_production_id', $val)
                            ->orWhereHas('wastes', fn ($wq) => $wq->where('error_production_id', $val));
                    });
                }),

            Tables\Filters\Filter::make('tanggal_entri')
                ->label('Rentang Tanggal Entri')
                ->form([
                    Forms\Components\DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                    Forms\Components\DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                ])
                ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                    return $query
                        ->when($data['dari_tanggal'] ?? null, fn ($q, $d) => $q->whereDate('tanggal_entri', '>=', $d))
                        ->when($data['sampai_tanggal'] ?? null, fn ($q, $d) => $q->whereDate('tanggal_entri', '<=', $d));
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

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->with([
            'spk',
            'mesin',
            'operator',
            'errorProduction',
            'wastes.errorProduction',
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