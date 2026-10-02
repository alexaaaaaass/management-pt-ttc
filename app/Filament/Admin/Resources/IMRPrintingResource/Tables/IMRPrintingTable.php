<?php

namespace App\Filament\Admin\Resources\IMRPrintingResource\Tables;

use App\Filament\Admin\Resources\IMRPrintingResource\Tables\ApproveAction;
use App\Filament\Admin\Resources\IMRPrintingResource\Tables\RejectAction;
use App\Models\ImrPrinting;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;

class IMRPrintingTable
{
    public static function make(Table $table): Table
    {
        return $table

            ->columns(
                Columns::make()
            )

            ->filters(
                Filters::make()
            )

            ->actions([

                ActionGroup::make([

                    ApproveAction::make(),

                    RejectAction::make(),

                    // =============================
                    // CHANGE STATUS
                    // =============================
                    Tables\Actions\Action::make('changeStatus')

                        ->label('Ubah Status')

                        ->icon('heroicon-o-arrow-path')

                        ->color('warning')

                        ->form([

                            Forms\Components\Select::make('status')

                                ->options([

                                    'Pending'  => 'Pending',

                                    'Approved' => 'Approved',

                                    'Partial'  => 'Partial',

                                    'Rejected' => 'Rejected',

                                    'Closed'   => 'Closed',

                                ])

                                ->default(fn ($record) => $record->status)

                                ->required(),

                        ])

                        ->action(function ($record, array $data) {

                            $record->update([

                                'status' => $data['status']

                            ]);

                        }),

                    Tables\Actions\Action::make('preview')

                        ->label('Preview PDF')

                        ->icon('heroicon-o-document')

                        ->color('gray')

                        ->url(fn (ImrPrinting $record) =>
                            route('imr.preview', $record)
                        )

                        ->openUrlInNewTab(),    

                    EditAction::make(),

                    DeleteAction::make(),

                ])
                ->icon('heroicon-m-ellipsis-vertical')

                

            ])

            ->bulkActions([

                DeleteBulkAction::make(),

            ]);
    }
}