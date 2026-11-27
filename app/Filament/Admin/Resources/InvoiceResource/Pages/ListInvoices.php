<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use App\Filament\Admin\Resources\InvPaymentResource;
use App\Filament\Admin\Widgets\PiutangJatuhTempoWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    public function shouldShowPageTableSummary(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('riwayatPembayaran')
                ->label('Riwayat Pembayaran / Cicilan')
                ->icon('heroicon-o-banknotes')
                ->color('info')
                ->url(fn () => InvPaymentResource::getUrl('index')),

            Actions\CreateAction::make()
                ->label('+ Buat Invoice'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PiutangJatuhTempoWidget::class,
        ];
    }
}
