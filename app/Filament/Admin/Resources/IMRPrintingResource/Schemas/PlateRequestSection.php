<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Schemas;

use App\Models\MasterSpk;
use App\Models\PracetakPlateArchive;
use App\Models\PracetakProcess;
use Filament\Forms;
use Filament\Forms\Get;
use Illuminate\Support\HtmlString;

class PlateRequestSection
{
    /**
     * Deteksi otomatis data Plate Cetak (Proses Pra Cetak / Arsip Rak) untuk suatu SPK.
     */
    public static function resolvePlateStateForSpk(?int $spkId): array
    {
        if (! $spkId) {
            return [
                'pracetak_process_id' => null,
                'plate_archive_id'    => null,
                'request_plate'       => false,
                'plate_qty_requested' => 0,
                'status'              => 'none',
                'process'             => null,
                'archive'             => null,
            ];
        }

        $process = PracetakProcess::with(['plateArchive', 'generatedArchive'])
            ->where('spk_id', $spkId)
            ->latest('id')
            ->first();

        $archive = $process?->plateArchive ?: $process?->generatedArchive;

        if (! $archive) {
            $spk = MasterSpk::with('salesOrder')->find($spkId);
            $fgId = ($spk?->salesOrder?->itemable_type === \App\Models\FinishGoodItem::class)
                ? $spk->salesOrder->itemable_id
                : null;
            if ($fgId) {
                $archive = PracetakPlateArchive::where('finish_good_item_id', $fgId)
                    ->whereIn('status_kondisi', ['tersimpan', 'dipakai_printing'])
                    ->latest('id')
                    ->first();
            }
        }

        $totalQty = (int) ($archive?->effective_qty_total ?: ($process?->hasil_baik ?: ($process?->jumlah_warna_plate ?: 0)));
        $qtyDiRak = $archive ? $archive->qty_di_rak : $totalQty;

        $isReady = false;
        $status = 'none';
        if ($process) {
            if ($process->status === 'selesai' && $qtyDiRak > 0) {
                $status = 'ready';
                $isReady = true;
            } elseif ($process->status === 'handed_over') {
                if ($archive && $qtyDiRak > 0) {
                    $status = 'ready';
                    $isReady = true;
                } else {
                    $status = 'handed_over';
                }
            } else {
                $status = 'in_progress';
            }
        } elseif ($archive) {
            if ($qtyDiRak > 0 && in_array($archive->status_kondisi, ['tersimpan', 'dipakai_printing'], true)) {
                $status = 'ready';
                $isReady = true;
            } else {
                $status = 'handed_over';
            }
        }

        return [
            'pracetak_process_id' => $process?->id,
            'plate_archive_id'    => $archive?->id,
            'request_plate'       => $isReady,
            'plate_qty_requested' => $isReady ? max(1, $qtyDiRak) : 0,
            'qty_di_rak'          => $qtyDiRak,
            'qty_total'           => $totalQty,
            'status'              => $status,
            'process'             => $process,
            'archive'             => $archive,
        ];
    }

    public static function make(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Permintaan Plate Cetak Jadi (dari Rak Pra Cetak)')
            ->description('Pengeluaran plate cetak ke divisi Printing dilakukan 1 pintu melalui IMR ini dan diproses Gudang di Pengeluaran Material.')
            ->icon('heroicon-o-cpu-chip')
            ->visible(fn (Get $get) => filled($get('spk_id')))
            ->schema([
                Forms\Components\Hidden::make('pracetak_process_id'),
                Forms\Components\Hidden::make('plate_archive_id'),

                Forms\Components\Placeholder::make('plate_status_card')
                    ->hiddenLabel()
                    ->content(function (Get $get) {
                        $info = self::resolvePlateStateForSpk($get('spk_id') ? (int) $get('spk_id') : null);
                        $process = $info['process'];
                        $archive = $info['archive'];

                        if ($info['status'] === 'ready') {
                            $nama = e($process?->nama_plat ?: ($archive?->nama_plat ?: '-'));
                            $kode = e($archive?->kode_arsip ?: 'Belum berkode arsip');
                            $rak = e($process?->lokasi_simpan_plate ?: ($archive?->lokasi_rak ?: 'Belum diisi'));
                            $diRak = (int) ($info['qty_di_rak'] ?? 0);
                            $total = (int) ($info['qty_total'] ?? $diRak);
                            $dipakai = max(0, $total - $diRak);
                            $infoDipakai = $dipakai > 0 ? " ({$dipakai} sedang di Printing)" : '';

                            return new HtmlString('
                                <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.35); border-left: 4px solid #10b981; border-radius: 8px; padding: 12px 14px; font-size: 12px;">
                                    <div style="font-weight: 700; color: #10b981; margin-bottom: 4px;">Plate Siap di Rak Pra Cetak — Dapat Diambil via IMR Ini</div>
                                    <div><strong>Nama Plate:</strong> ' . $nama . ' &nbsp;|&nbsp; <strong>Kode Arsip:</strong> ' . $kode . ' &nbsp;|&nbsp; <strong>Tersisa di Rak:</strong> ' . $diRak . ' / ' . $total . ' Lembar' . e($infoDipakai) . ' &nbsp;|&nbsp; <strong>Lokasi Rak:</strong> ' . $rak . '</div>
                                </div>
                            ');
                        }

                        if ($info['status'] === 'handed_over') {
                            $to = e($process?->handed_over_to ?: 'Divisi Printing');
                            $at = $process?->handed_over_at ? $process->handed_over_at->format('d M Y H:i') : '-';

                            return new HtmlString('
                                <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.35); border-left: 4px solid #3b82f6; border-radius: 8px; padding: 12px 14px; font-size: 12px;">
                                    <div style="font-weight: 700; color: #60a5fa; margin-bottom: 4px;">Plate Sudah Diserahkan ke Divisi Printing</div>
                                    <div>Diterima oleh <strong>' . $to . '</strong> pada ' . e($at) . '. Anda tetap dapat membuat IMR ini untuk meminta material bahan baku lainnya (tinta, kertas, dll.).</div>
                                </div>
                            ');
                        }

                        if ($info['status'] === 'in_progress') {
                            $stage = match ($process?->status) {
                                'design', 'trial_design' => 'A. Trial Design',
                                'prove_print'            => 'B. Prove Print',
                                'plat_making'            => 'C. Plate Making',
                                default                  => (string) ($process?->status ?? '-'),
                            };

                            return new HtmlString('
                                <div style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.35); border-left: 4px solid #f59e0b; border-radius: 8px; padding: 12px 14px; font-size: 12px;">
                                    <div style="font-weight: 700; color: #f59e0b; margin-bottom: 4px;">Plate Belum Selesai di Pra Cetak (Tahap: ' . e($stage) . ')</div>
                                    <div>Selesaikan Pra Cetak hingga <strong>Simpan ke Arsip Rak (Tahap D)</strong> terlebih dahulu agar plate dapat diajukan keluar lewat IMR.</div>
                                </div>
                            ');
                        }

                        return new HtmlString('
                            <div style="background: rgba(156, 163, 175, 0.1); border: 1px solid rgba(156, 163, 175, 0.3); border-left: 4px solid #9ca3af; border-radius: 8px; padding: 12px 14px; font-size: 12px;">
                                <div style="font-weight: 700; margin-bottom: 2px;">Belum Ada Data Plate Pra Cetak untuk SPK Ini</div>
                                <div style="opacity: 0.85;">Buat & selesaikan tugas Pra Cetak hingga Tahap D (Arsip Rak) jika SPK ini memerlukan pengambilan plate cetak.</div>
                            </div>
                        ');
                    })
                    ->columnSpanFull(),

                Forms\Components\Grid::make(3)
                    ->visible(function (Get $get): bool {
                        $info = self::resolvePlateStateForSpk($get('spk_id') ? (int) $get('spk_id') : null);

                        return $info['status'] === 'ready' || (bool) $get('request_plate');
                    })
                    ->schema([
                        Forms\Components\Toggle::make('request_plate')
                            ->label('Sertakan Pengambilan Plate di IMR')
                            ->inline(false)
                            ->live()
                            ->helperText('Otomatis aktif bila plate sudah siap di rak Pra Cetak.'),

                        Forms\Components\TextInput::make('plate_qty_requested')
                            ->label('Jumlah Plate Diminta (Lembar)')
                            ->numeric()
                            ->minValue(1)
                            ->visible(fn (Get $get) => (bool) $get('request_plate'))
                            ->required(fn (Get $get) => (bool) $get('request_plate')),

                        Forms\Components\TextInput::make('plate_notes')
                            ->label('Catatan Pengambilan Plate (Opsional)')
                            ->placeholder('Misal: Ambil 13 plate ULTRAMEN + pisau pond')
                            ->visible(fn (Get $get) => (bool) $get('request_plate')),
                    ]),
            ])
            ->columnSpanFull();
    }
}
