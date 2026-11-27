<?php

namespace App\Filament\Admin\Resources\PayrollBoronganResource\Pages;

use App\Filament\Admin\Resources\PayrollBoronganResource;
use App\Services\HR\PayrollExcelImportService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListPayrollBorongans extends ListRecords
{
    protected static string $resource = PayrollBoronganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('importExcelBorongan')
                ->label('Import Excel')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('warning')
                ->modalHeading('Import Data Payroll Borongan dari Excel')
                ->modalDescription('Unggah file Excel daftar upah borongan pekerja / freelance produksi.')
                ->form([
                    Forms\Components\FileUpload::make('attachment')
                        ->label('File Excel Borongan (.xlsx, .xls)')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'application/octet-stream',
                        ])
                        ->disk('local')
                        ->directory('payroll-imports')
                        ->required(),

                    Forms\Components\Grid::make(3)
                        ->schema([
                            Forms\Components\TextInput::make('periode')
                                ->label('Periode (YYYY-MM)')
                                ->default(now()->format('Y-m'))
                                ->required(),
                            Forms\Components\DatePicker::make('tanggal_transaksi')
                                ->label('Tanggal Transaksi')
                                ->default(now())
                                ->required(),
                            Forms\Components\Select::make('status')
                                ->label('Status Borongan')
                                ->options([
                                    'Draft'    => 'Draft',
                                    'Approved' => 'Approved',
                                ])
                                ->default('Draft')
                                ->required(),
                        ]),
                ])
                ->action(function (array $data) {
                    try {
                        $fullPath = Storage::disk('local')->path($data['attachment']);
                        $service = app(PayrollExcelImportService::class);
                        $res = $service->importBoronganSpreadsheet($fullPath, $data);

                        Notification::make()
                            ->title('Impor Borongan Berhasil!')
                            ->body("Berhasil membuat batch borongan {$res['kode_bukti']} dengan {$res['total_pekerja']} pekerja. Total: Rp " . number_format($res['grand_total'], 0, ',', '.'))
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('Gagal Impor Borongan')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\Action::make('downloadTemplateBorongan')
                ->label('Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(route('hrd.borongan.template'))
                ->openUrlInNewTab(),

            Actions\CreateAction::make()->label('+ Borongan'),
        ];
    }
}
