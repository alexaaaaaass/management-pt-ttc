<?php

namespace App\Filament\Admin\Resources\HariLiburResource\Pages;

use App\Filament\Admin\Resources\AbsensiResource;
use App\Filament\Admin\Resources\HariLiburResource;
use App\Models\HariLibur;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListHariLiburs extends ListRecords
{
    protected static string $resource = HariLiburResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backAbsensi')
                ->label('← Absensi & Rekap')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => AbsensiResource::getUrl('index')),

            Actions\Action::make('seedLibur2026')
                ->label('Isi Hari Libur Nasional 2026')
                ->icon('heroicon-o-sparkles')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Isi Kalender Hari Libur Nasional Indonesia 2026')
                ->modalDescription('Sistem akan menambahkan daftar Hari Libur Nasional Indonesia tahun 2026 secara otomatis (tanggal yang sudah ada tidak akan diduplikasi).')
                ->action(function () {
                    $holidays = [
                        '2026-01-01' => 'Tahun Baru 2026 Masehi',
                        '2026-01-16' => 'Isra Mikraj Nabi Muhammad SAW',
                        '2026-02-17' => 'Tahun Baru Imlek 2577 Kongzili',
                        '2026-03-19' => 'Hari Suci Nyepi (Tahun Baru Saka 1948)',
                        '2026-03-21' => 'Hari Raya Idul Fitri 1447 H (Hari ke-1)',
                        '2026-03-22' => 'Hari Raya Idul Fitri 1447 H (Hari ke-2)',
                        '2026-04-03' => 'Wafat Yesus Kristus',
                        '2026-04-05' => 'Kebangkitan Yesus Kristus (Paskah)',
                        '2026-05-01' => 'Hari Buruh Internasional',
                        '2026-05-14' => 'Kenaikan Yesus Kristus',
                        '2026-05-27' => 'Hari Raya Idul Adha 1447 H',
                        '2026-05-31' => 'Hari Raya Waisak 2570 BE',
                        '2026-06-01' => 'Hari Lahir Pancasila',
                        '2026-06-16' => '1 Muharam Tahun Baru Islam 1448 H',
                        '2026-08-17' => 'Hari Kemerdekaan Republik Indonesia ke-81',
                        '2026-08-25' => 'Maulid Nabi Muhammad SAW',
                        '2026-12-25' => 'Kelahiran Yesus Kristus (Natal)',
                    ];

                    $added = 0;
                    foreach ($holidays as $tgl => $ket) {
                        $model = HariLibur::firstOrCreate(
                            ['tanggal_libur' => $tgl],
                            ['keterangan' => $ket]
                        );
                        if ($model->wasRecentlyCreated) {
                            $added++;
                        }
                    }

                    Notification::make()
                        ->title('Kalender Libur Nasional 2026 Siap')
                        ->body($added > 0
                            ? "Berhasil menambahkan {$added} hari libur nasional 2026 (total daftar: " . count($holidays) . ' hari).'
                            : 'Seluruh daftar hari libur nasional 2026 sudah tersedia.')
                        ->success()
                        ->send();
                }),

            Actions\CreateAction::make()
                ->label('Tambah Hari Libur')
                ->modalHeading('Tambah Hari Libur')
                ->modalSubmitActionLabel('Simpan'),
        ];
    }
}