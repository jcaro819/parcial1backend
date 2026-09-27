<?php

namespace App\Http\Requests;

use App\Http\Rules\LuhnCardNumber;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validación de los datos del cliente y de la tarjeta en el checkout.
 */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required', 'string', 'max:150'],
            'customer_email' => ['required', 'email', 'max:150'],
            'card_holder' => ['required', 'string', 'max:150'],
            'card_number' => ['required', 'string', new LuhnCardNumber],
            'card_expiration' => ['required', 'regex:/^(0[1-9]|1[0-2])\/\d{2}$/', $this->notExpired(...)],
            'card_cvv' => ['required', 'digits_between:3,4'],
        ];
    }

    private function notExpired(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^(\d{2})\/(\d{2})$/', (string) $value, $m)) {
            return;
        }

        // La tarjeta es válida hasta el último día del mes de vencimiento.
        $expiresAt = now()->setDate(2000 + (int) $m[2], (int) $m[1], 1)->endOfMonth();

        if ($expiresAt->isPast()) {
            $fail('La tarjeta está vencida.');
        }
    }

    public function messages(): array
    {
        return [
            'card_expiration.regex' => 'El vencimiento debe tener el formato MM/AA.',
        ];
    }

    public function attributes(): array
    {
        return [
            'customer_name' => 'nombre',
            'customer_email' => 'email',
            'card_holder' => 'titular de la tarjeta',
            'card_number' => 'número de tarjeta',
            'card_expiration' => 'vencimiento',
            'card_cvv' => 'CVV',
        ];
    }
}
