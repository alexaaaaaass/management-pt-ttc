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
                        ->relationship(
                            'spk',
                            'no_spk',
                            modifyQueryUsing: fn (\Illuminate\Database\Eloquent\Builder $query) => $query->orderByDesc('id')
                        )
                        ->searchable()
                        ->preload()
                        ->live()
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
                        ->default('barang_hasil_baik')
                        ->required(),

                    Forms\Components\DatePicker::make('tgl_transfer')
                        ->default(now())
                        ->required(),

                    Forms\Components\Section::make('Informasi Kebutuhan & Hasil Produksi SPK')
                        ->schema([
                            Forms\Components\Placeholder::make('info_order_qty')
                                ->label('Target Qty Order')
                                ->content(fn ($get) => optional(\App\Models\MasterSpk::find($get('spk_id')))->order_qty ? number_format(\App\Models\MasterSpk::find($get('spk_id'))->order_qty, 0, ',', '.') . ' pcs' : '-'),

                            Forms\Components\Placeholder::make('info_finishing_good')
                                ->label('Hasil Baik Finishing / Die Making')
                                ->content(function ($get) {
                                    $spk = \App\Models\MasterSpk::with(['finishings', 'diemakings'])->find($get('spk_id'));
                                    if (! $spk) {
                                        return '-';
                                    }
                                    $fin = (int) $spk->finishings->sum('hasil_baik');
                                    $die = (int) $spk->diemakings->sum('hasil_baik');

                                    return 'Fin: ' . number_format($fin, 0, ',', '.') . ' · Die: ' . number_format($die, 0, ',', '.');
                                }),

                            Forms\Components\Placeholder::make('info_already_packed')
                                ->label('Sudah Ter-packing (Hasil Baik)')
                                ->content(fn ($get) => optional(\App\Models\MasterSpk::find($get('spk_id')))->total_good_packed !== null ? number_format(\App\Models\MasterSpk::find($get('spk_id'))->total_good_packed, 0, ',', '.') . ' pcs' : '-'),

                            Forms\Components\Placeholder::make('info_sisa')
                                ->label('Sisa Target Belum Packing')
                                ->content(function ($get) {
                                    $spk = \App\Models\MasterSpk::find($get('spk_id'));
                                    if (!$spk) return '-';
                                    $sisa = max(0, $spk->order_qty - $spk->total_good_packed);
                                    return number_format($sisa, 0, ',', '.') . ' pcs';
                                }),
                        ])
                        ->columns(4)
                        ->visible(fn ($get) => filled($get('spk_id'))),

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
                        ->quantityMask(0)
                        ->default(0)
                        ->live(),

                    Forms\Components\TextInput::make('qty_per_satuan_penuh')
                        ->quantityMask(0)
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
                        ->quantityMask(0)
                        ->default(0)
                        ->live(),

                    Forms\Components\TextInput::make('qty_per_satuan_sisa')
                        ->quantityMask(0)
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
                            $jml = (int) (\App\Support\MoneyHelper::clean($get('jumlah_satuan_penuh')) ?? 0);
                            $qty = (int) (\App\Support\MoneyHelper::clean($get('qty_per_satuan_penuh')) ?? 0);
                            return number_format($jml * $qty, 0, '.', ',');
                        }),

                    Forms\Components\Placeholder::make('total_satuan_sisa')
                        ->label('Total Satuan Sisa')
                        ->content(function ($get) {
                            $jml = (int) (\App\Support\MoneyHelper::clean($get('jumlah_satuan_sisa')) ?? 0);
                            $qty = (int) (\App\Support\MoneyHelper::clean($get('qty_per_satuan_sisa')) ?? 0);
                            return number_format($jml * $qty, 0, '.', ',');
                        }),

                    Forms\Components\Placeholder::make('grand_total')
                        ->label('Grand Total')
                        ->content(function ($get) {
                            $penuh = ((int) (\App\Support\MoneyHelper::clean($get('jumlah_satuan_penuh')) ?? 0))
                                * ((int) (\App\Support\MoneyHelper::clean($get('qty_per_satuan_penuh')) ?? 0));
                            $sisa = ((int) (\App\Support\MoneyHelper::clean($get('jumlah_satuan_sisa')) ?? 0))
                                * ((int) (\App\Support\MoneyHelper::clean($get('qty_per_satuan_sisa')) ?? 0));
                            return number_format($penuh + $sisa, 0, '.', ',');
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
                ->label('Tgl Transfer')
                ->date('d M Y')
                ->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('spk_id')
                ->label('No SPK')
                ->relationship('spk', 'no_spk')
                ->searchable()
                ->preload(),

            Tables\Filters\SelectFilter::make('satuan_transfer')
                ->label('Satuan Transfer')
                ->options([
                    'Box'     => 'Box',
                    'Pallete' => 'Pallete',
                    'Pack'    => 'Pack',
                ]),

            Tables\Filters\SelectFilter::make('jenis_transfer')
                ->label('Jenis Transfer')
                ->options([
                    'barang_hasil_baik' => 'Barang Hasil Baik',
                    'label_kuning'      => 'Label Kuning',
                    'blokir'            => 'Blokir',
                ]),

            Tables\Filters\Filter::make('tgl_transfer')
                ->label('Rentang Tanggal')
                ->form([
                    Forms\Components\DatePicker::make('dari_tanggal')->label('Dari Tanggal'),
                    Forms\Components\DatePicker::make('sampai_tanggal')->label('Sampai Tanggal'),
                ])
                ->query(fn (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder => $query
                    ->when($data['dari_tanggal'] ?? null, fn ($q, $d) => $q->whereDate('tgl_transfer', '>=', $d))
                    ->when($data['sampai_tanggal'] ?? null, fn ($q, $d) => $q->whereDate('tgl_transfer', '<=', $d))),
        ])

        ->actions([

            Tables\Actions\ActionGroup::make([

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->using(fn ($record) => app(\App\Services\Production\ProductionService::class)->deletePackaging($record)),

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