<?php

namespace App\Http\Requests;

use App\Models\Battalion;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreUnitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Unit::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'battalion_id' => ['required', 'integer', Rule::exists('battalions', 'id')],
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $user = $this->user();
            $battalionId = $this->input('battalion_id');

            if (! $battalionId || $user->isSuperAdmin()) {
                return;
            }

            $battalion = Battalion::find($battalionId);

            $allowed = match (true) {
                ! $battalion => false,
                $user->isBrigade() => $battalion->brigade_id === $user->brigade_id,
                default => $battalion->id === $user->battalion_id,
            };

            if (! $allowed) {
                $validator->errors()->add('battalion_id', 'Ви не маєте доступу до цього батальйону.');
            }
        });
    }
}
