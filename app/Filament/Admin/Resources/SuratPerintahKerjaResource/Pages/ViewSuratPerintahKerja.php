<?php

namespace App\Filament\Admin\Resources\SuratPerintahKerjaResource\Pages;

use App\Filament\Admin\Resources\SuratPerintahKerjaResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\RepeatableEntry;

class ViewSuratPerintahKerja extends ViewRecord
{
    protected static string $resource = SuratPerintahKerjaResource::class;

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
                        Grid::make(5)
                            ->schema([
                                TextEntry::make('order_qty')
                                    ->label('Target Qty Order')
                                    ->numeric()
                                    ->badge()
                                    ->color('primary'),

                                TextEntry::make('total_good_packed')
                                    ->label('Barang Jadi Selesai')
                                    ->numeric()
                                    ->badge()
                                    ->color('info'),

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

                                TextEntry::make('completion_rate')
                                    ->label('Progres Pemenuhan')
                                    ->formatStateUsing(fn ($state) => $state . '%')
                                    ->badge()
                                    ->color(fn ($state) => $state >= 100 ? 'success' : ($state > 0 ? 'warning' : 'danger')),
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
                                    ->label('Qty Order'),
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

                                        TextEntry::make('proses_printing')
                                            ->badge(),

                                        TextEntry::make('tahap_printing')
                                            ->badge(),

                                        TextEntry::make('hasil_baik')
                                            ->badge()
                                            ->color('success'),

                                        TextEntry::make('hasil_rusak')
                                            ->badge()
                                            ->color('danger'),

                                        TextEntry::make('semi_waste')
                                            ->badge()
                                            ->color('warning'),
                                    ])
                            ])
                            ->placeholder('Belum ada data pengerjaan Printing.')
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

                                        TextEntry::make('proses_finishing')
                                            ->badge(),

                                        TextEntry::make('tahap_finishing')
                                            ->badge(),

                                        TextEntry::make('hasil_baik')
                                            ->badge()
                                            ->color('success'),

                                        TextEntry::make('hasil_rusak')
                                            ->badge()
                                            ->color('danger'),

                                        TextEntry::make('semi_waste')
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
                                            ->badge()
                                            ->color(fn ($state) => $state === 'barang_hasil_baik' ? 'success' : ($state === 'blokir' ? 'danger' : 'warning')),

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