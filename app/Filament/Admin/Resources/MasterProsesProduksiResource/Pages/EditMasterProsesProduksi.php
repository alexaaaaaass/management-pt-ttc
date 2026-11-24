<?php

namespace App\Filament\Admin\Resources\MasterProsesProduksiResource\Pages;

use App\Filament\Admin\Resources\MasterProsesProduksiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMasterProsesProduksi extends EditRecord
{
    protected static string $resource = MasterProsesProduksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
