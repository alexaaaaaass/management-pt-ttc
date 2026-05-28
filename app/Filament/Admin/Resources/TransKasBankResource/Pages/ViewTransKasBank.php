<?php

namespace App\Filament\Admin\Resources\TransKasBankResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use App\Filament\Admin\Resources\TransKasBankResource;

class ViewTransKasBank extends ViewRecord
{
    protected static string $resource =
        TransKasBankResource::class;

    protected static string $view =
        'view';
}