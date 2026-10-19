<?php

namespace App\Filament\Admin\Resources\ApprovalFormResource\Pages;

use App\Filament\Admin\Resources\ApprovalFormResource;
use Filament\Resources\Pages\CreateRecord;

class CreateApprovalForm extends CreateRecord
{
    protected static string $resource = ApprovalFormResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
