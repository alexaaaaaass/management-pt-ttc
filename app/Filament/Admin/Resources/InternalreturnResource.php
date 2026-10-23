<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\InternalreturnResource\Pages;
use App\Models\IMRPrinting;
use App\Models\InternalReturn;
use App\Services\InternalReturn\InternalReturnService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InternalreturnResource extends Resource
{
    protected static ?string $model = InternalReturn::class;
    protected static ?string $navigationGroup = 'Purchase';
    protected static ?int $navigationSort = 9;
    protected static ?string $navigationIcon = 'heroicon-o-arrow-uturn-left';

    public static function getNavigationLabel(): string
    {
        return 'Internal Return';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pengembalian (Internal Return)')
                    ->schema([
                        Forms\Components\Select::make('imr_id')
                            ->label('No. IMR (Internal Material Request)')
                            ->relationship('imr', 'no_imr')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, Forms\Set $set) {
                                $set('spk_display', null);
                                $set('departemen_display', null);
                                $set('tanggal_request_display', null);
                                $set('items', []);

                                if (! $state) {
                                    return;
                                }

                                $imr = IMRPrinting::with([
                                    'spk',
                                    'departemen',
                                    'items.item.satuan',
                                ])->find($state);

                                if (! $imr) {
                                    return;
                                }

                                $set('spk_display', $imr->spk?->no_spk ?? '-');
                                $set('departemen_display', $imr->departemen?->nama_departemen ?? '-');
                                $set('tanggal_request_display', $imr->tanggal_request ? date('d/m/Y', strtotime($imr->tanggal_request)) : '-');

                                $items = $imr->items->map(function ($row) {
                                    $available = max(0, (float) $row->qty_issued - (float) $row->qty_returned);

                                    return [
                                        'imr_printing_item_id' => $row->id,
                                        'item_id'              => $row->item_id,
                                        'nama_item'            => ($row->item->kode_material ?? '-') . ' - ' . ($row->item->nama_master_item ?? '-'),
                                        'qty_issued'           => (float) $row->qty_issued,
                                        'qty_available'        => $available,
                                        'satuan'               => $row->item?->satuan?->nama_satuan ?? $row->satuan ?? '-',
                                        'qty_return'           => 0,
                                        'catatan'              => null,
                                    ];
                                })->toArray();

                                $set('items', $items);
                            }),

                        Forms\Components\DatePicker::make('tanggal_return')
                            ->label('Tanggal Return')
                            ->default(now())
                            ->required(),

                        Forms\Components\TextInput::make('spk_display')
                            ->label('No. SPK Terkait')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('departemen_display')
                            ->label('Departemen Asal')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('tanggal_request_display')
                            ->label('Tanggal Request IMR')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('no_return')
                            ->label('No. Dokumen Return')
                            ->placeholder('Otomatis dibuat sistem (IRT-...)')
                            ->disabled()
                            ->dehydrated(false),

                        Forms\Components\Textarea::make('alasan')
                            ->label('Alasan Pengembalian / Catatan')
                            ->placeholder('Contoh: Kelebihan sisa bahan setelah proses cetak selesai')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Daftar Bahan / Material yang Dikembalikan')
                    ->description('Tentukan kuantiti bahan baku yang dikembalikan dari lantai produksi ke gudang')
                    ->schema([
                        Forms\Components\Repeater::make('items')
                            ->relationship('items')
                            ->schema([
                                Forms\Components\Hidden::make('imr_printing_item_id')
                                    ->required()
                                    ->dehydrated(true),

                                Forms\Components\Hidden::make('item_id')
                                    ->dehydrated(true),

                                Forms\Components\TextInput::make('nama_item')
                                    ->label('Bahan / Material')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('qty_issued')
                                    ->label('Qty Terambil')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffix(fn (Forms\Get $get) => $get('satuan') ?? ''),

                                Forms\Components\TextInput::make('qty_available')
                                    ->label('Maksimal Bisa Return')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->suffix(fn (Forms\Get $get) => $get('satuan') ?? ''),

                                Forms\Components\Hidden::make('satuan')
                                    ->dehydrated(false),

                                Forms\Components\TextInput::make('qty_return')
                                    ->label('Qty Return')
                                    ->numeric()
                                    ->required()
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Get $get, Forms\Set $set) {
                                        $max = (float) ($get('qty_available') ?? 0);
                                        if ((float) $state > $max && $max > 0) {
                                            $set('qty_return', $max);
                                        }
                                    })
                                    ->helperText(fn (Forms\Get $get) => 'Maks: ' . ($get('qty_available') ?? 0)),

                                Forms\Components\TextInput::make('catatan')
                                    ->label('Catatan Kondisi')
                                    ->placeholder('Contoh: Masih utuh / belum terpakai'),
                            ])
                            ->columns(6)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('no_return')
                    ->label('No Return')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('imr.no_imr')
                    ->label('No IMR')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('imr.spk.no_spk')
                    ->label('No SPK')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('imr.departemen.nama_departemen')
                    ->label('Departemen')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_return')
                    ->label('Tanggal Return')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft'    => 'gray',
                        'approved' => 'success',
                        'cancel'   => 'danger',
                        default    => 'secondary',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'draft'    => 'DRAFT',
                        'approved' => 'APPROVED (MASUK GUDANG)',
                        'cancel'   => 'BATAL',
                        default    => strtoupper($state),
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'draft'    => 'Draft',
                        'approved' => 'Approved',
                        'cancel'   => 'Batal',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->visible(fn (InternalReturn $record) => $record->status === 'draft'),

                    Tables\Actions\Action::make('approve')
                        ->label('Approve Return')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Approve Pengembalian Material')
                        ->modalDescription('Apakah Anda yakin ingin menyetujui pengembalian material ini? Stok pada master material di gudang akan otomatis bertambah.')
                        ->modalSubmitActionLabel('Ya, Setujui & Tambah Stok')
                        ->visible(fn (InternalReturn $record) => $record->status === 'draft')
                        ->action(function (InternalReturn $record) {
                            try {
                                app(InternalReturnService::class)->approve($record);
                                Notification::make()
                                    ->title('Internal Return Berhasil Diapprove!')
                                    ->body("Stok material telah otomatis dikembalikan ke gudang.")
                                    ->success()
                                    ->send();
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Gagal Approve Return')
                                    ->body($e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Tables\Actions\Action::make('cancel')
                        ->label('Batalkan Return')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Batalkan Pengembalian Material')
                        ->modalDescription('Apakah Anda yakin ingin membatalkan pengembalian ini? Penambahan stok yang sudah terjadi akan ditarik kembali.')
                        ->modalSubmitActionLabel('Ya, Batalkan')
                        ->visible(fn (InternalReturn $record) => $record->status === 'approved')
                        ->action(function (InternalReturn $record) {
                            app(InternalReturnService::class)->cancel($record);
                            Notification::make()
                                ->title('Internal Return Dibatalkan')
                                ->warning()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->visible(fn (InternalReturn $record) => $record->status === 'draft'),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInternalreturns::route('/'),
            'create' => Pages\CreateInternalreturn::route('/create'),
            'edit'   => Pages\EditInternalreturn::route('/{record}/edit'),
        ];
    }
}