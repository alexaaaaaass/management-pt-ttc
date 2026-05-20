<?php

namespace App\Filament\Admin\Resources\SuratJalanResource\Pages;

use App\Filament\Admin\Resources\SuratJalanResource;
use Filament\Resources\Pages\ViewRecord;

use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;

class ViewSuratJalan extends ViewRecord
{
    protected static string $resource = SuratJalanResource::class;

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([

                /*
                |--------------------------------------------------------------------------
                | HEADER
                |--------------------------------------------------------------------------
                */
Section::make()
    ->schema([

        Grid::make(2)
            ->schema([

                /*
                |--------------------------------------------------------------------------
                | LEFT
                |--------------------------------------------------------------------------
                */

                Grid::make(1)
                    ->schema([

                        TextEntry::make('header_title')
                            ->label('')
                            ->state('SURAT JALAN')
                            ->weight('bold')
                            ->size(TextEntry\TextEntrySize::Large)
                            ->extraAttributes([
                                'class' => '
                                    text-3xl
                                    tracking-wide
                                    text-gray-900 dark:text-white
                                '
                            ]),

                        TextEntry::make('kode_surat_jalan')
                            ->label('')
                            ->badge()
                            ->color('primary')
                            ->extraAttributes([
                                'class' => 'mt-2'
                            ]),

                        TextEntry::make('status_pengiriman')
                            ->label('')
                            ->state('DOCUMENT PENGIRIMAN')
                            ->badge()
                            ->color('success'),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | RIGHT
                |--------------------------------------------------------------------------
                */

                Grid::make(1)
                    ->schema([

                        TextEntry::make('tanggal_label')
                            ->label('')
                            ->state('TANGGAL KIRIM')
                            ->weight('bold')
                            ->alignEnd()
                            ->extraAttributes([
                                'class' => '
                                    text-primary-600
                                    dark:text-primary-400
                                    tracking-wide
                                '
                            ]),

                        TextEntry::make('tanggal_surat_jalan')
                            ->label('')
                            ->date('d F Y')
                            ->weight('bold')
                            ->alignEnd()
                            ->size(TextEntry\TextEntrySize::Large)
                            ->extraAttributes([
                                'class' => '
                                    text-gray-900
                                    dark:text-white
                                '
                            ]),

                    ]),

            ]),

    ])

    ->extraAttributes([
        'class' => '
            relative
            overflow-hidden

            rounded-3xl
            border
            border-gray-200 dark:border-gray-700

            bg-gradient-to-br
            from-white
            via-gray-50
            to-primary-50

            dark:from-gray-900
            dark:via-gray-800
            dark:to-gray-900

            shadow-xl
            p-8
        ',
        'style' => '
            border-top: 6px solid rgb(37 99 235);
        '
    ]),
                /*
                |--------------------------------------------------------------------------
                | INFORMASI PENGIRIMAN
                |--------------------------------------------------------------------------
                */

                Grid::make(2)
                    ->schema([

                        /*
                        |--------------------------------------------------------------------------
                        | TUJUAN
                        |--------------------------------------------------------------------------
                        */

                        Section::make('TUJUAN PENGIRIMAN')
                            ->schema([

                                TextEntry::make('salesOrder.customer.nama_customer')
                                    ->label(' ')
                                    ->weight('bold')
                                    ->size(TextEntry\TextEntrySize::Large),

                                TextEntry::make('alamat_tujuan')
                                    ->label(' ')
                                    ->color('gray'),

                            ])
                            ->icon('heroicon-o-map-pin')
                          ->extraAttributes([
    'class' => '
        rounded-2xl
        bg-white dark:bg-gray-900
        shadow-sm
        border border-gray-200 dark:border-gray-700
        p-4
    '
]),

                        /*
                        |--------------------------------------------------------------------------
                        | KENDARAAN
                        |--------------------------------------------------------------------------
                        */

                        Section::make('INFORMASI KENDARAAN')
                            ->schema([

                                Grid::make(2)
                                    ->schema([

                                        TextEntry::make('transportasi'),

                                        TextEntry::make('no_polisi')
                                            ->label('No. Polisi'),

                                        TextEntry::make('driver'),

                                        TextEntry::make('pengirim')
                                            ->label('Petugas Pengirim'),

                                    ])

                            ])
                            ->icon('heroicon-o-truck')
                          ->extraAttributes([
    'class' => '
        rounded-2xl
        bg-white dark:bg-gray-900
        shadow-sm
        border border-gray-200 dark:border-gray-700
        p-4
    '
]),

                    ]),

                /*
                |--------------------------------------------------------------------------
                | RINCIAN BARANG
                |--------------------------------------------------------------------------
                */

                Section::make('RINCIAN BARANG')
                    ->schema([

                        Grid::make(3)
                            ->schema([

                                TextEntry::make('barang')
                                    ->label('Deskripsi Barang')
                                    ->state(function ($record) {

                                        return
                                            $record->salesOrder?->itemable?->nama_barang
                                            ?? '-';
                                    })
                                    ->weight('bold'),

                                TextEntry::make('spk.no_spk')
                                    ->label('No. KIK / SPK'),

                                TextEntry::make('qty_pengiriman')
                                    ->label('Jumlah Dikirim')
                                    ->suffix(' PCS')
                                    ->weight('bold')
                                    ->size(TextEntry\TextEntrySize::Large),

                            ]),

                        Grid::make(1)
                            ->schema([

                                TextEntry::make('po_customer')
                                    ->label(' ')
                                    ->state(function ($record) {

                                        return
                                            'PO Customer: ' .
                                            ($record->salesOrder?->no_po_customer ?? '-');
                                    })
                                    ->color('gray')

                            ])

                    ])
                    ->icon('heroicon-o-cube')
                    ->extraAttributes([
                        'class' => '
                            rounded-2xl
                            bg-white dark:bg-gray-900
                            shadow-sm
                            border border-gray-200 dark:border-gray-700
                            p-4
                        '
                    ]),

                /*
                |--------------------------------------------------------------------------
                | CATATAN
                |--------------------------------------------------------------------------
                */

                Section::make()
                    ->schema([

                        TextEntry::make('catatan')
                            ->label('Catatan Tambahan:')
                            ->state(fn ($record) =>
                                $record->keterangan ?: '-'
                            )
                            ->color('warning')

                    ])
                    ->extraAttributes([
                        'class' => '
                            rounded-2xl
                            bg-white dark:bg-gray-900
                            shadow-sm
                            border border-gray-200 dark:border-gray-700
                            p-4
                        '
                    ])

            ]);
    }
}