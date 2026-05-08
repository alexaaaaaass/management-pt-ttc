<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\IMRPrintingResource\Pages;
use App\Models\IMRPrinting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


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
    ->dehydrated(false),

Forms\Components\DatePicker::make('tanggal_request')
    ->required(),
                Forms\Components\Select::make('spk_id')
    ->label('Surat Perintah Kerja')
    ->searchable()

    // 🔥 ambil data saat user ngetik
    ->getSearchResultsUsing(fn (string $search) =>
        \App\Models\MasterSpk::query()
            ->where('no_spk', 'like', "%{$search}%")
            ->limit(20)
            ->pluck('no_spk', 'id')
    )

    // 🔥 tampilkan label saat sudah dipilih
    ->getOptionLabelUsing(fn ($value): ?string =>
        \App\Models\MasterSpk::find($value)?->no_spk
    )

    ->reactive()
    ->afterStateUpdated(function ($state, Set $set) {

        $spk = \App\Models\MasterSpk::with('salesOrder.itemable.materials.material')->find($state);

        if (!$spk) return;

        $materials = $spk->salesOrder->itemable->materials;

        $items = [];

        foreach ($materials as $bom) {
            $items[] = [
                'item_id' => $bom->master_item_id,
                'department' => 'PRINTING',
                'catatan' => '-',
                'satuan' => $bom->material->satuan->nama_satuan ?? '-',
                'total_pesanan' => $bom->qty * $spk->salesOrder->qty,
                'qty_approved' => 0,
                'qty_request' => 0,
                'qty_input' => 0,
            ];
        }

        $set('items', $items);
    })

    ->required(),

    Forms\Components\Repeater::make('items')
    ->relationship()
    ->schema([

        Forms\Components\TextInput::make('department')
            ->disabled(),

        Forms\Components\TextInput::make('item_id')
            ->label('Item ID')
            ->disabled(),

        Forms\Components\TextInput::make('satuan')
            ->disabled(),

        Forms\Components\TextInput::make('total_pesanan')
            ->disabled(),

        Forms\Components\TextInput::make('qty_approved')
            ->disabled(),

        Forms\Components\TextInput::make('qty_request')
            ->numeric()
            ->required(),

        Forms\Components\TextInput::make('qty_input')
            ->numeric()
            ->default(0),
    ])
    ->columns(4)
    ->columnSpanFull()
            ]);

            
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
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