<?php

namespace App\Filament\Admin\Resources\SuratJalanResource\Pages;

use App\Filament\Admin\Resources\SuratJalanResource;
use App\Models\MaterialStock;
use App\Models\SalesOrder;
use App\Models\SuratJalan;
use App\Models\SuratJalanItem;
use App\Services\Inventory\RecordMaterialMovement;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class CreateSuratJalan extends CreateRecord
{
    protected static string $resource = SuratJalanResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $detailItems = $data['items'] ?? [];
        unset($data['items']);

        return app(\App\Services\Dispatch\SuratJalanService::class)->createSuratJalan($data, $detailItems);
    }

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Surat Jalan berhasil dibuat')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return SuratJalanResource::getUrl('index');
    }
}