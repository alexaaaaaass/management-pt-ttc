<?php

namespace App\Filament\Admin\Resources\ReportPoVsPbResource\Pages;

use App\Filament\Admin\Resources\ReportPoVsPbResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditReportPoVsPb extends EditRecord
{
    protected static string $resource = ReportPoVsPbResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
