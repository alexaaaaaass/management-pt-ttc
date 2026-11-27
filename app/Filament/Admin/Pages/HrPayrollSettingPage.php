<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\HariLiburResource;
use App\Filament\Admin\Resources\HrShiftResource;
use App\Filament\Admin\Resources\MasterDepartemenResource;
use App\Models\HrPayrollSetting;
use App\Models\MasterCOA;
use Filament\Actions;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class HrPayrollSettingPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static string $view = 'filament.admin.pages.hr-payroll-setting-page';
    protected static ?string $navigationLabel = 'Pengaturan Payroll & Shift';
    protected static ?string $title = 'Pengaturan Payroll, Shift, Lembur & Borongan';
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 8;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('masterShift')
                ->label('Master Shift Kerja')
                ->icon('heroicon-o-clock')
                ->color('info')
                ->url(fn () => HrShiftResource::getUrl('index')),

            Actions\Action::make('hariLibur')
                ->label('Kalender Hari Libur')
                ->icon('heroicon-o-calendar-days')
                ->color('warning')
                ->url(fn () => HariLiburResource::getUrl('index')),

            Actions\Action::make('departemen')
                ->label('Master Departemen')
                ->icon('heroicon-o-building-office')
                ->color('gray')
                ->url(fn () => MasterDepartemenResource::getUrl('index')),
        ];
    }

    public ?array $data = [];

    public function mount(): void
    {
        $setting = HrPayrollSetting::current();
        $this->form->fill($setting->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Jam Kerja Standar, Cut-Off Payroll & Kuota Cuti')
                    ->description('Standar PT TTC menggunakan cut-off tanggal 26 bulan lalu s/d tanggal 25 bulan berjalan. Jam kerja detail per karyawan mengikuti Master Shift Kerja.')
                    ->schema([
                        Grid::make(5)->schema([
                            TextInput::make('jam_masuk_standar')->label('Jam Masuk Default (HH:MM)')->default('08:00')->required(),
                            TextInput::make('jam_pulang_standar')->label('Jam Pulang Default (HH:MM)')->default('17:00')->required(),
                            TextInput::make('toleransi_telat_menit')->label('Toleransi Telat (Menit)')->numeric()->default(10)->required(),
                            TextInput::make('kuota_cuti_tahunan')->label('Kuota Cuti Tahunan (Hari)')->numeric()->default(12)->required(),
                            TextInput::make('alert_kontrak_hari')
                                ->label('Pengingat Kontrak Habis (Hari)')
                                ->helperText('Mis. 7, 14, 30, atau 60 hari sebelum akhir kontrak')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(365)
                                ->default(30)
                                ->required(),
                        ]),
                        Grid::make(3)->schema([
                            Toggle::make('pakai_cutoff_bulanan')
                                ->label('Gunakan Cut-Off Bulanan (Tgl 26 – 25)')
                                ->helperText('Jika dimatikan, periode absensi/lembur memakai Tgl 1 s/d akhir bulan kalender.')
                                ->default(true),
                            TextInput::make('cutoff_tanggal_mulai')
                                ->label('Tanggal Mulai Cut-Off (Bulan Lalu)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(31)
                                ->default(26)
                                ->required(),
                            TextInput::make('cutoff_tanggal_selesai')
                                ->label('Tanggal Selesai Cut-Off (Bulan Berjalan)')
                                ->numeric()
                                ->minValue(1)
                                ->maxValue(31)
                                ->default(25)
                                ->required(),
                        ]),
                    ]),

                Section::make('Aturan Perhitungan Lembur & Uang Makan Lembur')
                    ->schema([
                        Grid::make(4)->schema([
                            Select::make('metode_lembur')
                                ->label('Metode Tarif Lembur')
                                ->options([
                                    'PER_173'     => 'Depnaker (1/173 × Gaji Pokok)',
                                    'TARIF_TETAP' => 'Tarif Tetap per Jam',
                                ])
                                ->default('PER_173')
                                ->required(),
                            TextInput::make('tarif_lembur_tetap_per_jam')->label('Tarif Tetap / Jam')->prefix('Rp')->currencyMask(0)->default(15000),
                            TextInput::make('pengali_lembur_hari_kerja')->label('Pengali Hari Kerja')->numeric()->step(0.5)->default(1),
                            TextInput::make('pengali_lembur_hari_libur')->label('Pengali Minggu / Libur')->numeric()->step(0.5)->default(2),
                        ]),
                        Grid::make(2)->schema([
                            TextInput::make('uang_makan_lembur_nominal')
                                ->label('Uang Makan Lembur Otomatis / Kejadian')
                                ->helperText('Otomatis ditambahkan ke Uang Lembur tiap kali lembur melebihi batas minimum jam. Isi 0 untuk menonaktifkan.')
                                ->prefix('Rp')
                                ->currencyMask(0)
                                ->default(15000),
                            TextInput::make('min_jam_uang_makan_lembur')
                                ->label('Minimal Durasi Lembur Dapat Uang Makan (Jam)')
                                ->helperText('Default: > 2 jam (durasi lembur di atas nilai ini akan mendapat uang makan lembur).')
                                ->numeric()
                                ->step(0.5)
                                ->default(2),
                        ]),
                    ]),

                Section::make('Potongan Kehadiran, Scorsing & BPJS')
                    ->schema([
                        Grid::make(4)->schema([
                            Select::make('metode_potongan_telat')
                                ->label('Metode Potongan Terlambat')
                                ->options([
                                    'FLAT'    => 'Denda Flat per Kejadian Telat',
                                    'PER_JAM' => 'Proporsional per Jam (GP / 173 × Jam Telat)',
                                ])
                                ->default('FLAT')
                                ->required(),
                            TextInput::make('potongan_telat_per_kali')->label('Denda Telat / Kali (Mode Flat)')->prefix('Rp')->currencyMask(0)->default(10000),
                            TextInput::make('potongan_alpha_per_hari')
                                ->label('Potongan Alpha / Scorsing / Hari')
                                ->helperText('Isi 0 agar otomatis menghitung 1/25 × Gaji Pokok')
                                ->prefix('Rp')
                                ->currencyMask(0)
                                ->default(0),
                            Toggle::make('potongan_sakit_tanpa_surat')
                                ->label('Potong Sakit Tanpa Surat Dokter')
                                ->helperText('Jika aktif, izin "Sakit (Tanpa Surat Dokter)" dipotong GP/25 seperti Alpha.')
                                ->default(false),
                        ]),
                        Toggle::make('auto_hitung_bpjs')
                            ->label('Hitung BPJS Otomatis (%)')
                            ->helperText('Aktif jika ingin memotong % BPJS otomatis pada karyawan tanpa nominal manual.'),
                        Grid::make(4)->schema([
                            TextInput::make('persen_bpjs_kesehatan')->label('BPJS Kes Karyawan (%)')->numeric()->step(0.1)->default(1),
                            TextInput::make('persen_bpjs_ketenagakerjaan')->label('BPJS TK Karyawan (%)')->numeric()->step(0.1)->default(2),
                            TextInput::make('persen_bpjs_tk_perusahaan')->label('BPJS TK Perusahaan (%)')->numeric()->step(0.1)->default(3.7)->helperText('Porsi beban PT (COA 6502)'),
                            TextInput::make('persen_bpjs_kes_perusahaan')->label('BPJS Kes Perusahaan (%)')->numeric()->step(0.1)->default(4.0),
                        ]),
                    ]),

                Section::make('Tarif Default Payroll Borongan / Part-Time')
                    ->schema([
                        Grid::make(5)->schema([
                            TextInput::make('tarif_protol_default')->label('Tarif Protol / Unit')->prefix('Rp')->currencyMask(0)->default(0),
                            TextInput::make('tarif_packing_default')->label('Tarif Packing / Unit')->prefix('Rp')->currencyMask(0)->default(0),
                            TextInput::make('tarif_lain_borongan_default')->label('Tarif Lainnya / Unit')->prefix('Rp')->currencyMask(0)->default(0),
                            TextInput::make('tunj_hadir_borongan_default')->label('Tunj. Hadir Default')->prefix('Rp')->currencyMask(0)->default(0),
                            TextInput::make('tunj_makan_borongan_default')->label('Tunj. Makan Default')->prefix('Rp')->currencyMask(0)->default(0),
                        ]),
                    ]),

                Section::make('Integrasi COA Finance ↔ HRD (Jurnal Multi-Line PSAK)')
                    ->description('Kosongkan jika ingin sistem mendeteksi akun COA standar PT TTC secara otomatis (5201 Produksi, 6101 Sales, 6201 Admin, 6301 Umum, 6502 BPJS PT, 1131 Piutang Karyawan, 2133 BPJS Terutang, 2122 PPh 21).')
                    ->schema(function () {
                        $allCoa = MasterCOA::orderBy('kode_akun')->get()->mapWithKeys(fn ($c) => [$c->id => trim((string) $c->kode_akun) . ' - ' . $c->nama_akun])->toArray();
                        $cashCoa = MasterCOA::where('kode_akun', 'like', '111%')->orderBy('kode_akun')->get()->mapWithKeys(fn ($c) => [$c->id => trim((string) $c->kode_akun) . ' - ' . $c->nama_akun])->toArray();

                        return [
                            Grid::make(3)->schema([
                                Select::make('default_account_kas_id')->label('Default Rekening Pembayar Gaji (111x)')->options($cashCoa)->searchable()->nullable(),
                                Select::make('coa_beban_gaji_produksi_id')->label('COA Gaji Produksi / Borongan (HPP 5201)')->options($allCoa)->searchable()->nullable(),
                                Select::make('coa_beban_gaji_umum_id')->label('COA Gaji Karyawan Umum (6301)')->options($allCoa)->searchable()->nullable(),
                            ]),
                            Grid::make(3)->schema([
                                Select::make('coa_beban_gaji_sales_id')->label('COA Gaji Bagian Penjualan (6101)')->options($allCoa)->searchable()->nullable(),
                                Select::make('coa_beban_gaji_admin_id')->label('COA Gaji Administrasi / Finance (6201)')->options($allCoa)->searchable()->nullable(),
                                Select::make('coa_beban_bpjs_id')->label('COA Beban BPJS Perusahaan (6502)')->options($allCoa)->searchable()->nullable(),
                            ]),
                            Grid::make(4)->schema([
                                Select::make('coa_piutang_karyawan_id')->label('COA Piutang / Kasbon (1131)')->options($allCoa)->searchable()->nullable(),
                                Select::make('coa_hutang_gaji_id')->label('COA Gaji Terutang (2131)')->options($allCoa)->searchable()->nullable(),
                                Select::make('coa_hutang_bpjs_id')->label('COA BPJS Terutang (2133)')->options($allCoa)->searchable()->nullable(),
                                Select::make('coa_hutang_pph21_id')->label('COA PPh 21 Terutang (2122)')->options($allCoa)->searchable()->nullable(),
                            ]),
                        ];
                    }),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $state = $this->form->getState();
        $setting = HrPayrollSetting::current();
        $setting->update($state);

        Notification::make()
            ->title('Pengaturan Payroll & Shift berhasil disimpan')
            ->success()
            ->send();
    }
}
