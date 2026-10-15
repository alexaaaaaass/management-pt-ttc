<?php

namespace App\Filament\Admin\Resources\SubcountInResource\Pages;

use App\Filament\Admin\Resources\SubcountInResource;
use App\Services\Subcontract\SubcontractService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubcountIn extends EditRecord
{
    protected static string $resource = SubcountInResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(fn ($record) => app(SubcontractService::class)->deleteSubcountIn($record)),
        ];
    }
}
