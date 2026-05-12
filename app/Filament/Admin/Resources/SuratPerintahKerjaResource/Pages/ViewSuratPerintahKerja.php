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
                | DETAIL SPK
                |--------------------------------------------------------------------------
                */

                Section::make('Detail Surat Perintah Kerja')
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

                Section::make('Bill Of Material')
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
                                            ->label('Kode'),

                                    ])
                            ])
                    ]),

                Section::make('Informasi Produksi Printing')
                    ->schema([

                        RepeatableEntry::make('printings')
                            ->schema([

                                Grid::make(6)
                                    ->schema([

                                        TextEntry::make('tanggal_entri')
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
                    ]),

                /*
                |--------------------------------------------------------------------------
                | INFORMASI PRODUKSI FINISHING
                |--------------------------------------------------------------------------
                */

                Section::make('Informasi Produksi Finishing')
                    ->schema([

                        RepeatableEntry::make('finishings')
                            ->schema([

                                Grid::make(6)
                                    ->schema([

                                        TextEntry::make('kode_finishing'),

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
                    ]),

                /*
                |--------------------------------------------------------------------------
                | TRANSFER BARANG JADI
                |--------------------------------------------------------------------------
                */

                Section::make('Transfer Barang Jadi')
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
                                            ->badge(),

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
                    ]),
            ]);
    }
}