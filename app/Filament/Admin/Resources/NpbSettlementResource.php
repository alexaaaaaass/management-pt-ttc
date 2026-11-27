<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\NpbSettlementResource\Pages;
use App\Models\MasterCOA;
use App\Models\NpbRequest;
use App\Models\NpbSettlement;
use App\Models\NpbSettlementItem;
use App\Services\Finance\FinanceHrdSyncService;
use App\Services\Finance\NpbService;
use App\Support\MoneyHelper;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

class NpbSettlementResource extends Resource
{
    protected static ?string $model = NpbSettlement::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Penyelesaian NPB (LPJ)';
    protected static ?string $modelLabel = 'Penyelesaian NPB (LPJ)';
    protected static ?string $pluralModelLabel = 'Penyelesaian NPB (LPJ)';

    protected static function getAllCoaOptions(): array
    {
        return MasterCOA::orderBy('kode_akun')
            ->get()
            ->mapWithKeys(fn ($c) => [$c->id => trim((string) $c->kode_akun) . ' - ' . $c->nama_akun])
            ->toArray();
    }

    protected static function getPaymentSourceCoaOptions(): array
    {
        return MasterCOA::where(function ($q) {
            $q->where('kode_akun', 'like', '111%')
                ->orWhere('kode_akun', 'like', '214%')
                ->orWhere('account_class', 'like', '%Bank%')
                ->orWhere('account_class', 'like', '%Cash%');
        })
            ->orderBy('kode_akun')
            ->get()
            ->mapWithKeys(fn ($c) => [$c->id => trim((string) $c->kode_akun) . ' - ' . $c->nama_akun])
            ->toArray();
    }

    protected static function hydrateFromNpb(?int $npbId, string $periodeRealisasi, Set $set): void
    {
        if (! $npbId) {
            return;
        }
        $npb = NpbRequest::with('items')->find($npbId);
        if (! $npb) {
            return;
        }

        $label = match ($periodeRealisasi) {
            'PROYEKSI' => $npb->periode_proyeksi ?: 'Periode Proyeksi',
            'GABUNGAN' => trim($npb->periode_utama . ($npb->periode_proyeksi ? ' - ' . $npb->periode_proyeksi : '')),
            default    => $npb->periode_utama,
        };

        $set('label_periode', $label);
        $danaCair = (float) $npb->nominal_dicairkan > 0
            ? round((float) $npb->nominal_dicairkan + (float) $npb->total_dana_tersedia, 2)
            : round((float) $npb->total_kebutuhan_utama, 2);
        $set('total_dana_dicairkan', MoneyHelper::format($danaCair, 2));
        $items = app(NpbService::class)->buildSettlementItemsFromNpb($npb, $periodeRealisasi);
        $keyed = [];
        foreach ($items as $row) {
            $row['nominal_anggaran'] = MoneyHelper::format($row['nominal_anggaran'], 2);
            $row['nominal_realisasi'] = MoneyHelper::format($row['nominal_realisasi'], 2);
            $keyed[(string) \Illuminate\Support\Str::uuid()] = $row;
        }
        $set('items', $keyed);
    }

    public static function form(Form $form): Form
    {
        $allCoa = fn () => static::getAllCoaOptions();
        $sourceCoa = fn () => static::getPaymentSourceCoaOptions();

        return $form
            ->schema([
                Section::make('Identitas Penyelesaian / Pertanggungjawaban NPB (LPJ)')
                    ->description('Pilih Nota Pengajuan Biaya (NPB) yang sudah disetujui untuk memuat rincian pos anggaran secara otomatis.')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('nomor_penyelesaian')
                                    ->label('Nomor LPJ / Penyelesaian')
                                    ->default('AUTO')
                                    ->required()
                                    ->maxLength(60),

                                Select::make('npb_request_id')
                                    ->label('Referensi NPB')
                                    ->options(function () {
                                        return NpbRequest::orderByDesc('id')
                                            ->get()
                                            ->mapWithKeys(fn (NpbRequest $r) => [
                                                $r->id => $r->nomor_pengajuan . ' — ' . $r->periode_utama . ' (' . (NpbRequest::STATUS_OPTIONS[$r->status] ?? $r->status) . ')',
                                            ])
                                            ->toArray();
                                    })
                                    ->default(fn () => request()->query('npb_request_id'))
                                    ->searchable()
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        static::hydrateFromNpb($state ? (int) $state : null, $get('periode_realisasi') ?: 'UTAMA', $set);
                                    })
                                    ->columnSpan(2),

                                DatePicker::make('tanggal_penyelesaian')
                                    ->label('Tanggal Penyelesaian')
                                    ->default(now())
                                    ->required(),
                            ]),

                        Grid::make(4)
                            ->schema([
                                Select::make('periode_realisasi')
                                    ->label('Periode Realisasi')
                                    ->options(NpbSettlement::PERIODE_OPTIONS)
                                    ->default('UTAMA')
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                        $npbId = $get('npb_request_id');
                                        if ($npbId) {
                                            static::hydrateFromNpb((int) $npbId, (string) $state, $set);
                                        }
                                    }),

                                TextInput::make('label_periode')
                                    ->label('Label Periode (Untuk Cetak PDF)')
                                    ->placeholder('Contoh: November 2026')
                                    ->required(),

                                TextInput::make('total_dana_dicairkan')
                                    ->label('Total Dana Tersedia + Dicairkan (Rp)')
                                    ->prefix('Rp')
                                    ->currencyMask(2)
                                    ->default(0)
                                    ->live(onBlur: true)
                                    ->required(),

                                Select::make('status')
                                    ->label('Status LPJ')
                                    ->options(NpbSettlement::STATUS_OPTIONS)
                                    ->default('DRAFT')
                                    ->disabled()
                                    ->dehydrated(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('posting_mode')
                                    ->label('Integrasi Pembukuan (Buku Besar)')
                                    ->options(NpbSettlement::POSTING_MODE_OPTIONS)
                                    ->default('MANUAL_LINK_ONLY')
                                    ->required()
                                    ->helperText('Pilih "Dokumen LPJ Saja" jika mutasi BRI/BCA sudah/akan diinput manual di Transaksi Bank agar saldo bank tidak terhitung dobel. Pilih "Auto-Posting" untuk membuat jurnal otomatis (termasuk 2144 Pinjaman PT Indigama).'),

                                Select::make('tindak_lanjut_sisa')
                                    ->label('Tindak Lanjut Sisa / Kekurangan Dana')
                                    ->options(NpbSettlement::TINDAK_LANJUT_OPTIONS)
                                    ->default('CARRY_OVER')
                                    ->required(),
                            ]),
                    ]),

                Section::make('Ringkasan Rekonsiliasi Anggaran vs Realisasi (Live)')
                    ->schema([
                        Placeholder::make('rekon_live')
                            ->label('')
                            ->content(function (Get $get) {
                                $items = $get('items') ?? [];
                                $totAng = 0.0;
                                $totReal = 0.0;
                                foreach ($items as $it) {
                                    $totAng += (float) (MoneyHelper::clean($it['nominal_anggaran'] ?? 0) ?? 0);
                                    $totReal += (float) (MoneyHelper::clean($it['nominal_realisasi'] ?? 0) ?? 0);
                                }
                                $dana = (float) (MoneyHelper::clean($get('total_dana_dicairkan') ?? 0) ?? 0);
                                $selisihAng = $totAng - $totReal;
                                $sisaDana = $dana - $totReal;
                                $pct = $totAng > 0 ? round(($totReal / $totAng) * 100, 1) : 0;

                                $fmt = fn ($v) => 'Rp ' . number_format((float) $v, 0, ',', '.');
                                $selColor = $selisihAng >= 0 ? '#059669' : '#dc2626';
                                $selLabel = $selisihAng >= 0 ? 'Hemat Anggaran' : 'Over Budget (Melebihi NPB)';
                                $sisaColor = $sisaDana >= 0 ? '#2563eb' : '#dc2626';
                                $sisaLabel = $sisaDana >= 0 ? 'Sisa Dana Akhir' : 'Kurang Bayar / Reimburse';

                                return new HtmlString(
                                    '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;">'
                                    . '<div style="padding:12px 14px;border-radius:10px;border:1px solid rgba(100,116,139,0.35);background:rgba(100,116,139,0.08);">'
                                    . '<div style="font-size:11px;font-weight:700;text-transform:uppercase;opacity:0.75;">Total Anggaran NPB</div>'
                                    . '<div style="font-size:18px;font-weight:800;margin-top:4px;">' . $fmt($totAng) . '</div></div>'
                                    . '<div style="padding:12px 14px;border-radius:10px;border:1px solid rgba(79,70,229,0.35);background:rgba(79,70,229,0.08);">'
                                    . '<div style="font-size:11px;font-weight:700;text-transform:uppercase;opacity:0.75;">Total Realisasi Aktual (' . $pct . '%)</div>'
                                    . '<div style="font-size:18px;font-weight:800;color:#4f46e5;margin-top:4px;">' . $fmt($totReal) . '</div></div>'
                                    . '<div style="padding:12px 14px;border-radius:10px;border:1px solid ' . $selColor . ';background:rgba(5,150,105,0.08);">'
                                    . '<div style="font-size:11px;font-weight:700;text-transform:uppercase;color:' . $selColor . ';">' . $selLabel . '</div>'
                                    . '<div style="font-size:18px;font-weight:800;color:' . $selColor . ';margin-top:4px;">' . ($selisihAng < 0 ? '-' : '') . $fmt(abs($selisihAng)) . '</div></div>'
                                    . '<div style="padding:12px 14px;border-radius:10px;border:1px solid ' . $sisaColor . ';background:rgba(37,99,235,0.08);">'
                                    . '<div style="font-size:11px;font-weight:700;text-transform:uppercase;color:' . $sisaColor . ';">' . $sisaLabel . '</div>'
                                    . '<div style="font-size:18px;font-weight:800;color:' . $sisaColor . ';margin-top:4px;">' . ($sisaDana < 0 ? '-' : '') . $fmt(abs($sisaDana)) . '</div></div>'
                                    . '</div>'
                                );
                            }),
                    ]),

                Section::make('Rincian Realisasi & Bukti Pendukung per Pos Biaya')
                    ->description('Isi nominal realisasi aktual, metode pembayaran (Bank/Kas TTC atau Talangan PT Indigama), dan lampirkan bukti transfer/nota. Anda juga dapat menambah pos insidental tambahan di luar anggaran.')
                    ->headerActions([
                        FormAction::make('pullRealisasiHrd')
                            ->label('Tarik Realisasi Payroll & Borongan HRD')
                            ->icon('heroicon-o-arrows-right-left')
                            ->color('success')
                            ->form([
                                Select::make('periode_hrd')
                                    ->label('Periode Payroll HRD')
                                    ->options(fn () => app(FinanceHrdSyncService::class)->getAvailablePeriods())
                                    ->default(fn (Get $get) => app(FinanceHrdSyncService::class)->parseLabelToPeriode($get('label_periode')) ?: now()->format('Y-m'))
                                    ->required(),
                            ])
                            ->action(function (array $data, Set $set, Get $get) {
                                $summary = app(FinanceHrdSyncService::class)->getPeriodSummary($data['periode_hrd']);
                                $items = $get('items') ?? [];
                                $updated = 0;

                                foreach ($items as $k => $it) {
                                    $u = strtolower((string) ($it['uraian'] ?? ''));
                                    if (str_contains($u, 'borong') || str_contains($u, 'freelance')) {
                                        if ($summary['total_borongan'] > 0) {
                                            $items[$k]['nominal_realisasi'] = MoneyHelper::format($summary['total_borongan'], 2);
                                            $items[$k]['keterangan'] = "Aktual HRD Borongan {$data['periode_hrd']} ({$summary['borongan_count']} batch)";
                                            $updated++;
                                        }
                                    } elseif (str_contains($u, 'bpjs')) {
                                        if ($summary['total_bpjs_setor'] > 0) {
                                            $items[$k]['nominal_realisasi'] = MoneyHelper::format($summary['total_bpjs_setor'], 2);
                                            $items[$k]['keterangan'] = "Aktual HRD BPJS {$data['periode_hrd']} (Kary 2% Rp " . number_format($summary['total_bpjs_karyawan'], 0, ',', '.') . " + PT 3.7% Rp " . number_format($summary['total_bpjs_perusahaan'], 0, ',', '.') . ")";
                                            $updated++;
                                        }
                                    } elseif (str_contains($u, 'gaji') || str_contains($u, 'payroll')) {
                                        if ($summary['total_thp'] > 0) {
                                            $items[$k]['nominal_realisasi'] = MoneyHelper::format($summary['total_thp'], 2);
                                            $items[$k]['keterangan'] = "Aktual THP HRD {$data['periode_hrd']} ({$summary['slip_count']} org · Paid: {$summary['paid_count']} · Pot.Kasbon Rp " . number_format($summary['total_potongan_kasbon'], 0, ',', '.') . ")";
                                            $updated++;
                                        }
                                    }
                                }

                                $set('items', $items);
                                Notification::make()
                                    ->title("Realisasi HRD ({$data['periode_hrd']}) Ditarik")
                                    ->body("{$updated} pos realisasi Gaji/BPJS/Borongan berhasil diperbarui sesuai data aktual HRD.")
                                    ->success()
                                    ->send();
                            }),

                        FormAction::make('reloadFromNpb')
                            ->label('Tarik Ulang Pos dari NPB')
                            ->icon('heroicon-o-arrow-path')
                            ->color('info')
                            ->requiresConfirmation()
                            ->action(function (Set $set, Get $get) {
                                $npbId = $get('npb_request_id');
                                if (! $npbId) {
                                    Notification::make()->title('Pilih Referensi NPB terlebih dahulu')->warning()->send();
                                    return;
                                }
                                static::hydrateFromNpb((int) $npbId, $get('periode_realisasi') ?: 'UTAMA', $set);
                                Notification::make()->title('Seluruh pos dari NPB berhasil dimuat ulang')->success()->send();
                            }),
                    ])
                    ->schema([
                        Repeater::make('items')
                            ->label('')
                            ->relationship('items')
                            ->orderColumn('urutan')
                            ->defaultItems(0)
                            ->addActionLabel('+ Tambah Pos Realisasi (Insidental / Tambahan)')
                            ->itemLabel(fn (array $state): ?string => trim(($state['uraian'] ?? 'Pos Realisasi') . ' [' . ($state['status_item'] ?? 'TEREALISASI') . ']'))
                            ->collapsible()
                            ->schema([
                                Hidden::make('npb_item_id'),

                                Grid::make(6)
                                    ->schema([
                                        Select::make('kategori')
                                            ->label('Kategori')
                                            ->options(NpbRequest::KATEGORI_OPTIONS)
                                            ->default('A_KEWAJIBAN_TETAP')
                                            ->required()
                                            ->columnSpan(2),

                                        TextInput::make('uraian')
                                            ->label('Uraian Pos Biaya')
                                            ->required()
                                            ->maxLength(255)
                                            ->columnSpan(2),

                                        Select::make('karakter')
                                            ->label('Karakter')
                                            ->options(['Bulanan' => 'Bulanan', 'Insidental' => 'Insidental'])
                                            ->default('Bulanan')
                                            ->required(),

                                        Select::make('status_item')
                                            ->label('Status Pos')
                                            ->options(NpbSettlementItem::STATUS_ITEM_OPTIONS)
                                            ->default('TEREALISASI')
                                            ->required(),
                                    ]),

                                Grid::make(4)
                                    ->schema([
                                        TextInput::make('nominal_anggaran')
                                            ->label('Anggaran NPB (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->required(),

                                        TextInput::make('nominal_realisasi')
                                            ->label('Realisasi Aktual (Rp)')
                                            ->prefix('Rp')
                                            ->currencyMask(2)
                                            ->default(0)
                                            ->live(onBlur: true)
                                            ->required(),

                                        DatePicker::make('tanggal_realisasi')
                                            ->label('Tanggal Bayar')
                                            ->default(now()),

                                        Select::make('metode_pembayaran')
                                            ->label('Metode / Sumber Bayar')
                                            ->options(NpbSettlementItem::METODE_OPTIONS)
                                            ->default('BANK_TTC')
                                            ->required()
                                            ->live()
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                if ($state === 'TALANGAN_INDIGAMA') {
                                                    $set('coa_sumber_id', MasterCOA::where('kode_akun', '2144')->value('id'));
                                                } elseif ($state === 'KAS_TTC') {
                                                    $set('coa_sumber_id', MasterCOA::where('kode_akun', '1116')->value('id'));
                                                } elseif ($state === 'BANK_TTC') {
                                                    $set('coa_sumber_id', MasterCOA::where('kode_akun', '1111')->value('id'));
                                                }
                                            }),
                                    ]),

                                Grid::make(4)
                                    ->schema([
                                        Select::make('coa_beban_id')
                                            ->label('Akun COA Beban / Aset (Debit)')
                                            ->options($allCoa)
                                            ->searchable()
                                            ->optionsLimit(50)
                                            ->nullable(),

                                        Select::make('coa_sumber_id')
                                            ->label('Akun Pembayar / 2144 Indigama (Kredit)')
                                            ->options($sourceCoa)
                                            ->searchable()
                                            ->optionsLimit(40)
                                            ->nullable(),

                                        TextInput::make('no_referensi_bukti')
                                            ->label('No. Bukti / Ref Transfer')
                                            ->placeholder('Contoh: TRF-BRI-001 / INV-PLN')
                                            ->maxLength(120),

                                        TextInput::make('keterangan')
                                            ->label('Catatan Selisih / Uraian')
                                            ->placeholder('Alasan selisih / penundaan')
                                            ->maxLength(255),
                                    ]),

                                FileUpload::make('lampiran_bukti')
                                    ->label('Upload Bukti Transfer / Nota / Kwitansi (Opsional — PDF/JPG/PNG)')
                                    ->directory('npb-bukti')
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                                    ->maxSize(5120)
                                    ->openable()
                                    ->downloadable(),
                            ])
                            ->columns(1),
                    ]),

                Section::make('Catatan & Penandatangan LPJ')
                    ->collapsed()
                    ->schema([
                        Textarea::make('catatan')
                            ->label('Catatan Pertanggungjawaban')
                            ->rows(3),

                        Grid::make(3)
                            ->schema([
                                TextInput::make('disusun_oleh')->label('Disusun oleh')->default('Bagian Keuangan'),
                                TextInput::make('diperiksa_oleh')->label('Diperiksa oleh')->default('Kepala'),
                                TextInput::make('disetujui_oleh')->label('Disetujui oleh')->default('Owner'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_penyelesaian')
                    ->label('No. Penyelesaian (LPJ)')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('npbRequest.nomor_pengajuan')
                    ->label('No. NPB')
                    ->searchable()
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('label_periode')
                    ->label('Periode')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_anggaran')
                    ->label('Anggaran NPB')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight(),

                Tables\Columns\TextColumn::make('total_realisasi')
                    ->label('Realisasi Aktual')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('selisih_anggaran')
                    ->label('Selisih Anggaran')
                    ->formatStateUsing(fn ($state) => ((float) $state < 0 ? '-' : '+') . 'Rp ' . number_format(abs((float) $state), 0, ',', '.'))
                    ->color(fn ($state) => (float) $state >= 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('sisa_dana')
                    ->label('Sisa Dana')
                    ->formatStateUsing(fn ($state) => ((float) $state < 0 ? '-' : '') . 'Rp ' . number_format(abs((float) $state), 0, ',', '.'))
                    ->color(fn ($state) => (float) $state >= 0 ? 'info' : 'danger')
                    ->weight('bold')
                    ->alignRight(),

                Tables\Columns\IconColumn::make('is_posted_to_ledger')
                    ->label('Jurnal GL')
                    ->boolean()
                    ->tooltip(fn ($state): string => $state ? 'Sudah diposting ke Buku Besar' : 'Belum / Mode LPJ Tracking Saja'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => NpbSettlement::STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'DRAFT'     => 'gray',
                        'SUBMITTED' => 'info',
                        'VERIFIED'  => 'warning',
                        'APPROVED'  => 'success',
                        default     => 'gray',
                    }),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(NpbSettlement::STATUS_OPTIONS),
            ])
            ->actions([
                Action::make('previewPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (NpbSettlement $record) => route('finance.npb-settlement.preview', $record))
                    ->openUrlInNewTab(),

                ActionGroup::make([
                    Action::make('downloadPdf')
                        ->label('Download PDF LPJ')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->url(fn (NpbSettlement $record) => route('finance.npb-settlement.download', $record))
                        ->openUrlInNewTab(),

                    Action::make('submit')
                        ->label('Ajukan Verifikasi')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('info')
                        ->visible(fn (NpbSettlement $r) => $r->status === 'DRAFT')
                        ->requiresConfirmation()
                        ->action(function (NpbSettlement $record) {
                            $record->update(['status' => 'SUBMITTED']);
                            Notification::make()->title("LPJ {$record->nomor_penyelesaian} diajukan")->success()->send();
                        }),

                    Action::make('verify')
                        ->label('Verifikasi (Kepala)')
                        ->icon('heroicon-o-eye')
                        ->color('warning')
                        ->visible(fn (NpbSettlement $r) => in_array($r->status, ['DRAFT', 'SUBMITTED'], true))
                        ->requiresConfirmation()
                        ->action(function (NpbSettlement $record) {
                            $record->update(['status' => 'VERIFIED']);
                            Notification::make()->title("LPJ {$record->nomor_penyelesaian} terverifikasi")->success()->send();
                        }),

                    Action::make('approve')
                        ->label('Approve & Tutup LPJ')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->visible(fn (NpbSettlement $r) => $r->status !== 'APPROVED')
                        ->requiresConfirmation()
                        ->modalDescription(fn (NpbSettlement $r) => $r->posting_mode === 'AUTO_JOURNAL'
                            ? 'LPJ akan disetujui, status NPB diperbarui, dan seluruh pos terealisasi otomatis diposting ke Buku Besar (termasuk 2144 Pinjaman Indigama untuk item Talangan Indigama).'
                            : 'LPJ akan disetujui dan status NPB diperbarui tanpa memposting jurnal ulang ke Buku Besar (mode Anti-Dobel).')
                        ->action(function (NpbSettlement $record) {
                            $record->update([
                                'status'       => 'APPROVED',
                                'disetujui_at' => now(),
                            ]);

                            $svc = app(NpbService::class);
                            $svc->recalculateSettlement($record->refresh());

                            $msg = "LPJ {$record->nomor_penyelesaian} disetujui & NPB diperbarui.";
                            if ($record->posting_mode === 'AUTO_JOURNAL') {
                                $posted = $svc->postSettlementToLedger($record->refresh());
                                $msg .= " ({$posted} pos diposting ke Buku Besar)";
                            }

                            Notification::make()->title($msg)->success()->send();
                        }),

                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ]),
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
            'index'  => Pages\ListNpbSettlements::route('/'),
            'create' => Pages\CreateNpbSettlement::route('/create'),
            'edit'   => Pages\EditNpbSettlement::route('/{record}/edit'),
        ];
    }
}
