<?php

namespace App\Filament\Admin\Resources\IssueMaterialResource\Tables\Actions;

use App\Models\ImrPrinting;
use App\Services\IMR\IMRPrintingService;
use Filament\Forms;
use Filament\Tables;
use Filament\Notifications\Notification;

class IssueAction
{
    public static function make(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('issue')

            ->label('Proses Material')

            ->icon('heroicon-o-truck')

            ->color('primary')

            ->modalWidth('7xl')

            ->modalHeading('Proses Pengeluaran Material')

            ->modalDescription(
                'Masukkan jumlah material yang akan dikeluarkan dari gudang.'
            )

            ->form(fn(ImrPrinting $record) => self::form($record))

          ->action(function (
    ImrPrinting $record,
    array $data
) {

    try {

        app(IMRPrintingService::class)
            ->issue($record, $data);

        Notification::make()

            ->title('Material berhasil dikeluarkan')

            ->body(
                "Material berhasil diproses dari gudang."
            )

            ->success()

            ->send();

    } catch (\Throwable $e) {

        Notification::make()

            ->title('Proses gagal')

            ->body($e->getMessage())

            ->danger()

            ->persistent()

            ->send();

    }

});
    }

    protected static function form(ImrPrinting $record): array
    {
        $record->loadMissing([
            'items.item.stock',
            'pracetakProcess',
            'plateArchive',
        ]);

        $schema = [];

        // Seksi 1 pintu serah terima Plate Cetak Jadi dari Rak Pra Cetak
        if ($record->request_plate && $record->plate_approved) {
            $proc = $record->pracetakProcess;
            $arch = $record->plateArchive;
            $namaPlat = $proc?->nama_plat ?: ($arch?->nama_plat ?: '-');
            $kodeArsip = $arch?->kode_arsip ?: '-';
            $lokasiRak = $proc?->lokasi_simpan_plate ?: ($arch?->lokasi_rak ?: '-');
            $qtyPlate = (int) ($record->plate_qty_requested ?: 0);

            if ($record->isPlateIssued()) {
                $schema[] = Forms\Components\Section::make("Plate Cetak Jadi: {$namaPlat} (Sudah Diserahkan)")
                    ->icon('heroicon-o-check-circle')
                    ->schema([
                        Forms\Components\Placeholder::make('plate_already_issued')
                            ->hiddenLabel()
                            ->content("Plate ({$qtyPlate} Lembar · {$kodeArsip} · {$lokasiRak}) telah diserahkan kepada {$record->plate_received_by} pada " . $record->plate_issued_at?->format('d M Y H:i') . '.'),
                    ]);
            } else {
                $schema[] = Forms\Components\Section::make("Serah Terima Plate Cetak Jadi: {$namaPlat}")
                    ->icon('heroicon-o-cpu-chip')
                    ->description('Pengeluaran plate jadi dari Rak Pra Cetak ke divisi Printing (tidak memotong stok bahan baku gudang umum).')
                    ->collapsible(false)
                    ->schema([
                        Forms\Components\Grid::make(4)->schema([
                            Forms\Components\Placeholder::make('plate_kode_arsip')
                                ->label('Kode Arsip')
                                ->content($kodeArsip),

                            Forms\Components\Placeholder::make('plate_qty_info')
                                ->label('Jumlah Plate')
                                ->content("{$qtyPlate} Lembar"),

                            Forms\Components\Placeholder::make('plate_rack_info')
                                ->label('Lokasi Rak')
                                ->content($lokasiRak),

                            Forms\Components\Placeholder::make('plate_cond_info')
                                ->label('Status Rak')
                                ->content('Tersimpan / Siap Keluar'),
                        ]),

                        Forms\Components\Toggle::make('issue_plate')
                            ->label('Serahkan Plate Cetak ke Divisi Printing Sekarang')
                            ->default(true)
                            ->live(),

                        Forms\Components\Grid::make(2)
                            ->visible(fn (Forms\Get $get) => (bool) $get('issue_plate'))
                            ->schema([
                                Forms\Components\TextInput::make('plate_received_by')
                                    ->label('Nama Penerima (Operator / Kepala Shift Printing)')
                                    ->placeholder('Contoh: Budi — Shift 1 Flexo Zonten')
                                    ->required(fn (Forms\Get $get) => (bool) $get('issue_plate')),

                                Forms\Components\TextInput::make('plate_handover_notes')
                                    ->label('Catatan Serah Terima Plate (Opsional)')
                                    ->default($record->plate_notes)
                                    ->placeholder('Contoh: Kondisi plate lengkap & baik + pisau pond'),
                            ]),
                    ]);
            }
        }

        foreach ($record->items as $index => $item) {
            $remaining = max(0, (float) $item->qty_approved - (float) $item->qty_issued);
            $onHand = (float) ($item->item->stock?->on_hand ?? 0);
            $maxIssuable = min($remaining, $onHand);

            $schema[] = Forms\Components\Section::make($item->item->nama_master_item)
                ->icon('heroicon-o-cube')
                ->description('Informasi material dan jumlah yang akan dikeluarkan dari gudang.')
                ->collapsible(false)
                ->schema([
                    Forms\Components\Grid::make(4)->schema([
                        Forms\Components\Placeholder::make("approved{$index}")
                            ->label('Qty Disetujui')
                            ->content(number_format((float) $item->qty_approved, 2)),

                        Forms\Components\Placeholder::make("issued{$index}")
                            ->label('Sudah Dikeluarkan')
                            ->content(number_format((float) $item->qty_issued, 2)),

                        Forms\Components\Placeholder::make("remaining{$index}")
                            ->label('Sisa yang Harus Dikeluarkan')
                            ->content(number_format($remaining, 2)),

                        Forms\Components\Placeholder::make("stock{$index}")
                            ->label('Stok Fisik Gudang (On Hand)')
                            ->content(number_format($onHand, 2)),
                    ]),

                    Forms\Components\TextInput::make("items.$index.qty_issue")
                        ->label('Jumlah Material Keluar')
                        ->numeric()
                        ->default($maxIssuable)
                        ->step(0.01)
                        ->suffix($item->satuan)
                        ->minValue(0)
                        ->maxValue($maxIssuable)
                        ->helperText('Maksimal ' . number_format($maxIssuable, 2))
                        ->required(),
                ]);
        }

        return $schema;
    }
}