<?php

namespace App\Filament\Admin\Pages;

use App\Models\Karyawan;
use App\Models\MasterDepartemen;
use App\Services\HR\AttendanceService;
use Filament\Pages\Page;

class RekapAbsensiBulanan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-table-cells';
    protected static string $view = 'filament.admin.pages.rekap-absensi-bulanan';
    protected static ?string $navigationLabel = 'Rekap Absensi';
    protected static ?string $title = 'Rekap Absensi Bulanan';
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 3;
    protected static bool $shouldRegisterNavigation = false;

    public int $bulan;
    public int $tahun;
    public ?string $departemenId = '';
    public ?string $tipeGaji = '';

    public function mount(): void
    {
        $this->bulan = (int) request()->query('bulan', now()->month);
        $this->tahun = (int) request()->query('tahun', now()->year);
    }

    public function getRecap(): array
    {
        return app(AttendanceService::class)->getMonthlyRecap(
            $this->tahun,
            $this->bulan,
            filled($this->departemenId) ? (int) $this->departemenId : null,
            filled($this->tipeGaji) ? $this->tipeGaji : null
        );
    }

    public function getDepartemens()
    {
        return MasterDepartemen::orderBy('nama_departemen')->get();
    }

    public function getTipeOptions(): array
    {
        return Karyawan::TIPE_GAJI_OPTIONS;
    }
}
