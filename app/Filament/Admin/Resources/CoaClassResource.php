<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CoaClassResource\Pages;
use App\Models\CoaClass;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CoaClassResource extends Resource
{
    protected static ?string $model = CoaClass::class;
    protected static ?string $navigationGroup = 'Finance';
    protected static ?string $navigationLabel = 'Master Coa Class';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

   public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\Select::make('karyawan_id')
                ->label('Karyawan')
                ->relationship('karyawan', 'nama')
                ->searchable()
                ->required(),

            Forms\Components\TextInput::make('code')
                ->label('Code')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(50),

            Forms\Components\TextInput::make('name')
                ->label('Name')
                ->required()
                ->maxLength(100),

            Forms\Components\Select::make('status')
                ->options([
                    'aktif' => 'Aktif',
                    'nonaktif' => 'Nonaktif',
                ])
                ->default('aktif')
                ->required(),
        ]);
}



    public static function getNavigationLabel(): string
{
    return 'Master Coa Class';
}
    

  public static function table(Table $table): Table
{
    return $table
        ->columns([
            Tables\Columns\TextColumn::make('karyawan.nama')
                ->label('Karyawan')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('code')
                ->label('Code')
                ->searchable()
                ->sortable(),

            Tables\Columns\TextColumn::make('name')
                ->label('Nama COA Class')
                ->searchable(),

            Tables\Columns\BadgeColumn::make('status')
                ->colors([
                    'success' => 'aktif',
                    'danger' => 'nonaktif',
                ])
                ->label('Status'),

            Tables\Columns\TextColumn::make('created_at')
                ->label('Dibuat')
                ->dateTime('d M Y')
                ->sortable(),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('status')
                ->options([
                    'aktif' => 'Aktif',
                    'nonaktif' => 'Nonaktif',
                ]),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ])
        ->bulkActions([
            Tables\Actions\BulkActionGroup::make([
                Tables\Actions\DeleteBulkAction::make(),
            ]),
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
            'index' => Pages\ListCoaClasses::route('/'),
            'create' => Pages\CreateCoaClass::route('/create'),
            'edit' => Pages\EditCoaClass::route('/{record}/edit'),
        ];
    }
}