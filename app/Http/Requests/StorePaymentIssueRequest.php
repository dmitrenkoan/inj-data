<?php

namespace App\Http\Requests;

use App\Enums\PaymentIssueType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentIssueRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('serviceman'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(PaymentIssueType::class)],
            'description' => ['required', 'string'],
            'return_to' => ['nullable', 'in:show,edit'],
        ];
    }
}
