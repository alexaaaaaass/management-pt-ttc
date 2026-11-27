<?php

namespace App\Filament\Admin\Resources\KaryawanResource\RelationManagers;

use App\Models\KaryawanDokumen;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class DokumensRelationManager extends RelationManager
{
    protected static string $relationship = 'dokumens';
    protected static ?string $title = 'Arsip Dokumen Karyawan';
    protected static ?string $icon = 'heroicon-o-folder-open';

    public function form(Form $form): Form
    {
        return $form->schema([
            Select::make('jenis_dokumen')
                ->label('Jenis Dokumen')
                ->options(KaryawanDokumen::JENIS_OPTIONS)
                ->searchable()
                ->required(),

            TextInput::make('nomor_dokumen')
                ->label('Nomor Dokumen (Opsional)')
                ->maxLength(100),

            DatePicker::make('tanggal_terbit')
                ->label('Tanggal Terbit'),

            DatePicker::make('tanggal_berlaku')
                ->label('Berlaku Sampai (Opsional)'),

            FileUpload::make('file_path')
                ->label('File Dokumen (PDF / JPG / PNG, maks 5MB)')
                ->disk('public')
                ->directory('karyawan-dokumen')
                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/webp'])
                ->maxSize(5120)
                ->openable()
                ->downloadable()
                ->required()
                ->columnSpanFull(),

            Textarea::make('keterangan')
                ->label('Catatan')
                ->rows(2)
                ->columnSpanFull(),
        ])->columns(2);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('jenis_dokumen')
                    ->label('Jenis Dokumen')
                    ->badge()
                    ->formatStateUsing(fn (?string $state) => KaryawanDokumen::JENIS_OPTIONS[$state] ?? $state)
                    ->color('primary'),

                TextColumn::make('nomor_dokumen')->label('Nomor')->placeholder('-'),
                TextColumn::make('tanggal_terbit')->label('Tgl Terbit')->date('d/m/Y')->placeholder('-'),
                TextColumn::make('tanggal_berlaku')->label('Berlaku s/d')->date('d/m/Y')->placeholder('Seumur Hidup / -'),
                TextColumn::make('keterangan')->label('Catatan')->limit(35)->placeholder('-'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()->label('+ Upload Dokumen'),
            ])
            ->actions([
                Tables\Actions\Action::make('lihatFile')
                    ->label('Buka File')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->color('info')
                    ->url(fn (KaryawanDokumen $record) => Storage::disk('public')->url($record->file_path))
                    ->openUrlInNewTab(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
