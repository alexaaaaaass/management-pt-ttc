<?php

namespace App\Filament\Admin\Resources\BlokirResource\Pages;

use App\Filament\Admin\Resources\BlokirResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBlokir extends EditRecord
{
    protected static string $resource = BlokirResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
