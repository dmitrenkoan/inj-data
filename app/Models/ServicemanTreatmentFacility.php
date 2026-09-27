<?php

namespace App\Models;

use App\Enums\FacilityType;
use Database\Factories\ServicemanTreatmentFacilityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['serviceman_id', 'facility_type', 'city', 'facility_name', 'changed_at'])]
class ServicemanTreatmentFacility extends Model
{
    /** @use HasFactory<ServicemanTreatmentFacilityFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'facility_type' => FacilityType::class,
            'changed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Serviceman, $this>
     */
    public function serviceman(): BelongsTo
    {
        return $this->belongsTo(Serviceman::class);
    }
}
