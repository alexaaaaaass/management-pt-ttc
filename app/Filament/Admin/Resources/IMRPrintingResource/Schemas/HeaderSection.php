<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Schemas;

use App\Models\MasterSpk;
use Filament\Forms;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;

class HeaderSection
{
    public static function make(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Informasi IMR')
            ->description('Data utama Internal Material Request.')
            ->schema([
                Forms\Components\TextInput::make('no_imr')
                    ->label('Nomor IMR')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Nomor IMR akan dibuat otomatis'),

                Forms\Components\DatePicker::make('tanggal_request')
                    ->label('Tanggal Request')
                    ->required()
                    ->native(false)
                    ->default(now()),

                Forms\Components\Select::make('spk_id')
                    ->label('Surat Perintah Kerja')
                    ->placeholder('Pilih SPK (Terbaru di Atas)')
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->relationship(
                        name: 'spk',
                        titleAttribute: 'no_spk',
                        modifyQueryUsing: fn (Builder $query) => $query
                            ->with(['salesOrder.customer', 'salesOrder.itemable'])
                            ->orderByDesc('created_at')
                            ->orderByDesc('id')
                    )
                    ->getOptionLabelFromRecordUsing(function (MasterSpk $spk): string {
                        $cust = $spk->salesOrder?->customer?->nama_customer ?? '-';
                        $prod = $spk->salesOrder?->product_name ?: '-';

                        return "{$spk->no_spk} — {$cust} · {$prod}";
                    })
                    ->afterStateUpdated(function ($state, Set $set) {
                        $plateState = PlateRequestSection::resolvePlateStateForSpk($state ? (int) $state : null);
                        $set('pracetak_process_id', $plateState['pracetak_process_id']);
                        $set('plate_archive_id', $plateState['plate_archive_id']);
                        $set('request_plate', $plateState['request_plate']);
                        $set('plate_qty_requested', $plateState['plate_qty_requested']);
                    }),
            ])
            ->columns(3);
    }
}