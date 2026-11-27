<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PayrollBoronganResource\Pages;
use App\Models\HrPayrollSetting;
use App\Models\Karyawan;
use App\Models\MasterCOA;
use App\Models\PayrollBorongan;
use App\Services\HR\PayrollService;
use App\Support\MoneyHelper;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class PayrollBoronganResource extends Resource
{
    protected static ?string $model = PayrollBorongan::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 6;
    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Payroll Borongan';
    protected static ?string $modelLabel = 'Payroll Borongan';
    protected static ?string $pluralModelLabel = 'Payroll Borongan';

    protected static function getCashBankOptions(): array
    {
        return MasterCOA::where(function ($q) {
            $q->where('kode_akun', 'like', '111%')
                ->orWhere('account_class', 'like', '%Bank%')
                ->orWhere('account_class', 'like', '%Cash%');
        })
            ->orderBy('kode_akun')
            ->get()
            ->mapWithKeys(fn ($c) => [$c->id => trim((string) $c->kode_akun) . ' - ' . $c->nama_akun])
            ->toArray();
    }

    protected static function getKaryawanOptions(): array
    {
        return Karyawan::where('status_pegawai', 'Aktif')
            ->orderBy('nama')
            ->get()
            ->mapWithKeys(fn ($k) => [$k->id => "{$k->nama} ({$k->nip} · {$k->tipe_gaji})"])
            ->toArray();
    }

    protected static function recalcItem(Get $get, Set $set): void
    {
        $c = fn (string $k) => MoneyHelper::clean($get($k)) ?? 0;

        $pQty = (float) ($get('protol_qty') ?? 0);
        $pTarif = $c('protol_tarif');
        $pGaji = $pQty > 0 && $pTarif > 0 ? round($pQty * $pTarif, 2) : $c('protol_gaji');
        if ($pQty > 0 && $pTarif > 0) {
            $set('protol_gaji', MoneyHelper::format($pGaji, 0));
        }

        $kQty = (float) ($get('packing_qty') ?? 0);
        $kTarif = $c('packing_tarif');
        $kGaji = $kQty > 0 && $kTarif > 0 ? round($kQty * $kTarif, 2) : $c('packing_gaji');
        if ($kQty > 0 && $kTarif > 0) {
            $set('packing_gaji', MoneyHelper::format($kGaji, 0));
        }

        $lQty = (float) ($get('lain_qty') ?? 0);
        $lTarif = $c('lain_tarif');
        $lGaji = $lQty > 0 && $lTarif > 0 ? round($lQty * $lTarif, 2) : $c('lain_gaji');
        if ($lQty > 0 && $lTarif > 0) {
            $set('lain_gaji', MoneyHelper::format($lGaji, 0));
        }

        $total = max(0, round($pGaji + $kGaji + $lGaji + $c('tunj_hadir') + $c('tunj_makan') + $c('tunj_lain') - $c('potongan'), 2));
        $set('total', MoneyHelper::format($total, 0));
    }

    public static function form(Form $form): Form
    {
        $setting = HrPayrollSetting::current();
        $karyawanOpts = fn () => self::getKaryawanOptions();

        return $form->schema([
            Section::make('Identitas Batch Payroll Borongan / Part-Time')
                ->description('Catat upah borongan mingguan atau bulanan (Protol, Packing, Finishing/Lainnya + Tunjangan Hadir & Makan). Mendukung karyawan terdaftar maupun pekerja lepas.')
                ->schema([
                    Grid::make(4)->schema([
                        TextInput::make('kode_bukti')->label('Kode Bukti')->default('AUTO')->disabled()->dehydrated(),
                        TextInput::make('periode')->label('Periode (YYYY-MM)')->default(now()->format('Y-m'))->required(),
                        DatePicker::make('tanggal_transaksi')->label('Tanggal Transaksi')->default(now())->required(),
                        Select::make('status')->label('Status')->options(PayrollBorongan::STATUS_OPTIONS)->default('Draft')->disabled()->dehydrated(),
                    ]),
                    Grid::make(4)->schema([
                        DatePicker::make('tanggal_mulai')->label('Periode Kerja Mulai')->default(now()->startOfWeek())->required(),
                        DatePicker::make('tanggal_selesai')->label('Periode Kerja Selesai')->default(now()->endOfWeek())->required(),
                        TextInput::make('kode_gudang')->label('Kode Gudang')->default('TTC')->required(),
                        Select::make('metode_pembayaran')->label('Metode Bayar')->options(['Tunai' => 'Tunai (Kas)', 'Transfer Bank' => 'Transfer Bank'])->default('Tunai')->required(),
                    ]),
                    Grid::make(2)->schema([
                        Select::make('account_kas_id')
                            ->label('Akun Kas / Bank Pembayar (111x)')
                            ->options(fn () => self::getCashBankOptions())
                            ->searchable()
                            ->placeholder('Pilih Kas/Bank jika dibayarkan'),
                        Toggle::make('post_to_ledger')
                            ->label('Auto-Posting ke Buku Besar saat Paid (COA 5201)')
                            ->helperText('Matikan jika pembayaran borongan sudah/akan dicatat di Transaksi Kas/Bank atau NPB.')
                            ->default(false),
                    ]),
                ]),

            Section::make('Ringkasan Total Borongan (Live)')
                ->schema([
                    Placeholder::make('ringkasan_borongan')
                        ->label('')
                        ->content(function (Get $get) {
                            $items = $get('items') ?? [];
                            $gaji = 0.0;
                            $tunj = 0.0;
                            $pot = 0.0;
                            $grand = 0.0;
                            foreach ($items as $it) {
                                $pg = (float) (MoneyHelper::clean($it['protol_gaji'] ?? 0) ?? 0);
                                $kg = (float) (MoneyHelper::clean($it['packing_gaji'] ?? 0) ?? 0);
                                $lg = (float) (MoneyHelper::clean($it['lain_gaji'] ?? 0) ?? 0);
                                $th = (float) (MoneyHelper::clean($it['tunj_hadir'] ?? 0) ?? 0);
                                $tm = (float) (MoneyHelper::clean($it['tunj_makan'] ?? 0) ?? 0);
                                $tl = (float) (MoneyHelper::clean($it['tunj_lain'] ?? 0) ?? 0);
                                $pt = (float) (MoneyHelper::clean($it['potongan'] ?? 0) ?? 0);
                                $gaji += ($pg + $kg + $lg);
                                $tunj += ($th + $tm + $tl);
                                $pot += $pt;
                                $grand += max(0, ($pg + $kg + $lg + $th + $tm + $tl - $pt));
                            }
                            $fmt = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');

                            return new HtmlString(
                                '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;">'
                                . '<div style="padding:10px 14px;border-radius:10px;border:1px solid rgba(59,130,246,0.35);background:rgba(59,130,246,0.08);"><div style="font-size:11px;font-weight:700;opacity:0.75;">TOTAL UPAH BORONGAN</div><div style="font-size:17px;font-weight:800;color:#2563eb;margin-top:3px;">' . $fmt($gaji) . '</div></div>'
                                . '<div style="padding:10px 14px;border-radius:10px;border:1px solid rgba(99,102,241,0.35);background:rgba(99,102,241,0.08);"><div style="font-size:11px;font-weight:700;opacity:0.75;">TOTAL TUNJANGAN</div><div style="font-size:17px;font-weight:800;color:#4f46e5;margin-top:3px;">' . $fmt($tunj) . '</div></div>'
                                . '<div style="padding:10px 14px;border-radius:10px;border:1px solid rgba(220,38,38,0.35);background:rgba(220,38,38,0.08);"><div style="font-size:11px;font-weight:700;opacity:0.75;">TOTAL POTONGAN</div><div style="font-size:17px;font-weight:800;color:#dc2626;margin-top:3px;">' . $fmt($pot) . '</div></div>'
                                . '<div style="padding:10px 14px;border-radius:10px;border:1px solid rgba(5,150,105,0.45);background:rgba(5,150,105,0.1);"><div style="font-size:11px;font-weight:700;color:#047857;">GRAND TOTAL DIBAYAR (' . count($items) . ' baris)</div><div style="font-size:17px;font-weight:800;color:#059669;margin-top:3px;">' . $fmt($grand) . '</div></div>'
                                . '</div>'
                            );
                        }),
                ]),

            Section::make('Daftar Pekerja & Hasil Borongan')
                ->headerActions([
                    FormAction::make('loadBoronganWorkers')
                        ->label('Muat Pekerja Borongan Aktif')
                        ->icon('heroicon-o-users')
                        ->color('success')
                        ->action(function (Set $set, Get $get) {
                            $st = HrPayrollSetting::current();
                            $workers = Karyawan::where('status_pegawai', 'Aktif')
                                ->where(function ($q) {
                                    $q->where('tipe_gaji', 'Borongan')->orWhere('is_borongan', true);
                                })
                                ->orderBy('nama')
                                ->get();

                            if ($workers->isEmpty()) {
                                Notification::make()->title('Belum ada karyawan bertipe Borongan. Anda tetap bisa tambah baris pekerja lepas secara manual.')->warning()->send();
                                return;
                            }

                            $tgl = $get('tanggal_selesai') ?: now()->toDateString();
                            $rows = [];
                            foreach ($workers as $w) {
                                $tHadir = (float) ($w->tunjangan_hadir ?: $st->tunj_hadir_borongan_default);
                                $tMakan = (float) ($w->tunjangan_makan ?: $st->tunj_makan_borongan_default);
                                $rows[(string) Str::uuid()] = [
                                    'karyawan_id'     => $w->id,
                                    'nik_pekerja'     => $w->nip,
                                    'nama_pekerja'    => $w->nama,
                                    'tanggal_kerja'   => $tgl,
                                    'jenis_pekerjaan' => 'Protol / Packing',
                                    'protol_qty'      => 0,
                                    'protol_tarif'    => MoneyHelper::format($st->tarif_protol_default, 0),
                                    'protol_gaji'     => MoneyHelper::format(0, 0),
                                    'packing_qty'     => 0,
                                    'packing_tarif'   => MoneyHelper::format($st->tarif_packing_default, 0),
                                    'packing_gaji'    => MoneyHelper::format(0, 0),
                                    'lain_qty'        => 0,
                                    'lain_tarif'      => MoneyHelper::format($st->tarif_lain_borongan_default, 0),
                                    'lain_gaji'       => MoneyHelper::format(0, 0),
                                    'tunj_hadir'      => MoneyHelper::format($tHadir, 0),
                                    'tunj_makan'      => MoneyHelper::format($tMakan, 0),
                                    'tunj_lain'       => MoneyHelper::format(0, 0),
                                    'potongan'        => MoneyHelper::format(0, 0),
                                    'total'           => MoneyHelper::format($tHadir + $tMakan, 0),
                                ];
                            }
                            $set('items', $rows);
                            Notification::make()->title(count($rows) . ' pekerja borongan berhasil dimuat')->success()->send();
                        }),
                ])
                ->schema([
                    Repeater::make('items')
                        ->label('')
                        ->relationship('items')
                        ->orderColumn('urutan')
                        ->defaultItems(1)
                        ->addActionLabel('+ Tambah Pekerja Borongan / Lepas')
                        ->itemLabel(fn (array $state): ?string => trim(($state['nama_pekerja'] ?? 'Pekerja Baru') . ' — Rp ' . ($state['total'] ?? '0')))
                        ->collapsible()
                        ->schema([
                            Grid::make(4)->schema([
                                Select::make('karyawan_id')
                                    ->label('Pilih Karyawan (Opsional)')
                                    ->options($karyawanOpts)
                                    ->searchable()
                                    ->nullable()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        if (! $state) {
                                            return;
                                        }
                                        $k = Karyawan::find((int) $state);
                                        if (! $k) {
                                            return;
                                        }
                                        $set('nama_pekerja', $k->nama);
                                        $set('nik_pekerja', $k->nip);
                                        if ((float) $k->tunjangan_hadir > 0) {
                                            $set('tunj_hadir', MoneyHelper::format($k->tunjangan_hadir, 0));
                                        }
                                        if ((float) $k->tunjangan_makan > 0) {
                                            $set('tunj_makan', MoneyHelper::format($k->tunjangan_makan, 0));
                                        }
                                        self::recalcItem($get, $set);
                                    }),
                                TextInput::make('nama_pekerja')->label('Nama Pekerja')->required()->maxLength(150),
                                TextInput::make('nik_pekerja')->label('NIK / NIP')->maxLength(50),
                                DatePicker::make('tanggal_kerja')->label('Tgl Kerja')->default(now()),
                            ]),

                            Grid::make(3)->schema([
                                TextInput::make('protol_qty')->label('Qty Protol')->numeric()->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                                TextInput::make('protol_tarif')->label('Tarif Protol / Unit')->prefix('Rp')->currencyMask(0)->default($setting->tarif_protol_default)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                                TextInput::make('protol_gaji')->label('Gaji Protol (Rp)')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                            ]),

                            Grid::make(3)->schema([
                                TextInput::make('packing_qty')->label('Qty Packing')->numeric()->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                                TextInput::make('packing_tarif')->label('Tarif Packing / Unit')->prefix('Rp')->currencyMask(0)->default($setting->tarif_packing_default)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                                TextInput::make('packing_gaji')->label('Gaji Packing (Rp)')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                            ]),

                            Grid::make(4)->schema([
                                TextInput::make('jenis_pekerjaan')->label('Jenis Borongan Lain')->placeholder('Finishing / Gluing / Sortir')->maxLength(100),
                                TextInput::make('lain_qty')->label('Qty Lainnya')->numeric()->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                                TextInput::make('lain_tarif')->label('Tarif Lainnya')->prefix('Rp')->currencyMask(0)->default($setting->tarif_lain_borongan_default)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                                TextInput::make('lain_gaji')->label('Gaji Lainnya (Rp)')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                            ]),

                            Grid::make(5)->schema([
                                TextInput::make('tunj_hadir')->label('Tunj. Hadir')->prefix('Rp')->currencyMask(0)->default($setting->tunj_hadir_borongan_default)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                                TextInput::make('tunj_makan')->label('Tunj. Makan')->prefix('Rp')->currencyMask(0)->default($setting->tunj_makan_borongan_default)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                                TextInput::make('tunj_lain')->label('Tunj. Lain')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                                TextInput::make('potongan')->label('Potongan')->prefix('Rp')->currencyMask(0)->default(0)->live(onBlur: true)->afterStateUpdated(fn ($state, Get $get, Set $set) => self::recalcItem($get, $set)),
                                TextInput::make('total')->label('Total Terima')->prefix('Rp')->currencyMask(0)->disabled()->dehydrated()->default(0),
                            ]),
                        ])
                        ->columns(1),
                ]),

            Textarea::make('keterangan')->label('Catatan Batch Borongan')->rows(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_bukti')->label('Kode Bukti')->searchable()->sortable()->badge()->color('primary'),
                TextColumn::make('periode')->label('Periode')->badge(),
                TextColumn::make('tanggal_mulai')
                    ->label('Rentang Kerja')
                    ->formatStateUsing(fn (PayrollBorongan $r) => ($r->tanggal_mulai?->format('d/m/Y') ?? '-') . ' s/d ' . ($r->tanggal_selesai?->format('d/m/Y') ?? '-')),
                TextColumn::make('items_count')->label('Pekerja')->counts('items')->badge()->color('gray'),
                TextColumn::make('total_gaji_borongan')->label('Upah Borongan')->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))->alignRight(),
                TextColumn::make('total_tunjangan')->label('Tunjangan')->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))->alignRight(),
                TextColumn::make('grand_total')->label('Grand Total')->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))->alignRight()->weight('bold')->color('success'),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Draft'    => 'warning',
                        'Approved' => 'info',
                        'Paid'     => 'success',
                        default    => 'gray',
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')->options(PayrollBorongan::STATUS_OPTIONS),
                Tables\Filters\SelectFilter::make('periode')->options(fn () => PayrollBorongan::select('periode')->distinct()->orderByDesc('periode')->pluck('periode', 'periode')->toArray()),
            ])
            ->actions([
                Action::make('previewPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (PayrollBorongan $record) => route('hrd.borongan.preview', $record))
                    ->openUrlInNewTab(),

                ActionGroup::make([
                    Action::make('downloadPdf')
                        ->label('Download PDF Borongan')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn (PayrollBorongan $record) => route('hrd.borongan.download', $record))
                        ->openUrlInNewTab(),

                    Action::make('approve')
                        ->label('Approve')
                        ->icon('heroicon-o-check-badge')
                        ->color('info')
                        ->visible(fn (PayrollBorongan $r) => $r->status === 'Draft')
                        ->requiresConfirmation()
                        ->action(function (PayrollBorongan $record) {
                            $record->update(['status' => 'Approved']);
                            Notification::make()->title("Payroll Borongan {$record->kode_bukti} disetujui")->success()->send();
                        }),

                    Action::make('pay')
                        ->label('Bayar Borongan (Paid)')
                        ->icon('heroicon-o-banknotes')
                        ->color('success')
                        ->visible(fn (PayrollBorongan $r) => $r->status !== 'Paid')
                        ->form([
                            Select::make('account_kas_id')
                                ->label('Akun Kas / Bank Pembayar (111x)')
                                ->options(fn () => self::getCashBankOptions())
                                ->searchable()
                                ->required(),
                            Select::make('metode_pembayaran')
                                ->label('Metode Pembayaran')
                                ->options(['Tunai' => 'Tunai (Kas)', 'Transfer Bank' => 'Transfer Bank'])
                                ->default('Tunai')
                                ->required(),
                            Toggle::make('post_to_ledger')
                                ->label('Auto-Posting ke Buku Besar (COA 5201)')
                                ->default(false),
                        ])
                        ->action(function (PayrollBorongan $record, array $data) {
                            app(PayrollService::class)->payBorongan(
                                $record,
                                (int) $data['account_kas_id'],
                                $data['metode_pembayaran'],
                                (bool) ($data['post_to_ledger'] ?? false)
                            );
                            Notification::make()->title("Payroll Borongan {$record->kode_bukti} berhasil dibayarkan")->success()->send();
                        }),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()
                        ->using(fn (PayrollBorongan $record) => app(PayrollService::class)->deleteBorongan($record)),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPayrollBorongans::route('/'),
            'create' => Pages\CreatePayrollBorongan::route('/create'),
            'edit'   => Pages\EditPayrollBorongan::route('/{record}/edit'),
        ];
    }
}
