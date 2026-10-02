<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Tables;

use App\Models\ImrPrinting;
use App\Services\IMR\IMRPrintingService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;

class ApproveAction
{
    public static function make(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('approve')

            ->label('Approve')

            ->icon('heroicon-o-check')

            ->color('success')

            ->visible(
                fn (ImrPrinting $record)
                    => $record->status === 'Pending'
            )

            ->requiresConfirmation()

            ->modalWidth('6xl')

            ->modalHeading('Approve Internal Material Request')

            ->modalDescription(
                'Masukkan jumlah material yang akan disetujui.'
            )

            ->modalSubmitActionLabel('Approve')

            ->form(

                fn (ImrPrinting $record)
                    => self::approvalForm($record)

            )

        ->action(function (ImrPrinting $record, array $data) {

    self::approve($record, $data);

});
    }

    /*
    |--------------------------------------------------------------------------
    | Modal Form
    |--------------------------------------------------------------------------
    */

    protected static function approvalForm(
        ImrPrinting $record
    ): array
    {
        $schema = [];

        $record->loadMissing([
            'items.item.stock',
            'items.item.satuan'
        ]);

        foreach ($record->items as $index => $item) {

            $schema[] = self::itemGrid(
                $item,
                $index
            );

        }

        return $schema;
    }

        /*
    |--------------------------------------------------------------------------
    | Item Grid
    |--------------------------------------------------------------------------
    */

    protected static function itemGrid($item, int $index): Forms\Components\Grid
    {
        return Forms\Components\Grid::make(5)
            ->schema([

                Forms\Components\Placeholder::make("item{$index}")
                    ->label('Material')
                    ->content(
                        $item->item?->nama_master_item ?? '-'
                    ),

                Forms\Components\Placeholder::make("stock{$index}")
                    ->label('Stock')
                    ->content(
                        $item->item?->stock?->on_hand ?? 0
                    ),

                Forms\Components\TextInput::make(
                    "items.{$index}.qty_request"
                )
                    ->label('Request')
                    ->disabled()
                    ->default($item->qty_request),

                Forms\Components\TextInput::make(
                    "items.{$index}.qty_approved"
                )
                    ->label('Approve')
                    ->numeric()
                    ->required()
                    ->default($item->qty_request)
                    ->minValue(0)
                    ->maxValue($item->qty_request),

                Forms\Components\Placeholder::make("satuan{$index}")
                    ->label('Satuan')
                    ->content(
                        $item->satuan
                    ),

            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    protected static function approve(
        ImrPrinting $record,
        array $data
    ): void
    {

        try {

            app(IMRPrintingService::class)
                ->approve(
                    $record,
                    $data
                );

            Notification::make()

                ->title('IMR berhasil diapprove.')

                ->success()

                ->send();

        } catch (\Throwable $e) {

            Notification::make()

                ->title('Approve gagal')

                ->body($e->getMessage())

                ->danger()

                ->persistent()

                ->send();

        }
    }

}