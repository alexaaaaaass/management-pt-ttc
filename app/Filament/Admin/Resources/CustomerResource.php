<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CustomerResource\Pages;
use App\Models\Customer;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\TextColumn;


class CustomerResource extends Resource
{
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $model = Customer::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';

  public static function form(Form $form): Form
{
    return $form
        ->schema([
            TextInput::make('kode_customer')
                ->required()
                ->unique(ignoreRecord: true),

            TextInput::make('nama_customer')
                ->required(),

            Textarea::make('alamat_lengkap')
                ->required()
                ->columnSpanFull(),

            Textarea::make('alamat_kedua')
                ->columnSpanFull(),

            Textarea::make('alamat_ketiga')
                ->columnSpanFull(),

            TextInput::make('kode_group')
                ->required(),

            TextInput::make('nama_group')
                ->required(),
        ])
        ->columns(2);
}

public static function getNavigationLabel(): string
{
    return 'Customer';
}
   public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('kode_customer')
                ->searchable(),

            TextColumn::make('nama_customer')
                ->searchable()
                ->sortable(),

            TextColumn::make('nama_group')
                ->label('Group'),

            TextColumn::make('alamat_lengkap')
                ->limit(40),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit' => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}