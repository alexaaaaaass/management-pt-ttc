<?php

namespace App\Filament\Admin\Resources\PoBillPayResource\Pages;

use App\Filament\Admin\Resources\PoBillPayResource;
use App\Services\Finance\PoBillPayService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditPoBillPay extends EditRecord
{
    protected static string $resource = PoBillPayResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return app(PoBillPayService::class)->updatePayment($record, $data);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(fn ($record) => app(PoBillPayService::class)->deletePayment($record)),
        ];
    }
}
