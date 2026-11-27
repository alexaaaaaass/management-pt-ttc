<?php

namespace App\Filament\Admin\Resources\KaryawanResource\Pages;

use App\Filament\Admin\Resources\HariLiburResource;
use App\Filament\Admin\Resources\HrShiftResource;
use App\Filament\Admin\Resources\KaryawanResource;
use App\Filament\Admin\Resources\MasterDepartemenResource;
use App\Services\HR\KaryawanExcelService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListKaryawans extends ListRecords
{
    protected static string $resource = KaryawanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ActionGroup::make([
                Actions\Action::make('masterDepartemen')
                    ->label('Master Departemen')
                    ->icon('heroicon-o-building-office')
                    ->url(fn () => MasterDepartemenResource::getUrl('index')),

                Actions\Action::make('masterShift')
                    ->label('Master Shift Kerja')
                    ->icon('heroicon-o-clock')
                    ->url(fn () => HrShiftResource::getUrl('index')),

                Actions\Action::make('hariLibur')
                    ->label('Kalender Hari Libur')
                    ->icon('heroicon-o-calendar-days')
                    ->url(fn () => HariLiburResource::getUrl('index')),
            ])
                ->label('Master HRD')
                ->icon('heroicon-o-squares-2x2')
                ->color('gray')
                ->button(),

            Actions\ActionGroup::make([
                Actions\Action::make('importExcel')
                    ->label('Import Excel Karyawan')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->color('success')
                    ->modalHeading('Import / Update Massal Data Karyawan dari Excel')
                    ->modalDescription('Gunakan file hasil Export Excel Karyawan atau Template resmi untuk menambah karyawan baru atau memperbarui massal gaji pokok, tunjangan, PIN fingerprint, shift, rekening bank, dan BPJS.')
                    ->form([
                        FileUpload::make('file_excel')
                            ->label('File Excel Karyawan (.xlsx / .xls)')
                            ->disk('local')
                            ->directory('temp-karyawan')
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                                'application/octet-stream',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $relPath = $data['file_excel'];
                        $absPath = Storage::disk('local')->path($relPath);

                        try {
                            $res = app(KaryawanExcelService::class)->importSpreadsheet($absPath);
                            Notification::make()
                                ->title('Import Data Karyawan Selesai!')
                                ->body("Total diproses: {$res['total']} karyawan ({$res['created']} baru, {$res['updated']} diperbarui, {$res['skipped']} dilewati).")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Gagal Import Excel Karyawan')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        } finally {
                            Storage::disk('local')->delete($relPath);
                        }
                    }),

                Actions\Action::make('exportExcel')
                    ->label('Export Excel Karyawan')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('info')
                    ->url(fn () => route('hrd.karyawan.export'))
                    ->openUrlInNewTab(),

                Actions\Action::make('templateExcel')
                    ->label('Download Template Excel')
                    ->icon('heroicon-o-document-arrow-down')
                    ->color('gray')
                    ->url(fn () => route('hrd.karyawan.export', ['template' => 1]))
                    ->openUrlInNewTab(),
            ])
                ->label('Excel')
                ->icon('heroicon-o-table-cells')
                ->color('success')
                ->button(),

            Actions\CreateAction::make()->label('+ Karyawan'),
        ];
    }
}
