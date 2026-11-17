<?php

namespace App\Filament\Admin\Pages;

use Filament\Pages\Page;

class RekapCutiTahunan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    protected static string $view =
        'filament.rekap-cuti-tahunan';

    protected static ?string $navigationLabel =
        'Cuti Tahunan';

    protected static ?string $navigationGroup =
        'HRD';
}