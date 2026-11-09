<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PaymentEntryGoodResource\Pages;
use App\Models\InvPayment;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentEntryGoodResource extends Resource
{
    protected static ?string $model = InvPayment::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = false;

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function getNavigationLabel(): string
    {
        return 'Payment Entry Good';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('invoice.kode_invoice')->label('No. Invoice'),
                Tables\Columns\TextColumn::make('tanggal_bayar')->label('Tanggal Bayar')->date('d/m/Y'),
                Tables\Columns\TextColumn::make('nominal_bayar')->label('Nominal')->money('IDR'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge(),
            ])
            ->filters([])
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
            'index' => Pages\ListPaymentEntryGoods::route('/'),
        ];
    }
}
