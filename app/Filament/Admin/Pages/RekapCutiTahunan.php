<?php

namespace App\Filament\Admin\Pages;

use App\Models\Cuti;
use App\Models\HrPayrollSetting;
use App\Models\Karyawan;
use Filament\Pages\Page;

class RekapCutiTahunan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';
    protected static string $view = 'filament.rekap-cuti-tahunan';
    protected static ?string $navigationLabel = 'Rekap Cuti Tahunan';
    protected static ?string $title = 'Rekap Cuti Tahunan';
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 4;
    protected static bool $shouldRegisterNavigation = false;

    public int $tahun;

    public function mount(): void
    {
        $this->tahun = (int) request()->query('tahun', now()->year);
    }

    public function getRecapData(): array
    {
        $kuota = (int) (HrPayrollSetting::current()->kuota_cuti_tahunan ?: 12);
        $karyawans = Karyawan::with('departemen')
            ->where('status_pegawai', 'Aktif')
            ->orderBy('nama')
            ->get();

        $usedByKaryawan = Cuti::query()
            ->where('jenis_cuti', 'Cuti Tahunan')
            ->whereYear('tanggal_mulai', $this->tahun)
            ->where(function ($q) {
                $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
            })
            ->selectRaw('karyawan_id, COALESCE(SUM(jumlah_hari), 0) as total_hari')
            ->groupBy('karyawan_id')
            ->pluck('total_hari', 'karyawan_id');

        $rows = [];
        foreach ($karyawans as $k) {
            $used = (int) ($usedByKaryawan[$k->id] ?? 0);
            $sisa = max(0, $kuota - $used);
            $rows[] = [
                'nama'       => $k->nama,
                'nip'        => $k->nip,
                'departemen' => $k->departemen?->nama_departemen ?: '-',
                'jabatan'    => $k->jabatan ?: '-',
                'kuota'      => $kuota,
                'digunakan'  => $used,
                'sisa'       => $sisa,
            ];
        }

        return [
            'tahun' => $this->tahun,
            'kuota' => $kuota,
            'rows'  => $rows,
        ];
    }
}
