<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseRequestResource\Pages;
use App\Models\PurchaseRequest;
use App\Models\MasterItem;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PurchaseRequestResource extends Resource
{
    protected static ?string $model = PurchaseRequest::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Purchase';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Section::make('Informasi Purchase Request')
                    ->schema([

                      Forms\Components\Select::make('departemen_id')
    ->label('Departemen')
    ->relationship('departemen', 'nama_departemen')
    ->searchable()
    ->preload()
    ->required(), 

                        Forms\Components\DatePicker::make('tanggal_pr')
                            ->label('Tanggal PR')
                            ->required(),

                    ])->columns(2),

                Forms\Components\Section::make('Item Request')
                    ->schema([

                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([

                               Forms\Components\Select::make('item_id')
    ->label('Item')
    ->relationship('item', 'nama_master_item')
    ->searchable()
    ->preload()
    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {

    $item = MasterItem::with('satuan')->find($state);

    if ($item) {

        $set('satuan', $item->satuan->nama_satuan ?? null);

        $set('qty', $item->qty ?? 1);
    }
})
                                    ->required(),

                              Forms\Components\TextInput::make('satuan')
    ->label('Satuan')
    ->disabled()
    ->dehydrated()
    ->reactive(),

                                Forms\Components\TextInput::make('qty')
                                    ->numeric()
                                    ->required(),

                                Forms\Components\DatePicker::make('eta')
                                    ->label('ETA'),

                                Forms\Components\TextInput::make('catatan')
                                    ->columnSpanFull(),

                            ])
                            ->columns(3)
                            ->createItemButtonLabel('Tambah Item')
                            ->collapsible()

                    ])
            ]);
    }

    public static function getNavigationLabel(): string
{
    return 'Purchase Request';
}

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

             Tables\Columns\TextColumn::make('nomor_pr')
    ->label('No PR')
    ->searchable()
    ->sortable(),

                Tables\Columns\TextColumn::make('departemen.nama_departemen')
                    ->label('Departemen')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_pr')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->counts('items')
                    ->label('Total Item'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPurchaseRequests::route('/'),
            'create' => Pages\CreatePurchaseRequest::route('/create'),
            'edit' => Pages\EditPurchaseRequest::route('/{record}/edit'),
        ];
    }
}