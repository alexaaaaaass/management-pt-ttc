<?php

namespace App\Filament\Admin\Resources\IMRDieMakingResource\Tables;

use App\Filament\Admin\Resources\IMRPrintingResource\Tables\ApproveAction;
use App\Filament\Admin\Resources\IMRPrintingResource\Tables\RejectAction;
use App\Models\IMRPrinting;
use App\Models\MaterialStock;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class IMRDieMakingTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_imr')
                    ->label('No IMR')
                    ->weight('bold')
                    ->copyable()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_request')
                    ->label('Tanggal Request')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('spk.no_spk')
                    ->label('No SPK')
                    ->description(fn (IMRPrinting $record): string => $record->spk?->salesOrder?->itemable?->nama_barang
                        ?? ($record->spk?->salesOrder?->product_name ?? '-'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('departemen.nama_departemen')
                    ->label('Departemen')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Jumlah Item')
                    ->counts('items')
                    ->badge()
                    ->alignCenter(),

                Tables\Columns\TextColumn::make('total_request')
                    ->label('Qty Request')
                    ->alignEnd()
                    ->getStateUsing(fn (IMRPrinting $record) => number_format((float) $record->items->sum('qty_request'), 2, ',', '.')),

                Tables\Columns\TextColumn::make('progress_issue')
                    ->label('Keluar / Approved')
                    ->alignEnd()
                    ->getStateUsing(function (IMRPrinting $record): string {
                        $approved = (float) $record->items->sum('qty_approved');
                        $issued = (float) $record->items->sum('qty_issued');

                        if ($approved <= 0) {
                            return '-';
                        }

                        return number_format($issued, 2, ',', '.') . ' / ' . number_format($approved, 2, ',', '.');
                    }),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending'        => 'warning',
                        'Approved'       => 'success',
                        'Partial'        => 'info',
                        'Issued'         => 'primary',
                        'Partial Issued' => 'indigo',
                        'Rejected'       => 'danger',
                        'Closed'         => 'gray',
                        default          => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Pending'        => 'Pending',
                        'Approved'       => 'Approved',
                        'Partial'        => 'Partial',
                        'Partial Issued' => 'Partial Issued',
                        'Issued'         => 'Issued',
                        'Rejected'       => 'Rejected',
                        'Closed'         => 'Closed',
                    ]),

                Tables\Filters\SelectFilter::make('spk_id')
                    ->label('No SPK')
                    ->relationship('spk', 'no_spk')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('tanggal_request')
                    ->label('Rentang Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('dari')->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('sampai')->label('Sampai Tanggal'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['dari'] ?? null, fn ($q, $d) => $q->whereDate('tanggal_request', '>=', $d))
                        ->when($data['sampai'] ?? null, fn ($q, $d) => $q->whereDate('tanggal_request', '<=', $d))),
            ])
            ->actions([
                ActionGroup::make([
                    ApproveAction::make(),

                    RejectAction::make(),

                    Tables\Actions\Action::make('changeStatus')
                        ->label('Ubah Status')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->visible(fn (IMRPrinting $record) => ! in_array($record->status, ['Issued', 'Closed'], true))
                        ->form(fn (IMRPrinting $record) => [
                            Forms\Components\Select::make('status')
                                ->label('Status Baru')
                                ->options(function () use ($record) {
                                    $hasIssued = $record->items->sum('qty_issued') > 0;
                                    $opts = ['Closed' => 'Closed (Tutup IMR)'];
                                    if (! $hasIssued) {
                                        $opts = ['Pending' => 'Pending (Kembalikan agar bisa di-Approve)'] + $opts;
                                    }

                                    return $opts;
                                })
                                ->helperText('Untuk menyetujui IMR beserta qty material, wajib gunakan tombol "Approve" saat status Pending.')
                                ->required(),
                        ])
                        ->action(function (IMRPrinting $record, array $data) {
                            $newStatus = $data['status'];
                            if ($newStatus === 'Pending') {
                                DB::transaction(function () use ($record) {
                                    $record->loadMissing('items');
                                    foreach ($record->items as $item) {
                                        $prevApproved = (float) $item->qty_approved;
                                        if ($prevApproved > 0) {
                                            $stock = MaterialStock::where('item_id', $item->item_id)
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

                                    $record->update(['status' => 'Pending']);
                                });
                            } else {
                                $record->update(['status' => $newStatus]);
                            }
                        }),

                    Tables\Actions\Action::make('preview')
                        ->label('Preview PDF')
                        ->icon('heroicon-o-document')
                        ->color('gray')
                        ->url(fn (IMRPrinting $record) => route('imr.preview', $record))
                        ->openUrlInNewTab(),

                    EditAction::make()
                        ->visible(fn (IMRPrinting $record) => $record->status === 'Pending'),

                    DeleteAction::make()
                        ->visible(fn (IMRPrinting $record) => in_array($record->status, ['Pending', 'Rejected'], true)),
                ])->icon('heroicon-m-ellipsis-vertical'),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
