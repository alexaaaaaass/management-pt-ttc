<?php

namespace App\Filament\Admin\Resources\ImrFinishResource\Schemas;

use App\Models\MasterDepartemen;
use Filament\Forms;

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
                    ->relationship('spk', 'no_spk')
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