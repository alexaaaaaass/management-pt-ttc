<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\FinishGoodStockResource\Pages;
use App\Models\FinishGoodStock;
use App\Services\Inventory\FinishGoodStockService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FinishGoodStockResource extends Resource
{
    protected static ?string $model = FinishGoodStock::class;

    protected static ?string $navigationGroup = 'Warehouse';

    protected static ?string $navigationLabel = 'Finish Good Stocks';

    protected static ?string $modelLabel = 'Finish Good Stock';

    protected static ?string $pluralModelLabel = 'Finish Good Stocks';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('finishGoodItem.kode_material_produk')
                    ->label('Kode Produk')
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('finishGoodItem.nama_barang')
                    ->label('Nama Barang Jadi')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('finishGoodItem.customer.nama_customer')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('finishGoodItem.jenis')
                    ->label('Jenis')
                    ->badge()
                    ->color('info')
                    ->placeholder('-'),

                Tables\Columns\TextColumn::make('finishGoodItem.satuan.nama_satuan')
                    ->label('Satuan')
                    ->placeholder('PCS'),

                Tables\Columns\TextColumn::make('on_hand')
                    ->label('On Hand (Total Fisik)')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->badge()
                    ->color(fn ($state) => (float) $state > 0 ? 'success' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('reserved')
                    ->label('Reserved (Booked SO)')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->badge()
                    ->color(fn ($state) => (float) $state > 0 ? 'warning' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('available')
                    ->label('Free Buffer Stock')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->badge()
                    ->color(fn ($state) => (float) $state > 0 ? 'info' : 'gray'),

                Tables\Columns\TextColumn::make('blocked')
                    ->label('Blocked (QC Hold)')
                    ->numeric(decimalPlaces: 0, decimalSeparator: ',', thousandsSeparator: '.')
                    ->badge()
                    ->color(fn ($state) => (float) $state > 0 ? 'warning' : 'gray')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Update Terakhir')
                    ->since()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('track')
                    ->label('Track')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalWidth('7xl')
                    ->modalHeading('Tracking Stok & Mutasi Barang Jadi')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Tutup')
                    ->modalContent(fn ($record) => view(
                        'filament.finish-good-stock.track',
                        ['stock' => $record]
                    )),

                Tables\Actions\Action::make('adjust')
                    ->label('Penyesuaian Stok')
                    ->icon('heroicon-o-adjustments-horizontal')
                    ->color('warning')
                    ->fillForm(fn (FinishGoodStock $record): array => [
                        'on_hand' => $record->on_hand,
                        'remarks' => null,
                    ])
                    ->form([
                        Forms\Components\TextInput::make('on_hand')
                            ->label('Stok Fisik Baru (On Hand)')
                            ->numeric()
                            ->required()
                            ->extraInputAttributes(['onwheel' => 'this.blur()']),
                        Forms\Components\Textarea::make('remarks')
                            ->label('Alasan Penyesuaian / Catatan Stock Opname')
                            ->placeholder('Contoh: Stock opname gudang barang jadi')
                            ->required(),
                    ])
                    ->action(function (FinishGoodStock $record, array $data): void {
                        app(FinishGoodStockService::class)->adjustStock(
                            finishGoodItemId: (int) $record->finish_good_item_id,
                            newOnHand: (float) $data['on_hand'],
                            remarks: $data['remarks'] ?? 'Penyesuaian Stok Manual'
                        );

                        Notification::make()
                            ->title('Stok Barang Jadi Diperbarui')
                            ->body("Stok '{$record->finishGoodItem?->nama_barang}' berhasil disesuaikan menjadi " . number_format((float) $data['on_hand'], 0, ',', '.') . ".")
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['finishGoodItem.customer', 'finishGoodItem.satuan']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFinishGoodStocks::route('/'),
        ];
    }
}

