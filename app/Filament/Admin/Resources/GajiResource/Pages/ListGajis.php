<?php

namespace App\Filament\Admin\Resources\GajiResource\Pages;

use App\Filament\Admin\Pages\FinanceHrdSyncPage;
use App\Filament\Admin\Resources\BonusKaryawanResource;
use App\Filament\Admin\Resources\GajiResource;
use App\Filament\Admin\Resources\PotTunjanganResource;
use App\Models\Gaji;
use App\Models\Karyawan;
use App\Services\Finance\FinanceHrdSyncService;
use App\Services\HR\AttendanceService;
use App\Services\HR\PayrollExcelImportService;
use App\Services\HR\PayrollService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListGajis extends ListRecords
{
    protected static string $resource = GajiResource::class;

    public function getSubheading(): ?string
    {
        return 'Skema Bulanan, Harian & Freelance';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importExcel')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Payroll dari Excel')
                ->modalDescription('Unggah file Excel laporan penggajian TTC (format resmi bulanan multi-sheet maupun template datar standar).')
                ->form([
                    Forms\Components\FileUpload::make('attachment')
                        ->label('File Excel Payroll (.xlsx, .xls)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'application/octet-stream',
                        ])
                        ->disk('local')
                        ->directory('payroll-imports')
                        ->required()
                        ->helperText('File format .xlsx / .xls. Contoh file resmi: 11._NOVEMBER_PAYROLL_2026_TTC.xlsx.'),

                    Forms\Components\Grid::make(2)
                        ->schema([
                            Forms\Components\TextInput::make('periode')
                                ->label('Periode Payroll (YYYY-MM)')
                                ->placeholder('Contoh: 2026-11 (Kosongkan jika auto-deteksi)')
                                ->helperText('Kosongkan agar sistem mendeteksi otomatis dari teks judul/laporan di Excel.'),

                            Forms\Components\Select::make('status')
                                ->label('Status Slip Hasil Impor')
                                ->options([
                                    'Draft'    => 'Draft (Rancangan)',
                                    'Approved' => 'Approved (Disetujui)',
                                ])
                                ->default('Draft')
                                ->required(),
                        ]),

                    Forms\Components\Toggle::make('auto_create_karyawan')
                        ->label('Otomatis Daftarkan Karyawan Baru')
                        ->helperText('Jika NIP/Nama karyawan belum terdaftar di master data HRD, sistem akan mendaftarkannya otomatis.')
                        ->default(true),

                    Forms\Components\Toggle::make('sync_kasbon')
                        ->label('Sinkronkan Sheet Kasbon ke Modul Pinjaman Karyawan')
                        ->helperText('Jika file Excel memiliki sheet Kasbon, otomatis mencatat master pinjaman karyawan aktif untuk pelacakan sisa cicilan.')
                        ->default(true),
                ])
                ->action(function (array $data) {
                    try {
                        $relativePath = $data['attachment'];
                        $fullPath = Storage::disk('local')->path($relativePath);

                        $service = app(PayrollExcelImportService::class);
                        $result = $service->importFromSpreadsheet($fullPath, [
                            'periode'              => $data['periode'] ?? null,
                            'status'               => $data['status'] ?? 'Draft',
                            'auto_create_karyawan' => (bool) ($data['auto_create_karyawan'] ?? true),
                            'sync_kasbon'          => (bool) ($data['sync_kasbon'] ?? true),
                        ]);

                        $msg = "Berhasil memproses {$result['total_records']} slip gaji ({$result['imported_count']} baru, {$result['updated_count']} diperbarui) untuk periode {$result['periode']}. Total Take Home Pay: Rp " . number_format($result['total_thp'], 0, ',', '.');
                        if ($result['karyawan_created_count'] > 0) {
                            $msg .= " ({$result['karyawan_created_count']} karyawan baru otomatis didaftarkan).";
                        }

                        Notification::make()
                            ->title('Impor Payroll Berhasil!')
                            ->body($msg)
                            ->success()
                            ->duration(10000)
                            ->send();

                        if (! empty($result['errors'])) {
                            Notification::make()
                                ->title('Catatan Impor')
                                ->body(implode("\n", $result['errors']))
                                ->warning()
                                ->send();
                        }
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Mengimpor Excel')
                            ->body($e->getMessage())
                            ->danger()
                            ->persistent()
                            ->send();
                    }
                }),

            Actions\ActionGroup::make([
                Actions\Action::make('bonusKaryawan')
                    ->label('Bonus Karyawan')
                    ->icon('heroicon-o-gift')
                    ->color('success')
                    ->url(fn () => BonusKaryawanResource::getUrl('index')),

                Actions\Action::make('potonganTunjangan')
                    ->label('Potongan Tunjangan')
                    ->icon('heroicon-o-scissors')
                    ->color('danger')
                    ->url(fn () => PotTunjanganResource::getUrl('index')),

                Actions\Action::make('openFinanceSync')
                    ->label('Sinkron Finance & NPB')
                    ->icon('heroicon-o-arrows-right-left')
                    ->color('primary')
                    ->url(fn () => FinanceHrdSyncPage::getUrl()),

                Actions\Action::make('cetakRekap')
                    ->label('PDF Rekap Payroll')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->form([
                        Forms\Components\TextInput::make('periode')
                            ->label('Periode (YYYY-MM)')
                            ->default(now()->format('Y-m'))
                            ->required(),
                        Forms\Components\Select::make('tipe')
                            ->label('Skema Gaji')
                            ->options([
                                ''          => 'Semua Skema',
                                'Bulanan'   => 'Bulanan',
                                'Harian'    => 'Harian',
                                'Freelance' => 'Freelance',
                            ])
                            ->default(''),
                    ])
                    ->action(function (array $data) {
                        $url = route('hrd.rekap-payroll.preview', [
                            'periode' => $data['periode'],
                            'tipe'    => $data['tipe'] ?? '',
                        ]);
                        $this->js("window.open(" . json_encode($url) . ", '_blank')");
                    }),

                Actions\Action::make('cetakSlipMassal')
                    ->label('PDF Slip Massal')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('gray')
                    ->form([
                        Forms\Components\TextInput::make('periode')
                            ->label('Periode (YYYY-MM)')
                            ->default(now()->format('Y-m'))
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $url = route('hrd.slip-gaji.batch', ['periode' => $data['periode']]);
                        $this->js("window.open(" . json_encode($url) . ", '_blank')");
                    }),

                Actions\Action::make('downloadTemplate')
                    ->label('Download Template Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->url(route('hrd.payroll.template'))
                    ->openUrlInNewTab(),
            ])
                ->label('Menu & Cetak')
                ->icon('heroicon-o-ellipsis-horizontal-circle')
                ->color('gray')
                ->button(),

            Actions\Action::make('generate_massal')
                ->label('Generate')
                ->icon('heroicon-o-bolt')
                ->color('info')
                ->form([
                    Forms\Components\Select::make('tipe_gaji')
                        ->label('Skema Karyawan')
                        ->options([
                            'Bulanan'   => 'Bulanan (Tetap / Kontrak)',
                            'Harian'    => 'Harian',
                            'Freelance' => 'Freelance',
                            'ALL'       => 'Semua (Bulanan + Harian + Freelance)',
                        ])
                        ->default('Bulanan')
                        ->required(),

                    Forms\Components\TextInput::make('periode')
                        ->label('Periode Payroll (YYYY-MM)')
                        ->default(now()->format('Y-m'))
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $set) {
                            if (is_string($state) && preg_match('/^(\d{4})-(\d{2})$/', $state, $m)) {
                                $range = app(AttendanceService::class)->getPeriodDateRange((int) $m[1], (int) $m[2]);
                                $set('tanggal_mulai', $range['start']->toDateString());
                                $set('tanggal_selesai', $range['end']->toDateString());
                            }
                        })
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal_mulai')
                        ->label('Tgl Mulai Hitung Absensi/Lembur')
                        ->default(fn () => app(AttendanceService::class)->getPeriodDateRange(now()->year, now()->month)['start'])
                        ->helperText('Otomatis mengikuti aturan cut-off 26–25 di Pengaturan Payroll')
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal_selesai')
                        ->label('Tgl Selesai Hitung Absensi/Lembur')
                        ->default(fn () => app(AttendanceService::class)->getPeriodDateRange(now()->year, now()->month)['end'])
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal_pembayaran')
                        ->label('Tanggal Pembayaran Default')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $periode = $data['periode'];
                    $tipe = $data['tipe_gaji'];

                    $query = Karyawan::where('status_pegawai', 'Aktif');
                    if ($tipe === 'ALL') {
                        $query->whereIn('tipe_gaji', ['Bulanan', 'Harian', 'Freelance']);
                    } else {
                        $query->where('tipe_gaji', $tipe);
                    }

                    $karyawans = $query->get();
                    $count = 0;
                    $service = app(PayrollService::class);

                    foreach ($karyawans as $k) {
                        if ($k->tipe_gaji === 'Bulanan' && Gaji::where('karyawan_id', $k->id)->where('periode', $periode)->where('tipe_payroll', 'Bulanan')->exists()) {
                            continue;
                        }

                        $calc = $service->calculateEmployeePayroll($k->id, $periode, $data['tanggal_mulai'], $data['tanggal_selesai']);
                        $calc['tanggal_pembayaran'] = $data['tanggal_pembayaran'];
                        $calc['status'] = 'Draft';
                        $calc['metode_pembayaran'] = 'Transfer Bank';
                        $calc['post_to_ledger'] = false;

                        $service->recordPayroll($calc);
                        $count++;
                    }

                    Notification::make()
                        ->title('Generate Payroll Selesai')
                        ->body("Berhasil membuat {$count} slip gaji draft ({$tipe}) untuk periode {$periode}.")
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('+ Slip Manual'),
        ];
    }
}
