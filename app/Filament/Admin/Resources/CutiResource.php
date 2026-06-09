<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CutiResource\Pages;
use App\Models\Cuti;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
<<<<<<< HEAD
use Filament\Tables\Columns\TextColumn;
=======
use Carbon\Carbon;
>>>>>>> 2b7f9fc8bdc1b557da06ff9a81056e9442b7b258

class CutiResource extends Resource
{
    protected static ?string $model = Cuti::class;
<<<<<<< HEAD
    protected static ?int $navigationSort = 7;
    protected static ?string $navigationGroup = 'HRD';

    protected static ?string $navigationIcon = 'heroicon-o-calendar-days';

    public static function getModelLabel(): string
{
    return 'Cuti';
}

public static function getPluralModelLabel(): string
{
    return 'Cuti';
}

=======
     protected static ?string $navigationGroup = 'HRD';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 7;
>>>>>>> 2b7f9fc8bdc1b557da06ff9a81056e9442b7b258

  public static function form(Form $form): Form
{
    return $form->schema([

        Forms\Components\Section::make('Pengajuan Cuti')
            ->schema([
<<<<<<< HEAD
                Select::make('karyawan_id')
                    ->relationship('karyawan', 'nama_lengkap')
                    ->searchable()
                    ->required(),

                DatePicker::make('tanggal_cuti')
                    ->required(),

                Select::make('jenis_cuti')
                    ->options([
                        'Cuti Tahunan' => 'Cuti Tahunan',
                        'Cuti Khusus' => 'Cuti Khusus',
                    ])
                    ->required(),

                FileUpload::make('lampiran')
                    ->directory('cuti'),

                Textarea::make('keterangan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('karyawan.nama_lengkap')
                    ->label('Karyawan')
                    ->searchable(),

                TextColumn::make('tanggal_cuti')
                    ->label('Tanggal Cuti')
                    ->date(),

                TextColumn::make('jenis_cuti')
                    ->label('Jenis Cuti'),

                TextColumn::make('lampiran')
                    ->label('Lampiran')
                    ->formatStateUsing(fn () => 'Lihat'),

                TextColumn::make('keterangan')
                    ->limit(50),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\CreateAction::make()
                    ->label('Tambah Data Cuti')]),
            ]);
    }
=======

                Forms\Components\Select::make('karyawan_id')
                    ->relationship('karyawan', 'nama')
                    ->searchable()
                    ->required(),

            Forms\Components\Select::make('jenis_cuti')
    ->label('Jenis Cuti')
    ->options([
        'Cuti Tahunan' => 'Cuti Tahunan',
        'Cuti Menikah' => 'Cuti Menikah',
        'Cuti Melahirkan' => 'Cuti Melahirkan',
        'Cuti Sakit' => 'Cuti Sakit',
        'Cuti Khusus' => 'Cuti Khusus',
    ])
    ->searchable()
    ->required(),

               Forms\Components\DatePicker::make('tanggal_mulai')
    ->required()
    ->live(),

Forms\Components\DatePicker::make('tanggal_selesai')
    ->required()
    ->live()
    ->afterStateUpdated(function ($state, callable $set, callable $get) {

        if (!$get('tanggal_mulai') || !$state) {
            return;
        }

        $jumlahHari = Carbon::parse($get('tanggal_mulai'))
            ->diffInDays(Carbon::parse($state)) + 1;

        $set('jumlah_hari', $jumlahHari);
    }),

Forms\Components\TextInput::make('jumlah_hari')
    ->disabled()
    ->dehydrated()
    ->numeric(),

                Forms\Components\FileUpload::make('lampiran')
                    ->directory('cuti')
                    ->image()
                    ->downloadable()
                    ->openable(),

                Forms\Components\Textarea::make('keterangan')
                    ->columnSpanFull(),

            ])
            ->columns(2),
    ]);
}

public static function getNavigationLabel(): string
{
    return 'Cuti';
}
    
    
  public static function table(Table $table): Table
{
    return $table
        ->columns([

            Tables\Columns\TextColumn::make('karyawan.nama')
                ->label('Karyawan')
                ->searchable(),

            Tables\Columns\TextColumn::make('tanggal_mulai')
                ->label('Tanggal Cuti')
                ->date(),

           Tables\Columns\TextColumn::make('jenis_cuti')
    ->label('Jenis Cuti'),  
        
            Tables\Columns\TextColumn::make('keterangan')
                ->limit(50),

          
        ]);
}
>>>>>>> 2b7f9fc8bdc1b557da06ff9a81056e9442b7b258

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCutis::route('/'),
            'create' => Pages\CreateCuti::route('/create'),
            'edit' => Pages\EditCuti::route('/{record}/edit'),
        ];
    }
}