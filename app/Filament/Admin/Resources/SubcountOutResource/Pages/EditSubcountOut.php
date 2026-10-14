<?php

namespace App\Filament\Admin\Resources\SubcountOutResource\Pages;

use App\Filament\Admin\Resources\SubcountOutResource;
use App\Services\Subcontract\SubcontractService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubcountOut extends EditRecord
{
    protected static string $resource = SubcountOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->hidden(fn ($record) => !app(SubcontractService::class)->canDeleteSubcountOut($record))
                ->using(fn ($record) => app(SubcontractService::class)->deleteSubcountOut($record)),
        ];
    }
}
