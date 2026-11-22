<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PracetakMaterialMovementResource\Pages;
use App\Models\MasterMaterialPlate;
use App\Models\MasterSupplier;
use App\Models\PracetakMaterialMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PracetakMaterialMovementResource extends Resource
{
    protected static ?string $model = PracetakMaterialMovement::class;

    protected static ?string $navigationGroup = 'Pra Cetak';

    protected static ?string $navigationLabel = 'Histori Material Plate';

    protected static ?string $navigationIcon = 'heroicon-o-clock';

    protected static ?int $navigationSort = 7;

    protected static bool $shouldRegisterNavigation = false;

    public static function canCreate(): bool
    {
        // Mutasi ledger hanya dicatat oleh transaksi sistem
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('nomor_transaksi')->disabled(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_transaksi')
                    ->label('No. Transaksi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('jenis_transaksi')
                    ->label('Jenis')
                    ->colors([
                        'success' => 'IN',
                        'danger'  => 'OUT',
                        'warning' => 'ADJUSTMENT',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('plate.kode_material')
                    ->label('Kode Plate')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plate.nama_material')
                    ->label('Nama Material')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\BadgeColumn::make('plate.brand')
                    ->label('Brand')
                    ->colors(['gray'])
                    ->searchable(),

                Tables\Columns\TextColumn::make('plate.ukuran_sheet')
                    ->label('Ukuran Sheet')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('qty_masuk')
                    ->label('Masuk (IN)')
                    ->formatStateUsing(fn ($state) => (float) $state > 0 ? '+' . rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') : '-')
                    ->color('success')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('qty_keluar')
                    ->label('Keluar (OUT)')
                    ->formatStateUsing(fn ($state) => (float) $state > 0 ? '-' . rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') : '-')
                    ->color('danger')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('saldo_setelah')
                    ->label('Saldo Akhir')
                    ->formatStateUsing(fn ($state) => rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') . ' Sheet')
                    ->weight('bold')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('harga_satuan')
                    ->label('Harga Transaksi')
                    ->money('IDR')
                    ->alignEnd()
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Operator / User')
                    ->placeholder('Sistem')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu Dicatat')
                    ->dateTime('d M Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('id', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->options([
                        'IN'         => 'IN (Masuk)',
                        'OUT'        => 'OUT (Keluar)',
                        'ADJUSTMENT' => 'Adjustment',
                    ]),

                Tables\Filters\SelectFilter::make('master_material_plate_id')
                    ->label('Material Plate')
                    ->options(MasterMaterialPlate::query()->pluck('nama_material', 'id'))
                    ->searchable(),

                Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Supplier')
                    ->options(MasterSupplier::query()->orderBy('nama_supplier')->pluck('nama_supplier', 'id'))
                    ->searchable(),

                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($q, $date) => $q->whereDate('tanggal', '>=', $date))
                            ->when($data['sampai'], fn ($q, $date) => $q->whereDate('tanggal', '<=', $date));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Rincian Mutasi Ledger')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('nomor_transaksi')->label('Nomor Transaksi')->weight('bold'),
                    Infolists\Components\TextEntry::make('tanggal')->label('Tanggal Transaksi')->date('d M Y'),
                    Infolists\Components\TextEntry::make('jenis_transaksi')
                        ->label('Jenis Transaksi')
                        ->badge()
                        ->color(fn ($state) => match ($state) {
                            'IN'         => 'success',
                            'OUT'        => 'danger',
                            'ADJUSTMENT' => 'warning',
                            default      => 'gray',
                        }),

                    Infolists\Components\TextEntry::make('plate.kode_material')->label('Kode Plate'),
                    Infolists\Components\TextEntry::make('plate.nama_material')->label('Nama Material Plate'),
                    Infolists\Components\TextEntry::make('plate.brand')->label('Brand / Merek')->badge(),

                    Infolists\Components\TextEntry::make('saldo_sebelum')
                        ->label('Saldo Sebelum')
                        ->formatStateUsing(fn ($state) => rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') . ' Sheet'),
                    Infolists\Components\TextEntry::make('qty_masuk')
                        ->label('Qty Masuk')
                        ->formatStateUsing(fn ($state) => (float) $state > 0 ? '+' . rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') . ' Sheet' : '-'),
                    Infolists\Components\TextEntry::make('qty_keluar')
                        ->label('Qty Keluar')
                        ->formatStateUsing(fn ($state) => (float) $state > 0 ? '-' . rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') . ' Sheet' : '-'),
                    Infolists\Components\TextEntry::make('saldo_setelah')
                        ->label('Saldo Akhir Setelah Mutasi')
                        ->formatStateUsing(fn ($state) => rtrim(rtrim(number_format((float) $state, 4, '.', ','), '0'), '.') . ' Sheet')
                        ->weight('bold'),

                    Infolists\Components\TextEntry::make('harga_satuan')->label('Harga Satuan Transaksi')->money('IDR'),
                    Infolists\Components\TextEntry::make('total_nominal')->label('Total Nominal Transaksi')->money('IDR'),

                    Infolists\Components\TextEntry::make('supplier.nama_supplier')->label('Supplier')->placeholder('-'),
                    Infolists\Components\TextEntry::make('user.name')->label('Dicatat Oleh')->placeholder('Sistem'),
                    Infolists\Components\TextEntry::make('created_at')->label('Waktu Dicatat')->dateTime('d M Y H:i:s'),

                    Infolists\Components\TextEntry::make('catatan')->label('Catatan')->columnSpanFull()->placeholder('-'),
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
            'index' => Pages\ListPracetakMaterialMovements::route('/'),
            'view'  => Pages\ViewPracetakMaterialMovement::route('/{record}'),
        ];
    }
}
