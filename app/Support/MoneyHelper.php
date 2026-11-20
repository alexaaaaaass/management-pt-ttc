<?php

namespace App\Support;

use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;

class MoneyHelper
{
    /**
     * Format a numeric or string value for display with thousand separators.
     */
    public static function format(mixed $value, int $precision = 2): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        $val = static::clean($value) ?? 0.0;
        $hasCents = $precision > 0 && fmod(round($val, $precision), 1.0) != 0.0;
        return number_format($val, $hasCents ? $precision : 0, '.', ',');
    }

    /**
     * Clean a formatted currency or quantity string into a float, preserving decimals.
     */
    public static function clean(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $cleaned = trim((string) $value);
        $cleaned = str_replace(',', '', $cleaned);
        $cleaned = preg_replace('/[^\d.]/', '', $cleaned);
        return $cleaned !== '' ? (float) $cleaned : null;
    }

    /**
     * Apply real-time money mask and currency dehydration to a TextInput.
     */
    public static function applyMask(TextInput $input, int $precision = 2): TextInput
    {
        return $input
            ->mask(RawJs::make("\$money(\$input || '', '.', ',', {$precision})"))
            ->stripCharacters(',')
            ->extraInputAttributes(['inputmode' => 'decimal'])
            ->formatStateUsing(fn ($state) => static::format($state, $precision) ?? '')
            ->dehydrateStateUsing(fn ($state) => static::clean($state));
    }

    /**
     * Apply real-time quantity mask (thousand separators) to a TextInput.
     * Default precision is 0 for integer counts (pcs, lembar, box),
     * or custom precision for decimals (kg, meter).
     */
    public static function applyQuantityMask(TextInput $input, int $precision = 0): TextInput
    {
        return $input
            ->mask(RawJs::make("\$money(\$input || '', '.', ',', {$precision})"))
            ->stripCharacters(',')
            ->extraInputAttributes(['inputmode' => $precision > 0 ? 'decimal' : 'numeric'])
            ->formatStateUsing(fn ($state) => static::format($state, $precision) ?? '')
            ->dehydrateStateUsing(fn ($state) => static::clean($state));
    }

    /**
     * Konversi angka nominal uang menjadi kalimat terbilang Bahasa Indonesia.
     * Contoh: 713498 -> "Tujuh Ratus Tiga Belas Ribu Empat Ratus Sembilan Puluh Delapan Rupiah"
     */
    public static function terbilang(mixed $value): string
    {
        $cleaned = static::clean($value);
        if ($cleaned === null || $cleaned <= 0) {
            return 'Nol Rupiah';
        }

        $nilai = round(abs($cleaned));
        $hasil = trim(static::penyebut($nilai));

        return preg_replace('/\s+/', ' ', $hasil) . ' Rupiah';
    }

    public static function penyebut(float $nilai): string
    {
        $nilai = abs($nilai);
        $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $temp = "";

        if ($nilai < 12) {
            $temp = " " . $huruf[(int) $nilai];
        } elseif ($nilai < 20) {
            $temp = static::penyebut($nilai - 10) . " Belas";
        } elseif ($nilai < 100) {
            $temp = static::penyebut(floor($nilai / 10)) . " Puluh" . static::penyebut(fmod($nilai, 10));
        } elseif ($nilai < 200) {
            $temp = " Seratus" . static::penyebut($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = static::penyebut(floor($nilai / 100)) . " Ratus" . static::penyebut(fmod($nilai, 100));
        } elseif ($nilai < 2000) {
            $temp = " Seribu" . static::penyebut($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = static::penyebut(floor($nilai / 1000)) . " Ribu" . static::penyebut(fmod($nilai, 1000));
        } elseif ($nilai < 1000000000) {
            $temp = static::penyebut(floor($nilai / 1000000)) . " Juta" . static::penyebut(fmod($nilai, 1000000));
        } elseif ($nilai < 1000000000000) {
            $temp = static::penyebut(floor($nilai / 1000000000)) . " Milyar" . static::penyebut(fmod($nilai, 1000000000));
        } else {
            $temp = static::penyebut(floor($nilai / 1000000000000)) . " Triliun" . static::penyebut(fmod($nilai, 1000000000000));
        }

        return $temp;
    }
}
