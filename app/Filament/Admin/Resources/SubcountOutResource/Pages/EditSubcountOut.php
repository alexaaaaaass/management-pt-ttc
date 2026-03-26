<?php

namespace App\Filament\Admin\Resources\SubcountOutResource\Pages;

use App\Filament\Admin\Resources\SubcountOutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubcountOut extends EditRecord
{
    protected static string $resource = SubcountOutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
