<?php

namespace App\Helpers;

/**
 * Single place for every currency display decision in the app.
 *
 * Right now we only support USD. When you add multi-currency
 * support later, this is the one file you change — not eight.
 *
 * The method signature intentionally accepts a string OR a float.
 * The model methods return numeric strings (bcadd/bcsub output).
 * number_format() needs a float. The cast happens here, at the
 * boundary, and nowhere else in the app.
 */
class Money
{
    /**
     * Format a numeric value as a currency string.
     *
     * Examples:
     *   Money::format('1234.50')   → '$1,234.50'
     *   Money::format('-50.00')    → '-$50.00'
     *   Money::format('0')         → '$0.00'
     *   Money::format(1000)        → '$1,000.00'
     *
     * Negative values: the minus sign sits outside the dollar sign.
     * This is the standard US accounting convention and matches
     * how banks display negative balances.
     */
    public static function format(string|float|int $amount): string
    {
        $value = (float) $amount;

        if ($value < 0) {
            return '-$' . number_format(abs($value), 2);
        }

        return '$' . number_format($value, 2);
    }
}
