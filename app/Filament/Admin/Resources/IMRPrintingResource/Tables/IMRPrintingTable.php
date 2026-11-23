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
                    // CHANGE STATUS (Aman — tidak boleh bypass Approve!)
                    // =============================
                    Tables\Actions\Action::make('changeStatus')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn (ImrPrinting $record) => ! in_array($record->status, ['Issued', 'Closed'], true))
                        ->form(fn (ImrPrinting $record) => [
                            Forms\Components\Select::make('status')
                                ->label('Status Baru')
                                ->options(function () use ($record) {
                                    $hasIssued = $record->items->sum('qty_issued') > 0 || $record->isPlateIssued();
                                    $opts = ['Closed' => 'Closed (Tutup IMR)'];
                                    if (! $hasIssued) {
                                        $opts = ['Pending' => 'Pending (Kembalikan agar bisa di-Approve)'] + $opts;
                                    }

                                    return $opts;
                                })
                                ->helperText('Untuk menyetujui IMR beserta qty material & plate, wajib gunakan tombol "Approve" saat status Pending.')
                                ->required(),
                        ])
                        ->action(function (ImrPrinting $record, array $data) {
                            $newStatus = $data['status'];
                            if ($newStatus === 'Pending') {
                                \Illuminate\Support\Facades\DB::transaction(function () use ($record) {
                                    $record->loadMissing('items');
                                    foreach ($record->items as $item) {
                                        $prevApproved = (float) $item->qty_approved;
                                        if ($prevApproved > 0) {
                                            $stock = \App\Models\MaterialStock::where('item_id', $item->item_id)
                                                ->lockForUpdate()
                                                ->first();
                                            if ($stock) {
                                                $stock->update([
                                                    'allocation' => max(0, round((float) $stock->allocation - $prevApproved, 4)),
                                                ]);
                                            }
                                            $item->update(['qty_approved' => 0]);
                                        }
                                    }

                                    $record->update([
                                        'status'         => 'Pending',
                                        'plate_approved' => false,
                                    ]);
                                });
                            } else {
                                $record->update([
                                    'status' => $newStatus,
                                ]);
                            }
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