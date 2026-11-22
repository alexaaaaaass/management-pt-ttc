<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PracetakPlateArchiveResource\Pages;
use App\Models\PracetakPlateArchive;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PracetakPlateArchiveResource extends Resource
{
    protected static ?string $model = PracetakPlateArchive::class;

    protected static ?string $navigationGroup = 'Pra Cetak';

    protected static ?string $navigationLabel = 'Arsip & Rak Plate';

    protected static ?string $pluralLabel = 'Arsip & Rak Plate Jadi';

    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Arsip Plate Jadi')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('kode_arsip')
                        ->label('Kode Arsip Plate')
                        ->default(fn () => PracetakPlateArchive::generateKode())
                        ->required()
                        ->unique(ignoreRecord: true),

                    Forms\Components\Select::make('customer_id')
                        ->label('Customer')
                        ->relationship('customer', 'nama_customer')
                        ->searchable()
                        ->preload(),

                    Forms\Components\Select::make('finish_good_item_id')
                        ->label('Master Finish Good (Produk)')
                        ->relationship('finishGoodItem', 'nama_barang')
                        ->searchable()
                        ->preload(),

                    Forms\Components\TextInput::make('nama_plat')
                        ->label('Nama / Judul Plate')
                        ->required()
                        ->columnSpan(2),

                    Forms\Components\Select::make('status_kondisi')
                        ->label('Kondisi & Status Plate')
                        ->options([
                            'tersimpan'        => 'Tersimpan di Rak (Siap Pakai)',
                            'dipakai_printing' => 'Sedang Dipakai Mesin Printing',
                            'perlu_remake'     => 'Perlu Remake Sebagian',
                            'rusak_hilang'     => 'Rusak / Hilang',
                            'retired'          => 'Retired (Tidak Dipakai Lagi)',
                        ])
                        ->default('tersimpan')
                        ->required(),
                ]),

            Forms\Components\Section::make('Spesifikasi & Lokasi Rak Penyimpanan')
                ->columns(3)
                ->schema([
                    Forms\Components\TextInput::make('jumlah_warna')
                        ->label('Jumlah Warna')
                        ->numeric()
                        ->default(4)
                        ->required(),

                    Forms\Components\TextInput::make('detail_warna')
                        ->label('Rincian Warna / Separasi')
                        ->placeholder('Contoh: CMYK + Pantone Gold'),

                    Forms\Components\TextInput::make('dimensi_plate_mm')
                        ->label('Dimensi Plate (mm)')
                        ->placeholder('Contoh: 432.8 × 280 mm'),

                    Forms\Components\TextInput::make('lokasi_rak')
                        ->label('Lokasi Rak Penyimpanan')
                        ->placeholder('Contoh: Rak A-03 / Box 12')
                        ->required(),

                    Forms\Components\TextInput::make('jumlah_dipakai')
                        ->label('Jumlah Pemakaian (Repeat)')
                        ->numeric()
                        ->default(0),

                    Forms\Components\TextInput::make('max_pemakaian')
                        ->label('Batas Maksimal Pemakaian (Opsional)')
                        ->numeric()
                        ->helperText('Kosongkan jika tanpa batas tetap'),

                    Forms\Components\TextInput::make('toleransi_rusak_hilang')
                        ->label('Toleransi Rusak / Hilang')
                        ->placeholder('Contoh: Maks 1 plate per 100.000 cetak')
                        ->columnSpan(3),

                    Forms\Components\Textarea::make('material_pendukung')
                        ->label('Arsip Material Pendukung (Finishing)')
                        ->placeholder('Pisau pond, master film spot UV, emboss, hotprint...')
                        ->rows(2)
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('catatan')
                        ->label('Catatan / Riwayat Kondisi Plate')
                        ->rows(2)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('kode_arsip')
                    ->label('Kode Arsip')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('nama_plat')
                    ->label('Nama Plate / Produk')
                    ->description(fn (PracetakPlateArchive $r) => $r->customer?->nama_customer ?: '-')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('lokasi_rak')
                    ->label('Lokasi Rak')
                    ->badge()
                    ->color('info')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_warna')
                    ->label('Warna')
                    ->formatStateUsing(fn ($state) => "{$state} Warna")
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_dipakai')
                    ->label('Dipakai')
                    ->formatStateUsing(fn ($state, PracetakPlateArchive $r) => $r->max_pemakaian ? "{$state} / {$r->max_pemakaian}×" : "{$state}×")
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status_kondisi')
                    ->label('Kondisi')
                    ->colors([
                        'success' => 'tersimpan',
                        'primary' => 'dipakai_printing',
                        'warning' => 'perlu_remake',
                        'danger'  => 'rusak_hilang',
                        'gray'    => 'retired',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'tersimpan'        => 'Tersimpan di Rak',
                        'dipakai_printing' => 'Dipakai Printing',
                        'perlu_remake'     => 'Perlu Remake',
                        'rusak_hilang'     => 'Rusak / Hilang',
                        'retired'          => 'Retired',
                        default            => ucfirst((string) $state),
                    }),

                Tables\Columns\TextColumn::make('material_pendukung')
                    ->label('Material Pendukung')
                    ->limit(35)
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('last_used_at')
                    ->label('Terakhir Dipakai')
                    ->dateTime('d M Y H:i')
                    ->placeholder('-')
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_kondisi')
                    ->label('Kondisi Plate')
                    ->options([
                        'tersimpan'        => 'Tersimpan di Rak',
                        'dipakai_printing' => 'Dipakai Printing',
                        'perlu_remake'     => 'Perlu Remake',
                        'rusak_hilang'     => 'Rusak / Hilang',
                        'retired'          => 'Retired',
                    ]),

                Tables\Filters\SelectFilter::make('customer_id')
                    ->label('Customer')
                    ->relationship('customer', 'nama_customer')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\Action::make('returnToRack')
                    ->label('Kembalikan ke Rak')
                    ->icon('heroicon-o-archive-box-arrow-down')
                    ->color('success')
                    ->visible(fn (PracetakPlateArchive $record) => $record->status_kondisi === 'dipakai_printing')
                    ->action(function (PracetakPlateArchive $record) {
                        $record->update(['status_kondisi' => 'tersimpan']);
                        Notification::make()->title("Plate {$record->kode_arsip} dikembalikan ke {$record->lokasi_rak}")->success()->send();
                    }),

                Tables\Actions\Action::make('markDamaged')
                    ->label('Lapor Rusak / Remake')
                    ->icon('heroicon-o-exclamation-triangle')
                    ->color('danger')
                    ->visible(fn (PracetakPlateArchive $record) => in_array($record->status_kondisi, ['tersimpan', 'dipakai_printing'], true))
                    ->form([
                        Forms\Components\Select::make('status_kondisi')
                            ->label('Status Baru')
                            ->options([
                                'perlu_remake' => 'Perlu Remake Sebagian',
                                'rusak_hilang' => 'Rusak / Hilang Total',
                            ])
                            ->default('perlu_remake')
                            ->required(),
                        Forms\Components\Textarea::make('catatan_kerusakan')
                            ->label('Keterangan Kerusakan / Kehilangan')
                            ->required(),
                    ])
                    ->action(function (PracetakPlateArchive $record, array $data) {
                        $record->update([
                            'status_kondisi' => $data['status_kondisi'],
                            'catatan'        => trim(($record->catatan ? $record->catatan . "\n" : '') . '[' . now()->format('d/m/Y H:i') . '] ' . $data['catatan_kerusakan']),
                        ]);
                        Notification::make()->title("Status plate {$record->kode_arsip} diperbarui")->warning()->send();
                    }),

                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['customer', 'finishGoodItem', 'pracetakProcess.spk']);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPracetakPlateArchives::route('/'),
            'create' => Pages\CreatePracetakPlateArchive::route('/create'),
            'edit'   => Pages\EditPracetakPlateArchive::route('/{record}/edit'),
        ];
    }
}
