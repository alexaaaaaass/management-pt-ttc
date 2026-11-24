<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\DieMakingResource\Pages;
use App\Models\DieMaking;
use App\Models\ErrorProduction;
use App\Models\MasterProsesProduksi;
use App\Models\MasterSpk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class DieMakingResource extends Resource
{
    protected static ?string $model = DieMaking::class;

    protected static ?string $navigationGroup = 'Die Making';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-scissors';

    protected static ?string $navigationLabel = 'Laporan Die Making';

    protected static ?string $modelLabel = 'Die Making';

    protected static ?string $pluralModelLabel = 'Die Making';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Laporan Hasil Akhir Stasiun Die Making')
                ->description('Input 1 kali setelah seluruh proses Die Making untuk SPK ini selesai (bisa memilih banyak proses sekaligus).')
                ->columns(2)
                ->schema([
                    Forms\Components\Select::make('spk_id')
                        ->label('Surat Perintah Kerja (SPK)')
                        ->relationship(
                            'spk',
                            'no_spk',
                            modifyQueryUsing: fn (Builder $query) => $query->with('diemakings')->orderByDesc('id')
                        )
                        ->getOptionLabelFromRecordUsing(function (MasterSpk $record): string {
                            $hasFinal = $record->diemakings->where('status_laporan', 'selesai')->isNotEmpty();

                            return $record->no_spk . ($hasFinal ? ' (Sudah Lapor Selesai)' : '');
                        })
                        ->searchable()
                        ->preload()
                        ->live()
                        ->required(),

                    Forms\Components\Select::make('status_laporan')
                        ->label('Status Laporan')
                        ->options([
                            'selesai' => 'Selesai (Laporan Akhir Stasiun)',
                            'parsial' => 'Parsial / Bertahap',
                        ])
                        ->default('selesai')
                        ->required(),

                    Forms\Components\Placeholder::make('handover_printing_info')
                        ->label('Info Hasil Stasiun Sebelumnya (Printing & Target SPK)')
                        ->visible(fn (Forms\Get $get) => filled($get('spk_id')))
                        ->content(function (Forms\Get $get): HtmlString {
                            $spk = MasterSpk::with(['salesOrder.itemable', 'printings'])->find($get('spk_id'));
                            if (! $spk) {
                                return new HtmlString('-');
                            }

                            $produk = e($spk->salesOrder?->itemable?->nama_barang ?? '-');
                            $orderQty = number_format((int) $spk->order_qty, 0, ',', '.');
                            $printGood = number_format((int) $spk->printings->sum('hasil_baik'), 0, ',', '.');
                            $printUnit = $spk->printings->last()?->satuan_unit ?? 'm';
                            $printProses = $spk->printings->map(fn ($p) => $p->proses_summary)->filter(fn ($s) => $s !== '-')->unique()->implode(' | ');

                            if ($spk->printings->isEmpty()) {
                                return new HtmlString(
                                    '<div style="padding:8px 12px;border-radius:8px;border:1px solid rgba(245,158,11,0.35);background:rgba(245,158,11,0.1);font-size:12px;color:#fbbf24;">'
                                    . "Produk: <strong>{$produk}</strong> · Target Order: <strong>{$orderQty} pcs</strong> · Belum ada laporan hasil Printing untuk SPK ini."
                                    . '</div>'
                                );
                            }

                            return new HtmlString(
                                '<div style="padding:8px 12px;border-radius:8px;border:1px solid rgba(59,130,246,0.35);background:rgba(59,130,246,0.1);font-size:12px;color:#60a5fa;">'
                                . "Produk: <strong>{$produk}</strong> · Target Order: <strong>{$orderQty} pcs</strong> · Hasil Baik Printing: <strong>{$printGood} " . e($printUnit) . '</strong>'
                                . ($printProses ? ' · Proses Printing: <strong>' . e($printProses) . '</strong>' : '')
                                . '</div>'
                            );
                        })
                        ->columnSpanFull(),

                    Forms\Components\Select::make('mesin_id')
                        ->label('Mesin Die Making')
                        ->relationship('mesin', 'nama_mesin')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Select::make('operator_id')
                        ->label('Operator Die Making')
                        ->relationship('operator', 'nama_operator')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal_entri')
                        ->label('Tanggal Laporan / Selesai')
                        ->default(now())
                        ->required(),

                    Forms\Components\Select::make('keterangan_spk')
                        ->label('Keterangan SPK')
                        ->options([
                            'Reguler'  => 'Reguler',
                            'Subcount' => 'Subcount',
                        ])
                        ->default('Reguler')
                        ->required(),

                    Forms\Components\Select::make('proses_list')
                        ->label('Proses Die Making yang Dikerjakan (Bisa Pilih Lebih dari 1)')
                        ->multiple()
                        ->options(fn () => MasterProsesProduksi::optionsFor(MasterProsesProduksi::STASIUN_DIEMAKING))
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('Pilih semua proses Die Making yang telah selesai dikerjakan untuk SPK ini (misal: Hot Print + Emboss + Pond/Cutting sekaligus).')
                        ->createOptionForm([
                            Forms\Components\TextInput::make('kode_proses')
                                ->label('Kode Proses')
                                ->default(fn () => MasterProsesProduksi::suggestKode(MasterProsesProduksi::STASIUN_DIEMAKING))
                                ->required()
                                ->unique('master_proses_produksi', 'kode_proses'),
                            Forms\Components\TextInput::make('nama_proses')
                                ->label('Nama Proses Die Making')
                                ->placeholder('Contoh: Pond / Die-Cutting')
                                ->required(),
                        ])
                        ->createOptionUsing(function (array $data): string {
                            $created = MasterProsesProduksi::create([
                                'kode_proses' => $data['kode_proses'],
                                'stasiun'     => MasterProsesProduksi::STASIUN_DIEMAKING,
                                'nama_proses' => $data['nama_proses'],
                                'urutan'      => (int) (MasterProsesProduksi::where('stasiun', MasterProsesProduksi::STASIUN_DIEMAKING)->max('urutan') ?? 0) + 1,
                                'is_active'   => true,
                            ]);

                            return $created->nama_proses;
                        })
                        ->columnSpanFull(),
                ]),

            Forms\Components\Section::make('Hasil Akhir & Rincian Waste Die Making')
                ->columns(3)
                ->schema([
                    Forms\Components\Select::make('satuan_hasil')
                        ->label('Satuan Hasil & Waste')
                        ->options(MasterProsesProduksi::satuanHasilOptions())
                        ->default('lbr')
                        ->live()
                        ->required()
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('hasil_baik')
                        ->label(fn (Forms\Get $get) => 'Hasil Baik (' . MasterProsesProduksi::satuanLabel($get('satuan_hasil'), 'lbr') . ')')
                        ->numeric()
                        ->suffix(fn (Forms\Get $get) => MasterProsesProduksi::satuanSuffix($get('satuan_hasil'), 'lbr'))
                        ->default(0)
                        ->minValue(0)
                        ->required(),

                    Forms\Components\TextInput::make('hasil_rusak')
                        ->label(fn (Forms\Get $get) => 'Hasil Rusak / Total Waste (' . MasterProsesProduksi::satuanLabel($get('satuan_hasil'), 'lbr') . ')')
                        ->numeric()
                        ->suffix(fn (Forms\Get $get) => MasterProsesProduksi::satuanSuffix($get('satuan_hasil'), 'lbr'))
                        ->default(0)
                        ->minValue(0)
                        ->helperText('Otomatis terisi dari total Qty Waste di bawah bila rincian Note Waste diisi')
                        ->required(),

                    Forms\Components\TextInput::make('semi_waste')
                        ->label(fn (Forms\Get $get) => 'Semi Waste (' . MasterProsesProduksi::satuanLabel($get('satuan_hasil'), 'lbr') . ')')
                        ->numeric()
                        ->suffix(fn (Forms\Get $get) => MasterProsesProduksi::satuanSuffix($get('satuan_hasil'), 'lbr'))
                        ->default(0)
                        ->minValue(0)
                        ->required(),

                    Forms\Components\Repeater::make('wastes')
                        ->label('Rincian Note Waste & Qty Waste')
                        ->relationship('wastes')
                        ->defaultItems(0)
                        ->addActionLabel('Tambah Note Waste')
                        ->reorderable(false)
                        ->collapsible()
                        ->columnSpanFull()
                        ->itemLabel(function (array $state, $livewire): string {
                            $errId = $state['error_production_id'] ?? null;
                            $name = $errId ? (ErrorProduction::whereKey($errId)->value('nama_error') ?? 'Note Waste') : 'Pilih Note Waste';
                            $qty = (float) ($state['qty_waste'] ?? 0);
                            $unit = MasterProsesProduksi::satuanSuffix($livewire->data['satuan_hasil'] ?? 'lbr', 'lbr');

                            return $qty > 0 ? "{$name} — " . rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',') . " {$unit}" : $name;
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
                                    modifyQueryUsing: fn (Builder $query) => $query->orderBy('kode_error')
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
                                        ->required(),
                                ]),

                            Forms\Components\TextInput::make('qty_waste')
                                ->label(fn (Forms\Get $get) => 'Qty Waste (' . MasterProsesProduksi::satuanLabel($get('../../satuan_hasil'), 'lbr') . ')')
                                ->numeric()
                                ->minValue(0)
                                ->default(0)
                                ->suffix(fn (Forms\Get $get) => MasterProsesProduksi::satuanSuffix($get('../../satuan_hasil'), 'lbr'))
                                ->required()
                                ->live(onBlur: true)
                                ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                    $wastes = $get('../../wastes') ?? [];
                                    $sum = collect($wastes)->sum(fn ($row) => (float) ($row['qty_waste'] ?? 0));
                                    $set('../../hasil_rusak', (int) round($sum));
                                }),

                            Forms\Components\TextInput::make('catatan')
                                ->label('Catatan (Opsional)')
                                ->placeholder('Contoh: Pisau pond tumpul / register foil geser'),
                        ])
                        ->columns(3),

                    Forms\Components\Textarea::make('catatan')
                        ->label('Catatan Tambahan (Opsional)')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->searchPlaceholder('Cari No SPK, Mesin, Operator, Proses, Waste...')
            ->columns([
                Tables\Columns\TextColumn::make('spk.no_spk')
                    ->label('No SPK')
                    ->description(fn (DieMaking $r) => $r->spk?->salesOrder?->itemable?->nama_barang ?: null)
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
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('proses_summary')
                    ->label('Proses Die Making')
                    ->getStateUsing(fn (DieMaking $r) => $r->proses_labels)
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('status_laporan')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'parsial' ? 'Parsial' : 'Selesai')
                    ->color(fn (?string $state) => $state === 'parsial' ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('hasil_baik')
                    ->label('Hasil Baik')
                    ->formatStateUsing(fn ($state, DieMaking $record) => number_format((float) $state, 0, ',', '.') . ' ' . $record->satuan_unit)
                    ->color('success')
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hasil_rusak')
                    ->label('Hasil Rusak')
                    ->formatStateUsing(fn ($state, DieMaking $record) => number_format((float) $state, 0, ',', '.') . ' ' . $record->satuan_unit)
                    ->color(fn ($state) => (float) $state > 0 ? 'danger' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('semi_waste')
                    ->label('Semi Waste')
                    ->formatStateUsing(fn ($state, DieMaking $record) => number_format((float) $state, 0, ',', '.') . ' ' . $record->satuan_unit)
                    ->color(fn ($state) => (float) $state > 0 ? 'warning' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('waste_details')
                    ->label('Note & Qty Waste')
                    ->getStateUsing(function (DieMaking $record): string {
                        if ($record->wastes->isNotEmpty()) {
                            $unit = $record->satuan_unit;

                            return $record->wastes->map(function ($w) use ($unit) {
                                $name = $w->errorProduction?->nama_error ?? '-';
                                $qty = rtrim(rtrim(number_format((float) $w->qty_waste, 2, ',', '.'), '0'), ',');

                                return "{$name} ({$qty} {$unit})";
                            })->implode(', ');
                        }

                        return $record->errorProduction?->nama_error ?: ($record->note_waste ?: '-');
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->whereHas('wastes.errorProduction', fn ($wq) => $wq->where('nama_error', 'like', "%{$search}%"))
                                ->orWhere('proses_diemaking', 'like', "%{$search}%")
                                ->orWhere('note_waste', 'like', "%{$search}%");
                        });
                    })
                    ->wrap(),
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

                Tables\Filters\SelectFilter::make('proses')
                    ->label('Proses Die Making')
                    ->options(fn () => MasterProsesProduksi::optionsFor(MasterProsesProduksi::STASIUN_DIEMAKING))
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $val = $data['value'] ?? null;
                        if (! $val) {
                            return $query;
                        }

                        return $query->where(function ($q) use ($val) {
                            $q->whereJsonContains('proses_list', $val)
                                ->orWhere('proses_diemaking', 'like', "%{$val}%");
                        });
                    }),

                Tables\Filters\SelectFilter::make('status_laporan')
                    ->label('Status Laporan')
                    ->options([
                        'selesai' => 'Selesai',
                        'parsial' => 'Parsial',
                    ]),

                Tables\Filters\SelectFilter::make('note_waste')
                    ->label('Note Waste')
                    ->options(fn () => ErrorProduction::orderBy('kode_error')->pluck('nama_error', 'id'))
                    ->searchable()
                    ->preload()
                    ->query(function (Builder $query, array $data): Builder {
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
                    ->label('Rentang Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['dari_tanggal'] ?? null, fn ($q, $d) => $q->whereDate('tanggal_entri', '>=', $d))
                        ->when($data['sampai_tanggal'] ?? null, fn ($q, $d) => $q->whereDate('tanggal_entri', '<=', $d))),
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'spk.salesOrder.itemable',
            'mesin',
            'operator',
            'errorProduction',
            'wastes.errorProduction',
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDieMakings::route('/'),
            'create' => Pages\CreateDieMaking::route('/create'),
            'edit'   => Pages\EditDieMaking::route('/{record}/edit'),
        ];
    }
}
