<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'symbol' => [
                'required',
                'string',
                Rule::in(['BTC', 'ETH']),
            ],
            'side' => [
                'required',
                'string',
                Rule::in(['buy', 'sell']),
            ],
            'price' => [
                'required',
                'numeric',
                'min:0.00000001',
                'max:999999999.99999999',
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.00000001',
                'max:999999999.99999999',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'symbol.in' => 'Symbol must be BTC or ETH.',
            'side.in' => 'Side must be buy or sell.',
            'price.min' => 'Price must be greater than zero.',
            'amount.min' => 'Amount must be greater than zero.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'symbol' => strtoupper($this->string('symbol')->toString()),
        ]);
    }
}
