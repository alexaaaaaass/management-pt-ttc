<?php

namespace App\Filament\Admin\Resources\TransKasBankResource\Pages;

use App\Filament\Admin\Resources\TransKasBankResource;
use App\Services\Finance\TreasuryService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditTransKasBank extends EditRecord
{
    protected static string $resource = TransKasBankResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(fn ($record) => app(TreasuryService::class)->deleteBank($record)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(TreasuryService::class)->updateBank($record, $data);
    }
}
