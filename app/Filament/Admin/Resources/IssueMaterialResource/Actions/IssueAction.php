<?php

namespace App\Filament\Admin\Resources\IssueMaterialResource\Actions;

use App\Models\ImrPrinting;
use App\Services\IMR\IMRPrintingService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;

class IssueAction
{
    public static function make(): Tables\Actions\Action
    {
        return Tables\Actions\Action::make('issue')

            ->label('Issue')

            ->icon('heroicon-o-arrow-up-on-square')

            ->color('success')

            ->visible(fn (ImrPrinting $record) =>
                in_array($record->status, [
                    'Approved',
                    'Partial Issued'
                ])
            )

            ->modalHeading('Issue Material')

            ->modalSubmitActionLabel('Issue')

            ->modalWidth('7xl')

            ->form(fn (ImrPrinting $record) =>
                self::issueForm($record)
            )

            ->action(fn (ImrPrinting $record, array $data) =>
                self::issue($record, $data)
            );
    }

    protected static function issue(
        ImrPrinting $record,
        array $data
    ): void
    {
        try {

            app(IMRPrintingService::class)
                ->issue($record, $data);

            Notification::make()

                ->title('Material berhasil di Issue')

                ->success()

                ->send();

        } catch (\Throwable $e) {

            Notification::make()

                ->title('Issue gagal')

                ->body($e->getMessage())

                ->danger()

                ->persistent()

                ->send();

        }
    }

    protected static function issueForm(
        ImrPrinting $record
    ): array
    {
        $record->loadMissing([
            'items.item.stock'
        ]);

        $schema = [];

        foreach ($record->items as $index => $item) {

            $remaining =
                $item->qty_approved -
                $item->qty_issued;

            $schema[] = Forms\Components\Grid::make(5)

                ->schema([

                    Forms\Components\Placeholder::make("material{$index}")
                        ->label('Material')
                        ->content(
                            $item->item->nama_master_item
                        ),

                    Forms\Components\Placeholder::make("approved{$index}")
                        ->label('Approved')
                        ->content($item->qty_approved),

                    Forms\Components\Placeholder::make("issued{$index}")
                        ->label('Issued')
                        ->content($item->qty_issued),

                    Forms\Components\Placeholder::make("remaining{$index}")
                        ->label('Remaining')
                        ->content($remaining),

                    Forms\Components\TextInput::make(
                        "items.$index.qty_issue"
                    )
                        ->label('Issue')
                        ->numeric()
                        ->default(0)
                        ->minValue(0)
                        ->maxValue($remaining)
                        ->step(0.01),

                ]);

        }

        return $schema;
    }
}