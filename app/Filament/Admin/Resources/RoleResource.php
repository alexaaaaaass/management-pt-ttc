<?php

namespace App\Filament\Admin\Resources;

<<<<<<< HEAD
=======
use App\Filament\Admin\Resources\RoleResource\Pages;
>>>>>>> 2b7f9fc8bdc1b557da06ff9a81056e9442b7b258
use App\Models\Role;
use Filament\Forms\Form;
use Filament\Tables\Table;
<<<<<<< HEAD
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use App\Filament\Admin\Resources\RoleResource\Pages;
=======

>>>>>>> 2b7f9fc8bdc1b557da06ff9a81056e9442b7b258

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;
<<<<<<< HEAD
=======
    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 3;
>>>>>>> 2b7f9fc8bdc1b557da06ff9a81056e9442b7b258

    protected static ?string $navigationGroup = 'HRD';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Role';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('Nama Role')
                    ->required(),

                TextInput::make('guard_name')
                    ->default('web')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Role')
                    ->searchable(),

                TextColumn::make('guard_name')
                    ->label('Guard'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->label('Dibuat'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}