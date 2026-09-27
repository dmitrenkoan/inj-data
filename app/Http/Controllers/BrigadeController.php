<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBrigadeRequest;
use App\Http\Requests\UpdateBrigadeRequest;
use App\Models\Brigade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class BrigadeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', Brigade::class);

        return Inertia::render('Brigades/Index', [
            'brigades' => Brigade::query()->withCount(['battalions', 'users'])->orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBrigadeRequest $request): RedirectResponse
    {
        Brigade::create($request->validated());

        return Redirect::route('brigades.index')->with('status', 'Військову частину додано.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBrigadeRequest $request, Brigade $brigade): RedirectResponse
    {
        $brigade->update($request->validated());

        return Redirect::route('brigades.index')->with('status', 'Військову частину оновлено.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brigade $brigade): RedirectResponse
    {
        Gate::authorize('delete', $brigade);

        if ($brigade->battalions()->exists() || $brigade->units()->exists()) {
            return Redirect::route('brigades.index')->with('error', 'Неможливо видалити військову частину, у якій є батальйони або підрозділи.');
        }

        $brigade->delete();

        return Redirect::route('brigades.index')->with('status', 'Військову частину видалено.');
    }
}
