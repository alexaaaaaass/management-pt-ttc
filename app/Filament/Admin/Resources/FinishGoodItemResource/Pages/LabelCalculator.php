<?php

namespace App\Filament\Admin\Resources\FinishGoodItemResource\Pages;
use App\Filament\Admin\Resources\FinishGoodItemResource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Concerns\InteractsWithForms;

class LabelCalculator extends Page implements HasForms
{
   use InteractsWithForms;

    protected static string $resource = FinishGoodItemResource::class;

  protected static string $view = 'filament.admin\resources.admin-resource\pages.label-calculator';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([

                Forms\Components\Section::make('Kalkulator Ukuran Etiket')
                    ->schema([

                        Forms\Components\Grid::make(3)
                            ->schema([

                                Forms\Components\TextInput::make('panjang_roll')
                                    ->label('Panjang Roll')
                                    ->numeric()
                                    ->suffix('m')
                                    ->required(),

                                Forms\Components\TextInput::make('lebar_roll')
                                    ->label('Lebar Roll')
                                    ->numeric()
                                    ->suffix('m')
                                    ->required(),

                                Forms\Components\TextInput::make('jumlah_cetak')
                                    ->label('Jumlah Cetak')
                                    ->numeric()
                                    ->suffix('pcs')
                                    ->required(),

                            ]),

                        Forms\Components\Grid::make(2)
                            ->schema([

                                Forms\Components\TextInput::make('panjang_pcs')
                                    ->label('Panjang per PCS')
                                    ->suffix('m')
                                    ->disabled(),

                                Forms\Components\TextInput::make('lebar_pcs')
                                    ->label('Lebar per PCS')
                                    ->suffix('m')
                                    ->disabled(),

                            ]),

                    ]),

            ]);
    }

    public function hitung(): void
    {
        $data = $this->form->getState();

        $panjangRoll = floatval($data['panjang_roll'] ?? 0);
        $lebarRoll = floatval($data['lebar_roll'] ?? 0);
        $jumlahCetak = floatval($data['jumlah_cetak'] ?? 0);

        if (
            $panjangRoll <= 0 ||
            $lebarRoll <= 0 ||
            $jumlahCetak <= 0
        ) {
            return;
        }

        $luasTotal = $panjangRoll * $lebarRoll;

        $luasPerPcs = $luasTotal / $jumlahCetak;

        $rasio = $panjangRoll / $lebarRoll;

        $panjangPcs = sqrt($luasPerPcs * $rasio);

        $lebarPcs = $panjangPcs / $rasio;

        $this->form->fill([
            ...$data,

            'panjang_pcs' => round($panjangPcs, 4),

            'lebar_pcs' => round($lebarPcs, 4),
        ]);
    }
}