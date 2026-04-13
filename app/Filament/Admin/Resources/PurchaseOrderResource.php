<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PurchaseOrderResource\Pages;
use App\Models\PurchaseOrder;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class PurchaseOrderResource extends Resource
    {
        protected static ?string $model = PurchaseOrder::class;
        protected static ?string $navigationGroup = 'Purchase';

        protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
{
    return $form->schema([

        Forms\Components\Section::make('Purchase Order Details')
            ->schema([

         Forms\Components\Select::make('purchase_request_id')
        ->label('Purchase Request')
        ->relationship('purchaseRequest','nomor_pr')
        ->preload()
        ->required()
        ->reactive()
        ->afterStateUpdated(function ($state, $livewire) {

            if ($state) {
                $livewire->getPRItems($state);
            }

        }),

                Forms\Components\DatePicker::make('po_date')
                    ->required(),

                Forms\Components\Select::make('supplier_id')
                    ->label('Supplier')
                    ->relationship('supplier','nama_supplier')
                    ->preload()
                    ->required(),

            ]),

        Forms\Components\Section::make('Additional Information')
            ->schema([

                Forms\Components\DatePicker::make('eta'),

                Forms\Components\Select::make('currency')
                    ->options([
                        'IDR' => 'IDR',
                        'USD' => 'USD'
                    ])
                    ->default('IDR'),

                Forms\Components\TextInput::make('ppn')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('ongkir')
                    ->numeric()
                    ->default(0),

                Forms\Components\TextInput::make('dp')
                    ->numeric()
                    ->default(0),

            ]),

        Forms\Components\Section::make('Purchase Order Items')
            ->schema([
                Forms\Components\View::make('filament.purchase-order.pr-items')
            ])

    ]);
}

        public static function table(Table $table): Table
    {
        return $table
            ->columns([

            Tables\Columns\TextColumn::make('no_po')
    ->label('No PO')
    ->searchable()
    ->sortable(),

                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier'),

                Tables\Columns\TextColumn::make('po_date')
                    ->date(),

                Tables\Columns\TextColumn::make('currency'),

                Tables\Columns\TextColumn::make('ppn'),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
                'index' => Pages\ListPurchaseOrders::route('/'),
                'create' => Pages\CreatePurchaseOrder::route('/create'),
                'edit' => Pages\EditPurchaseOrder::route('/{record}/edit'),
            ];
        }
    }