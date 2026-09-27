<?php

namespace App\Http\Requests;

use App\Enums\UserRole;
use App\Models\Battalion;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', User::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => [$this->isMethod('post') ? 'required' : 'nullable', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'brigade_id' => ['nullable', 'integer', Rule::exists('brigades', 'id')],
            'battalion_id' => ['nullable', 'integer', Rule::exists('battalions', 'id')],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $currentUser = $this->user();
            $role = UserRole::tryFrom((string) $this->input('role'));
            $brigadeId = $this->input('brigade_id');
            $battalionId = $this->input('battalion_id');

            if (! $currentUser->isSuperAdmin()) {
                if ($role === UserRole::SuperAdmin) {
                    $validator->errors()->add('role', 'Ви не можете призначити роль супер адміна.');
                }

                if ($brigadeId && $brigadeId != $currentUser->brigade_id) {
                    $validator->errors()->add('brigade_id', 'Ви не маєте доступу до цієї бригади.');
                }
            }

            if ($role === UserRole::Brigade && ! $brigadeId) {
                $validator->errors()->add('brigade_id', 'Оберіть бригаду для цієї ролі.');
            }

            if ($role === UserRole::Battalion) {
                if (! $battalionId) {
                    $validator->errors()->add('battalion_id', 'Оберіть батальйон для цієї ролі.');
                } elseif ($battalion = Battalion::find($battalionId)) {
                    if (! $currentUser->isSuperAdmin() && $battalion->brigade_id != $currentUser->brigade_id) {
                        $validator->errors()->add('battalion_id', 'Ви не маєте доступу до цього батальйону.');
                    }
                }
            }
        });
    }
}
