<?php

namespace App\Filament\Admin\Resources\ImrFinishResource\Pages;

use App\Filament\Admin\Resources\ImrFinishResource;
use App\Services\IMRFinish\ImrFinishService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateImrFinish extends CreateRecord
{
    protected static string $resource = ImrFinishResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return app(ImrFinishService::class)->create($data);
    }
}