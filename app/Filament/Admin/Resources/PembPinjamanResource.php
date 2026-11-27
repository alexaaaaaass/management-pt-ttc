<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PembPinjamanResource\Pages;
use App\Models\MasterCOA;
use App\Models\PembPinjaman;
use App\Models\PengPinjaman;
use App\Services\Finance\TreasuryService;
use App\Support\MoneyHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PembPinjamanResource extends Resource
{
    protected static ?string $model = PembPinjaman::class;
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 7;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationLabel = 'Pembayaran Pinjaman';
    protected static ?string $modelLabel = 'Pembayaran Pinjaman';
    protected static ?string $pluralModelLabel = 'Pembayaran Pinjaman';
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('peng_pinjaman_id')
                    ->label('Pinjaman Aktif (Belum Lunas)')
                    ->options(function (?PembPinjaman $record) {
                        return PengPinjaman::with(['karyawan', 'pembayaran'])
                            ->where(function ($q) {
                                $q->whereNull('status')->orWhere('status', '!=', 'Rejected');
                            })
                            ->get()
                            ->filter(function (PengPinjaman $p) use ($record) {
                                if ($record && (int) $record->peng_pinjaman_id === (int) $p->id) {
                                    return true;
                                }
                                $paid = (float) $p->pembayaran->sum('nominal_pembayaran');

                                return ((float) $p->nilai_pinjaman - $paid) > 0.01;
                            })
                            ->mapWithKeys(function (PengPinjaman $item) {
                                $sisa = max(0, (float) $item->nilai_pinjaman - (float) $item->pembayaran->sum('nominal_pembayaran'));

                                return [
                                    $item->id => $item->nomor_bukti . ' - ' . ($item->karyawan?->nama ?? '-') . ' (Sisa: Rp ' . number_format($sisa, 0, ',', '.') . ')',
                                ];
                            });
                    })
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        $pinjaman = PengPinjaman::with('pembayaran')->find($state);
                        if (! $pinjaman) {
                            return;
                        }
                        $sudahBayar = (float) $pinjaman->pembayaran->sum('nominal_pembayaran');
                        $sisa = max(0.0, round((float) $pinjaman->nilai_pinjaman - $sudahBayar, 2));
                        $cicilan = min((float) $pinjaman->cicilan_per_bulan, $sisa);
                        $nextTahap = ((int) $pinjaman->pembayaran->max('tahap_cicilan')) + 1;

                        $set('karyawan_id', $pinjaman->karyawan_id);
                        $set('tahap_cicilan', $nextTahap);
                        $set('nominal_pembayaran', MoneyHelper::format($cicilan > 0 ? $cicilan : $sisa, 2));
                    }),

                Forms\Components\Hidden::make('karyawan_id'),

                Forms\Components\TextInput::make('tahap_cicilan')
                    ->label('Tahap Cicilan Ke-')
                    ->numeric()
                    ->minValue(1)
                    ->default(1)
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_pembayaran')
                    ->label('Tanggal Pembayaran')
                    ->default(now())
                    ->required(),

                Forms\Components\TextInput::make('nominal_pembayaran')
                    ->label('Nominal Pembayaran')
                    ->prefix('Rp')
                    ->currencyMask(2)
                    ->placeholder('0.00')
                    ->extraInputAttributes(['onwheel' => 'this.blur()'])
                    ->helperText('Otomatis dibulatkan ke cicilan/sisa jika selisih ≤ Rp 1.000. Tidak boleh melebihi sisa pinjaman.')
                    ->live(onBlur: true)
                    ->rules([
                        fn (Forms\Get $get, ?PembPinjaman $record): \Closure => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                            $pinjaman = PengPinjaman::with('pembayaran')->find($get('peng_pinjaman_id'));
                            if (! $pinjaman) {
                                return;
                            }
                            $sudahBayar = (float) $pinjaman->pembayaran
                                ->when($record, fn ($c) => $c->where('id', '!=', $record->id))
                                ->sum('nominal_pembayaran');
                            $sisa = max(0.0, round((float) $pinjaman->nilai_pinjaman - $sudahBayar, 2));
                            $val = MoneyHelper::clean($value) ?? 0;
                            if ($val <= 0) {
                                $fail('Nominal pembayaran harus lebih besar dari 0.');
                            } elseif ($val > ($sisa + 1000)) {
                                $fail('Nominal pembayaran melebihi sisa pinjaman (Sisa: Rp ' . number_format($sisa, 0, ',', '.') . ').');
                            }
                        },
                    ])
                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                        $pinjaman = PengPinjaman::find($get('peng_pinjaman_id'));
                        if (! $pinjaman) {
                            return;
                        }
                        $val = MoneyHelper::clean($state) ?? 0;
                        $cicilan = (float) $pinjaman->cicilan_per_bulan;
                        if ($cicilan > 0 && $val > 0 && abs($cicilan - $val) > 0 && abs($cicilan - $val) <= 1000) {
                            $set('nominal_pembayaran', MoneyHelper::format($cicilan, 2));
                        }
                    })
                    ->required(),

                Forms\Components\Select::make('account_kas_id')
                    ->label('Akun Kas / Bank Penerima (Pelunasan Manual)')
                    ->options(fn () => MasterCOA::where(function ($q) {
                        $q->where('kode_akun', 'like', '111%')
                            ->orWhere('account_class', 'like', '%Bank%')
                            ->orWhere('account_class', 'like', '%Cash%');
                    })->orderBy('kode_akun')->get()->mapWithKeys(fn ($c) => [$c->id => trim((string) $c->kode_akun) . ' - ' . $c->nama_akun])->toArray())
                    ->searchable()
                    ->nullable()
                    ->hidden(fn (?PembPinjaman $record) => ! empty($record?->gaji_id))
                    ->helperText('Diisi jika karyawan menyetor cicilan manual (tunai/transfer langsung di luar potong gaji).'),

                Forms\Components\Toggle::make('post_to_ledger')
                    ->label('Auto-Posting ke Buku Besar (DEBIT Kas/Bank, KREDIT 1131 Piutang Karyawan)')
                    ->hidden(fn (?PembPinjaman $record) => ! empty($record?->gaji_id))
                    ->default(false),

                Forms\Components\Textarea::make('keterangan')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_bukti')->label('No Bukti')->searchable()->weight('bold'),
                Tables\Columns\TextColumn::make('pengPinjaman.nomor_bukti')->label('Ref. Pinjaman')->badge()->color('info'),
                Tables\Columns\TextColumn::make('karyawan.nama')->label('Karyawan')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('tanggal_pembayaran')->label('Tgl Bayar')->date('d/m/Y')->sortable(),
                Tables\Columns\TextColumn::make('nominal_pembayaran')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('tahap_cicilan')->label('Cicilan Ke')->badge(),
                Tables\Columns\TextColumn::make('gaji.kode_slip')
                    ->label('Sumber')
                    ->placeholder('Manual')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('keterangan')->limit(30),
            ])
            ->defaultSort('id', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(fn (PembPinjaman $record) => app(TreasuryService::class)->deletePinjamanManualPayment($record)),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPembPinjamen::route('/'),
            'create' => Pages\CreatePembPinjaman::route('/create'),
            'edit'   => Pages\EditPembPinjaman::route('/{record}/edit'),
            'rekap'  => Pages\RekapPinjaman::route('/rekap'),
        ];
    }
}
