<?php

namespace App\Filament\Admin\Resources\TransFakturResource\Pages;

use App\Filament\Admin\Resources\TransFakturResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTransFaktur extends ViewRecord
{
    protected static string $resource =
        TransFakturResource::class;

    protected static string $view =
        'view-trans-faktur';

    protected function getHeaderActions(): array
    {
        return [];
    }
}