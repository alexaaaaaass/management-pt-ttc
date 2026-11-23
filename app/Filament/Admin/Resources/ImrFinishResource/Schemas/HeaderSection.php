<?php

namespace App\Filament\Admin\Resources\ImrFinishResource\Schemas;

use App\Models\MasterDepartemen;
use App\Models\MasterSpk;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;

class HeaderSection
{
    public static function make(): Forms\Components\Section
    {
        return Forms\Components\Section::make('Informasi IMR Finish')
            ->description('Data utama permintaan material untuk proses finishing.')
            ->schema([
                Forms\Components\TextInput::make('no_imr')
                    ->label('Nomor IMR')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Nomor IMR dibuat otomatis'),

                Forms\Components\DatePicker::make('tanggal_request')
                    ->label('Tanggal Request')
                    ->required()
                    ->native(false)
                    ->default(now()),

                Forms\Components\Select::make('spk_id')
                    ->label('Surat Perintah Kerja')
                    ->placeholder('Pilih SPK (Terbaru di Atas)')
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
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('departemen_id')
                    ->label('Departemen Peminta')
                    ->options(fn () => MasterDepartemen::query()
                        ->where('status', 'aktif')
                        ->orderBy('nama_departemen')
                        ->pluck('nama_departemen', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Pilih departemen yang bertanggung jawab atas permintaan ini.'),
            ])
            ->columns(2);
    }
}