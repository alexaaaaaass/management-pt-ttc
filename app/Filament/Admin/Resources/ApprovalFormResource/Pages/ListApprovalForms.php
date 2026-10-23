<?php

namespace App\Filament\Admin\Resources\ApprovalFormResource\Pages;

use App\Filament\Admin\Resources\ApprovalFormResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListApprovalForms extends ListRecords
{
    protected static string $resource = ApprovalFormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Approval Form'),
        ];
    }
}
