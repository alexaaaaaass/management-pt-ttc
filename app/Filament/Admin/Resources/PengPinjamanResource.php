<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PengPinjamanResource\Pages;
use App\Filament\Admin\Resources\PengPinjamanResource\RelationManagers;
use App\Models\HrPayrollSetting;
use App\Models\MasterCOA;
use App\Models\PengPinjaman;
use App\Services\Finance\FinanceHrdSyncService;
use App\Services\Finance\TreasuryService;
use App\Support\MoneyHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PengPinjamanResource extends Resource
{
    protected static ?string $model = PengPinjaman::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel = 'Pinjaman Karyawan';
    protected static ?string $modelLabel = 'Pinjaman Karyawan';
    protected static ?string $pluralModelLabel = 'Pinjaman Karyawan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('karyawan_id')
                    ->label('Karyawan')
                    ->relationship('karyawan', 'nama')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\TextInput::make('kode_gudang')
                    ->label('Kode Gudang')
                    ->default('TTC')
                    ->required()
                    ->maxLength(50),

                Forms\Components\DatePicker::make('tanggal_pengajuan')
                    ->label('Tanggal Pengajuan')
                    ->required()
                    ->default(now()),

                Forms\Components\Select::make('status')
                    ->label('Status Approval')
                    ->options([
                        'Pending'  => 'Pending',
                        'Approved' => 'Approved',
                        'Rejected' => 'Rejected',
                    ])
                    ->default('Pending')
                    ->disabled()
                    ->dehydrated(),

                Forms\Components\TextInput::make('nilai_pinjaman')
                    ->label('Nilai Pinjaman')
                    ->prefix('Rp')
                    ->currencyMask(2)
                    ->placeholder('0.00')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::hitungCicilan($get, $set)),

                Forms\Components\TextInput::make('jangka_waktu')
                    ->label('Jangka Waktu (Bulan)')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, callable $get, callable $set) => self::hitungCicilan($get, $set)),

                Forms\Components\TextInput::make('cicilan_per_bulan')
                    ->label('Cicilan Per Bulan')
                    ->prefix('Rp')
                    ->currencyMask(2)
                    ->placeholder('0.00')
                    ->required(),

                Forms\Components\Textarea::make('keperluan_pinjaman')
                    ->label('Keperluan Pinjaman')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function hitungCicilan(callable $get, callable $set): void
    {
        $nilai = MoneyHelper::clean($get('nilai_pinjaman')) ?? 0;
        $waktu = (int) ($get('jangka_waktu') ?? 0);

        if ($nilai > 0 && $waktu > 0) {
            $set('cicilan_per_bulan', MoneyHelper::format(round($nilai / $waktu, 2), 2));
        }
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_bukti')
                    ->label('Nomor Bukti')
                    ->searchable()
                    ->weight('bold')
                    ->color('primary'),

                Tables\Columns\TextColumn::make('karyawan.nama')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_pengajuan')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('nilai_pinjaman')
                    ->label('Nilai Pinjaman')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->sortable(),

                Tables\Columns\TextColumn::make('jangka_waktu')
                    ->label('Tenor')
                    ->suffix(' Bln'),

                Tables\Columns\TextColumn::make('cicilan_per_bulan')
                    ->label('Cicilan / Bln')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight(),

                Tables\Columns\TextColumn::make('sisa_pinjaman')
                    ->label('Sisa Pinjaman')
                    ->getStateUsing(fn (PengPinjaman $r) => $r->sisa_pinjaman)
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->color(fn ($state) => (float) $state <= 0 ? 'success' : 'danger')
                    ->weight('bold')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state) => match ($state) {
                        'Approved' => 'success',
                        'Rejected' => 'danger',
                        default    => 'warning',
                    }),

                Tables\Columns\IconColumn::make('is_disbursed')
                    ->label('Cair')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-clock')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->alignCenter(),
            ])
            ->defaultSort('id', 'desc')
            ->searchPlaceholder('Cari nomor bukti atau nama karyawan...')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['Pending' => 'Pending', 'Approved' => 'Approved', 'Rejected' => 'Rejected']),
                Tables\Filters\TernaryFilter::make('is_disbursed')
                    ->label('Status Pencairan Finance'),
            ])
            ->actions([
                Tables\Actions\Action::make('cairkanDana')
                    ->label('Cairkan')
                    ->icon('heroicon-o-banknotes')
                    ->color('success')
                    ->visible(fn (PengPinjaman $r) => $r->status !== 'Rejected' && ! $r->is_disbursed)
                    ->modalHeading(fn (PengPinjaman $r) => "Cairkan Pinjaman / Kasbon — {$r->karyawan?->nama}")
                    ->modalDescription('Mencatat pencairan dana dari Kas/Bank Finance ke karyawan (DEBIT 1131 Piutang Karyawan / KREDIT 111x Kas/Bank).')
                    ->form([
                        Forms\Components\Select::make('account_kas_id')
                            ->label('Rekening Kas / Bank Pembayar (111x)')
                            ->options(fn () => MasterCOA::where(function ($q) {
                                $q->where('kode_akun', 'like', '111%')
                                    ->orWhere('account_class', 'like', '%Bank%')
                                    ->orWhere('account_class', 'like', '%Cash%');
                            })->orderBy('kode_akun')->get()->mapWithKeys(fn ($c) => [$c->id => trim((string) $c->kode_akun) . ' - ' . $c->nama_akun])->toArray())
                            ->default(fn () => HrPayrollSetting::first()?->default_account_kas_id)
                            ->searchable()
                            ->required(),
                        Forms\Components\DatePicker::make('tanggal_pencairan')
                            ->label('Tanggal Pencairan')
                            ->default(now())
                            ->required(),
                        Forms\Components\Toggle::make('post_to_ledger')
                            ->label('Auto-Posting ke Buku Besar (DEBIT 1131 Piutang Karyawan / KREDIT Kas/Bank)')
                            ->helperText('Matikan jika mutasi transfer kasbon sudah/akan dicatat di Transaksi Bank agar tidak dobel.')
                            ->default(true),
                    ])
                    ->action(function (PengPinjaman $record, array $data) {
                        app(FinanceHrdSyncService::class)->disbursePinjaman(
                            $record,
                            (int) $data['account_kas_id'],
                            $data['tanggal_pencairan'],
                            (bool) ($data['post_to_ledger'] ?? true)
                        );
                        Notification::make()
                            ->title('Pinjaman Berhasil Dicairkan!')
                            ->body("Kasbon {$record->karyawan?->nama} sebesar Rp " . number_format((float) $record->nilai_pinjaman, 0, ',', '.') . (! empty($data['post_to_ledger']) ? ' tercatat di COA 1131 Piutang Karyawan.' : '.'))
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('previewPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (PengPinjaman $record) => route('hrd.pinjaman.preview', $record))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(fn (PengPinjaman $record) => app(TreasuryService::class)->deletePinjamanDisbursement($record)),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PembayaranRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPengPinjamen::route('/'),
            'create' => Pages\CreatePengPinjaman::route('/create'),
            'edit'   => Pages\EditPengPinjaman::route('/{record}/edit'),
        ];
    }
}
