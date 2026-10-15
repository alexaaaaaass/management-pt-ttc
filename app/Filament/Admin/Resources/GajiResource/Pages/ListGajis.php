<?php

namespace App\Filament\Admin\Resources\GajiResource\Pages;

use App\Filament\Admin\Resources\GajiResource;
use App\Models\Gaji;
use App\Models\Karyawan;
use App\Services\HR\PayrollService;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListGajis extends ListRecords
{
    protected static string $resource = GajiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('generate_massal')
                ->label('Generate Payroll Bulanan')
                ->icon('heroicon-o-bolt')
                ->color('success')
                ->form([
                    Forms\Components\TextInput::make('periode')
                        ->label('Periode Payroll (YYYY-MM)')
                        ->default(now()->format('Y-m'))
                        ->placeholder('2026-10')
                        ->required(),

                    Forms\Components\DatePicker::make('tanggal_pembayaran')
                        ->label('Tanggal Pembayaran Default')
                        ->default(now())
                        ->required(),
                ])
                ->action(function (array $data) {
                    $periode = $data['periode'];
                    $karyawans = Karyawan::where('status_pegawai', 'Aktif')->get();
                    $count = 0;
                    $service = app(PayrollService::class);

                    foreach ($karyawans as $k) {
                        // Lewati jika sudah ada slip gaji untuk periode ini
                        if (Gaji::where('karyawan_id', $k->id)->where('periode', $periode)->exists()) {
                            continue;
                        }

                        $calc = $service->calculateEmployeePayroll($k->id, $periode);
                        $calc['tanggal_pembayaran'] = $data['tanggal_pembayaran'];
                        $calc['status'] = 'Draft';
                        $calc['metode_pembayaran'] = 'Transfer Bank';

                        $service->recordPayroll($calc);
                        $count++;
                    }

                    Notification::make()
                        ->title('Generate Selesai')
                        ->body("Berhasil men-generate {$count} slip gaji draft untuk periode {$periode}.")
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Buat Slip Manual'),
        ];
    }
}
