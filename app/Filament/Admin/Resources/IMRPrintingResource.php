<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IMRPrintingResource\Pages;
use App\Models\IMRPrinting;
use App\Models\MaterialStock;
use Illuminate\Support\Facades\DB;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\ActionGroup;

class IMRPrintingResource extends Resource
{
    protected static ?string $model = IMRPrinting::class;
    protected static ?string $navigationGroup = 'Printing';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\TextInput::make('no_imr')
                    ->disabled()
                    ->placeholder('No IMR akan terisi otomatis setelah disimpan')
                    ->dehydrated(false),

                Forms\Components\DatePicker::make('tanggal_request')
                    ->required(),

                Forms\Components\Select::make('spk_id')
                    ->label('Surat Perintah Kerja')
                    ->placeholder('Input No SPK')
                    ->searchable()
                    ->getSearchResultsUsing(fn (string $search) =>
                        \App\Models\MasterSpk::query()
                            ->where('no_spk', 'like', "%{$search}%")
                            ->limit(20)
                            ->pluck('no_spk', 'id')
                    )
                    ->getOptionLabelUsing(fn ($value): ?string =>
                        \App\Models\MasterSpk::find($value)?->no_spk
                    )
                    ->required(),

                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('loadBom')
                        ->label('Load Material IMR')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function ($get, $set) {

                            $spkId = $get('spk_id');
                            if (!$spkId) return;

                            // reset state
                            $set('imr_items', []);

                            $spk = \App\Models\MasterSpk::with([
                                'salesOrder.itemable.materials.material.satuan',
                                'salesOrder.itemable.materials.departemen',
                            ])->find($spkId);

                            if (!$spk || !$spk->salesOrder || !$spk->salesOrder->itemable) {
                                return;
                            }

                            $items = [];

                            foreach ($spk->salesOrder->itemable->materials as $bom) {

                                $items[] = [
                                    'item_id'        => $bom->master_item_id ?? null,
                                    'nama_item'      => $bom->material->nama_master_item ?? '-',
                                    'department'     => optional($bom->departemen)->nama_departemen ?? '-',
                                    'satuan'         => $bom->material->satuan->nama_satuan ?? '-',
                                    'total_pesanan'  => (float) ($bom->qty ?? 0) * (float) ($spk->salesOrder->qty ?? 0),
                                    'qty_approved'   => 0,
                                    'qty_request'    => 0,
                                    'qty_input'      => 0,
                                ];
                            }

                            $set('imr_items', $items);
                        })
                ]),

                Forms\Components\Section::make('IMR Items')
                    ->schema([

                        Forms\Components\Repeater::make('imr_items')
                            ->label('Material Request')
                            ->default([]) // 🔥 penting banget
                            ->itemLabel(function ($state) {

                                // 🔥 SAFE ACCESS (ANTI ERROR TOTAL)
                                $nama   = $state['nama_item'] ?? 'Item';
                                $qty    = $state['total_pesanan'] ?? 0;
                                $satuan = $state['satuan'] ?? '';
                                $dept   = $state['department'] ?? '-';

                                return "{$nama} - {$dept} ({$qty} {$satuan})";
                            })
                            ->schema([

                                Forms\Components\Hidden::make('item_id'),

                                Forms\Components\TextInput::make('nama_item')
                                    ->default('-')
                                    ->disabled(),

                                Forms\Components\TextInput::make('department')
                                    ->label('Departemen')
                                    ->default('-')
                                    ->disabled(),

                                Forms\Components\TextInput::make('satuan')
                                    ->default('-')
                                    ->disabled(),

                                Forms\Components\TextInput::make('total_pesanan')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled(),

                                Forms\Components\TextInput::make('qty_approved')
                                    ->label('Qty Approved')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled(),

                               Forms\Components\TextInput::make('qty_request')
                                    ->numeric()
                                    ->default(0)
                                    ->disabled()
                                    ->dehydrated(true),

                                Forms\Components\TextInput::make('qty_input')
                                    ->numeric()
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(fn ($state, $set) => 
                                        $set('qty_request', $state)
                                    ),

                            ])
                            ->columns(3)
                            ->collapsible()
                            ->collapsed(),

                    ])

            ]);
    }

public static function table(Table $table): Table
{
    return $table
   
        ->columns([
            

            Tables\Columns\TextColumn::make('no_imr')
                ->label('No IMR')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('tanggal_request')
                ->label('Tanggal')
                ->date()
                ->sortable(),

            Tables\Columns\TextColumn::make('spk.no_spk')
                ->label('No SPK')
                ->searchable(),

            Tables\Columns\TextColumn::make('items_count')
                ->label('Jumlah Item Material')
                ->counts('items'),

            Tables\Columns\TextColumn::make('total_request')
                ->label('Total Request')
                ->getStateUsing(fn ($record) => $record->items->sum('qty_request')),

            Tables\Columns\TextColumn::make('status')
                ->badge()
              ->color(fn ($state) => match ($state) {
    'pending' => 'warning',
    'approved' => 'success',
    'partial' => 'info',
    'rejected' => 'danger',
    default => 'gray',
}),
        ])

        ->actions([

    ActionGroup::make([

   Tables\Actions\Action::make('approve')
    ->label('Approve')
    ->color('success')
    ->icon('heroicon-o-check')

    ->modalHeading('Persetujuan IMR')
    ->modalDescription('Silakan tentukan jumlah barang yang disetujui untuk setiap item. Pastikan jumlah tidak melebihi stok yang tersedia.')
    ->modalSubmitActionLabel('Setujui IMR')

    ->form(function ($record) {

        $schema = [];

        foreach ($record->items as $index => $item) {

            $schema[] = Forms\Components\Grid::make(3)
                ->schema([

                    Forms\Components\Placeholder::make("items.$index.nama_item")
                        ->label('Item')
                        ->content($item->item->nama_master_item ?? 'Item'),

                    Forms\Components\TextInput::make("items.$index.qty_request")
                        ->label('Qty Request')
                        ->default($item->qty_request)
                        ->disabled(),

                    Forms\Components\TextInput::make("items.$index.qty_approved")
                        ->label('Qty Approve')
                        ->numeric()
                        ->required()
                        ->default($item->qty_request),
                ]);
        }

        return $schema;
    })

  ->action(function ($record, $data) {

    DB::transaction(function () use ($record, $data) {

        $isPartial = false;
        $allZero   = true;

        foreach ($record->items as $index => $item) {

            $qtyApproved = (int) ($data['items'][$index]['qty_approved'] ?? 0);

            if ($qtyApproved > 0) {
                $allZero = false;
            }

            // LOCK STOCK
            $stock = MaterialStock::where('item_id', $item->item_id)
                ->lockForUpdate()
                ->first();

            if (!$stock) {

                throw new \Exception(
                    "Stock tidak ditemukan untuk item: " .
                    ($item->item->nama_master_item ?? $item->item_id)
                );
            }

            // VALIDASI STOCK
           if ($qtyApproved > $stock->on_hand){

                throw new \Exception(
                    "Stock tidak cukup untuk item: " .
                    ($item->item->nama_master_item ?? $item->item_id) .
                    " | Available: {$stock->outstanding}"
                );
            }

            // DETEKSI PARTIAL
            if ($qtyApproved < $item->qty_request) {
                $isPartial = true;
            }

            // UPDATE ITEM
            $item->update([
                'qty_approved' => $qtyApproved
            ]);

            // KURANGI STOCK
          if ($qtyApproved > 0) {

    // 🔥 stock gudang berkurang
    $stock->decrement('on_hand', $qtyApproved);

    // 🔥 material masuk proses produksi
    $stock->increment('allocation', $qtyApproved);
}
        }

        // STATUS FINAL
        if ($allZero) {

            $status = 'rejected';

        } elseif ($isPartial) {

            $status = 'partial';

        } else {

            $status = 'approved';
        }

        $record->update([
            'status' => $status
        ]);
    });
})

    ->requiresConfirmation()
    ->visible(fn ($record) => $record->status === 'pending'),

   Tables\Actions\Action::make('reject')
    ->label('Reject')
    ->color('danger')
    ->icon('heroicon-o-x-mark')
    ->requiresConfirmation()

    ->action(function ($record) {

        $record->update([
            'status' => 'rejected'
        ]);
    })

    ->visible(fn ($record) => $record->status === 'pending'),
           

        Tables\Actions\EditAction::make(),

        Tables\Actions\DeleteAction::make(),

    ])
    ->icon('heroicon-m-ellipsis-vertical') // 🔥 icon 3 titik
    ->tooltip('Actions') // optional

])

        ->filters([
        Tables\Filters\SelectFilter::make('status')
    ->options([
        'pending' => 'pending',
        'approved' => 'approved',
        'partial' => 'partial',
        'rejected' => 'rejected',
    ])
    ->default('pending')
        ])

        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make(),
        ]);
}

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListIMRPrintings::route('/'),
            'create' => Pages\CreateIMRPrinting::route('/create'),
            'edit' => Pages\EditIMRPrinting::route('/{record}/edit'),
        ];
    }
}