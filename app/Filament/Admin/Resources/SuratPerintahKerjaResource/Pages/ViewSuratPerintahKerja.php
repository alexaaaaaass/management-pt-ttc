<?php

namespace App\Filament\Admin\Resources\SuratPerintahKerjaResource\Pages;

use App\Filament\Admin\Resources\PackagingResource;
use App\Filament\Admin\Resources\SuratPerintahKerjaResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class ViewSuratPerintahKerja extends ViewRecord
{
    protected static string $resource = SuratPerintahKerjaResource::class;

    protected function getHeaderActions(): array
    {
        $unpacked = (int) $this->record->unpacked_finishing_qty;

        return [
            Actions\Action::make('transfer_sisa_buffer')
                ->label('Transfer Sisa ke Buffer Stock (+' . number_format($unpacked, 0, ',', '.') . ' pcs)')
                ->icon('heroicon-o-arrow-up-on-square-stack')
                ->color('warning')
                ->visible($unpacked > 0)
                ->url(fn () => PackagingResource::getUrl('create', [
                    'spk_id'         => $this->record->id,
                    'jenis_transfer' => 'buffer_stock',
                    'qty'            => $unpacked,
                ])),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                /*
                |--------------------------------------------------------------------------
                | RINGKASAN EKSEKUTIF ALUR MANUFAKTUR
                |--------------------------------------------------------------------------
                */
                Section::make('Ringkasan Progres Produksi & Pengiriman')
                    ->extraAttributes([
                        'style' => 'border-left: 5px solid #10b981; border-radius: 10px; padding: 12px;',
                    ])
                    ->schema([
                        Grid::make(6)
                            ->schema([
                                TextEntry::make('order_qty')
                                    ->label('Target Qty Order')
                                    ->numeric()
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('total_finishing_good')
                                    ->label('Hasil Baik Finishing')
                                    ->numeric()
                                    ->badge()
                                    ->color('warning'),

                                TextEntry::make('total_good_packed')
                                    ->label('Masuk Gudang (Packed)')
                                    ->numeric()
                                    ->badge()
                                    ->color('info'),

                                TextEntry::make('unpacked_finishing_qty')
                                    ->label('Sisa Belum Di-Packing')
                                    ->numeric()
                                    ->badge()
                                    ->color(fn ($state) => (int) $state > 0 ? 'warning' : 'success'),

                                TextEntry::make('total_shipped')
                                    ->label('Terkirim ke Customer')
                                    ->numeric()
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('on_hand_stock')
                                    ->label('Stok Siap Kirim')
                                    ->numeric()
                                    ->badge()
                                    ->color(fn ($state) => $state <= 0 ? 'danger' : 'success'),
                            ]),
                    ]),

                /*
                |--------------------------------------------------------------------------
                | DETAIL SPK
                |--------------------------------------------------------------------------
                */
                Section::make('Detail Surat Perintah Kerja')
                    ->extraAttributes([
                        'style' => 'border-left: 5px solid #3b82f6; border-radius: 10px; padding: 12px;',
                    ])
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('no_spk')
                                    ->label('No SPK'),

                                TextEntry::make('salesOrder.no_sales_order')
                                    ->label('No Sales Order'),

                                TextEntry::make('status')
                                    ->badge(),

                                TextEntry::make('salesOrder.customer.nama_customer')
                                    ->label('Customer'),

                                TextEntry::make('salesOrder.itemable.nama_barang')
                                    ->label('Nama Produk'),

                                TextEntry::make('salesOrder.qty')
                                    ->label('Qty Order')
                                    ->numeric(),

                                TextEntry::make('up_satu')
                                    ->label('UP 1 — Total UP / Repeat')
                                    ->placeholder('-'),

                                TextEntry::make('up_dua')
                                    ->label('UP 2 — Arah Repeat')
                                    ->placeholder('-'),

                                TextEntry::make('up_tiga')
                                    ->label('UP 3 — Arah Lebar (Lane)')
                                    ->placeholder('-'),

                                TextEntry::make('ukuran_potong')
                                    ->label('Ukuran Potong / Jadi (Die-Cut)')
                                    ->placeholder('-'),

                                TextEntry::make('ukuran_cetak')
                                    ->label('Ukuran Cetak (Web × Repeat)')
                                    ->placeholder('-'),

                                TextEntry::make('spesifikasi_kertas')
                                    ->label('Spesifikasi Kertas / Material')
                                    ->placeholder('-')
                                    ->columnSpanFull(),
                            ])
                    ]),

                /*
                |--------------------------------------------------------------------------
                | BILL OF MATERIAL
                |--------------------------------------------------------------------------
                */
                Section::make('Bill Of Material (Kebutuhan Bahan Baku)')
                    ->extraAttributes([
                        'style' => 'border-left: 5px solid #ef4444; border-radius: 10px; padding: 12px;',
                    ])
                    ->schema([
                        RepeatableEntry::make('salesOrder.itemable.materials')
                            ->schema([
                                Grid::make(5)
                                    ->schema([
                                        TextEntry::make('material.nama_master_item')
                                            ->label('Material'),

                                        TextEntry::make('qty')
                                            ->label('Qty BOM'),

                                        TextEntry::make('material.satuan.nama_satuan')
                                            ->label('Satuan'),

                                        TextEntry::make('departemen.nama_departemen')
                                            ->label('Departemen'),

                                        TextEntry::make('material.kode_material')
                                            ->label('Kode Material'),
                                    ])
                            ])
                    ]),

                /*
                |--------------------------------------------------------------------------
                | STASIUN 1: INFORMASI PRODUKSI PRA-CETAK
                |--------------------------------------------------------------------------
                */
                Section::make('1. Informasi Produksi Pra-Cetak (Pre-Press / Plat)')
                    ->extraAttributes([
                        'style' => 'border-left: 5px solid #8b5cf6; border-radius: 10px; padding: 12px;',
                    ])
                    ->schema([
                        RepeatableEntry::make('pracetaks')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        TextEntry::make('tgl_entry')
                                            ->label('Tanggal Entry')
                                            ->date(),

                                        TextEntry::make('nama_plat')
                                            ->label('Nama Plat')
                                            ->badge(),

                                        TextEntry::make('mesin.nama_mesin')
                                            ->label('Mesin Pra-Cetak')
                                            ->placeholder('-'),

                                        TextEntry::make('operator.nama_operator')
                                            ->label('Operator')
                                            ->placeholder('-'),

                                        TextEntry::make('hasil_baik')
                                            ->label('Hasil Baik')
                                            ->badge()
                                            ->color('success'),

                                        TextEntry::make('hasil_rusak')
                                            ->label('Hasil Rusak')
                                            ->badge()
                                            ->color('danger'),
                                    ])
                            ])
                            ->placeholder('Belum ada data pengerjaan Pra-Cetak.')
                    ]),

                /*
                |--------------------------------------------------------------------------
                | STASIUN 2: INFORMASI PRODUKSI PRINTING
                |--------------------------------------------------------------------------
                */
                Section::make('2. Informasi Produksi Printing (Mesin Cetak)')
                    ->extraAttributes([
                        'style' => 'border-left: 5px solid #06b6d4; border-radius: 10px; padding: 12px;',
                    ])
                    ->schema([
                        RepeatableEntry::make('printings')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        TextEntry::make('tanggal_entri')
                                            ->label('Tanggal Cetak')
                                            ->date(),

                                        TextEntry::make('proses_summary')
                                            ->label('Proses Printing')
                                            ->badge(),

                                        TextEntry::make('status_laporan')
                                            ->label('Status')
                                            ->badge(),

                                        TextEntry::make('hasil_baik')
                                            ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 0, ',', '.') . ' ' . $record->satuan_unit)
                                            ->badge()
                                            ->color('success'),

                                        TextEntry::make('hasil_rusak')
                                            ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 0, ',', '.') . ' ' . $record->satuan_unit)
                                            ->badge()
                                            ->color('danger'),

                                        TextEntry::make('semi_waste')
                                            ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 0, ',', '.') . ' ' . $record->satuan_unit)
                                            ->badge()
                                            ->color('warning'),
                                    ])
                            ])
                            ->placeholder('Belum ada data pengerjaan Printing.')
                    ]),

                /*
                |--------------------------------------------------------------------------
                | STASIUN 2B: INFORMASI PRODUKSI DIE MAKING
                |--------------------------------------------------------------------------
                */
                Section::make('2B. Informasi Produksi Die Making')
                    ->extraAttributes([
                        'style' => 'border-left: 5px solid #0ea5e9; border-radius: 10px; padding: 12px;',
                    ])
                    ->schema([
                        RepeatableEntry::make('diemakings')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        TextEntry::make('tanggal_entri')
                                            ->label('Tanggal')
                                            ->date(),

                                        TextEntry::make('proses_summary')
                                            ->label('Proses Die Making')
                                            ->badge(),

                                        TextEntry::make('status_laporan')
                                            ->label('Status')
                                            ->badge(),

                                        TextEntry::make('hasil_baik')
                                            ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 0, ',', '.') . ' ' . $record->satuan_unit)
                                            ->badge()
                                            ->color('success'),

                                        TextEntry::make('hasil_rusak')
                                            ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 0, ',', '.') . ' ' . $record->satuan_unit)
                                            ->badge()
                                            ->color('danger'),

                                        TextEntry::make('semi_waste')
                                            ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 0, ',', '.') . ' ' . $record->satuan_unit)
                                            ->badge()
                                            ->color('warning'),
                                    ])
                            ])
                            ->placeholder('Belum ada data pengerjaan Die Making.')
                    ]),

                /*
                |--------------------------------------------------------------------------
                | STASIUN 3: INFORMASI PRODUKSI FINISHING
                |--------------------------------------------------------------------------
                */
                Section::make('3. Informasi Produksi Finishing')
                    ->extraAttributes([
                        'style' => 'border-left: 5px solid #f59e0b; border-radius: 10px; padding: 12px;',
                    ])
                    ->schema([
                        RepeatableEntry::make('finishings')
                            ->schema([
                                Grid::make(6)
                                    ->schema([
                                        TextEntry::make('kode_finishing')
                                            ->label('Kode Finishing'),

                                        TextEntry::make('proses_summary')
                                            ->label('Proses Finishing')
                                            ->badge(),

                                        TextEntry::make('status_laporan')
                                            ->label('Status')
                                            ->badge(),

                                        TextEntry::make('hasil_baik')
                                            ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 0, ',', '.') . ' ' . $record->satuan_unit)
                                            ->badge()
                                            ->color('success'),

                                        TextEntry::make('hasil_rusak')
                                            ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 0, ',', '.') . ' ' . $record->satuan_unit)
                                            ->badge()
                                            ->color('danger'),

                                        TextEntry::make('semi_waste')
                                            ->formatStateUsing(fn ($state, $record) => number_format((float) $state, 0, ',', '.') . ' ' . $record->satuan_unit)
                                            ->badge()
                                            ->color('warning'),
                                    ])
                            ])
                            ->placeholder('Belum ada data pengerjaan Finishing.')
                    ]),

                /*
                |--------------------------------------------------------------------------
                | STASIUN 4: TRANSFER BARANG JADI (PACKAGING)
                |--------------------------------------------------------------------------
                */
                Section::make('4. Transfer Barang Jadi (Packaging / Pengepakan)')
                    ->extraAttributes([
                        'style' => 'border-left: 5px solid #10b981; border-radius: 10px; padding: 12px;',
                    ])
                    ->schema([
                        RepeatableEntry::make('packagings')
                            ->schema([
                                Grid::make(7)
                                    ->schema([
                                        TextEntry::make('kode_packaging')
                                            ->badge()
                                            ->color('primary'),

                                        TextEntry::make('satuan_transfer'),

                                        TextEntry::make('jenis_transfer')
                                            ->formatStateUsing(fn (?string $state): string => match ($state) {
                                                'barang_hasil_baik' => 'Barang Hasil Baik',
                                                'buffer_stock'      => 'Buffer Stock',
                                                'label_kuning'      => 'Label Kuning',
                                                'blokir'            => 'Blokir',
                                                default             => (string) $state,
                                            })
                                            ->badge()
                                            ->color(fn ($state) => match ($state) {
                                                'barang_hasil_baik' => 'success',
                                                'buffer_stock'      => 'info',
                                                'blokir'            => 'danger',
                                                default             => 'warning',
                                            }),

                                        TextEntry::make('total_satuan_penuh')
                                            ->badge()
                                            ->color('success'),

                                        TextEntry::make('total_satuan_sisa')
                                            ->badge()
                                            ->color('warning'),

                                        TextEntry::make('grand_total')
                                            ->badge()
                                            ->color('primary'),

                                        TextEntry::make('tgl_transfer')
                                            ->date(),
                                    ])
                            ])
                            ->placeholder('Belum ada data hasil Packaging.')
                    ]),

                /*
                |--------------------------------------------------------------------------
                | PENGIRIMAN (SURAT JALAN)
                |--------------------------------------------------------------------------
                */
                Section::make('5. Riwayat Pengiriman (Surat Jalan)')
                    ->extraAttributes([
                        'style' => 'border-left: 5px solid #6366f1; border-radius: 10px; padding: 12px;',
                    ])
                    ->schema([
                        RepeatableEntry::make('suratJalans')
                            ->schema([
                                Grid::make(7)
                                    ->schema([
                                        TextEntry::make('kode_surat_jalan')
                                            ->label('Kode SJ')
                                            ->badge()
                                            ->color('primary'),

                                        TextEntry::make('qty_pengiriman')
                                            ->label('Qty Kirim')
                                            ->badge()
                                            ->color('success'),

                                        TextEntry::make('transportasi')
                                            ->badge()
                                            ->color('info'),

                                        TextEntry::make('driver')
                                            ->badge()
                                            ->color('warning'),

                                        TextEntry::make('pengirim')
                                            ->badge()
                                            ->color('gray'),

                                        TextEntry::make('no_polisi')
                                            ->label('No Polisi'),

                                        TextEntry::make('tanggal_surat_jalan')
                                            ->label('Tanggal Kirim')
                                            ->date(),
                                    ]),

                                Grid::make(2)
                                    ->schema([
                                        TextEntry::make('alamat_tujuan')
                                            ->label('Alamat Tujuan'),

                                        TextEntry::make('keterangan')
                                            ->label('Keterangan')
                                            ->placeholder('-'),
                                    ])
                            ])
                            ->placeholder('Belum ada pengiriman via Surat Jalan.')
                    ]),
            ]);
    }
}