<?php

namespace App\Http\Requests;

use App\Enums\DisabilityGroup;
use App\Enums\FacilityType;
use App\Enums\MaterialAidStatus;
use App\Enums\MilitaryStatus;
use App\Enums\RemoteVlkStatus;
use App\Enums\ServicemanStatus;
use App\Enums\Severity;
use App\Enums\TreatmentStatus;
use App\Models\Serviceman;
use App\Models\Unit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreServicemanRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Serviceman::class);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'unit_id' => ['required', 'integer', Rule::exists('units', 'id')],

            'full_name' => ['required', 'string', 'max:255'],
            'rank' => ['nullable', 'string', 'max:255'],
            'position' => ['nullable', 'string', 'max:255'],
            'tax_id' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'family_contact' => ['nullable', 'string'],
            'status' => ['nullable', Rule::enum(ServicemanStatus::class)],
            'military_status' => ['nullable', Rule::enum(MilitaryStatus::class)],
            'is_combat_veteran' => ['boolean'],
            'treatment_status' => ['nullable', Rule::enum(TreatmentStatus::class)],
            'curator_id' => ['nullable', 'integer', Rule::exists('users', 'id')],

            'evacuation_date' => ['nullable', 'date'],
            'diagnosis' => ['nullable', 'string'],
            'severity' => ['nullable', Rule::enum(Severity::class)],
            'has_amputation' => ['boolean'],
            'has_prosthetic' => ['boolean'],
            'has_certificate_5' => ['boolean'],
            'ecopfo_date' => ['nullable', 'date'],
            'disability_group' => ['nullable', Rule::enum(DisabilityGroup::class)],
            'work_capacity_loss_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'facility_type' => ['nullable', Rule::enum(FacilityType::class)],
            'facility_settlement_id' => ['nullable', 'integer', Rule::exists('settlements', 'id')],
            'facility_name' => ['nullable', 'string', 'max:255'],
            'remote_vlk_status' => ['nullable', Rule::enum(RemoteVlkStatus::class)],

            'material_aid_status' => ['nullable', Rule::enum(MaterialAidStatus::class)],

            'first_contact_after_evacuation_date' => ['nullable', 'date'],
            'last_contact_date' => ['nullable', 'date'],
            'last_visit_date' => ['nullable', 'date'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $unitId = $this->input('unit_id');

            if (! $unitId) {
                return;
            }

            $unit = Unit::with('battalion')->find($unitId);
            $user = $this->user();

            $allowed = match (true) {
                ! $unit => false,
                $user->isSuperAdmin() => true,
                $user->isBrigade() => $unit->battalion->brigade_id === $user->brigade_id,
                default => $unit->battalion_id === $user->battalion_id,
            };

            if (! $allowed) {
                $validator->errors()->add('unit_id', 'Ви не маєте доступу до цього підрозділу.');
            }
        });
    }
}
