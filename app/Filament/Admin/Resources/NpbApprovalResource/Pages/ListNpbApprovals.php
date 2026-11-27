<?php

namespace App\Filament\Admin\Resources\NpbApprovalResource\Pages;

use App\Filament\Admin\Resources\NpbApprovalResource;
use App\Filament\Admin\Resources\NpbRequestResource;
use App\Models\NpbRequest;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListNpbApprovals extends ListRecords
{
    protected static string $resource = NpbApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('backNpb')
                ->label('← Kembali ke NPB & LPJ')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url(fn () => NpbRequestResource::getUrl('index')),
        ];
    }

    public function getTabs(): array
    {
        return [
            'semua' => Tab::make('Semua NPB')
                ->badge(NpbRequest::count()),

            'pending' => Tab::make('Menunggu Approval')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['DRAFT', 'SUBMITTED', 'REVIEWED']))
                ->badge(NpbRequest::whereIn('status', ['DRAFT', 'SUBMITTED', 'REVIEWED'])->count())
                ->badgeColor('warning'),

            'approved' => Tab::make('Sudah Approved')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('status', ['APPROVED', 'DISBURSED', 'PARTIAL_SETTLED', 'SETTLED']))
                ->badge(NpbRequest::whereIn('status', ['APPROVED', 'DISBURSED', 'PARTIAL_SETTLED', 'SETTLED'])->count())
                ->badgeColor('success'),

            'rejected' => Tab::make('Ditolak')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'REJECTED'))
                ->badge(NpbRequest::where('status', 'REJECTED')->count())
                ->badgeColor('danger'),
        ];
    }
}
