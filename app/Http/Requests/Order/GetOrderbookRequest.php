<?php

declare(strict_types=1);

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class GetOrderbookRequest extends FormRequest
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
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'symbol' => strtoupper($this->query('symbol', '')),
        ]);
    }
}

