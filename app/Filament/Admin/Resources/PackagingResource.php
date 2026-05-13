<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PackagingResource\Pages;
use App\Models\Packaging;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class PackagingResource extends Resource
{
    protected static ?string $model = Packaging::class;
     protected static ?string $navigationGroup = 'Finishing';
     protected static ?int $navigationSort = 2;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

public static function form(Form $form): Form
{
    return $form
        ->schema([

            Forms\Components\Section::make('Data Packaging')
                ->schema([

                    Forms\Components\Select::make('spk_id')
                        ->label('SPK')
                        ->relationship('spk', 'no_spk')
                        ->searchable()
                        ->preload()
                        ->required(),

                    Forms\Components\Hidden::make('kode_packaging'),

                    Forms\Components\Select::make('satuan_transfer')
                        ->options([
                            'Box' => 'Box',
                            'Pallete' => 'Pallete',
                            'Pack' => 'Pack',
                        ])
                        ->required(),

                    Forms\Components\Select::make('jenis_transfer')
                        ->options([
                            'barang_hasil_baik' => 'Barang Hasil Baik',
                            'label_kuning' => 'Label Kuning',
                            'blokir' => 'Blokir',
                        ])
                        ->required(),

                    Forms\Components\DatePicker::make('tgl_transfer')
                        ->required(),

                ])
                ->columns(2),

            /*
            |--------------------------------------------------------------------------
            | SATUAN PENUH
            |--------------------------------------------------------------------------
            */

            Forms\Components\Section::make('Satuan Penuh')
                ->schema([

                    Forms\Components\TextInput::make('jumlah_satuan_penuh')
                        ->numeric()
                        ->default(0)
                        ->live(),

                    Forms\Components\TextInput::make('qty_per_satuan_penuh')
                        ->numeric()
                        ->default(0)
                        ->live(),

                ])
                ->columns(2),

            /*
            |--------------------------------------------------------------------------
            | SATUAN SISA
            |--------------------------------------------------------------------------
            */

            Forms\Components\Section::make('Satuan Sisa')
                ->schema([

                    Forms\Components\TextInput::make('jumlah_satuan_sisa')
                        ->numeric()
                        ->default(0)
                        ->live(),

                    Forms\Components\TextInput::make('qty_per_satuan_sisa')
                        ->numeric()
                        ->default(0)
                        ->live(),

                ])
                ->columns(2),

            /*
            |--------------------------------------------------------------------------
            | RINGKASAN
            |--------------------------------------------------------------------------
            */

            Forms\Components\Section::make('Ringkasan Packaging')
                ->schema([

                    Forms\Components\Placeholder::make('total_satuan_penuh')
                        ->label('Total Satuan Penuh')
                        ->content(function ($get) {

                            return
                                ((int) $get('jumlah_satuan_penuh'))
                                *
                                ((int) $get('qty_per_satuan_penuh'));
                        }),

                    Forms\Components\Placeholder::make('total_satuan_sisa')
                        ->label('Total Satuan Sisa')
                        ->content(function ($get) {

                            return
                                ((int) $get('jumlah_satuan_sisa'))
                                *
                                ((int) $get('qty_per_satuan_sisa'));
                        }),

                    Forms\Components\Placeholder::make('grand_total')
                        ->label('Grand Total')
                        ->content(function ($get) {

                            $penuh =
                                ((int) $get('jumlah_satuan_penuh'))
                                *
                                ((int) $get('qty_per_satuan_penuh'));

                            $sisa =
                                ((int) $get('jumlah_satuan_sisa'))
                                *
                                ((int) $get('qty_per_satuan_sisa'));

                            return $penuh + $sisa;
                        }),

                ])
                ->columns(3),
        ]);
}

  public static function table(Table $table): Table
{
    return $table
        ->defaultSort('created_at', 'desc')

        ->columns([

            Tables\Columns\TextColumn::make('spk.no_spk')
                ->label('No SPK')
                ->searchable(),

                Tables\Columns\TextColumn::make('kode_packaging')
    ->label('Kode Packaging')
    ->searchable()
    ->sortable()
    ->badge()
    ->color('primary'),

            Tables\Columns\TextColumn::make('nama_produk')
                ->label('Nama Produk')
                ->getStateUsing(fn ($record) =>

                    $record->spk?->salesOrder?->itemable?->nama_barang
                    ?? '-'
                ),

            Tables\Columns\BadgeColumn::make('satuan_transfer')
                ->colors([
                    'primary' => 'Box',
                    'success' => 'Pallete',
                    'warning' => 'Pack',
                ]),

            Tables\Columns\BadgeColumn::make('jenis_transfer')
                ->colors([
                    'success' => 'barang_hasil_baik',
                    'warning' => 'label_kuning',
                    'danger' => 'blokir',
                ]),

            Tables\Columns\TextColumn::make('total_satuan_penuh')
                ->label('Total Penuh')
                ->badge()
                ->color('success'),

            Tables\Columns\TextColumn::make('total_satuan_sisa')
                ->label('Total Sisa')
                ->badge()
                ->color('warning'),

            Tables\Columns\TextColumn::make('grand_total')
                ->badge()
                ->color('primary'),

            Tables\Columns\TextColumn::make('tgl_transfer')
                ->date(),
        ])

        ->actions([

            Tables\Actions\ActionGroup::make([

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),

            ])
        ])

        ->bulkActions([

            Tables\Actions\DeleteBulkAction::make(),

        ]);
}

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPackagings::route('/'),
            'create' => Pages\CreatePackaging::route('/create'),
            'edit' => Pages\EditPackaging::route('/{record}/edit'),
        ];
    }
}