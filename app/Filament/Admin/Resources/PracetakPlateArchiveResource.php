<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PracetakPlateArchiveResource\Pages;
use App\Models\IMRPrinting;
use App\Models\PracetakPlateArchive;
use App\Services\IMR\IMRPrintingService;
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

    public static function getNavigationBadge(): ?string
    {
        $pending = IMRPrinting::query()
            ->whereNotNull('plate_archive_id')
            ->where('request_plate', true)
            ->where('plate_approved', true)
            ->whereNull('plate_issued_at')
            ->whereIn('status', ['Approved', 'Partial', 'Partial Issued'])
            ->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

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

                Tables\Columns\TextColumn::make('active_imr_request')
                    ->label('Request IMR Aktif')
                    ->badge()
                    ->getStateUsing(function (PracetakPlateArchive $record): string {
                        if ($imr = $record->approvedPendingPlateImr()) {
                            return "{$imr->no_imr} · {$imr->plate_qty_requested} Plate (Siap Keluar)";
                        }
                        if ($imr = $record->activeImrRequest()) {
                            return "{$imr->no_imr} · {$imr->plate_qty_requested} Plate (Menunggu Approve)";
                        }
                        if ($record->status_kondisi === 'dipakai_printing' && ($issued = $record->latestIssuedImr())) {
                            return "{$issued->no_imr} · {$issued->plate_received_by}";
                        }

                        return '-';
                    })
                    ->color(function (PracetakPlateArchive $record): string {
                        if ($record->approvedPendingPlateImr()) {
                            return 'warning';
                        }
                        if ($record->activeImrRequest()) {
                            return 'info';
                        }
                        if ($record->status_kondisi === 'dipakai_printing' && $record->latestIssuedImr()) {
                            return 'primary';
                        }

                        return 'gray';
                    })
                    ->tooltip(function (PracetakPlateArchive $record): ?string {
                        if ($imr = $record->approvedPendingPlateImr()) {
                            return "SPK {$imr->spk?->no_spk} — IMR sudah disetujui, siap diserahkan dari rak {$record->lokasi_rak}.";
                        }
                        if ($imr = $record->activeImrRequest()) {
                            return "SPK {$imr->spk?->no_spk} — Menunggu approval IMR Printing terlebih dahulu.";
                        }
                        if ($record->status_kondisi === 'dipakai_printing' && ($issued = $record->latestIssuedImr())) {
                            return "Diserahkan via {$issued->no_imr} kepada {$issued->plate_received_by} (" . $issued->plate_issued_at?->format('d M Y H:i') . ').';
                        }

                        return null;
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
                Tables\Actions\Action::make('issuePlateViaImr')
                    ->label('Serahkan Plate (via IMR)')
                    ->icon('heroicon-o-truck')
                    ->color('warning')
                    ->visible(fn (PracetakPlateArchive $record) => $record->approvedPendingPlateImr() !== null)
                    ->modalHeading(fn (PracetakPlateArchive $record) => 'Serah Terima Plate via ' . ($record->approvedPendingPlateImr()?->no_imr ?? 'IMR'))
                    ->modalDescription(function (PracetakPlateArchive $record): string {
                        $imr = $record->approvedPendingPlateImr();
                        $spkNo = $imr?->spk?->no_spk ?: '-';
                        $qty = (int) ($imr?->plate_qty_requested ?: $record->jumlah_warna);

                        return "Mengeluarkan {$qty} lembar plate {$record->nama_plat} ({$record->kode_arsip}) dari {$record->lokasi_rak} untuk SPK {$spkNo}. Pengeluaran akan tercatat langsung pada dokumen {$imr?->no_imr}.";
                    })
                    ->form([
                        Forms\Components\TextInput::make('plate_received_by')
                            ->label('Nama Penerima (Operator / Kepala Shift Printing)')
                            ->placeholder('Contoh: Budi — Shift 1 Flexo Zonten')
                            ->required(),

                        Forms\Components\Textarea::make('plate_handover_notes')
                            ->label('Catatan Serah Terima Plate (Opsional)')
                            ->default(fn (PracetakPlateArchive $record) => $record->approvedPendingPlateImr()?->plate_notes)
                            ->placeholder('Contoh: Kondisi plate lengkap & baik + pisau pond')
                            ->rows(2),
                    ])
                    ->action(function (PracetakPlateArchive $record, array $data) {
                        $imr = $record->approvedPendingPlateImr();
                        if (! $imr) {
                            Notification::make()
                                ->title('Tidak ada IMR aktif yang disetujui untuk plate ini.')
                                ->warning()
                                ->send();

                            return;
                        }

                        try {
                            app(IMRPrintingService::class)->issue($imr, [
                                'issue_plate'          => true,
                                'plate_received_by'    => $data['plate_received_by'],
                                'plate_handover_notes' => $data['plate_handover_notes'] ?? '',
                                'items'                => [],
                            ]);

                            Notification::make()
                                ->title("Plate {$record->kode_arsip} Berhasil Diserahkan")
                                ->body("Tercatat pada {$imr->no_imr}, diterima oleh {$data['plate_received_by']}. Status arsip & proses Pra Cetak otomatis diperbarui.")
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()
                                ->title('Serah Terima Plate Gagal')
                                ->body($e->getMessage())
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),

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
        return parent::getEloquentQuery()->with(['customer', 'finishGoodItem', 'pracetakProcess.spk', 'imrPrintings.spk']);
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
