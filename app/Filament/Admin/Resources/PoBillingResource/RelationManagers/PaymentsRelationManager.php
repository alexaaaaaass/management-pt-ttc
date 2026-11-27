<?php

namespace App\Filament\Admin\Resources\PoBillingResource\RelationManagers;

use App\Filament\Admin\Resources\PoBillPayResource;
use App\Models\PoBillPay;
use App\Services\Finance\PoBillPayService;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Riwayat Payment Order (Pembayaran PO)';
    protected static ?string $icon = 'heroicon-o-banknotes';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal_pembayaran', 'desc')
            ->columns([
                TextColumn::make('kode_pembayaran')
                    ->label('No. Bukti')
                    ->badge()
                    ->color('primary'),

                TextColumn::make('tanggal_pembayaran')
                    ->label('Tgl Bayar')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('total_pembayaran')
                    ->label('Nominal Dibayar')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('karyawan.nama')
                    ->label('PIC'),
            ])
            ->headerActions([
                Action::make('createPayment')
                    ->label('+ Bayar Tagihan PO')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->visible(fn () => (float) $this->getOwnerRecord()->sisa_tagihan > 0)
                    ->url(fn () => PoBillPayResource::getUrl('create', ['po_billing_id' => $this->getOwnerRecord()->id])),
            ])
            ->actions([
                Action::make('previewPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (PoBillPay $record) => route('finance.voucher.po-bill-pay.preview', $record))
                    ->openUrlInNewTab(),

                Action::make('editPayment')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (PoBillPay $record) => PoBillPayResource::getUrl('edit', ['record' => $record])),

                Tables\Actions\DeleteAction::make()
                    ->using(fn (PoBillPay $record) => app(PoBillPayService::class)->deletePayment($record)),
            ]);
    }
}
