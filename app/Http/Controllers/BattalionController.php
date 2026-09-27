<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBattalionRequest;
use App\Http\Requests\UpdateBattalionRequest;
use App\Models\Battalion;
use App\Models\Brigade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class BattalionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Battalion::class);

        $user = $request->user();

        return Inertia::render('Battalions/Index', [
            'battalions' => Battalion::query()->visibleTo($user)->with('brigade')->withCount('units')->orderBy('name')->get(),
            'brigades' => $user->isSuperAdmin() ? Brigade::query()->orderBy('name')->get() : [],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBattalionRequest $request): RedirectResponse
    {
        Battalion::create($request->validated());

        return Redirect::route('battalions.index')->with('status', 'Батальйон додано.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBattalionRequest $request, Battalion $battalion): RedirectResponse
    {
        $battalion->update($request->validated());

        return Redirect::route('battalions.index')->with('status', 'Батальйон оновлено.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Battalion $battalion): RedirectResponse
    {
        Gate::authorize('delete', $battalion);

        if ($battalion->units()->exists()) {
            return Redirect::route('battalions.index')->with('error', 'Неможливо видалити батальйон, у якому є підрозділи.');
        }

        $battalion->delete();

        return Redirect::route('battalions.index')->with('status', 'Батальйон видалено.');
    }
}
