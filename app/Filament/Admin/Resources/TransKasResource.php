<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TransKasResource\Pages;
use App\Models\TransKas;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Hidden;

class TransKasResource extends Resource
{
    protected static ?string $model = TransKas::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?int $navigationSort = 8;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
               Section::make('Transaksi Kas')

    ->extraAttributes(fn ($get) => [

        'class' =>

            $get('tipe_transaksi') === 'KAS_MASUK'

                ?

                '
                border-s-8
                border-success-500
                dark:border-success-400

                rounded-2xl

                bg-white
                dark:bg-gray-900
                '

                :

                '
                border-s-8
                border-danger-500
                dark:border-danger-400

                rounded-2xl

                bg-white
                dark:bg-gray-900
                '

    ])
                    ->schema([

                        Placeholder::make('tipe_transaksi_placeholder')
                            ->label('Tipe Transaksi')
                            ->content(fn ($get) => 
                                $get('tipe_transaksi') === 'KAS_MASUK'
                                    ? '🟢 Kas Masuk'
                                    : ($get('tipe_transaksi') === 'KAS_KELUAR' ? '🔴 Kas Keluar' : '—')
                            ),

                        Grid::make(2)
                            ->schema([
                                // Tambah ->live() di sini agar Placeholder di atas ikut berubah secara real-time
                        Hidden::make('tipe_transaksi')
    ->dehydrated(true),
                                TextInput::make('kode_transaksi')
                                    ->disabled()
                                    ->default('AUTO'),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('gudang'),
                                DatePicker::make('tanggal_transaksi')
                                    ->default(now())
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('periode')
                                    ->default(now()->format('F Y')),

                                Select::make('karyawan_id')
                                    ->relationship('karyawan', 'nama')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('account_bank_id')
                                    ->label('Account Bank')
                                    ->relationship('accountBank', 'nama_akun')
                                    ->searchable()
                                    ->preload(),

                                Select::make('account_kas_id')
                                    ->label('Account Kas')
                                    ->relationship('accountKas', 'nama_akun')
                                    ->searchable()
                                    ->preload(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('customer_id')
                                    ->numeric(),

                                TextInput::make('nominal')
                                    ->numeric()
                                    ->required(),
                            ]),

                        Grid::make(2)
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'ACTIVE' => 'ACTIVE',
                                        'INACTIVE' => 'INACTIVE',
                                    ])
                                    ->default('ACTIVE'),
                            ]),

                        Textarea::make('keterangan')
                            ->rows(4)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_transaksi')
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('tipe_transaksi')
                    ->colors([
                        'success' => 'KAS_MASUK',
                        'danger' => 'KAS_KELUAR',
                    ]),

                Tables\Columns\TextColumn::make('tanggal_transaksi')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('nominal')
                    ->money('IDR'),

                Tables\Columns\TextColumn::make('accountBank.nama_akun')
                    ->label('Bank'),

                Tables\Columns\TextColumn::make('accountKas.nama_akun')
                    ->label('Kas'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'success' => 'ACTIVE',
                        'danger' => 'INACTIVE',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\Action::make('kas_masuk')
                    ->label('Kas Masuk')
                    ->icon('heroicon-o-arrow-down-circle')
                    ->color('success')
                    ->url(fn () => static::getUrl('create', ['type' => 'KAS_MASUK'])),

                Tables\Actions\Action::make('kas_keluar')
                    ->label('Kas Keluar')
                    ->icon('heroicon-o-arrow-up-circle')
                    ->color('danger')
                    ->url(fn () => static::getUrl('create', ['type' => 'KAS_KELUAR'])),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTransKas::route('/'),
            'create' => Pages\CreateTransKas::route('/create'),
            'edit' => Pages\EditTransKas::route('/{record}/edit'),
        ];
    }
}