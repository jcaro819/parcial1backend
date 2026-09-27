<?php

namespace App\Http\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Valida un número de tarjeta con el algoritmo de Luhn (el que usan todas
 * las tarjetas de crédito reales para detectar errores de tipeo).
 */
class LuhnCardNumber implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $digits = preg_replace('/[\s-]/', '', (string) $value);

        if (! preg_match('/^\d{13,19}$/', $digits)) {
            $fail('El número de tarjeta debe tener entre 13 y 19 dígitos.');

            return;
        }

        $sum = 0;
        $double = false;

        for ($i = strlen($digits) - 1; $i >= 0; $i--) {
            $n = (int) $digits[$i];
            if ($double) {
                $n *= 2;
                if ($n > 9) {
                    $n -= 9;
                }
            }
            $sum += $n;
            $double = ! $double;
        }

        if ($sum % 10 !== 0) {
            $fail('El número de tarjeta no es válido.');
        }
    }
}
