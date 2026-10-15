<?php

namespace App\Filament\Admin\Resources\OperasionalPayResource\Pages;

use App\Filament\Admin\Resources\OperasionalPayResource;
use App\Services\Finance\TreasuryService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateOperasionalPay extends CreateRecord
{
    protected static string $resource = OperasionalPayResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(TreasuryService::class)->recordOperasional($data);
    }
}
