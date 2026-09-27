<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAwardRequest;
use App\Models\Serviceman;
use App\Models\ServicemanAward;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;

class ServicemanAwardController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAwardRequest $request, Serviceman $serviceman): RedirectResponse
    {
        $data = $request->validated();

        $serviceman->awards()->create(collect($data)->only(['name', 'submission_date', 'awarded_date'])->all());

        return Redirect::route($this->returnRoute($data), $serviceman)->with('status', 'Нагороду додано.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Serviceman $serviceman, ServicemanAward $award): RedirectResponse
    {
        Gate::authorize('update', $serviceman);
        abort_unless($award->serviceman_id === $serviceman->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'submission_date' => ['nullable', 'date'],
            'awarded_date' => ['nullable', 'date'],
            'return_to' => ['nullable', 'in:show,edit'],
        ]);

        $award->update(collect($data)->only(['name', 'submission_date', 'awarded_date'])->all());

        return Redirect::route($this->returnRoute($data), $serviceman)->with('status', 'Нагороду оновлено.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Serviceman $serviceman, ServicemanAward $award): RedirectResponse
    {
        Gate::authorize('update', $serviceman);
        abort_unless($award->serviceman_id === $serviceman->id, 404);

        $data = $request->validate([
            'return_to' => ['nullable', 'in:show,edit'],
        ]);

        $award->delete();

        return Redirect::route($this->returnRoute($data), $serviceman)->with('status', 'Нагороду видалено.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function returnRoute(array $data): string
    {
        return ($data['return_to'] ?? null) === 'edit' ? 'servicemen.edit' : 'servicemen.show';
    }
}
