<?php

namespace App\Filament\Admin\Resources\InvoiceResource\RelationManagers;

use App\Filament\Admin\Resources\InvPaymentResource;
use App\Models\InvPayment;
use App\Services\Finance\InvoicePaymentService;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';
    protected static ?string $title = 'Riwayat Pembayaran / Cicilan Customer';
    protected static ?string $icon = 'heroicon-o-banknotes';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('tanggal_bayar', 'desc')
            ->columns([
                TextColumn::make('tanggal_bayar')
                    ->label('Tgl Bayar')
                    ->date('d/m/Y')
                    ->sortable(),

                TextColumn::make('metodeBayar.metode_pembayaran')
                    ->label('Metode')
                    ->badge()
                    ->color('info'),

                TextColumn::make('coa.nama_akun')
                    ->label('Rekening Penerima')
                    ->formatStateUsing(fn ($record) => $record->coa ? trim($record->coa->kode_akun) . ' - ' . $record->coa->nama_akun : '-'),

                TextColumn::make('nominal_bayar')
                    ->label('Nominal Diterima')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold')
                    ->color('success'),

                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(40),
            ])
            ->headerActions([
                Action::make('createPayment')
                    ->label('+ Catat Pembayaran / Cicilan')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->visible(fn () => (float) $this->getOwnerRecord()->sisa_tagihan > 0)
                    ->url(fn () => InvPaymentResource::getUrl('create', ['invoice' => $this->getOwnerRecord()->id])),
            ])
            ->actions([
                Action::make('editPayment')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn (InvPayment $record) => InvPaymentResource::getUrl('edit', ['record' => $record])),

                Tables\Actions\DeleteAction::make()
                    ->using(fn (InvPayment $record) => app(InvoicePaymentService::class)->deletePayment($record)),
            ]);
    }
}
