<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterMaterialPlateResource\Pages;
use App\Models\MasterMaterialPlate;
use App\Support\MoneyHelper;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rules\Unique;

class MasterMaterialPlateResource extends Resource
{
    protected static ?string $model = MasterMaterialPlate::class;

    protected static ?string $navigationGroup = 'Pra Cetak';
    protected static ?string $navigationLabel = 'Master Material Plate';
    protected static ?string $navigationIcon = 'heroicon-o-square-3-stack-3d';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Material Plate';
    protected static ?string $pluralLabel = 'Master Material Plate';
    protected static ?string $slug = 'master-material-plates';

    /** Saran awal tipe plate (user tetap bebas mengetik tipe lain). */
    protected const SARAN_TIPE_PLATE = [
        'Digital (LAMS)',
        'Analog',
        'Solvent Washable',
        'Water Washable',
        'Thermal',
        'Flat Top Dot',
    ];

    /** Saran awal brand (user tetap bebas mengetik brand lain). */
    protected const SARAN_BRAND = ['TOYOBO', 'CORE'];

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Identitas Material')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('kode_material')
                            ->label('Kode Material')
                            ->required()
                            ->maxLength(50)
                            ->default(fn () => MasterMaterialPlate::suggestKode())
                            ->unique(ignoreRecord: true)
                            ->dehydrateStateUsing(fn ($state) => strtoupper(trim((string) $state)))
                            ->helperText('Harus unik. Contoh: MPL-0001'),

                        Forms\Components\TextInput::make('nama_material')
                            ->label('Nama Material Plate')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Cosmolight NEO 1.14'),
                    ]),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('brand')
                            ->label('Brand / Merek')
                            ->required()
                            ->maxLength(100)
                            ->datalist(fn () => static::brandOptions())
                            ->dehydrateStateUsing(fn ($state) => strtoupper(trim((string) $state)))
                            ->helperText('Ketik bebas atau pilih dari saran. Merek baru otomatis tersedia sebagai saran berikutnya.'),

                        Forms\Components\TextInput::make('tipe_plate')
                            ->label('Tipe Plate')
                            ->maxLength(100)
                            ->datalist(fn () => static::tipeOptions())
                            ->helperText('Ketik bebas atau pilih dari saran.'),
                    ]),
                ]),

            Forms\Components\Section::make('Spesifikasi Fisik (mm)')
                ->schema([
                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\TextInput::make('ketebalan_mm')
                            ->label('Ketebalan (mm)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.001)
                            ->suffix('mm')
                            ->placeholder('1.140'),

                        Forms\Components\TextInput::make('panjang_sheet_mm')
                            ->label('Panjang Sheet (mm)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('mm')
                            ->placeholder('1270'),

                        Forms\Components\TextInput::make('lebar_sheet_mm')
                            ->label('Lebar Sheet (mm)')
                            ->required()
                            ->numeric()
                            ->minValue(0)
                            ->step(0.01)
                            ->suffix('mm')
                            ->placeholder('900'),
                    ]),
                ]),

            Forms\Components\Section::make('Pembelian & Status')
                ->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('satuan_id')
                            ->label('Satuan')
                            ->relationship('satuan', 'nama_satuan')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\TextInput::make('harga_beli')
                            ->label('Harga Beli')
                            ->prefix('Rp')
                            ->required()
                            ->currencyMask(2)
                            ->placeholder('0.00')
                            ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                                if ((float) MoneyHelper::clean($value) < 0) {
                                    $fail('Harga beli tidak boleh bernilai negatif.');
                                }
                            })
                            ->helperText('Harga beli per satuan yang dipilih.'),
                    ]),

                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'nama_supplier')
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih supplier (opsional)'),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'aktif'    => 'Aktif',
                                'nonaktif' => 'Non Aktif',
                            ])
                            ->default('aktif')
                            ->required(),
                    ]),

                    Forms\Components\Textarea::make('catatan')
                        ->label('Catatan')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('kode_material', 'asc')
            ->columns([
                Tables\Columns\TextColumn::make('kode_material')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nama_material')
                    ->label('Nama Material')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('brand')
                    ->label('Brand')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipe_plate')
                    ->label('Tipe Plate')
                    ->placeholder('-')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ketebalan_mm')
                    ->label('Ketebalan')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 3, '.', ',') . ' mm')
                    ->sortable(),

                Tables\Columns\TextColumn::make('ukuran_sheet')
                    ->label('Ukuran Sheet (P × L)'),

                Tables\Columns\TextColumn::make('harga_beli')
                    ->label('Harga Beli')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('satuan.nama_satuan')
                    ->label('Satuan')
                    ->placeholder('-')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('supplier.nama_supplier')
                    ->label('Supplier')
                    ->placeholder('-')
                    ->searchable()
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'aktif',
                        'danger'  => 'nonaktif',
                    ])
                    ->formatStateUsing(fn ($state) => $state === 'aktif' ? 'Aktif' : 'Non Aktif')
                    ->sortable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated At')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('brand')
                    ->label('Brand')
                    ->options(fn () => MasterMaterialPlate::query()
                        ->whereNotNull('brand')
                        ->distinct()
                        ->orderBy('brand')
                        ->pluck('brand', 'brand')
                        ->toArray()),

                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'aktif'    => 'Aktif',
                        'nonaktif' => 'Non Aktif',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (MasterMaterialPlate $record, Tables\Actions\DeleteAction $action) {
                        static::guardDelete($record, $action);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->before(function (Collection $records, Tables\Actions\DeleteBulkAction $action) {
                            $used = $records->filter(fn (MasterMaterialPlate $r) => $r->isUsedInTransactions());
                            if ($used->isNotEmpty()) {
                                Notification::make()
                                    ->title('Sebagian material sudah digunakan dalam transaksi')
                                    ->body('Material: ' . $used->pluck('kode_material')->implode(', ') . '. Nonaktifkan material tersebut, jangan dihapus.')
                                    ->danger()
                                    ->send();
                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('Identitas Material')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('kode_material')->label('Kode Material'),
                    Infolists\Components\TextEntry::make('nama_material')->label('Nama Material Plate'),
                    Infolists\Components\TextEntry::make('brand')->label('Brand / Merek')->badge(),
                    Infolists\Components\TextEntry::make('tipe_plate')->label('Tipe Plate')->placeholder('-'),
                ]),

            Infolists\Components\Section::make('Spesifikasi Fisik')
                ->columns(3)
                ->schema([
                    Infolists\Components\TextEntry::make('ketebalan_mm')
                        ->label('Ketebalan')
                        ->formatStateUsing(fn ($state) => number_format((float) $state, 3, '.', ',') . ' mm'),
                    Infolists\Components\TextEntry::make('panjang_sheet_mm')
                        ->label('Panjang Sheet')
                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ',') . ' mm'),
                    Infolists\Components\TextEntry::make('lebar_sheet_mm')
                        ->label('Lebar Sheet')
                        ->formatStateUsing(fn ($state) => number_format((float) $state, 2, '.', ',') . ' mm'),
                ]),

            Infolists\Components\Section::make('Pembelian & Status')
                ->columns(2)
                ->schema([
                    Infolists\Components\TextEntry::make('satuan.nama_satuan')->label('Satuan')->placeholder('-'),
                    Infolists\Components\TextEntry::make('harga_beli')->label('Harga Beli')->money('IDR'),
                    Infolists\Components\TextEntry::make('supplier.nama_supplier')->label('Supplier')->placeholder('-'),
                    Infolists\Components\TextEntry::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn ($state) => $state === 'aktif' ? 'success' : 'danger')
                        ->formatStateUsing(fn ($state) => $state === 'aktif' ? 'Aktif' : 'Non Aktif'),
                    Infolists\Components\TextEntry::make('catatan')->label('Catatan')->placeholder('-')->columnSpanFull(),
                    Infolists\Components\TextEntry::make('created_at')->label('Dibuat')->dateTime('d M Y H:i'),
                    Infolists\Components\TextEntry::make('updated_at')->label('Terakhir Diubah')->dateTime('d M Y H:i'),
                ]),
        ]);
    }

    /**
     * Cegah penghapusan material yang sudah dipakai transaksi.
     */
    public static function guardDelete(MasterMaterialPlate $record, $action): void
    {
        if ($record->isUsedInTransactions()) {
            Notification::make()
                ->title('Material tidak dapat dihapus')
                ->body("Material {$record->kode_material} sudah digunakan dalam transaksi. Ubah status menjadi Non Aktif.")
                ->danger()
                ->send();

            $action->cancel();
        }
    }

    protected static function brandOptions(): array
    {
        $existing = MasterMaterialPlate::query()
            ->whereNotNull('brand')
            ->distinct()
            ->pluck('brand')
            ->all();

        return array_values(array_unique(array_merge(self::SARAN_BRAND, $existing)));
    }

    protected static function tipeOptions(): array
    {
        $existing = MasterMaterialPlate::query()
            ->whereNotNull('tipe_plate')
            ->distinct()
            ->pluck('tipe_plate')
            ->all();

        return array_values(array_unique(array_merge(self::SARAN_TIPE_PLATE, $existing)));
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListMasterMaterialPlates::route('/'),
            'create' => Pages\CreateMasterMaterialPlate::route('/create'),
            'view'   => Pages\ViewMasterMaterialPlate::route('/{record}'),
            'edit'   => Pages\EditMasterMaterialPlate::route('/{record}/edit'),
        ];
    }
}
