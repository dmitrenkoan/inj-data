<?php

namespace App\Http\Controllers;

use App\Enums\DisabilityGroup;
use App\Enums\FacilityType;
use App\Enums\MaterialAidStatus;
use App\Enums\MilitaryStatus;
use App\Enums\PaymentIssueType;
use App\Enums\RemoteVlkStatus;
use App\Enums\ServicemanStatus;
use App\Enums\Severity;
use App\Enums\TreatmentStatus;
use App\Http\Requests\StoreServicemanRequest;
use App\Http\Requests\UpdateServicemanRequest;
use App\Models\Brigade;
use App\Models\Serviceman;
use App\Models\Settlement;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ServicemanController extends Controller
{
    private const SORTABLE_COLUMNS = ['full_name', 'evacuation_date', 'severity', 'status'];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $sort = in_array($request->string('sort')->value(), self::SORTABLE_COLUMNS, true)
            ? $request->string('sort')->value()
            : 'full_name';
        $direction = $request->string('direction')->value() === 'desc' ? 'desc' : 'asc';

        $servicemen = Serviceman::query()
            ->visibleTo($user)
            ->with(['unit.battalion.brigade', 'curator'])
            ->when($request->string('search')->trim()->isNotEmpty(), fn ($query) => $query->where('full_name', 'ilike', '%'.$request->string('search')->trim().'%'))
            ->when($request->filled('brigade_id'), fn ($query) => $query->whereHas('unit.battalion', fn ($q) => $q->where('brigade_id', $request->integer('brigade_id'))))
            ->when($request->filled('unit_id'), fn ($query) => $query->where('unit_id', $request->integer('unit_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('severity'), fn ($query) => $query->where('severity', $request->string('severity')))
            ->when($request->filled('military_status'), fn ($query) => $query->where('military_status', $request->string('military_status')))
            ->when($request->filled('treatment_status'), fn ($query) => $query->where('treatment_status', $request->string('treatment_status')))
            ->when($request->filled('disability_group'), fn ($query) => $query->where('disability_group', $request->string('disability_group')))
            ->when($request->filled('material_aid_status'), fn ($query) => $query->where('material_aid_status', $request->string('material_aid_status')))
            ->when($request->filled('curator_id'), fn ($query) => $query->where('curator_id', $request->integer('curator_id')))
            ->when($request->filled('has_amputation'), fn ($query) => $query->where('has_amputation', $request->boolean('has_amputation')))
            ->when($request->filled('evacuation_date_from'), fn ($query) => $query->whereDate('evacuation_date', '>=', $request->string('evacuation_date_from')))
            ->when($request->filled('evacuation_date_to'), fn ($query) => $query->whereDate('evacuation_date', '<=', $request->string('evacuation_date_to')))
            ->when($request->filled('facility_oblast'), fn ($query) => $query->whereHas('facilitySettlement', fn ($q) => $q->where('oblast', $request->string('facility_oblast'))))
            ->when($request->filled('facility_settlement_id'), fn ($query) => $query->where('facility_settlement_id', $request->integer('facility_settlement_id')))
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Servicemen/Index', [
            'servicemen' => $servicemen,
            'sort' => $sort,
            'direction' => $direction,
            'units' => Unit::query()->visibleTo($user)->with('battalion.brigade')->orderBy('name')->get(),
            'brigades' => $user->isSuperAdmin() ? Brigade::query()->orderBy('name')->get() : [],
            'curators' => User::query()->availableAsCuratorFor($user)->orderBy('name')->get(),
            'statuses' => $this->enumOptions(ServicemanStatus::cases()),
            'severities' => $this->enumOptions(Severity::cases()),
            'militaryStatuses' => $this->enumOptions(MilitaryStatus::cases()),
            'treatmentStatuses' => $this->enumOptions(TreatmentStatus::cases()),
            'disabilityGroups' => $this->enumOptions(DisabilityGroup::cases()),
            'materialAidStatuses' => $this->enumOptions(MaterialAidStatus::cases()),
            'facilityOblasts' => Settlement::query()->distinct()->orderBy('oblast')->pluck('oblast'),
            'facilitySettlementFilter' => $request->filled('facility_settlement_id')
                ? Settlement::find($request->integer('facility_settlement_id'))
                : null,
            'filters' => $request->only([
                'search',
                'brigade_id',
                'unit_id',
                'status',
                'severity',
                'military_status',
                'treatment_status',
                'disability_group',
                'material_aid_status',
                'curator_id',
                'has_amputation',
                'evacuation_date_from',
                'evacuation_date_to',
                'facility_oblast',
                'facility_settlement_id',
            ]),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request): Response
    {
        Gate::authorize('create', Serviceman::class);

        return Inertia::render('Servicemen/Create', $this->formOptions($request));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServicemanRequest $request): RedirectResponse
    {
        $serviceman = Serviceman::create($request->validated());

        return Redirect::route('servicemen.show', $serviceman)->with('status', 'Картку створено.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Serviceman $serviceman): Response
    {
        Gate::authorize('view', $serviceman);

        $serviceman->load(['unit.battalion.brigade', 'curator', 'facilitySettlement', 'treatmentFacilityHistory', 'paymentIssues', 'awards']);

        return Inertia::render('Servicemen/Show', [
            'serviceman' => $serviceman,
            'paymentIssueTypes' => $this->enumOptions(PaymentIssueType::cases()),
            'statuses' => $this->enumOptions(ServicemanStatus::cases()),
            'severities' => $this->enumOptions(Severity::cases()),
            'disabilityGroups' => $this->enumOptions(DisabilityGroup::cases()),
            'facilityTypes' => $this->enumOptions(FacilityType::cases()),
            'remoteVlkStatuses' => $this->enumOptions(RemoteVlkStatus::cases()),
            'materialAidStatuses' => $this->enumOptions(MaterialAidStatus::cases()),
            'militaryStatuses' => $this->enumOptions(MilitaryStatus::cases()),
            'treatmentStatuses' => $this->enumOptions(TreatmentStatus::cases()),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Serviceman $serviceman): Response
    {
        Gate::authorize('update', $serviceman);

        $serviceman->load(['facilitySettlement', 'treatmentFacilityHistory', 'paymentIssues', 'awards']);

        return Inertia::render('Servicemen/Edit', [
            ...$this->formOptions($request),
            'serviceman' => $serviceman,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServicemanRequest $request, Serviceman $serviceman): RedirectResponse
    {
        $serviceman->update($request->validated());

        return Redirect::route('servicemen.show', $serviceman)->with('status', 'Картку оновлено.');
    }

    /**
     * Change the serviceman's current treatment facility. The previous
     * values are archived to the treatment facility history automatically.
     */
    public function updateFacility(Request $request, Serviceman $serviceman): RedirectResponse
    {
        Gate::authorize('update', $serviceman);

        $data = $request->validate([
            'facility_type' => ['nullable', Rule::enum(FacilityType::class)],
            'facility_settlement_id' => ['nullable', 'integer', Rule::exists('settlements', 'id')],
            'facility_name' => ['nullable', 'string', 'max:255'],
            'return_to' => ['nullable', 'in:show,edit'],
        ]);

        $serviceman->update([
            'facility_type' => $data['facility_type'] ?? null,
            'facility_settlement_id' => $data['facility_settlement_id'] ?? null,
            'facility_name' => $data['facility_name'] ?? null,
        ]);

        $route = ($data['return_to'] ?? null) === 'edit' ? 'servicemen.edit' : 'servicemen.show';

        return Redirect::route($route, $serviceman)->with('status', 'Заклад лікування оновлено.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Serviceman $serviceman): RedirectResponse
    {
        Gate::authorize('delete', $serviceman);

        $serviceman->delete();

        return Redirect::route('servicemen.index')->with('status', 'Картку видалено.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(Request $request): array
    {
        $user = $request->user();

        return [
            'units' => Unit::query()->visibleTo($user)->with('battalion.brigade')->orderBy('name')->get(),
            'curators' => User::query()->availableAsCuratorFor($user)->orderBy('name')->get(),
            'statuses' => $this->enumOptions(ServicemanStatus::cases()),
            'severities' => $this->enumOptions(Severity::cases()),
            'disabilityGroups' => $this->enumOptions(DisabilityGroup::cases()),
            'facilityTypes' => $this->enumOptions(FacilityType::cases()),
            'remoteVlkStatuses' => $this->enumOptions(RemoteVlkStatus::cases()),
            'materialAidStatuses' => $this->enumOptions(MaterialAidStatus::cases()),
            'paymentIssueTypes' => $this->enumOptions(PaymentIssueType::cases()),
            'militaryStatuses' => $this->enumOptions(MilitaryStatus::cases()),
            'treatmentStatuses' => $this->enumOptions(TreatmentStatus::cases()),
        ];
    }

    /**
     * @param  array<int, \BackedEnum>  $cases
     * @return array<int, array{value: string, label: string}>
     */
    private function enumOptions(array $cases): array
    {
        return array_map(fn ($case) => ['value' => $case->value, 'label' => $case->label()], $cases);
    }
}
