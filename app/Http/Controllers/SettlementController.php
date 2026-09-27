<?php

namespace App\Http\Controllers;

use App\Models\Settlement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class SettlementController extends Controller
{
    /**
     * Display a paginated, searchable listing of the resource.
     */
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', Settlement::class);

        $search = $request->string('search')->trim();

        $settlements = Settlement::query()
            ->when($search->isNotEmpty(), fn ($query) => $query->where('name', 'ilike', "%{$search}%"))
            ->orderBy('oblast')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Settlements/Index', [
            'settlements' => $settlements,
            'filters' => ['search' => (string) $search],
        ]);
    }

    /**
     * Search settlements for the treatment-location autocomplete. Available
     * to any authenticated user (read-only).
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->string('q')->trim();

        if ($query->isEmpty()) {
            return response()->json([]);
        }

        $settlements = Settlement::query()
            ->where('name', 'ilike', "{$query}%")
            ->orderByRaw('lower(name) = ? desc', [mb_strtolower($query)])
            ->orderByRaw("(type = 'місто') desc")
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'raion', 'oblast', 'type']);

        return response()->json($settlements);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('create', Settlement::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'raion' => ['nullable', 'string', 'max:255'],
            'oblast' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
        ]);

        Settlement::create($data);

        return Redirect::route('settlements.index')->with('status', 'Населений пункт додано.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Settlement $settlement): RedirectResponse
    {
        Gate::authorize('update', $settlement);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'raion' => ['nullable', 'string', 'max:255'],
            'oblast' => ['required', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
        ]);

        $settlement->update($data);

        return Redirect::route('settlements.index')->with('status', 'Населений пункт оновлено.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Settlement $settlement): RedirectResponse
    {
        Gate::authorize('delete', $settlement);

        if ($settlement->servicemen()->exists()) {
            return Redirect::route('settlements.index')->with('error', 'Неможливо видалити населений пункт, прив\'язаний до карток.');
        }

        $settlement->delete();

        return Redirect::route('settlements.index')->with('status', 'Населений пункт видалено.');
    }
}
