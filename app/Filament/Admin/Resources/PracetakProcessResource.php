<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\PracetakProcessResource\Pages;
use App\Models\PracetakProcess;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class PracetakProcessResource extends Resource
{
    protected static ?string $model = PracetakProcess::class;
    protected static ?string $navigationGroup = 'Pra Cetak';
    protected static ?string $navigationLabel = 'Pra Cetak';
    protected static ?int $navigationSort = 1;
    protected static ?string $pluralLabel = 'Proses Pra Cetak';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

  public static function form(Form $form): Form
{
    return $form->schema([

        Forms\Components\Section::make('Data Utama')
            ->schema([

            Forms\Components\Section::make('Detail SPK')
    ->schema([
        Forms\Components\Placeholder::make('so')
            ->label('No Sales Order')
            ->content(fn ($get) =>
                optional(\App\Models\MasterSpk::with('salesOrder')->find($get('spk_id')))
                    ?->salesOrder?->no_sales_order ?? '-'
            ),
    ])
    ->visible(fn ($get) => filled($get('spk_id'))),
               Forms\Components\Select::make('spk_id')
    ->label('No SPK')
    ->relationship('spk', 'no_spk')
    ->searchable()
    ->preload()
    ->reactive() // 🔥 wajib
    ->afterStateUpdated(function ($state, callable $set) {

        $spk = \App\Models\MasterSpk::with([
            'salesOrder.itemable.materials.item',
            'salesOrder.itemable.materials.departemen'
        ])->find($state);

        if (!$spk || !$spk->salesOrder || !$spk->salesOrder->itemable) {
            $set('materials_preview', []);
            return;
        }

        $materials = $spk->salesOrder->itemable->materials;

        $data = $materials->map(function ($mat) use ($spk) {

            $qtySo = $spk->salesOrder->qty ?? 0;

            return [
                'nama_material' => $mat->item->nama_master_item ?? '-',
                'qty' => $qtySo * $mat->qty, // 🔥 AUTO HITUNG
                'departemen' => $mat->departemen->nama_departemen ?? '-',
            ];
        })->toArray();

        $set('materials_preview', $data);
    })
    ->required(),

                Forms\Components\TextInput::make('qty')
                ->placeholder('Masukkan qty plat yg dicetak')
                    ->numeric()
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options([
                        'design' => 'Design',
                        'plat_making' => 'Plat Making',
                        'selesai' => 'Selesai',
                    ])
                    ->default('design')
                    ->disabledOn('create') // 🔥 auto design saat create
                    ->required(),

                Forms\Components\Select::make('ket_spk')
                    ->options([
                        'reguler' => 'Reguler',
                        'subcount' => 'Subcount',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('nama_plat')
                    ->required(),
            ]),

            Forms\Components\Section::make('Detail Sales Order')
    ->extraAttributes([
        'style' => '
            border-left: 5px solid #3b82f6;
            border-radius: 10px;
            padding: 12px;
        ',
    ])
    ->schema([

        Forms\Components\Grid::make(2)->schema([

            // 🔹 KIRI
            Forms\Components\Grid::make(1)->schema([

                Forms\Components\Placeholder::make('no_so')
                    ->label('No Sales Order')
                    ->content(fn ($get) =>
                        optional(\App\Models\MasterSpk::with('salesOrder')->find($get('spk_id')))
                            ?->salesOrder?->no_sales_order ?? '-'
                    ),

                Forms\Components\Placeholder::make('no_po')
                    ->label('No PO Customer')
                    ->content(fn ($get) =>
                        optional(\App\Models\MasterSpk::with('salesOrder')->find($get('spk_id')))
                            ?->salesOrder?->no_po_customer ?? '-'
                    ),

                Forms\Components\Placeholder::make('customer')
                    ->label('Customer')
                    ->content(function ($get) {
                        $spk = \App\Models\MasterSpk::with('salesOrder.customer')
                            ->find($get('spk_id'));

                        return $spk?->salesOrder?->customer?->nama_customer ?? '-';
                    }),

                Forms\Components\Placeholder::make('nama_barang')
                    ->label('Nama Barang')
                    ->content(function ($get) {
                        $spk = \App\Models\MasterSpk::with('salesOrder.itemable')
                            ->find($get('spk_id'));

                        return $spk?->salesOrder?->itemable?->nama_barang
                            ?? $spk?->salesOrder?->itemable?->nama_master_item
                            ?? '-';
                    }),

                Forms\Components\Placeholder::make('deskripsi')
                    ->label('Deskripsi')
                    ->content(function ($get) {
                        $spk = \App\Models\MasterSpk::with('salesOrder.itemable')
                            ->find($get('spk_id'));

                        return $spk?->salesOrder?->itemable?->deskripsi ?? '-';
                    }),

            ]),

            // 🔹 KANAN
            Forms\Components\Grid::make(1)->schema([

                Forms\Components\Placeholder::make('qty')
                    ->label('Jumlah Pesanan')
                    ->content(fn ($get) =>
                        optional(\App\Models\MasterSpk::with('salesOrder')->find($get('spk_id')))
                            ?->salesOrder?->qty ?? '-'
                    ),

                Forms\Components\Placeholder::make('toleransi')
                    ->label('Toleransi')
                    ->content(function ($get) {
                        $spk = \App\Models\MasterSpk::with('salesOrder')
                            ->find($get('spk_id'));

                        return $spk?->salesOrder?->toleransi_pengiriman
                            ? $spk->salesOrder->toleransi_pengiriman . ' %'
                            : '-';
                    }),

                Forms\Components\Placeholder::make('tipe')
                    ->label('Tipe Pesanan')
                    ->content(fn ($get) =>
                        optional(\App\Models\MasterSpk::with('salesOrder')->find($get('spk_id')))
                            ?->salesOrder?->tipe_pesanan ?? '-'
                    ),

                Forms\Components\Placeholder::make('tanggal')
                    ->label('Tanggal Pesanan')
                    ->content(fn ($get) =>
                        optional(\App\Models\MasterSpk::with('salesOrder')->find($get('spk_id')))
                            ?->salesOrder?->tanggal_po ?? '-'
                    ),
            ]),
        ]),
    ])
    ->visible(fn ($get) => filled($get('spk_id'))),

        // 🔥 CONDITIONAL FIELD
        Forms\Components\Section::make('Hasil Produksi')
            ->visible(fn ($get) => $get('status') === 'selesai')
            ->schema([

                Forms\Components\TextInput::make('hasil_baik')
                    ->numeric()
                    ->required(),

                Forms\Components\TextInput::make('hasil_rusak')
                    ->numeric()
                    ->required(),

                Forms\Components\DatePicker::make('tgl_entry')
                    ->required(),

                Forms\Components\Select::make('operator_id')
                    ->relationship('operator', 'nama_operator')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('mesin_id')
                    ->relationship('mesin', 'nama_mesin')
                    ->searchable()
                    ->required(),
            ]),
    ]);
}

   public static function table(Table $table): Table
{
    return $table
        ->defaultSort('created_at', 'desc')
        ->columns([

            Tables\Columns\TextColumn::make('spk.no_spk')
                ->label('No SPK')
                ->searchable(),

            Tables\Columns\TextColumn::make('qty'),

            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'warning' => 'design',
                    'info' => 'plat_making',
                    'success' => 'selesai',
                ]),

            Tables\Columns\TextColumn::make('nama_plat'),

            Tables\Columns\TextColumn::make('hasil_baik'),

            Tables\Columns\TextColumn::make('operator.nama_operator')
                ->label('Operator'),

            Tables\Columns\TextColumn::make('created_at')
                ->dateTime(),
        ])
       ->actions([

    Tables\Actions\Action::make('toDesign')
        ->label('Design')
        ->color('warning')
        ->visible(fn ($record) => $record->status !== 'design')
        ->action(fn ($record) => $record->update(['status' => 'design'])),

    Tables\Actions\Action::make('toPlat')
        ->label('Plat Making')
        ->color('info')
        ->visible(fn ($record) => $record->status === 'design')
        ->action(fn ($record) => $record->update(['status' => 'plat_making'])),

    Tables\Actions\Action::make('toSelesai')
    ->label('Selesai')
    ->color('success')
    ->visible(fn ($record) => $record->status === 'plat_making')
    ->form([

        Forms\Components\TextInput::make('hasil_baik')
            ->numeric()
            ->required(),

        Forms\Components\TextInput::make('hasil_rusak')
            ->numeric()
            ->required(),

        Forms\Components\DatePicker::make('tgl_entry')
            ->required(),

        Forms\Components\Select::make('operator_id')
            ->relationship('operator', 'nama_operator')
            ->required(),

        Forms\Components\Select::make('mesin_id')
            ->relationship('mesin', 'nama_mesin')
            ->required(),
    ])
    ->action(function ($record, $data) {

        $record->update([
            ...$data,
            'status' => 'selesai'
        ]);
    }),
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
            'index' => Pages\ListPracetakProcesses::route('/'),
            'create' => Pages\CreatePracetakProcess::route('/create'),
            'edit' => Pages\EditPracetakProcess::route('/{record}/edit'),
        ];
    }
}