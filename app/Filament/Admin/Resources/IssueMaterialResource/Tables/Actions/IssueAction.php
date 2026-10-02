<?php

namespace App\Filament\Admin\Resources\IssueMaterialResource\Tables\Actions;

use App\Models\ImrPrinting;
use App\Services\IMR\IMRPrintingService;
use Filament\Forms;
use Filament\Tables;
use Filament\Notifications\Notification;

class IssueAction
{
    public static function make(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('issue')

            ->label('Proses Material')

            ->icon('heroicon-o-truck')

            ->color('primary')

            ->modalWidth('7xl')

            ->modalHeading('Proses Pengeluaran Material')

            ->modalDescription(
                'Masukkan jumlah material yang akan dikeluarkan dari gudang.'
            )

            ->form(fn(ImrPrinting $record) => self::form($record))

          ->action(function (
    ImrPrinting $record,
    array $data
) {

    try {

        app(IMRPrintingService::class)
            ->issue($record, $data);

        Notification::make()

            ->title('Material berhasil dikeluarkan')

            ->body(
                "Material berhasil diproses dari gudang."
            )

            ->success()

            ->send();

    } catch (\Throwable $e) {

        Notification::make()

            ->title('Proses gagal')

            ->body($e->getMessage())

            ->danger()

            ->persistent()

            ->send();

    }

});
    }

    protected static function form(ImrPrinting $record): array
{
    $record->loadMissing([
        'items.item.stock',
    ]);

    $schema = [];

    foreach ($record->items as $index => $item) {

        $remaining =
            $item->qty_approved -
            $item->qty_issued;

        $available =
            $item->item->stock?->available ?? 0;

        $schema[] = Forms\Components\Section::make(
            $item->item->nama_master_item
        )

            ->icon('heroicon-o-cube')

            ->description(
                'Informasi material dan jumlah yang akan dikeluarkan dari gudang.'
            )

            ->collapsible(false)

            ->schema([

                Forms\Components\Grid::make(4)

                    ->schema([

                        Forms\Components\Placeholder::make("approved{$index}")

                            ->label('Qty Disetujui')

                            ->content(
                                number_format(
                                    $item->qty_approved,
                                    2
                                )
                            ),

                        Forms\Components\Placeholder::make("issued{$index}")

                            ->label('Sudah Dikeluarkan')

                            ->content(
                                number_format(
                                    $item->qty_issued,
                                    2
                                )
                            ),

                        Forms\Components\Placeholder::make("remaining{$index}")

                            ->label('Sisa yang Harus Dikeluarkan')

                            ->content(
                                number_format(
                                    $remaining,
                                    2
                                )
                            ),

                        Forms\Components\Placeholder::make("stock{$index}")

                            ->label('Stok Gudang')

                            ->content(
                                number_format(
                                    $available,
                                    2
                                )
                            ),

                    ]),

                Forms\Components\TextInput::make(
                    "items.$index.qty_issue"
                )

                    ->label('Jumlah Material Keluar')

                    ->numeric()

                    ->default(0)

                    ->step(0.01)

                    ->suffix(
                        $item->satuan
                    )

                    ->minValue(0)

                    ->maxValue(
                        min(
                            $remaining,
                            $available
                        )
                    )

                    ->helperText(
                        "Maksimal " .
                        number_format(
                            min($remaining, $available),
                            2
                        )
                    )

                    ->required(),

            ]);

    }

    return $schema;
}
}