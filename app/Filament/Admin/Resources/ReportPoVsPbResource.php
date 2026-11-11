<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ReportPoVsPbResource\Pages;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ReportPoVsPbResource extends Resource
{
    protected static ?string $model = PurchaseOrder::class;
    protected static ?string $navigationGroup = 'Report';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationLabel = 'Report PO VS PB';
    protected static ?string $modelLabel = 'Report PO vs Penerimaan Barang';
    protected static ?string $pluralModelLabel = 'Report Analisa Purchase Order (PO) vs Penerimaan Barang (PB)';

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'supplier',
                'purchaseRequest',
                'items.item',
                'penerimaanBarangs.items',
            ])
            ->orderBy('po_date', 'desc')
            ->orderBy('id', 'desc');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_po')
                    ->label('No. PO')
                    ->searchable()
                    ->sortable()
                    ->weight('bold')
                    ->copyable(),

                Tables\Columns\TextColumn::make('po_date')
                    ->label('Tanggal PO')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('rincian_item')
                    ->label('Material / Bahan Baku')
                    ->state(function (PurchaseOrder $record): string {
                        $names = $record->items
                            ->map(fn ($it) => $it->item?->nama_master_item)
                            ->filter()
                            ->values();
                        return $names->isNotEmpty() ? $names->implode(', ') : '-';
                    })
                    ->wrap(),

                Tables\Columns\TextColumn::make('total_qty_po')
                    ->label('Qty PO')
                    ->state(fn (PurchaseOrder $record): float => (float) $record->items->sum('qty_po'))
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('total_qty_pb')
                    ->label('Qty Diterima (PB)')
                    ->state(fn (PurchaseOrder $record): float => (float) $record->items->sum('qty_received'))
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignRight()
                    ->color('success'),

                Tables\Columns\TextColumn::make('qty_outstanding')
                    ->label('Selisih / Sisa')
                    ->state(fn (PurchaseOrder $record): float => max(
                        0,
                        (float) $record->items->sum('qty_po') - (float) $record->items->sum('qty_received')
                    ))
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->alignRight()
                    ->color(fn ($state) => (float) $state > 0 ? 'warning' : 'gray'),

                Tables\Columns\TextColumn::make('no_pb_list')
                    ->label('No. Penerimaan (PB)')
                    ->state(function (PurchaseOrder $record): string {
                        $pbs = $record->penerimaanBarangs->pluck('no_penerimaan')->filter()->values();
                        return $pbs->isNotEmpty() ? $pbs->implode(', ') : 'Belum PB';
                    })
                    ->badge()
                    ->color(fn (string $state) => $state === 'Belum PB' ? 'gray' : 'info'),

                Tables\Columns\TextColumn::make('nilai_po')
                    ->label('Total Nilai PO')
                    ->state(fn (PurchaseOrder $record): float => (float) $record->items->sum('total'))
                    ->money('IDR')
                    ->alignRight(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status PO vs PB')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'Completed' => 'success',
                        'Partial'   => 'warning',
                        default     => 'danger',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status Penerimaan')
                    ->options([
                        'Completed' => 'Completed (Lunas PB)',
                        'Partial'   => 'Partial (Sebagian)',
                        'Open'      => 'Open (Belum PB)',
                    ]),
            ])
            ->actions([])
            ->bulkActions([]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReportPoVsPbs::route('/'),
        ];
    }
}
