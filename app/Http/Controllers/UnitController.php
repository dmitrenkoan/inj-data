<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUnitRequest;
use App\Http\Requests\UpdateUnitRequest;
use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class UnitController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('Units/Index', [
            'units' => Unit::query()->visibleTo($user)->with(['battalion.brigade', 'brigade'])->withCount('servicemen')->orderBy('name')->get(),
            'battalions' => $user->isBattalion() ? [] : Battalion::query()->visibleTo($user)->with('brigade')->orderBy('name')->get(),
            'brigades' => $user->isSuperAdmin() ? Brigade::query()->orderBy('name')->get() : [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUnitRequest $request): RedirectResponse
    {
        Unit::create($request->validated());

        return Redirect::route('units.index')->with('status', 'Підрозділ додано.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateUnitRequest $request, Unit $unit): RedirectResponse
    {
        $data = $request->validated();
        $data['battalion_id'] ??= null;
        $data['brigade_id'] ??= null;

        $unit->update($data);

        return Redirect::route('units.index')->with('status', 'Підрозділ оновлено.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Unit $unit): RedirectResponse
    {
        Gate::authorize('delete', $unit);

        if ($unit->servicemen()->exists()) {
            return Redirect::route('units.index')->with('error', 'Неможливо видалити підрозділ, до якого прив\'язані поранені.');
        }

        $unit->delete();

        return Redirect::route('units.index')->with('status', 'Підрозділ видалено.');
    }
}
