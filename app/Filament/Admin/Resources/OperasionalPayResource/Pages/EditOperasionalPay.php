<?php

namespace App\Filament\Admin\Resources\OperasionalPayResource\Pages;

use App\Filament\Admin\Resources\OperasionalPayResource;
use App\Services\Finance\TreasuryService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditOperasionalPay extends EditRecord
{
    protected static string $resource = OperasionalPayResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(fn ($record) => app(TreasuryService::class)->deleteOperasional($record)),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(TreasuryService::class)->updateOperasional($record, $data);
    }
}
