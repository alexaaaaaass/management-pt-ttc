<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CoaLedgerResource\Pages;
use App\Models\CoaMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\Summarizers\Sum;
use Illuminate\Database\Eloquent\Builder;

class CoaLedgerResource extends Resource
{
    protected static ?string $model = CoaMovement::class;

    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'General Ledger';
    protected static ?string $modelLabel = 'General Ledger';
    protected static ?string $pluralModelLabel = 'General Ledger';
    protected static ?int $navigationSort = 11;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['coa', 'user'])
            ->orderBy('tanggal')
            ->orderBy('id');
    }

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('coa.kode_akun')
                    ->label('Kode Akun')
                    ->formatStateUsing(fn ($state) => trim((string) $state))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('coa.nama_akun')
                    ->label('Nama Akun')
                    ->searchable()
                    ->sortable()
                    ->weight('medium'),

                Tables\Columns\TextColumn::make('reference_type')
                    ->label('Sumber Transaksi')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'TRANS_KAS' => 'Kas',
                        'TRANS_BANK' => 'Bank',
                        'OPERASIONAL_PAY' => 'Operasional',
                        'INV_PAYMENT' => 'Pembayaran Invoice',
                        'PO_BILL_PAY' => 'Pembayaran Tagihan PO',
                        'PAYROLL' => 'Payroll',
                        default => $state ?: '-',
                    })
                    ->badge(),

                Tables\Columns\TextColumn::make('reference_no')
                    ->label('No. Referensi')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('debit')
                    ->label('Debit')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight()
                    ->summarize(
                        Sum::make()
                            ->label('Total Debit')
                            ->numeric(decimalPlaces: 2)
                    ),

                Tables\Columns\TextColumn::make('kredit')
                    ->label('Kredit')
                    ->numeric(decimalPlaces: 2)
                    ->alignRight()
                    ->summarize(
                        Sum::make()
                            ->label('Total Kredit')
                            ->numeric(decimalPlaces: 2)
                    ),

Tables\Columns\TextColumn::make('balance_after')
    ->label('Saldo Berjalan')
    ->state(function ($record) {
        $saldoAwal = (float) ($record->coa?->saldo_debit ?? 0)
            - (float) ($record->coa?->saldo_kredit ?? 0);

        $mutasi = CoaMovement::query()
            ->where('coa_id', $record->coa_id)
            ->where(function ($query) use ($record) {
                $query->where('tanggal', '<', $record->tanggal)
                    ->orWhere(function ($query) use ($record) {
                        $query->whereDate(
                            'tanggal',
                            $record->tanggal->toDateString()
                        )->where('id', '<=', $record->id);
                    });
            })
            ->selectRaw(
                'COALESCE(SUM(debit), 0) as total_debit,
                 COALESCE(SUM(kredit), 0) as total_kredit'
            )
            ->first();

        return $saldoAwal
            + (float) $mutasi->total_debit
            - (float) $mutasi->total_kredit;
    })
    ->money('IDR')
    ->alignRight()
    ->weight('bold'),


                Tables\Columns\TextColumn::make('remarks')
                    ->label('Keterangan')
                    ->searchable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Operator')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('coa_id')
                    ->label('Akun COA')
                    ->relationship('coa', 'nama_akun')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('reference_type')
                    ->label('Tipe Dokumen')
                    ->options([
                        'TRANS_KAS' => 'Kas',
                        'TRANS_BANK' => 'Bank',
                        'OPERASIONAL_PAY' => 'Operasional',
                        'INV_PAYMENT' => 'Pembayaran Invoice',
                        'PO_BILL_PAY' => 'Pembayaran Tagihan PO',
                        'PAYROLL' => 'Payroll',
                    ]),

                Tables\Filters\Filter::make('tanggal')
                    ->label('Periode')
                    ->form([
                        Forms\Components\DatePicker::make('dari')
                            ->label('Tanggal Mulai'),
                        Forms\Components\DatePicker::make('sampai')
                            ->label('Tanggal Akhir'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['dari'] ?? null,
                                fn (Builder $q, $date) =>
                                    $q->whereDate('tanggal', '>=', $date)
                            )
                            ->when(
                                $data['sampai'] ?? null,
                                fn (Builder $q, $date) =>
                                    $q->whereDate('tanggal', '<=', $date)
                            );
                    }),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoaLedgers::route('/'),
        ];
    }
}