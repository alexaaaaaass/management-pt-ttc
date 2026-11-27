<?php

namespace App\Filament\Admin\Pages;

use App\Filament\Admin\Resources\CutiResource;
use App\Filament\Admin\Resources\IzinResource;
use App\Filament\Admin\Resources\LemburResource;
use App\Models\Cuti;
use App\Models\Izin;
use App\Models\Lembur;
use App\Models\PengPinjaman;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class HrdApprovalPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-check-badge';
    protected static string $view = 'filament.admin.pages.hrd-approval-page';
    protected static ?string $navigationLabel = 'Izin, Cuti & Lembur';
    protected static ?string $title = 'Izin, Cuti & Lembur';
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 4;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('kelolaIzin')
                ->label('Izin & Scorsing')
                ->icon('heroicon-o-document-text')
                ->color('warning')
                ->url(fn () => IzinResource::getUrl('index')),

            Actions\Action::make('kelolaCuti')
                ->label('Cuti')
                ->icon('heroicon-o-calendar')
                ->color('info')
                ->url(fn () => CutiResource::getUrl('index')),

            Actions\Action::make('kelolaLembur')
                ->label('Lembur')
                ->icon('heroicon-o-clock')
                ->color('success')
                ->url(fn () => LemburResource::getUrl('index')),

            Actions\Action::make('rekapCuti')
                ->label('Rekap Cuti')
                ->icon('heroicon-o-calendar-days')
                ->color('gray')
                ->url(fn () => RekapCutiTahunan::getUrl()),
        ];
    }

    public string $activeTab = 'cuti';
    public bool $onlyPending = false;

    public static function getNavigationBadge(): ?string
    {
        $cnt = Cuti::where('status', 'Pending')->count()
            + Izin::where('status', 'Pending')->count()
            + Lembur::where('status', 'Pending')->count()
            + PengPinjaman::where('status', 'Pending')->count();

        return $cnt > 0 ? (string) $cnt : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function approveItem(string $type, int $id): void
    {
        $model = $this->resolveModel($type, $id);
        if (! $model) {
            return;
        }

        $model->update([
            'status'      => 'Approved',
            'approved_by' => auth()->user()?->name ?? 'Admin HRD',
            'approved_at' => now(),
        ]);

        Notification::make()->title('Pengajuan disetujui (Approved)')->success()->send();
    }

    public function rejectItem(string $type, int $id): void
    {
        $model = $this->resolveModel($type, $id);
        if (! $model) {
            return;
        }

        $model->update([
            'status'      => 'Rejected',
            'approved_by' => auth()->user()?->name ?? 'Admin HRD',
            'approved_at' => now(),
        ]);

        Notification::make()->title('Pengajuan ditolak (Rejected)')->danger()->send();
    }

    protected function resolveModel(string $type, int $id)
    {
        return match ($type) {
            'cuti'     => Cuti::find($id),
            'izin'     => Izin::find($id),
            'lembur'   => Lembur::find($id),
            'pinjaman' => PengPinjaman::find($id),
            default    => null,
        };
    }

    public function getCounts(): array
    {
        return [
            'cuti'     => Cuti::where('status', 'Pending')->count(),
            'izin'     => Izin::where('status', 'Pending')->count(),
            'lembur'   => Lembur::where('status', 'Pending')->count(),
            'pinjaman' => PengPinjaman::where('status', 'Pending')->count(),
        ];
    }

    public function getItems()
    {
        $q = match ($this->activeTab) {
            'izin'     => Izin::with('karyawan'),
            'lembur'   => Lembur::with('karyawan'),
            'pinjaman' => PengPinjaman::with('karyawan'),
            default    => Cuti::with('karyawan'),
        };

        if ($this->onlyPending) {
            $q->where('status', 'Pending');
        }

        return $q->orderByDesc('id')->limit(100)->get();
    }
}
