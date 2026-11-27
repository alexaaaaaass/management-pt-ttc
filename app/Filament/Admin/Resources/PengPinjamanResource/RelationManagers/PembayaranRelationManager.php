<?php

namespace App\Filament\Admin\Resources\PengPinjamanResource\RelationManagers;

use App\Models\MasterCOA;
use App\Models\PembPinjaman;
use App\Services\Finance\TreasuryService;
use App\Support\MoneyHelper;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PembayaranRelationManager extends RelationManager
{
    protected static string $relationship = 'pembayaran';
    protected static ?string $title = 'Riwayat Cicilan & Pelunasan Pinjaman';
    protected static ?string $icon = 'heroicon-o-credit-card';

    public function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('tahap_cicilan')
                ->label('Cicilan Ke-')
                ->numeric()
                ->minValue(1)
                ->default(fn () => ((int) $this->getOwnerRecord()->pembayaran()->max('tahap_cicilan')) + 1)
                ->required(),

            DatePicker::make('tanggal_pembayaran')
                ->label('Tanggal Pembayaran')
                ->default(now())
                ->required(),

            TextInput::make('nominal_pembayaran')
                ->label('Nominal Pembayaran')
                ->prefix('Rp')
                ->currencyMask(0)
                ->default(function () {
                    $owner = $this->getOwnerRecord();
                    $sisa = (float) $owner->sisa_pinjaman;
                    $cicilan = (float) $owner->cicilan_per_bulan;
                    return MoneyHelper::format(min($cicilan > 0 ? $cicilan : $sisa, $sisa), 0);
                })
                ->required(),

            Select::make('account_kas_id')
                ->label('Rekening Kas / Bank Penerima (Pelunasan Manual)')
                ->options(fn () => MasterCOA::where('kode_akun', 'like', '111%')
                    ->orderBy('kode_akun')
                    ->get()
                    ->mapWithKeys(fn ($c) => [$c->id => trim((string) $c->kode_akun) . ' - ' . $c->nama_akun])
                    ->toArray())
                ->searchable()
                ->nullable(),

            Toggle::make('post_to_ledger')
                ->label('Auto-Posting ke Buku Besar (DEBIT Kas/Bank, KREDIT 1131 Piutang Karyawan)')
                ->default(false)
                ->columnSpanFull(),

            Textarea::make('keterangan')
                ->label('Keterangan')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('tahap_cicilan', 'asc')
            ->columns([
                TextColumn::make('no_bukti')->label('No. Bukti')->badge()->color('primary'),
                TextColumn::make('tahap_cicilan')->label('Cicilan Ke')->badge()->alignCenter(),
                TextColumn::make('tanggal_pembayaran')->label('Tgl Bayar')->date('d/m/Y'),
                TextColumn::make('nominal_pembayaran')
                    ->label('Nominal')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold')
                    ->color('success'),
                TextColumn::make('gaji.kode_slip')
                    ->label('Sumber')
                    ->placeholder('Manual')
                    ->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray'),
                TextColumn::make('keterangan')->label('Keterangan')->limit(35),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('+ Catat Cicilan Manual')
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['karyawan_id'] = $this->getOwnerRecord()->karyawan_id;
                        return $data;
                    })
                    ->after(fn (PembPinjaman $record) => app(TreasuryService::class)->syncPinjamanManualPayment($record)),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->after(fn (PembPinjaman $record) => app(TreasuryService::class)->syncPinjamanManualPayment($record)),
                Tables\Actions\DeleteAction::make()
                    ->before(fn (PembPinjaman $record) => app(TreasuryService::class)->deletePinjamanManualPayment($record)),
            ]);
    }
}
