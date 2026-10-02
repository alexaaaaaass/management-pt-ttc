<?php

namespace App\Filament\Admin\Resources\ExternalReturnResource\Pages;

use App\Filament\Admin\Resources\ExternalReturnResource;
use Filament\Resources\Pages\EditRecord;

class EditExternalReturn extends EditRecord
{
    protected static string $resource =
        ExternalReturnResource::class;

    protected function authorizeAccess(): void
    {
        abort_if(
            $this->record->status == 'approved',
            403,
            'External Return sudah diapprove.'
        );
    }
}