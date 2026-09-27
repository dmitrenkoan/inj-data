<?php

namespace App\Http\Requests;

use App\Models\Battalion;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBattalionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Battalion::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'brigade_id' => ['required', 'integer', Rule::exists('brigades', 'id')],
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
            $brigadeId = $this->input('brigade_id');

            if ($brigadeId && ! $user->isSuperAdmin() && $brigadeId != $user->brigade_id) {
                $validator->errors()->add('brigade_id', 'Ви не маєте доступу до цієї бригади.');
            }
        });
    }
}
