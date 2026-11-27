<?php

namespace App\Filament\Admin\Resources\NpbRequestResource\Pages;

use App\Filament\Admin\Resources\NpbApprovalResource;
use App\Filament\Admin\Resources\NpbRequestResource;
use App\Filament\Admin\Resources\NpbSettlementResource;
use App\Models\NpbRequest;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNpbRequests extends ListRecords
{
    protected static string $resource = NpbRequestResource::class;

    protected function getHeaderActions(): array
    {
        $pending = NpbRequest::whereIn('status', ['DRAFT', 'SUBMITTED', 'REVIEWED'])->count();

        return [
            Actions\Action::make('openApproval')
                ->label($pending > 0 ? "Approval NPB ({$pending})" : 'Approval NPB')
                ->icon('heroicon-o-check-badge')
                ->color($pending > 0 ? 'warning' : 'success')
                ->url(fn () => NpbApprovalResource::getUrl('index')),

            Actions\Action::make('openSettlements')
                ->label('Daftar LPJ')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('info')
                ->url(fn () => NpbSettlementResource::getUrl('index')),

            Actions\CreateAction::make()
                ->label('+ Buat NPB'),
        ];
    }
}
