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
            ->mask(RawJs::make('$money($input, ".", ",", ' . $precision . ')'))
            ->stripCharacters(',')
            ->extraInputAttributes(['inputmode' => 'decimal'])
            ->formatStateUsing(fn ($state) => static::format($state, $precision))
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
            ->mask(RawJs::make('$money($input, ".", ",", ' . $precision . ')'))
            ->stripCharacters(',')
            ->extraInputAttributes(['inputmode' => $precision > 0 ? 'decimal' : 'numeric'])
            ->formatStateUsing(fn ($state) => static::format($state, $precision))
            ->dehydrateStateUsing(fn ($state) => static::clean($state));
    }
}
