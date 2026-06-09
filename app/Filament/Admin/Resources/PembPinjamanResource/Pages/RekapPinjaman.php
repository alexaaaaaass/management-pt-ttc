<?php

namespace App\Filament\Admin\Resources\PembPinjamanResource\Pages;

use App\Filament\Admin\Resources\PembPinjamanResource;
use App\Models\PengPinjaman;
use Filament\Resources\Pages\Page;

class RekapPinjaman extends Page
{
    protected static string $resource = PembPinjamanResource::class;

    protected static string $view = 'filament.admin.resources.pemb-pinjaman-resource.pages.rekap-pinjaman';

    public $data = [];

    public function mount(): void
    {
        $this->data = PengPinjaman::with([
            'karyawan',
            'pembayaran',
        ])->get();
    }
}