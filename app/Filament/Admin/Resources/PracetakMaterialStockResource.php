<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PracetakMaterialStockResource\Pages;
use App\Models\MasterMaterialPlate;
use App\Models\PracetakMaterialStock;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PracetakMaterialStockResource extends Resource
{
    protected static ?string $model = PracetakMaterialStock::class;

    protected static ?string $navigationGroup = 'Pra Cetak';

    protected static ?string $navigationLabel = 'Stok Material Plate';

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        // Saldo stok dihasilkan dari transaksi, bukan diinput manual
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Informasi Saldo Stok')
                ->columns(2)
                ->schema([
                    Forms\Components\TextInput::make('plate.kode_material')->label('Kode Plate')->disabled(),
                    Forms\Components\TextInput::make('plate.nama_material')->label('Nama Material')->disabled(),
                    Forms\Components\TextInput::make('stok_sheet_utuh')->label('Stok Sheet Utuh')->disabled(),
                    Forms\Components\TextInput::make('total_nilai_stok')->label('Total Nilai Stok')->currencyMask(2)->disabled(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('plate.kode_material')
                    ->label('Kode')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('plate.nama_material')
                    ->label('Nama Material Plate')
                    ->searchable()
                    ->sortable()
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('plate.brand')
                    ->label('Brand')
                    ->colors(['primary'])
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plate.ketebalan_mm')
                    ->label('Ketebalan')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 3, '.', ',') . ' mm')
                    ->sortable(),

                Tables\Columns\TextColumn::make('plate.ukuran_sheet')
                    ->label('Ukuran Sheet (P × L)'),

                Tables\Columns\TextColumn::make('stok_sheet_utuh')
                    ->label('Stok Sheet Tersedia')
                    ->formatStateUsing(fn ($state) => rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') . ' Sheet')
                    ->alignEnd()
                    ->weight('bold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('plate.satuan.nama_satuan')
                    ->label('Satuan')
                    ->placeholder('Sheet')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('harga_terakhir')
                    ->label('Harga Terakhir')
                    ->money('IDR')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_nilai_stok')
                    ->label('Total Nilai Stok')
                    ->money('IDR')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('plate.status')
                    ->label('Status Master')
                    ->colors([
                        'success' => 'aktif',
                        'danger'  => 'nonaktif',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'aktif' ? 'Aktif' : 'Non Aktif')
                    ->sortable(),

                Tables\Columns\TextColumn::make('last_movement_at')
                    ->label('Mutasi Terakhir')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('stok_sheet_utuh', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('brand')
                    ->label('Brand')
                    ->relationship('plate', 'brand'),

                Tables\Filters\Filter::make('ada_stok')
                    ->label('Hanya Yang Memiliki Stok (> 0)')
                    ->query(fn ($query) => $query->where('stok_sheet_utuh', '>', 0)),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Spesifikasi Material Plate')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('plate.kode_material')->label('Kode Material')->weight('bold'),
                    Infolists\Components\TextEntry::make('plate.nama_material')->label('Nama Material Plate'),
                    Infolists\Components\TextEntry::make('plate.brand')->label('Brand / Merek')->badge(),
                    Infolists\Components\TextEntry::make('plate.tipe_plate')->label('Tipe Plate')->placeholder('-'),
                    Infolists\Components\TextEntry::make('plate.ketebalan_mm')->label('Ketebalan')->suffix(' mm'),
                    Infolists\Components\TextEntry::make('plate.ukuran_sheet')->label('Ukuran Sheet (P × L)'),
                    Infolists\Components\TextEntry::make('plate.supplier.nama_supplier')->label('Supplier Master')->placeholder('-'),
                    Infolists\Components\TextEntry::make('plate.status')->label('Status Master')->badge(),
                ]),

            Infolists\Components\Section::make('Posisi Saldo Persediaan')
                ->columns(4)
                ->schema([
                    Infolists\Components\TextEntry::make('stok_sheet_utuh')
                        ->label('Stok Sheet Tersedia Saat Ini')
                        ->formatStateUsing(fn ($state) => rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') . ' Sheet')
                        ->weight('bold'),
                    Infolists\Components\TextEntry::make('total_masuk')
                        ->label('Akumulasi Masuk (IN)')
                        ->formatStateUsing(fn ($state) => rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') . ' Sheet'),
                    Infolists\Components\TextEntry::make('total_keluar')
                        ->label('Akumulasi Keluar (OUT)')
                        ->formatStateUsing(fn ($state) => rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') . ' Sheet'),
                    Infolists\Components\TextEntry::make('harga_terakhir')->label('Harga Terakhir')->money('IDR'),
                    Infolists\Components\TextEntry::make('total_nilai_stok')->label('Total Nilai Stok Persediaan')->money('IDR'),
                    Infolists\Components\TextEntry::make('harga_rata_rata')->label('Estimasi Rata-rata / Sheet')->money('IDR'),
                    Infolists\Components\TextEntry::make('last_movement_at')->label('Waktu Mutasi Terakhir')->dateTime('d M Y H:i')->placeholder('-'),
                ]),
        ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPracetakMaterialStocks::route('/'),
            'view'  => Pages\ViewPracetakMaterialStock::route('/{record}'),
        ];
    }
}
