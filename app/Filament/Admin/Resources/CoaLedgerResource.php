<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CoaLedgerResource\Pages;
use App\Models\CoaMovement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CoaLedgerResource extends Resource
{
    protected static ?string $model = CoaMovement::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Buku Kas & Bank';
    protected static ?string $modelLabel = 'Buku Kas & Bank';
    protected static ?string $pluralModelLabel = 'Buku Kas & Bank';
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
            ->latest('tanggal')
            ->latest('id');
    }

    public static function form(Form $form): Form
    {
        return $form;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('tanggal')
                    ->label('Tanggal')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('coa.kode_akun')
                    ->label('Kode Akun')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('coa.nama_akun')
                    ->label('Nama Akun')
                    ->searchable()
                    ->weight('medium'),

                Tables\Columns\BadgeColumn::make('reference_type')
                    ->label('Tipe Mutasi')
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'TRANS_KAS'       => 'Kas',
                        'TRANS_BANK'      => 'Bank',
                        'OPERASIONAL_PAY' => 'Operasional',
                        'INV_PAYMENT'     => 'Customer AR',
                        'PO_BILL_PAY'     => 'Supplier AP',
                        'PAYROLL'         => 'Payroll HRD',
                        default           => $state,
                    })
                    ->colors([
                        'primary' => 'TRANS_KAS',
                        'info'    => 'TRANS_BANK',
                        'warning' => 'OPERASIONAL_PAY',
                        'success' => 'INV_PAYMENT',
                        'danger'  => 'PO_BILL_PAY',
                        'purple'  => 'PAYROLL',
                    ]),

                Tables\Columns\TextColumn::make('reference_no')
                    ->label('No. Referensi')
                    ->searchable()
                    ->copyable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('debit')
                    ->label('Debit (+)')
                    ->money('IDR')
                    ->alignRight()
                    ->color(fn ($record) => $record->debit > 0 ? 'success' : null)
                    ->formatStateUsing(fn ($record) => $record->debit > 0
                        ? '+' . number_format($record->debit, 0, ',', '.')
                        : '-'
                    ),

                Tables\Columns\TextColumn::make('kredit')
                    ->label('Kredit (-)')
                    ->money('IDR')
                    ->alignRight()
                    ->color(fn ($record) => $record->kredit > 0 ? 'danger' : null)
                    ->formatStateUsing(fn ($record) => $record->kredit > 0
                        ? '-' . number_format($record->kredit, 0, ',', '.')
                        : '-'
                    ),

                Tables\Columns\TextColumn::make('balance_after')
                    ->label('Saldo Berjalan')
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
                        'TRANS_KAS'       => 'Kas',
                        'TRANS_BANK'      => 'Bank',
                        'OPERASIONAL_PAY' => 'Operasional',
                        'INV_PAYMENT'     => 'Customer AR (Invoice)',
                        'PO_BILL_PAY'     => 'Supplier AP (Tagihan PO)',
                        'PAYROLL'         => 'Payroll HRD (Gaji Karyawan)',
                    ]),

                Tables\Filters\Filter::make('tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->label('Tanggal Mulai'),
                        Forms\Components\DatePicker::make('sampai')->label('Tanggal Akhir'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['dari'], fn ($q, $d) => $q->whereDate('tanggal', '>=', $d))
                            ->when($data['sampai'], fn ($q, $d) => $q->whereDate('tanggal', '<=', $d));
                    }),
            ])
            ->actions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCoaLedgers::route('/'),
        ];
    }
}
