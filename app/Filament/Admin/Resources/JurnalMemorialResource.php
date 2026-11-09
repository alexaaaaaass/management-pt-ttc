<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\JurnalMemorialResource\Pages;
use App\Models\JournalEntry;
use App\Models\Karyawan;
use App\Models\MasterCOA;
use App\Services\Finance\TreasuryService;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class JurnalMemorialResource extends Resource
{
    protected static ?string $model = JournalEntry::class;

    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Jurnal Memorial';
    protected static ?string $modelLabel = 'Memorial Adjust';
    protected static ?string $pluralModelLabel = 'Transaksi Memorial (Memorial Adjust)';

    public static function form(Form $form): Form
    {
        $coaOptions = fn () => MasterCOA::orderBy('kode_akun')
            ->get()
            ->mapWithKeys(fn (MasterCOA $coa) => [
                $coa->id => trim((string) $coa->kode_akun) . ' - ' . $coa->nama_akun,
            ])
            ->toArray();

        return $form
            ->schema([
                Section::make('Memorial Adjust (Transaksi Memorial)')
                    ->description('Catat jurnal umum, penyesuaian (adjustment), penyusutan, atau koreksi pembukuan dengan prinsip Double-Entry (Total Debit = Total Kredit).')
                    ->extraAttributes([
                        'class' => 'rounded-2xl border-l-8 border-indigo-600 shadow-sm bg-white dark:bg-gray-900',
                    ])
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('kode_jurnal')
                                    ->label('No. Bukti Jurnal')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default('AUTO'),

                                DatePicker::make('tanggal')
                                    ->label('Tanggal Jurnal')
                                    ->default(now())
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Set $set) {
                                        if ($state) {
                                            $set('periode', \Carbon\Carbon::parse($state)->format('F Y'));
                                        }
                                    }),

                                TextInput::make('periode')
                                    ->label('Periode')
                                    ->default(now()->format('F Y')),

                                Select::make('jenis_jurnal')
                                    ->label('Jenis Jurnal')
                                    ->options([
                                        'MEMORIAL'    => 'Jurnal Memorial / Umum',
                                        'PENYESUAIAN' => 'Jurnal Penyesuaian (Adjustment)',
                                        'PENYUSUTAN'  => 'Jurnal Penyusutan Aset',
                                        'KOREKSI'     => 'Jurnal Koreksi',
                                        'PENUTUP'     => 'Jurnal Penutup (Closing)',
                                    ])
                                    ->default('MEMORIAL')
                                    ->required(),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextInput::make('no_referensi')
                                    ->label('No. Referensi / Dokumen')
                                    ->placeholder('Contoh: BA-001 / INV / Memo'),

                                Select::make('karyawan_id')
                                    ->label('Dibuat Oleh / PIC')
                                    ->options(fn () => Karyawan::pluck('nama', 'id')->toArray())
                                    ->searchable()
                                    ->preload(),

                                TextInput::make('gudang')
                                    ->label('Unit / Gudang')
                                    ->default('TTC'),

                                Select::make('status')
                                    ->label('Status Posting')
                                    ->options([
                                        'POSTED' => 'POSTED (Masuk Buku Besar)',
                                        'DRAFT'  => 'DRAFT (Simpan Sementara)',
                                    ])
                                    ->default('POSTED')
                                    ->required(),
                            ]),

                        Textarea::make('keterangan')
                            ->label('Keterangan / Uraian Jurnal Memorial')
                            ->rows(2)
                            ->placeholder('Uraian singkat transaksi memorial / penyesuaian...')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Rincian Ayat Jurnal (Double-Entry Lines)')
                    ->description('Pastikan jumlah Total Debit sama persis dengan Total Kredit (Balance).')
                    ->schema([
                        Repeater::make('lines')
                            ->label('Baris Ayat Jurnal')
                            ->defaultItems(2)
                            ->minItems(2)
                            ->addActionLabel('+ Tambah Baris Akun COA')
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                $totalDebit = collect($state ?? [])->sum(fn ($row) => (float) ($row['debit'] ?? 0));
                                $totalKredit = collect($state ?? [])->sum(fn ($row) => (float) ($row['kredit'] ?? 0));
                                $set('total_debit', round($totalDebit, 2));
                                $set('total_kredit', round($totalKredit, 2));
                            })
                            ->schema([
                                Grid::make(12)
                                    ->schema([
                                        Select::make('coa_id')
                                            ->label('Akun COA')
                                            ->options($coaOptions)
                                            ->searchable()
                                            ->preload()
                                            ->required()
                                            ->columnSpan(5),

                                        TextInput::make('keterangan')
                                            ->label('Keterangan Baris')
                                            ->placeholder('Catatan per akun (opsional)')
                                            ->columnSpan(3),

                                        TextInput::make('debit')
                                            ->label('Debit (Rp)')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->prefix('Rp')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                if ((float) $state > 0) {
                                                    $set('kredit', 0);
                                                }
                                            })
                                            ->columnSpan(2),

                                        TextInput::make('kredit')
                                            ->label('Kredit (Rp)')
                                            ->numeric()
                                            ->default(0)
                                            ->minValue(0)
                                            ->prefix('Rp')
                                            ->live(onBlur: true)
                                            ->afterStateUpdated(function ($state, Set $set) {
                                                if ((float) $state > 0) {
                                                    $set('debit', 0);
                                                }
                                            })
                                            ->columnSpan(2),
                                    ]),
                            ]),

                        Grid::make(3)
                            ->schema([
                                Placeholder::make('summary_debit')
                                    ->label('Total Debit')
                                    ->content(function (Get $get) {
                                        $lines = $get('lines') ?? [];
                                        $sum = collect($lines)->sum(fn ($r) => (float) ($r['debit'] ?? 0));
                                        return 'Rp ' . number_format($sum, 0, ',', '.');
                                    }),

                                Placeholder::make('summary_kredit')
                                    ->label('Total Kredit')
                                    ->content(function (Get $get) {
                                        $lines = $get('lines') ?? [];
                                        $sum = collect($lines)->sum(fn ($r) => (float) ($r['kredit'] ?? 0));
                                        return 'Rp ' . number_format($sum, 0, ',', '.');
                                    }),

                                Placeholder::make('summary_status')
                                    ->label('Status Keseimbangan (Balance Check)')
                                    ->content(function (Get $get) {
                                        $lines = $get('lines') ?? [];
                                        $debit = round(collect($lines)->sum(fn ($r) => (float) ($r['debit'] ?? 0)), 2);
                                        $kredit = round(collect($lines)->sum(fn ($r) => (float) ($r['kredit'] ?? 0)), 2);
                                        $diff = abs($debit - $kredit);

                                        if ($debit <= 0 && $kredit <= 0) {
                                            return '⚪ Belum ada nominal diinput';
                                        }

                                        if ($diff < 0.01) {
                                            return '🟢 SEIMBANG (BALANCE) — Rp ' . number_format($debit, 0, ',', '.');
                                        }

                                        return '🔴 TIDAK SEIMBANG (Selisih: Rp ' . number_format($diff, 0, ',', '.') . ')';
                                    }),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('kode_jurnal')
                    ->label('No. Jurnal')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('jenis_jurnal')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'MEMORIAL'    => 'info',
                        'PENYESUAIAN' => 'warning',
                        'PENYUSUTAN'  => 'gray',
                        'KOREKSI'     => 'danger',
                        'PENUTUP'     => 'success',
                        default       => 'primary',
                    }),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(45)
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_debit')
                    ->label('Total Debit')
                    ->money('IDR')
                    ->alignRight()
                    ->weight('bold')
                    ->sortable()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total Debit')
                            ->money('IDR')
                    ),

                Tables\Columns\TextColumn::make('total_kredit')
                    ->label('Total Kredit')
                    ->money('IDR')
                    ->alignRight()
                    ->weight('bold')
                    ->sortable()
                    ->summarize(
                        Tables\Columns\Summarizers\Sum::make()
                            ->label('Total Kredit')
                            ->money('IDR')
                    ),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'POSTED' => 'success',
                        'DRAFT'  => 'warning',
                        default  => 'gray',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_jurnal')
                    ->label('Jenis Jurnal')
                    ->options([
                        'MEMORIAL'    => 'Jurnal Memorial / Umum',
                        'PENYESUAIAN' => 'Jurnal Penyesuaian',
                        'PENYUSUTAN'  => 'Jurnal Penyusutan',
                        'KOREKSI'     => 'Jurnal Koreksi',
                        'PENUTUP'     => 'Jurnal Penutup',
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'POSTED' => 'POSTED',
                        'DRAFT'  => 'DRAFT',
                    ]),
            ])
            ->actions([
                Tables\Actions\Action::make('preview_pdf')
                    ->label('Bukti PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (JournalEntry $record) => route('finance.voucher.memorial.preview', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('download_pdf')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn (JournalEntry $record) => route('finance.voucher.memorial.download', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->using(fn (JournalEntry $record) => app(TreasuryService::class)->deleteJournalMemorial($record)),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListJurnalMemorials::route('/'),
            'create' => Pages\CreateJurnalMemorial::route('/create'),
            'edit'   => Pages\EditJurnalMemorial::route('/{record}/edit'),
            'view'   => Pages\ViewJurnalMemorial::route('/{record}'),
        ];
    }
}

