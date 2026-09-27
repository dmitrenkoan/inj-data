<?php

namespace App\Models;

use App\Enums\DisabilityGroup;
use App\Enums\FacilityType;
use App\Enums\MaterialAidStatus;
use App\Enums\MilitaryStatus;
use App\Enums\RemoteVlkStatus;
use App\Enums\ServicemanStatus;
use App\Enums\Severity;
use App\Enums\TreatmentStatus;
use App\Observers\ServicemanObserver;
use Database\Factories\ServicemanFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

#[ObservedBy(ServicemanObserver::class)]
#[Appends([
    'vlk4_months_date',
    'vlk8_months_date',
    'vlk12_months_date',
    'facility_city',
    'facility_oblast',
    'facility_raion',
    'needs_first_contact',
    'needs_warning',
    'needs_attention',
    'needs_visit',
])]
#[Fillable([
    'unit_id',
    'full_name',
    'rank',
    'position',
    'tax_id',
    'phone',
    'birth_date',
    'notes',
    'family_contact',
    'status',
    'military_status',
    'is_combat_veteran',
    'treatment_status',
    'curator_id',
    'evacuation_date',
    'diagnosis',
    'severity',
    'has_amputation',
    'has_prosthetic',
    'has_certificate_5',
    'ecopfo_date',
    'disability_group',
    'work_capacity_loss_percent',
    'facility_type',
    'facility_settlement_id',
    'facility_name',
    'remote_vlk_status',
    'material_aid_status',
    'first_contact_after_evacuation_date',
    'last_contact_date',
    'last_visit_date',
])]
class Serviceman extends Model
{
    /** @use HasFactory<ServicemanFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'status' => ServicemanStatus::class,
            'military_status' => MilitaryStatus::class,
            'is_combat_veteran' => 'boolean',
            'treatment_status' => TreatmentStatus::class,
            'evacuation_date' => 'date',
            'severity' => Severity::class,
            'has_amputation' => 'boolean',
            'has_prosthetic' => 'boolean',
            'has_certificate_5' => 'boolean',
            'ecopfo_date' => 'date',
            'disability_group' => DisabilityGroup::class,
            'work_capacity_loss_percent' => 'integer',
            'facility_type' => FacilityType::class,
            'remote_vlk_status' => RemoteVlkStatus::class,
            'material_aid_status' => MaterialAidStatus::class,
            'first_contact_after_evacuation_date' => 'date',
            'last_contact_date' => 'date',
            'last_visit_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Unit, $this>
     */
    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function curator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'curator_id');
    }

    /**
     * @return BelongsTo<Settlement, $this>
     */
    public function facilitySettlement(): BelongsTo
    {
        return $this->belongsTo(Settlement::class, 'facility_settlement_id');
    }

    /**
     * @return HasMany<ServicemanTreatmentFacility, $this>
     */
    public function treatmentFacilityHistory(): HasMany
    {
        return $this->hasMany(ServicemanTreatmentFacility::class)->orderByDesc('changed_at');
    }

    /**
     * @return HasMany<ServicemanPaymentIssue, $this>
     */
    public function paymentIssues(): HasMany
    {
        return $this->hasMany(ServicemanPaymentIssue::class)->latest();
    }

    /**
     * @return HasMany<ServicemanAward, $this>
     */
    public function awards(): HasMany
    {
        return $this->hasMany(ServicemanAward::class)->latest('submission_date');
    }

    protected function vlk4MonthsDate(): Attribute
    {
        return Attribute::get(fn () => $this->evacuation_date?->copy()->addMonths(4));
    }

    protected function vlk8MonthsDate(): Attribute
    {
        return Attribute::get(fn () => $this->evacuation_date?->copy()->addMonths(8));
    }

    protected function vlk12MonthsDate(): Attribute
    {
        return Attribute::get(fn () => $this->evacuation_date?->copy()->addMonths(12));
    }

    protected function facilityCity(): Attribute
    {
        return Attribute::get(fn () => $this->facilitySettlement?->name);
    }

    protected function facilityOblast(): Attribute
    {
        return Attribute::get(fn () => $this->facilitySettlement?->oblast);
    }

    protected function facilityRaion(): Attribute
    {
        return Attribute::get(fn () => $this->facilitySettlement?->raion);
    }

    /**
     * No first contact has been logged yet and the configured number of
     * days has passed since evacuation.
     */
    protected function needsFirstContact(): Attribute
    {
        return Attribute::get(function () {
            if ($this->first_contact_after_evacuation_date !== null) {
                return false;
            }

            return $this->isOverdue($this->evacuation_date, AppSetting::current()->first_contact_days);
        });
    }

    /**
     * It has been longer than the "warning" threshold since the last
     * contact (or, if there was never one, since evacuation).
     */
    protected function needsWarning(): Attribute
    {
        return Attribute::get(fn () => $this->isOverdue(
            $this->last_contact_date ?? $this->evacuation_date,
            AppSetting::current()->warning_days,
        ));
    }

    /**
     * It has been longer than the "attention" threshold since the last
     * contact (or, if there was never one, since evacuation).
     */
    protected function needsAttention(): Attribute
    {
        return Attribute::get(fn () => $this->isOverdue(
            $this->last_contact_date ?? $this->evacuation_date,
            AppSetting::current()->attention_days,
        ));
    }

    /**
     * It has been longer than the configured number of days since the
     * last visit (or, if there was never one, since evacuation).
     */
    protected function needsVisit(): Attribute
    {
        return Attribute::get(fn () => $this->isOverdue(
            $this->last_visit_date ?? $this->evacuation_date,
            AppSetting::current()->visit_days,
        ));
    }

    private function isOverdue(?Carbon $reference, ?int $thresholdDays): bool
    {
        if ($reference === null || $thresholdDays === null || $reference->isFuture()) {
            return false;
        }

        return $reference->diffInDays(now()) > $thresholdDays;
    }

    /**
     * Scope the query to the servicemen a given user is allowed to see.
     *
     * @param  Builder<Serviceman>  $query
     * @return Builder<Serviceman>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isBrigade()) {
            return $query->whereHas(
                'unit',
                fn (Builder $unit) => $unit->forBrigade($user->brigade_id),
            );
        }

        return $query->whereHas(
            'unit',
            fn (Builder $unit) => $unit->where('battalion_id', $user->battalion_id),
        );
    }
}
