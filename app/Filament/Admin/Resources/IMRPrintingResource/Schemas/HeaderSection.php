<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Schemas;

use Filament\Forms;

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

                    ->placeholder(
                        'Nomor IMR akan dibuat otomatis'
                    ),

                Forms\Components\DatePicker::make(
                    'tanggal_request'
                )

                    ->label('Tanggal Request')

                    ->required()

                    ->native(false)

                    ->default(now()),

                Forms\Components\Select::make('spk_id')

                    ->label('Surat Perintah Kerja')

                    ->placeholder('Pilih SPK')

                    ->searchable()

                    ->preload()

                    ->required()

                    ->relationship(
                        'spk',
                        'no_spk'
                    ),

            ])

            ->columns(3);
    }
}