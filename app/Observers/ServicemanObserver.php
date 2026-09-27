<?php

namespace App\Observers;

use App\Models\Serviceman;
use App\Models\ServicemanTreatmentFacility;
use App\Models\Settlement;

class ServicemanObserver
{
    /**
     * When the current treatment facility fields change, archive the
     * previous values as a treatment facility history entry.
     */
    public function updating(Serviceman $serviceman): void
    {
        $facilityFields = ['facility_type', 'facility_settlement_id', 'facility_name'];

        if (! $serviceman->isDirty($facilityFields)) {
            return;
        }

        $original = $serviceman->getRawOriginal();

        if (! array_filter(array_intersect_key($original, array_flip($facilityFields)))) {
            return;
        }

        $originalCity = $original['facility_settlement_id']
            ? Settlement::find($original['facility_settlement_id'])?->name
            : null;

        ServicemanTreatmentFacility::create([
            'serviceman_id' => $serviceman->id,
            'facility_type' => $original['facility_type'],
            'city' => $originalCity,
            'facility_name' => $original['facility_name'],
            'changed_at' => now(),
        ]);
    }
}
