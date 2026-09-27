<?php

namespace App\Http\Controllers;

use App\Enums\FacilityType;
use App\Models\Serviceman;
use App\Models\ServicemanTreatmentFacility;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class ServicemanTreatmentFacilityController extends Controller
{
    /**
     * Update a past treatment facility history entry.
     */
    public function update(Request $request, Serviceman $serviceman, ServicemanTreatmentFacility $treatmentFacility): RedirectResponse
    {
        Gate::authorize('update', $serviceman);
        abort_unless($treatmentFacility->serviceman_id === $serviceman->id, 404);

        $data = $request->validate([
            'facility_type' => ['nullable', Rule::enum(FacilityType::class)],
            'city' => ['nullable', 'string', 'max:255'],
            'facility_name' => ['nullable', 'string', 'max:255'],
            'return_to' => ['nullable', 'in:show,edit'],
        ]);

        $treatmentFacility->update(collect($data)->except('return_to')->all());

        return Redirect::route($this->returnRoute($data), $serviceman)->with('status', 'Запис історії оновлено.');
    }

    /**
     * Remove a past treatment facility history entry.
     */
    public function destroy(Request $request, Serviceman $serviceman, ServicemanTreatmentFacility $treatmentFacility): RedirectResponse
    {
        Gate::authorize('update', $serviceman);
        abort_unless($treatmentFacility->serviceman_id === $serviceman->id, 404);

        $data = $request->validate([
            'return_to' => ['nullable', 'in:show,edit'],
        ]);

        $treatmentFacility->delete();

        return Redirect::route($this->returnRoute($data), $serviceman)->with('status', 'Запис історії видалено.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function returnRoute(array $data): string
    {
        return ($data['return_to'] ?? null) === 'edit' ? 'servicemen.edit' : 'servicemen.show';
    }
}
