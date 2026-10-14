<?php

namespace App\Filament\Admin\Resources\MasterCOAResource\Pages;

use App\Filament\Admin\Resources\MasterCOAResource;
use App\Services\Finance\TreasuryService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterCOA extends EditRecord
{
    protected static string $resource = MasterCOAResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => !app(TreasuryService::class)->canDeleteCoa($record)),
        ];
    }
}
