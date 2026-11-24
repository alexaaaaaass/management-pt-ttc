<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterFinishingResource\Pages;
use App\Models\ErrorProduction;
use App\Models\MasterFinishing;
use App\Models\MasterProsesProduksi;
use App\Models\MasterSpk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class MasterFinishingResource extends Resource
{
    protected static ?string $model = MasterFinishing::class;

    protected static ?string $navigationGroup = 'Finishing';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Laporan Hasil Akhir Stasiun Finishing')
                    ->description('Input 1 kali setelah seluruh proses Finishing untuk SPK ini selesai (bisa memilih banyak proses sekaligus).')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('kode_finishing')
                            ->label('Kode Finishing')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto Generate'),

                        Forms\Components\Select::make('status_laporan')
                            ->label('Status Laporan')
                            ->options([
                                'selesai' => 'Selesai (Laporan Akhir Stasiun)',
                                'parsial' => 'Parsial / Bertahap',
                            ])
                            ->default('selesai')
                            ->required(),

                        Forms\Components\Select::make('spk_id')
                            ->label('Surat Perintah Kerja (SPK)')
                            ->relationship(
                                'spk',
                                'no_spk',
                                modifyQueryUsing: fn (Builder $query) => $query->with('finishings')->orderByDesc('id')
                            )
                            ->getOptionLabelFromRecordUsing(function (MasterSpk $record): string {
                                $hasFinal = $record->finishings->where('status_laporan', 'selesai')->isNotEmpty();

                                return $record->no_spk . ($hasFinal ? ' (Sudah Lapor Selesai)' : '');
                            })
                            ->searchable()
                            ->preload()
                            ->live()
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\Placeholder::make('handover_prev_station_info')
                            ->label('Info Hasil Stasiun Sebelumnya (Printing & Die Making)')
                            ->visible(fn (Forms\Get $get) => filled($get('spk_id')))
                            ->content(function (Forms\Get $get): HtmlString {
                                $spk = MasterSpk::with(['salesOrder.itemable', 'printings', 'diemakings'])->find($get('spk_id'));
                                if (! $spk) {
                                    return new HtmlString('-');
                                }

                                $produk = e($spk->salesOrder?->itemable?->nama_barang ?? '-');
                                $orderQty = number_format((int) $spk->order_qty, 0, ',', '.');
                                $printGood = number_format((int) $spk->printings->sum('hasil_baik'), 0, ',', '.');
                                $dieGood = number_format((int) $spk->diemakings->sum('hasil_baik'), 0, ',', '.');

                                return new HtmlString(
                                    '<div style="padding:8px 12px;border-radius:8px;border:1px solid rgba(59,130,246,0.35);background:rgba(59,130,246,0.1);font-size:12px;color:#60a5fa;">'
                                    . "Produk: <strong>{$produk}</strong> · Target Order: <strong>{$orderQty} pcs</strong>"
                                    . " · Printing Baik: <strong>{$printGood} m</strong>"
                                    . " · Die Making Baik: <strong>{$dieGood}</strong>"
                                    . '</div>'
                                );
                            })
                            ->columnSpanFull(),

                        Forms\Components\Select::make('mesin_finishing_id')
                            ->label('Mesin Finishing')
                            ->relationship('mesinFinishing', 'nama_mesin')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('operator_finishing_id')
                            ->label('Operator Finishing')
                            ->relationship('operatorFinishing', 'nama_operator')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_entri')
                            ->label('Tanggal Laporan / Selesai')
                            ->default(now())
                            ->required(),

                        Forms\Components\Select::make('tahap_finishing')
                            ->label('Kategori Hasil')
                            ->options([
                                'reguler'    => 'Reguler',
                                'blokir'     => 'Blokir',
                                'semi_waste' => 'Semi Waste',
                                'retur'      => 'Retur',
                            ])
                            ->default('reguler')
                            ->required(),

                        Forms\Components\Select::make('keterangan_spk')
                            ->label('Keterangan SPK')
                            ->options([
                                'reguler'  => 'Reguler',
                                'subcount' => 'Subcount',
                            ])
                            ->default('reguler')
                            ->required(),

                        Forms\Components\Select::make('proses_list')
                            ->label('Proses Finishing yang Dikerjakan (Bisa Pilih Lebih dari 1)')
                            ->multiple()
                            ->options(fn () => MasterProsesProduksi::optionsFor(MasterProsesProduksi::STASIUN_FINISHING))
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Pilih semua proses Finishing yang telah selesai dikerjakan (misal: Protol + Sorter + Lem sekaligus dalam 1 laporan).')
                            ->createOptionForm([
                                Forms\Components\TextInput::make('kode_proses')
                                    ->label('Kode Proses')
                                    ->default(fn () => MasterProsesProduksi::suggestKode(MasterProsesProduksi::STASIUN_FINISHING))
                                    ->required()
                                    ->unique('master_proses_produksi', 'kode_proses'),
                                Forms\Components\TextInput::make('nama_proses')
                                    ->label('Nama Proses Finishing')
                                    ->placeholder('Contoh: Window Patching')
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data): string {
                                $created = MasterProsesProduksi::create([
                                    'kode_proses' => $data['kode_proses'],
                                    'stasiun'     => MasterProsesProduksi::STASIUN_FINISHING,
                                    'nama_proses' => $data['nama_proses'],
                                    'urutan'      => (int) (MasterProsesProduksi::where('stasiun', MasterProsesProduksi::STASIUN_FINISHING)->max('urutan') ?? 0) + 1,
                                    'is_active'   => true,
                                ]);

                                return $created->nama_proses;
                            })
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('Hasil Akhir & Rincian Waste Finishing')
                    ->columns(3)
                    ->schema([
                        Forms\Components\TextInput::make('hasil_baik')
                            ->label('Hasil Baik (Pcs / Lembar)')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->required(),

                        Forms\Components\TextInput::make('hasil_rusak')
                            ->label('Hasil Rusak / Total Waste')
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->helperText('Otomatis terisi dari total Qty Waste di bawah bila rincian Note Waste diisi')
                            ->required(),

                        Forms\Components\TextInput::make('semi_waste')
                            ->label('Semi Waste')
                            ->numeric()
                            ->default(0)
                            ->minValue(0),

                        Forms\Components\Repeater::make('wastes')
                            ->label('Rincian Note Waste & Qty Waste')
                            ->relationship('wastes')
                            ->defaultItems(0)
                            ->addActionLabel('Tambah Note Waste')
                            ->reorderable(false)
                            ->collapsible()
                            ->columnSpanFull()
                            ->itemLabel(function (array $state): string {
                                $errId = $state['error_production_id'] ?? null;
                                $name = $errId ? (ErrorProduction::whereKey($errId)->value('nama_error') ?? 'Note Waste') : 'Pilih Note Waste';
                                $qty = (float) ($state['qty_waste'] ?? 0);

                                return $qty > 0 ? "{$name} — " . rtrim(rtrim(number_format($qty, 2, ',', '.'), '0'), ',') : $name;
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
                                    ->label('Qty Waste')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Forms\Get $get, Forms\Set $set) {
                                        $wastes = $get('../../wastes') ?? [];
                                        $sum = collect($wastes)->sum(fn ($row) => (float) ($row['qty_waste'] ?? 0));
                                        $set('../../hasil_rusak', (int) round($sum));
                                    }),

                                Forms\Components\TextInput::make('catatan')
                                    ->label('Catatan (Opsional)')
                                    ->placeholder('Contoh: Lem kurang merekat / sobek saat protol'),
                            ])
                            ->columns(3),

                        Forms\Components\Textarea::make('keterangan')
                            ->rows(2)
                            ->label('Keterangan Tambahan (Opsional)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return 'Laporan Finishing';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->searchPlaceholder('Cari No SPK, Kode, Mesin, Operator, Proses, Waste...')
            ->columns([
                Tables\Columns\TextColumn::make('spk.no_spk')
                    ->label('No SPK')
                    ->description(fn (MasterFinishing $r) => $r->spk?->salesOrder?->itemable?->nama_barang ?: null)
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('kode_finishing')
                    ->label('Kode Finishing')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('tanggal_entri')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('proses_summary')
                    ->label('Proses Finishing')
                    ->getStateUsing(fn (MasterFinishing $r) => $r->proses_labels)
                    ->badge()
                    ->color('warning'),

                Tables\Columns\TextColumn::make('status_laporan')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => $state === 'parsial' ? 'Parsial' : 'Selesai')
                    ->color(fn (?string $state) => $state === 'parsial' ? 'warning' : 'success'),

                Tables\Columns\TextColumn::make('hasil_baik')
                    ->label('Hasil Baik')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.'))
                    ->badge()
                    ->color('success')
                    ->sortable(),

                Tables\Columns\TextColumn::make('hasil_rusak')
                    ->label('Hasil Rusak')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0, ',', '.'))
                    ->badge()
                    ->color(fn ($state) => (float) $state > 0 ? 'danger' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('waste_details')
                    ->label('Note & Qty Waste')
                    ->getStateUsing(function (MasterFinishing $record): string {
                        if ($record->wastes->isNotEmpty()) {
                            return $record->wastes->map(function ($w) {
                                $name = $w->errorProduction?->nama_error ?? '-';
                                $qty = rtrim(rtrim(number_format((float) $w->qty_waste, 2, ',', '.'), '0'), ',');

                                return "{$name} ({$qty})";
                            })->implode(', ');
                        }

                        return $record->errorProduction?->nama_error ?? '-';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->where(function ($q) use ($search) {
                            $q->whereHas('wastes.errorProduction', fn ($wq) => $wq->where('nama_error', 'like', "%{$search}%"))
                                ->orWhereHas('errorProduction', fn ($eq) => $eq->where('nama_error', 'like', "%{$search}%"))
                                ->orWhere('proses_finishing', 'like', "%{$search}%");
                        });
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('mesinFinishing.nama_mesin')
                    ->label('Mesin')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('operatorFinishing.nama_operator')
                    ->label('Operator')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('spk_id')
                    ->label('No SPK')
                    ->relationship('spk', 'no_spk')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('mesin_finishing_id')
                    ->label('Mesin Finishing')
                    ->relationship('mesinFinishing', 'nama_mesin')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('operator_finishing_id')
                    ->label('Operator')
                    ->relationship('operatorFinishing', 'nama_operator')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('proses')
                    ->label('Proses Finishing')
                    ->options(fn () => MasterProsesProduksi::optionsFor(MasterProsesProduksi::STASIUN_FINISHING))
                    ->searchable()
                    ->query(function (Builder $query, array $data): Builder {
                        $val = $data['value'] ?? null;
                        if (! $val) {
                            return $query;
                        }

                        return $query->where(function ($q) use ($val) {
                            $q->whereJsonContains('proses_list', $val)
                                ->orWhere('proses_finishing', 'like', "%{$val}%");
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
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->requiresConfirmation()
                        ->color('danger'),
                    Tables\Actions\ViewAction::make(),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Actions'),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->requiresConfirmation(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with([
            'spk.salesOrder.itemable',
            'mesinFinishing',
            'operatorFinishing',
            'errorProduction',
            'wastes.errorProduction',
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMasterFinishings::route('/'),
            'create' => Pages\CreateMasterFinishing::route('/create'),
            'edit'   => Pages\EditMasterFinishing::route('/{record}/edit'),
        ];
    }
}
