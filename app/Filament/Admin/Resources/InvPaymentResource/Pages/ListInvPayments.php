<?php

namespace App\Filament\Admin\Resources\InvPaymentResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use App\Filament\Admin\Resources\InvPaymentResource;
use App\Filament\Admin\Widgets\PiutangJatuhTempoWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInvPayments extends ListRecords
{
    protected static string $resource = InvPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backInvoices')
                ->label('← Kembali ke Invoice')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => InvoiceResource::getUrl('index')),

            Actions\CreateAction::make()
                ->label('+ Catat Pembayaran'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PiutangJatuhTempoWidget::class,
        ];
    }
}
