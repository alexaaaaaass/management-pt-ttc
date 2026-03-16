<?php

namespace App\Filament\Admin\Resources\PotTunjanganResource\Pages;

use App\Filament\Admin\Resources\PotTunjanganResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPotTunjangan extends EditRecord
{
    protected static string $resource = PotTunjanganResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
