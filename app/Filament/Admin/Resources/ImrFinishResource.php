<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ImrFinishResource\Pages;
use App\Filament\Admin\Resources\ImrFinishResource\Schemas\ImrFinishForm;
use App\Models\ImrFinish;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ImrFinishResource extends Resource
{
    protected static ?string $model = ImrFinish::class;

    protected static ?string $navigationGroup = 'Finishing';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function getNavigationLabel(): string
    {
        return 'IMR Finish';
    }

    public static function form(Form $form): Form
    {
        return ImrFinishForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('no_imr')
                    ->label('Nomor IMR')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_request')
                    ->label('Tanggal Request')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('spk.no_spk')
                    ->label('Nomor SPK')
                    ->searchable(),

                Tables\Columns\TextColumn::make('departemen.nama_departemen')
                    ->label('Departemen')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Jumlah Material')
                    ->counts('items'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Approved' => 'success',
                        'Partial' => 'warning',
                        'Rejected' => 'danger',
                        'Closed' => 'gray',
                        default => 'primary',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Pending' => 'Pending',
                        'Approved' => 'Approved',
                        'Partial' => 'Partial',
                        'Rejected' => 'Rejected',
                        'Closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListImrFinishes::route('/'),
            'create' => Pages\CreateImrFinish::route('/create'),
            'edit' => Pages\EditImrFinish::route('/{record}/edit'),
        ];
    }
}