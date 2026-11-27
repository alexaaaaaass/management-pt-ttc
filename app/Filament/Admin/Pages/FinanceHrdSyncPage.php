<?php

namespace App\Filament\Admin\Pages;

use App\Models\Gaji;
use App\Models\HrPayrollSetting;
use App\Models\MasterCOA;
use App\Models\NpbRequest;
use App\Services\Finance\FinanceHrdSyncService;
use App\Services\Finance\TreasuryService;
use App\Support\MoneyHelper;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Enums\MaxWidth;

class FinanceHrdSyncPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationLabel = 'Sinkronisasi Finance & HRD';
    protected static ?string $title = 'Command Center — Sinkronisasi Finance ↔ HRD';
    protected static string $view = 'filament.admin.pages.finance-hrd-sync-page';

    public string $selectedPeriode = '';

    public function getMaxContentWidth(): MaxWidth
    {
        return MaxWidth::Full;
    }

    public static function getNavigationBadge(): ?string
    {
        $unpaid = Gaji::whereIn('status', ['Draft', 'Approved'])->count();
        return $unpaid > 0 ? (string) $unpaid : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function mount(): void
    {
        $periods = array_keys(app(FinanceHrdSyncService::class)->getAvailablePeriods());
        $requested = (string) request()->query('periode', '');
        $this->selectedPeriode = $requested ?: ($periods[0] ?? now()->format('Y-m'));
    }

    public function getAvailablePeriodsProperty(): array
    {
        return app(FinanceHrdSyncService::class)->getAvailablePeriods();
    }

    public function getSummaryProperty(): array
    {
        return app(FinanceHrdSyncService::class)->getPeriodSummary($this->selectedPeriode);
    }

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

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('syncToNpb')
                ->label('Sinkron ke NPB')
                ->icon('heroicon-o-document-currency-dollar')
                ->color('primary')
                ->modalHeading('Sinkronkan Anggaran Gaji, BPJS & Borongan HRD ke NPB')
                ->modalDescription('Otomatis memperbarui atau menambahkan pos Gaji Karyawan, BPJS Ketenagakerjaan (2% + 3,7%), dan Upah Borongan ke Nota Pengajuan Biaya (NPB).')
                ->form([
                    Forms\Components\Select::make('npb_request_id')
                        ->label('Target NPB (Kosongkan untuk Deteksi / Buat NPB Baru)')
                        ->options(fn () => NpbRequest::orderByDesc('id')->get()->mapWithKeys(fn (NpbRequest $n) => [
                            $n->id => "{$n->nomor_pengajuan} — {$n->periode_utama} ({$n->status})",
                        ])->toArray())
                        ->default(fn () => $this->summary['npb']['id'] ?? null)
                        ->searchable()
                        ->nullable(),

                    Forms\Components\Select::make('basis_gaji')
                        ->label('Basis Nominal Pos "Gaji Karyawan" di NPB')
                        ->options([
                            'THP'        => 'Take Home Pay (Kas Aktual Ditransfer ke Karyawan)',
                            'GAJI_POKOK' => 'Total Gaji Pokok (Sesuai Format Pengajuan Awal TTC)',
                            'GROSS'      => 'Total Beban Gaji Net (THP + Potongan Kasbon/BPJS)',
                        ])
                        ->default('THP')
                        ->required(),
                ])
                ->action(function (array $data) {
                    try {
                        $res = app(FinanceHrdSyncService::class)->syncPeriodToNpb(
                            $this->selectedPeriode,
                            ! empty($data['npb_request_id']) ? (int) $data['npb_request_id'] : null,
                            $data['basis_gaji'] ?? 'THP'
                        );

                        Notification::make()
                            ->title('Sinkronisasi ke NPB Berhasil!')
                            ->body("NPB {$res['nomor_pengajuan']} diperbarui ({$res['updated_items']} pos diupdate, {$res['added_items']} pos ditambahkan). Total Kebutuhan Utama: Rp " . number_format($res['total_kebutuhan'], 0, ',', '.'))
                            ->success()
                            ->duration(8000)
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Gagal Sinkron NPB')->body($e->getMessage())->danger()->send();
                    }
                }),

            Actions\Action::make('batchPay')
                ->label('Bayar & Posting GL Massal')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->modalHeading('Pembayaran & Posting Jurnal Multi-Line PSAK Massal')
                ->modalDescription('Membayar seluruh slip gaji periode terpilih sekaligus membentuk jurnal seimbang: DEBIT Beban Gaji (per Cost Center) + BPJS Perusahaan (6502) vs KREDIT Bank (THP) + Piutang Karyawan (1131) + BPJS Terutang (2133).')
                ->form([
                    Forms\Components\Select::make('account_kas_id')
                        ->label('Rekening Kas / Bank Pembayar (111x)')
                        ->options(fn () => self::getCashBankOptions())
                        ->default(fn () => HrPayrollSetting::first()?->default_account_kas_id)
                        ->searchable()
                        ->required(),

                    Forms\Components\Select::make('metode_pembayaran')
                        ->label('Metode Pembayaran')
                        ->options([
                            'Transfer Bank' => 'Transfer Bank',
                            'Tunai'         => 'Tunai (Kas)',
                        ])
                        ->default('Transfer Bank')
                        ->required(),

                    Forms\Components\Toggle::make('post_to_ledger')
                        ->label('Posting Jurnal Multi-Line ke Buku Besar (GL)')
                        ->helperText('Aktifkan untuk mencatat otomatis Beban Gaji, BPJS PT, Kasbon (1131), dan BPJS Terutang (2133). Matikan jika pembayaran sudah diimpor lewat mutasi bank agar kas tidak dobel.')
                        ->default(true),

                    Forms\Components\Toggle::make('include_borongan')
                        ->label('Ikutkan Batch Payroll Borongan Periode Ini')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    try {
                        $res = app(FinanceHrdSyncService::class)->batchApproveAndPayPeriod(
                            $this->selectedPeriode,
                            (int) $data['account_kas_id'],
                            $data['metode_pembayaran'],
                            (bool) ($data['post_to_ledger'] ?? true),
                            (bool) ($data['include_borongan'] ?? false)
                        );

                        Notification::make()
                            ->title('Pembayaran Massal Selesai!')
                            ->body("Periode {$res['periode']}: {$res['paid_slips']} slip dibayar, {$res['reposted_slips']} slip di-sync ulang ke GL, {$res['paid_borongan']} batch borongan dibayar. Total Kas THP: Rp " . number_format($res['total_cash'], 0, ',', '.'))
                            ->success()
                            ->duration(9000)
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Gagal Bayar Massal')->body($e->getMessage())->danger()->send();
                    }
                }),

            Actions\Action::make('remitBpjs')
                ->label('Setor BPJS ke GL')
                ->icon('heroicon-o-shield-check')
                ->color('warning')
                ->modalHeading('Catat Penyetoran Iuran BPJS Ketenagakerjaan & Kesehatan')
                ->modalDescription('Membentuk jurnal penyetoran: DEBIT Kewajiban BPJS Terutang (2133) / KREDIT Kas atau Bank (111x).')
                ->form([
                    Forms\Components\TextInput::make('nominal')
                        ->label('Nominal Disetorkan ke BPJS (Rp)')
                        ->prefix('Rp')
                        ->currencyMask(0)
                        ->default(fn () => MoneyHelper::format($this->summary['total_bpjs_setor'] ?? 0, 0))
                        ->required(),

                    Forms\Components\Select::make('account_kas_id')
                        ->label('Rekening Bank / Kas Pembayar (111x)')
                        ->options(fn () => self::getCashBankOptions())
                        ->default(fn () => HrPayrollSetting::first()?->default_account_kas_id)
                        ->searchable()
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal')
                        ->label('Tanggal Setor BPJS')
                        ->default(now())
                        ->required(),

                    Forms\Components\TextInput::make('keterangan')
                        ->label('Keterangan / Nomor Billing BPJS')
                        ->default(fn () => "Setoran BPJS Ketenagakerjaan Periode {$this->selectedPeriode}"),
                ])
                ->action(function (array $data) {
                    try {
                        $nom = (float) (MoneyHelper::clean($data['nominal']) ?? 0);
                        app(TreasuryService::class)->postBpjsRemittance(
                            $this->selectedPeriode,
                            $nom,
                            (int) $data['account_kas_id'],
                            $data['tanggal'],
                            $data['keterangan'] ?? null
                        );
                        Notification::make()
                            ->title('Penyetoran BPJS Tercatat di Buku Besar!')
                            ->body('DEBIT 2133 BPJS Terutang / KREDIT Bank sebesar Rp ' . number_format($nom, 0, ',', '.'))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()->title('Gagal Posting Setor BPJS')->body($e->getMessage())->danger()->send();
                    }
                }),

            Actions\Action::make('previewRekapPdf')
                ->label('PDF Rekap Payroll')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn () => route('hrd.rekap-payroll.preview', ['periode' => $this->selectedPeriode]))
                ->openUrlInNewTab(),
        ];
    }
}
