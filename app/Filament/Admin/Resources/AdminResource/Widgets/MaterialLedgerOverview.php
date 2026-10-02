<?php

namespace App\Filament\Admin\Widgets;

use App\Models\MaterialMovement;
use App\Models\MaterialStock;
use Filament\Widgets\Widget;

class MaterialLedgerOverview extends Widget
{
    protected static string $view =
        'filament.widgets.material-ledger-overview';

    protected int|string|array $columnSpan = 'full';

    protected function getViewData(): array
    {
        $today = now()->toDateString();

        return [

            'lpb' => MaterialMovement::where(
                'movement_type',
                'LPB'
            )->whereDate(
                'created_at',
                $today
            )->count(),

            'approve' => MaterialMovement::where(
                'movement_type',
                'APPROVE'
            )->whereDate(
                'created_at',
                $today
            )->count(),

            'issue' => MaterialMovement::where(
                'movement_type',
                'ISSUE'
            )->whereDate(
                'created_at',
                $today
            )->count(),

            'return' => MaterialMovement::where(
                'movement_type',
                'RETURN'
            )->whereDate(
                'created_at',
                $today
            )->count(),

            'qtyMasuk' => MaterialMovement::where(
                'movement_type',
                'LPB'
            )->whereDate(
                'created_at',
                $today
            )->sum('qty'),

            'qtyIssue' => abs(
                MaterialMovement::where(
                    'movement_type',
                    'ISSUE'
                )->whereDate(
                    'created_at',
                    $today
                )->sum('qty')
            ),

            'materialAktif' => MaterialStock::count(),

            'aktivitas' => MaterialMovement::whereDate(
                'created_at',
                $today
            )->count(),

        ];
    }
}