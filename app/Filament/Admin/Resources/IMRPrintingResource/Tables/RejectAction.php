<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Tables;

use App\Models\ImrPrinting;
use App\Services\IMR\IMRPrintingService;
use Filament\Notifications\Notification;
use Filament\Tables;

class RejectAction
{
    public static function make(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('reject')

            ->label('Reject')

            ->icon('heroicon-o-x-circle')

            ->color('danger')

            ->requiresConfirmation()

            ->modalHeading('Reject Internal Material Request')

            ->modalDescription(
                'Apakah Anda yakin ingin menolak IMR ini?'
            )

            ->visible(
                fn (ImrPrinting $record)
                    => $record->status === 'Pending'
            )

            ->action(
                fn (ImrPrinting $record)
                    => self::reject($record)
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    protected static function reject(
        ImrPrinting $record
    ): void
    {
        try {

            app(IMRPrintingService::class)
                ->reject($record);

            Notification::make()

                ->title('IMR berhasil direject.')

                ->success()

                ->send();

        } catch (\Throwable $e) {

            Notification::make()

                ->title('Reject gagal')

                ->body($e->getMessage())

                ->danger()

                ->persistent()

                ->send();

        }
    }
}