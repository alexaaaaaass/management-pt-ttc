<?php

namespace App\Filament\Admin\Resources\PackagingResource\Pages;

use App\Filament\Admin\Resources\PackagingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPackaging extends EditRecord
{
    protected static string $resource = PackagingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
