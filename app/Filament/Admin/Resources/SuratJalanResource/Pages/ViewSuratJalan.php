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

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('preview_pdf')
                ->label('Preview PDF')
                ->icon('heroicon-o-document')
                ->color('warning')
                ->url(fn () => route('suratjalan.preview', $this->record))
                ->openUrlInNewTab(),

            \Filament\Actions\Action::make('download_pdf')
                ->label('Download PDF')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('suratjalan.download', $this->record)),

            \Filament\Actions\EditAction::make(),
        ];
    }

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

                        TextEntry::make('invoice_badge')
                            ->label('')
                            ->state(fn ($record) => $record->invoice ? 'INVOICE: ' . $record->invoice->kode_invoice : 'BELUM DITAGIHKAN')
                            ->badge()
                            ->color(fn ($record) => $record->invoice ? 'primary' : 'gray'),

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

                        TextEntry::make('so_number_header')
                            ->label('')
                            ->state(fn ($record) => 'Sales Order: ' . ($record->salesOrder?->no_sales_order ?? '-'))
                            ->alignEnd()
                            ->color('gray'),

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
                | INFORMASI ORDER & INVOICE
                |--------------------------------------------------------------------------
                */

                Section::make('INFORMASI ORDER & INVOICE')
                    ->schema([
                        Grid::make(4)->schema([
                            TextEntry::make('salesOrder.no_sales_order')
                                ->label('No. Sales Order')
                                ->weight('bold')
                                ->copyable(),

                            TextEntry::make('salesOrder.no_po_customer')
                                ->label('PO Customer')
                                ->weight('bold')
                                ->default('-'),

                            TextEntry::make('spk.no_spk')
                                ->label('No. SPK')
                                ->default('-'),

                            TextEntry::make('invoice_info')
                                ->label('Invoice Terkait')
                                ->state(function ($record) {
                                    if (!$record->invoice) return 'Belum Dibuat Invoice';
                                    return $record->invoice->kode_invoice . ' • ' . strtoupper($record->invoice->status ?? 'UNPAID');
                                })
                                ->badge()
                                ->color(fn ($record) => $record->invoice ? 'success' : 'gray'),
                        ]),
                    ])
                    ->icon('heroicon-o-document-text')
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
                                        return $record->salesOrder?->itemable?->nama_barang
                                            ?? $record->salesOrder?->product_name
                                            ?? '-';
                                    })
                                    ->weight('bold')
                                    ->size(TextEntry\TextEntrySize::Large),

                                TextEntry::make('qty_pengiriman')
                                    ->label('Jumlah Dikirim')
                                    ->suffix(' PCS')
                                    ->weight('bold')
                                    ->color('success')
                                    ->size(TextEntry\TextEntrySize::Large),

                                TextEntry::make('qty_order_so')
                                    ->label('Total Order SO')
                                    ->state(fn ($record) => number_format((float) ($record->salesOrder?->qty ?? 0), 0, ',', '.') . ' PCS')
                                    ->color('gray'),

                            ]),

                        Grid::make(4)
                            ->schema([
                                TextEntry::make('jenis')
                                    ->label('Jenis')
                                    ->state(fn ($record) => $record->salesOrder?->effective_jenis ?? '-'),

                                TextEntry::make('komponen')
                                    ->label('Komponen')
                                    ->state(fn ($record) => $record->salesOrder?->effective_komponen ?? '-'),

                                TextEntry::make('kertas')
                                    ->label('Kertas')
                                    ->state(fn ($record) => $record->salesOrder?->effective_kertas ?? '-'),

                                TextEntry::make('finishing')
                                    ->label('Finishing')
                                    ->state(fn ($record) => $record->salesOrder?->effective_finishing ?? '-'),
                            ]),

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