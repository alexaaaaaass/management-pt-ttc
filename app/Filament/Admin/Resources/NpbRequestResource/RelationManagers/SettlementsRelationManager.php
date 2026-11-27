<?php

namespace App\Filament\Admin\Resources\NpbRequestResource\RelationManagers;

use App\Filament\Admin\Resources\NpbSettlementResource;
use App\Models\NpbSettlement;
use App\Services\Finance\NpbService;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SettlementsRelationManager extends RelationManager
{
    protected static string $relationship = 'settlements';
    protected static ?string $title = 'Penyelesaian NPB (LPJ)';
    protected static ?string $icon = 'heroicon-o-clipboard-document-check';

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('nomor_penyelesaian')
                    ->label('No. LPJ')
                    ->weight('bold')
                    ->color('primary'),

                TextColumn::make('tanggal_penyelesaian')
                    ->label('Tanggal LPJ')
                    ->date('d M Y'),

                TextColumn::make('label_periode')
                    ->label('Periode'),

                TextColumn::make('total_anggaran')
                    ->label('Anggaran')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight(),

                TextColumn::make('total_realisasi')
                    ->label('Realisasi')
                    ->formatStateUsing(fn ($state) => 'Rp ' . number_format((float) $state, 0, ',', '.'))
                    ->alignRight()
                    ->weight('bold'),

                TextColumn::make('sisa_dana')
                    ->label('Sisa Dana')
                    ->formatStateUsing(fn ($state) => ((float) $state < 0 ? '-' : '') . 'Rp ' . number_format(abs((float) $state), 0, ',', '.'))
                    ->color(fn ($state) => (float) $state >= 0 ? 'info' : 'danger')
                    ->alignRight()
                    ->weight('bold'),

                IconColumn::make('is_posted_to_ledger')
                    ->label('GL')
                    ->boolean()
                    ->alignCenter(),

                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => NpbSettlement::STATUS_OPTIONS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        'DRAFT'     => 'gray',
                        'SUBMITTED' => 'info',
                        'VERIFIED'  => 'warning',
                        'APPROVED'  => 'success',
                        default     => 'gray',
                    }),
            ])
            ->headerActions([
                Action::make('createLpj')
                    ->label('+ Buat Penyelesaian NPB (LPJ)')
                    ->icon('heroicon-o-plus-circle')
                    ->color('success')
                    ->url(fn () => NpbSettlementResource::getUrl('create', ['npb_request_id' => $this->getOwnerRecord()->id])),
            ])
            ->actions([
                Action::make('previewPdf')
                    ->label('PDF')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (NpbSettlement $record) => route('finance.npb-settlement.preview', $record))
                    ->openUrlInNewTab(),

                Action::make('editLpj')
                    ->label('Buka / Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->color('warning')
                    ->url(fn (NpbSettlement $record) => NpbSettlementResource::getUrl('edit', ['record' => $record])),

                Action::make('approveLpj')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (NpbSettlement $r) => $r->status !== 'APPROVED')
                    ->requiresConfirmation()
                    ->action(function (NpbSettlement $record) {
                        $record->update(['status' => 'APPROVED', 'disetujui_at' => now()]);
                        $svc = app(NpbService::class);
                        $svc->recalculateSettlement($record->refresh());
                        $msg = "LPJ {$record->nomor_penyelesaian} disetujui.";
                        if ($record->posting_mode === 'AUTO_JOURNAL') {
                            $posted = $svc->postSettlementToLedger($record->refresh());
                            $msg .= " ({$posted} pos diposting ke Buku Besar)";
                        }
                        Notification::make()->title($msg)->success()->send();
                    }),
            ]);
    }
}
