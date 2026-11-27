<?php

namespace App\Filament\Admin\Resources\AbsensiResource\Pages;

use App\Filament\Admin\Pages\RekapAbsensiBulanan;
use App\Filament\Admin\Resources\AbsensiResource;
use App\Filament\Admin\Resources\HariLiburResource;
use App\Services\HR\AttendanceService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListAbsensis extends ListRecords
{
    protected static string $resource = AbsensiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('rekapAbsensi')
                ->label('Rekap Bulanan')
                ->icon('heroicon-o-table-cells')
                ->color('info')
                ->url(fn () => RekapAbsensiBulanan::getUrl()),

            Actions\ActionGroup::make([
                Actions\Action::make('importExcel')
                    ->label('Import Fingerprint (CSV/Excel)')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->form([
                        FileUpload::make('file_absensi')
                            ->label('File Ekspor Mesin Fingerprint (.xlsx / .xls / .csv)')
                            ->disk('local')
                            ->directory('temp-absensi')
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/plain',
                                'application/vnd.ms-excel',
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $relPath = $data['file_absensi'];
                        $absPath = Storage::disk('local')->path($relPath);

                        try {
                            $res = app(AttendanceService::class)->importFromFile($absPath);
                            Notification::make()
                                ->title('Import Absensi Selesai')
                                ->body("Masuk: {$res['imported']} scan · Lewati/Duplikat: {$res['skipped']} · Tidak cocok karyawan: {$res['unmatched']}")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal Import Absensi')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        } finally {
                            Storage::disk('local')->delete($relPath);
                        }
                    }),

                Actions\Action::make('downloadTemplate')
                    ->label('Download Template CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $csv = "PIN,NIP,Nama,Tanggal Scan,IO,Mesin\n101,K01,Heri Purnomo," . now()->format('Y-m-d') . " 08:00:00,Masuk,Fingerprint-1\n101,K01,Heri Purnomo," . now()->format('Y-m-d') . " 17:00:00,Keluar,Fingerprint-1\n";

                        return response()->streamDownload(fn () => print($csv), 'template_import_absensi_ttc.csv', [
                            'Content-Type' => 'text/csv',
                        ]);
                    }),

                Actions\Action::make('hariLibur')
                    ->label('Kalender Hari Libur')
                    ->icon('heroicon-o-calendar-days')
                    ->color('warning')
                    ->url(fn () => HariLiburResource::getUrl('index')),
            ])
                ->label('Import & Libur')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->button(),

            Actions\CreateAction::make()
                ->label('+ Input Manual'),
        ];
    }
}
