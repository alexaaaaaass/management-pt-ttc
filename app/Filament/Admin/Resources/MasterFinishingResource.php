<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\MasterFinishingResource\Pages;
use App\Models\MasterFinishing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;


class MasterFinishingResource extends Resource
{
    protected static ?string $model = MasterFinishing::class;
     protected static ?string $navigationGroup = 'Finishing';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

      public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('spk_id')
                    ->label('SPK')
                    ->relationship('spk', 'no_spk')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('mesin_finishing_id')
                    ->label('Mesin Finishing')
                    ->relationship('mesinFinishing', 'nama_mesin')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Select::make('operator_finishing_id')
                    ->label('Operator')
                    ->relationship('operatorFinishing', 'nama_operator')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\DatePicker::make('tanggal_entri')
                    ->required(),

                Forms\Components\Select::make('proses_finishing')
                    ->options([
                        'protol' => 'Protol',
                        'sorter' => 'Sorter',
                        'lem' => 'Lem',
                    ])
                    ->required(),

                Forms\Components\Select::make('tahap_finishing')
                    ->options([
                        'reguler' => 'Reguler',
                        'blokir' => 'Blokir',
                        'semi_waste' => 'Semi Waste',
                        'retur' => 'Retur',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('hasil_baik')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\TextInput::make('hasil_rusak')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\TextInput::make('semi_waste')
                    ->numeric()
                    ->default(0),

                Forms\Components\Select::make('note_waste')
                    ->options([
                        'CETAK KOTOR' => 'CETAK KOTOR',
                        'CETAK LUNTUR' => 'CETAK LUNTUR',
                        'CETAK BINTIK' => 'CETAK BINTIK',
                    ]),

                Forms\Components\Select::make('keterangan_spk')
                    ->options([
                        'reguler' => 'Reguler',
                        'subcount' => 'Subcount',
                    ])
                    ->required(),

                Forms\Components\Textarea::make('keterangan')
                    ->rows(3)
                    ->label('Keterangan Tambahan (opsional)')
                    ->columnSpanFull(),
            ]);
    }

    public static function getNavigationLabel(): string
{
    return 'Master Finishing';
}

   public static function table(Table $table): Table
{
    return $table
        ->defaultSort('tanggal_entri', 'desc')
        ->columns([

            Tables\Columns\TextColumn::make('spk.no_spk')
                ->label('No SPK')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('mesinFinishing.nama_mesin')
                ->label('Mesin')
                ->sortable(),

            Tables\Columns\TextColumn::make('operatorFinishing.nama_operator')
                ->label('Operator'),

            Tables\Columns\TextColumn::make('tanggal_entri')
                ->date()
                ->sortable(),

            Tables\Columns\BadgeColumn::make('proses_finishing')
                ->colors([
                    'primary' => 'protol',
                    'warning' => 'sorter',
                    'success' => 'lem',
                ]),

            Tables\Columns\BadgeColumn::make('tahap_finishing')
                ->colors([
                    'primary' => 'reguler',
                    'danger' => 'blokir',
                    'warning' => 'semi_waste',
                    'gray' => 'retur',
                ]),

            Tables\Columns\TextColumn::make('hasil_baik'),
            Tables\Columns\TextColumn::make('hasil_rusak'),
            Tables\Columns\TextColumn::make('semi_waste'),

            Tables\Columns\TextColumn::make('note_waste')
                ->toggleable(), // 🔥 bisa hide/show

            Tables\Columns\BadgeColumn::make('keterangan_spk')
                ->colors([
                    'success' => 'reguler',
                    'warning' => 'subcount',
                ]),
        ])

        ->actions([
            \Filament\Tables\Actions\ActionGroup::make([

                Tables\Actions\EditAction::make(),

                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation() // 🔥 penting biar gak kehapus tanpa sengaja
                    ->color('danger'),

                // 🔥 OPTIONAL (biar lebih pro)
                Tables\Actions\ViewAction::make(),

            ])
            ->icon('heroicon-m-ellipsis-vertical')
            ->tooltip('Actions')
        ])

        ->bulkActions([
            Tables\Actions\DeleteBulkAction::make()
                ->requiresConfirmation(),
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
            'index' => Pages\ListMasterFinishings::route('/'),
            'create' => Pages\CreateMasterFinishing::route('/create'),
            'edit' => Pages\EditMasterFinishing::route('/{record}/edit'),
        ];
    }
}