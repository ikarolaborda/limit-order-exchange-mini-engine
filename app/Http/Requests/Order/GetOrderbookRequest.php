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
            'side' => [
                'sometimes',
                'nullable',
                'string',
                Rule::in(['buy', 'sell']),
            ],
            'status' => [
                'sometimes',
                'nullable',
                'integer',
                Rule::in([1, 2, 3]),
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'symbol' => strtoupper($this->query('symbol', '')),
            'side' => $this->query('side') ? strtolower($this->query('side')) : null,
            'status' => $this->query('status') ? (int) $this->query('status') : null,
        ]);
    }
}

