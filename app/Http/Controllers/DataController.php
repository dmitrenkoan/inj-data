<?php

namespace App\Http\Controllers;

use App\Enums\FacilityType;
use App\Enums\MaterialAidStatus;
use App\Enums\PaymentIssueType;
use App\Enums\RemoteVlkStatus;
use App\Enums\ServicemanStatus;
use App\Enums\Severity;
use App\Enums\TreatmentStatus;
use App\Models\Battalion;
use App\Models\Brigade;
use App\Models\Serviceman;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DataController extends Controller
{
    /**
     * Display the treatment-location breakdown.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $servicemen = Serviceman::query()
            ->visibleTo($user)
            ->whereNotNull('facility_settlement_id')
            ->with(['unit.battalion.brigade', 'facilitySettlement'])
            ->when($request->filled('unit_id'), fn (Builder $query) => $query->where('unit_id', $request->integer('unit_id')))
            ->when(
                ! $request->filled('unit_id') && $request->filled('battalion_id'),
                fn (Builder $query) => $query->whereHas('unit', fn (Builder $unit) => $unit->where('battalion_id', $request->integer('battalion_id'))),
            )
            ->when(
                ! $request->filled('unit_id') && ! $request->filled('battalion_id') && $request->filled('brigade_id'),
                fn (Builder $query) => $query->whereHas('unit.battalion', fn (Builder $battalion) => $battalion->where('brigade_id', $request->integer('brigade_id'))),
            )
            ->get();

        $oblasts = $servicemen
            ->groupBy('facility_oblast')
            ->map(function ($group, $oblast) {
                $cities = $group
                    ->groupBy('facility_city')
                    ->map(function ($cityGroup, $city) {
                        return [
                            'city' => $city,
                            'count' => $cityGroup->count(),
                            'servicemen' => $cityGroup->map(fn (Serviceman $s) => [
                                'id' => $s->id,
                                'full_name' => $s->full_name,
                                'unit_label' => $this->unitLabel($s),
                            ])->values()->all(),
                        ];
                    })
                    ->sortByDesc('count')
                    ->values();

                return [
                    'oblast' => $oblast,
                    'count' => $group->count(),
                    'cities' => $cities,
                ];
            })
            ->sortByDesc('count')
            ->values();

        return Inertia::render('Data/Index', [
            'oblasts' => $oblasts,
            'totalCount' => $servicemen->count(),
            'filters' => $request->only(['brigade_id', 'battalion_id', 'unit_id']),
            'brigades' => $user->isSuperAdmin() ? Brigade::query()->orderBy('name')->get() : [],
            'battalions' => $user->isSuperAdmin() || $user->isBrigade()
                ? Battalion::query()->visibleTo($user)->with('brigade')->orderBy('name')->get()
                : [],
            'units' => Unit::query()->visibleTo($user)->with('battalion.brigade')->orderBy('name')->get(),
        ]);
    }

    /**
     * Display the accompaniment-status breakdown, filterable by brigade and battalion.
     */
    public function statistics(Request $request): Response
    {
        $user = $request->user();

        $base = Serviceman::query()
            ->visibleTo($user)
            ->when(
                $request->filled('battalion_id'),
                fn (Builder $query) => $query->whereHas('unit', fn (Builder $unit) => $unit->where('battalion_id', $request->integer('battalion_id'))),
            )
            ->when(
                ! $request->filled('battalion_id') && $request->filled('brigade_id'),
                fn (Builder $query) => $query->whereHas('unit.battalion', fn (Builder $battalion) => $battalion->where('brigade_id', $request->integer('brigade_id'))),
            );

        $total = (clone $base)->count();
        $percentage = fn (int $count): float => $total > 0 ? round($count / $total * 100, 1) : 0.0;

        $rows = collect(ServicemanStatus::cases())->map(function (ServicemanStatus $status) use ($base, $percentage) {
            $count = (clone $base)->where('status', $status)->count();

            return [
                'status' => $status->value,
                'label' => $status->label(),
                'count' => $count,
                'percentage' => $percentage($count),
            ];
        });

        $treatmentRows = collect(TreatmentStatus::cases())->map(function (TreatmentStatus $status) use ($base, $percentage) {
            $count = (clone $base)->where('treatment_status', $status)->count();

            return [
                'status' => $status->value,
                'label' => $status->label(),
                'count' => $count,
                'percentage' => $percentage($count),
            ];
        });

        $treatmentStatusNoneCount = (clone $base)->whereNull('treatment_status')->count();

        if ($treatmentStatusNoneCount > 0) {
            $treatmentRows->push([
                'status' => null,
                'label' => 'Без статусу',
                'count' => $treatmentStatusNoneCount,
                'percentage' => $percentage($treatmentStatusNoneCount),
            ]);
        }

        $forControls = (clone $base)->get([
            'id',
            'evacuation_date',
            'first_contact_after_evacuation_date',
            'last_contact_date',
            'last_visit_date',
        ]);

        $controlCounts = [
            'first_contact' => $forControls->filter(fn (Serviceman $s) => $s->needs_first_contact)->count(),
            'attention' => $forControls->filter(fn (Serviceman $s) => $s->needs_attention)->count(),
            'visit' => $forControls->filter(fn (Serviceman $s) => $s->needs_visit)->count(),
        ];

        $controls = collect([
            ['key' => 'first_contact', 'label' => 'Контроль першого контакту'],
            ['key' => 'attention', 'label' => 'Контроль регулярного контакту'],
            ['key' => 'visit', 'label' => 'Контроль відвідувань'],
        ])->map(function (array $control) use ($controlCounts, $total, $percentage) {
            $needsCount = $controlCounts[$control['key']];
            $okCount = $total - $needsCount;

            return [
                'key' => $control['key'],
                'label' => $control['label'],
                'ok_count' => $okCount,
                'ok_percentage' => $percentage($okCount),
                'needs_count' => $needsCount,
                'needs_percentage' => $percentage($needsCount),
            ];
        });

        $additionalRows = collect([
            [
                'key' => 'amputation',
                'label' => 'Ампутації',
                'count' => (clone $base)->where('has_amputation', true)->count(),
            ],
            [
                'key' => 'no_certificate_5',
                'label' => 'Відсутність довідки 5',
                'count' => (clone $base)->where('has_certificate_5', false)->count(),
            ],
            [
                'key' => 'additional_remuneration_issue',
                'label' => 'Проблеми з виплатою додаткової винагороди',
                'count' => (clone $base)->whereHas(
                    'paymentIssues',
                    fn (Builder $query) => $query->where('type', PaymentIssueType::AdditionalRemuneration),
                )->count(),
            ],
            [
                'key' => 'material_aid_needed',
                'label' => 'Потреба у виплаті матдопомоги',
                'count' => (clone $base)->where('material_aid_status', MaterialAidStatus::No)->count(),
            ],
            [
                'key' => 'no_combat_veteran_status',
                'label' => 'Відсутність статусу УБД',
                'count' => (clone $base)
                    ->where('is_combat_veteran', false)
                    ->where(
                        fn (Builder $query) => $query
                            ->whereNull('treatment_status')
                            ->orWhereNotIn('treatment_status', [TreatmentStatus::Awol, TreatmentStatus::DiedInHospital]),
                    )
                    ->count(),
            ],
        ])->map(fn (array $row) => [
            'key' => $row['key'],
            'label' => $row['label'],
            'count' => $row['count'],
            'percentage' => $percentage($row['count']),
        ]);

        $foreignBase = (clone $base)->where('facility_type', FacilityType::Foreign);
        $foreignTotal = (clone $foreignBase)->count();
        $foreignPercentage = fn (int $count): float => $foreignTotal > 0 ? round($count / $foreignTotal * 100, 1) : 0.0;

        $remoteVlkRows = collect(RemoteVlkStatus::cases())->map(function (RemoteVlkStatus $status) use ($foreignBase, $foreignPercentage) {
            $count = (clone $foreignBase)->where('remote_vlk_status', $status)->count();

            return [
                'status' => $status->value,
                'label' => $status->label(),
                'count' => $count,
                'percentage' => $foreignPercentage($count),
            ];
        });

        return Inertia::render('Data/Statistics', [
            'rows' => $rows,
            'treatmentRows' => $treatmentRows,
            'controls' => $controls,
            'additionalRows' => $additionalRows,
            'remoteVlkRows' => $remoteVlkRows,
            'remoteVlkTotal' => $foreignTotal,
            'total' => $total,
            'filters' => $request->only(['brigade_id', 'battalion_id']),
            'brigades' => $user->isSuperAdmin() ? Brigade::query()->orderBy('name')->get() : [],
            'battalions' => $user->isSuperAdmin() || $user->isBrigade()
                ? Battalion::query()->visibleTo($user)->with('brigade')->orderBy('name')->get()
                : [],
        ]);
    }

    /**
     * Display the brigade-level readiness report.
     */
    public function report(Request $request): Response
    {
        $user = $request->user();

        $servicemen = Serviceman::query()
            ->visibleTo($user)
            ->with(['unit.battalion.brigade', 'paymentIssues', 'awards'])
            ->get();

        $isSevereOrAmputated = fn (Serviceman $s) => $s->severity === Severity::Severe || $s->has_amputation;

        $rows = $servicemen
            ->groupBy(fn (Serviceman $s) => $s->unit?->battalion?->brigade?->id ?? 'none')
            ->map(function ($group) use ($isSevereOrAmputated) {
                $brigade = $group->first()->unit?->battalion?->brigade;

                return [
                    'brigade' => $brigade?->name ?? 'Без бригади',
                    'total' => $group->count(),
                    'severe' => $group->filter(fn (Serviceman $s) => $s->severity === Severity::Severe)->count(),
                    'amputated' => $group->filter(fn (Serviceman $s) => $s->has_amputation)->count(),
                    'prosthetic' => $group->filter(fn (Serviceman $s) => $s->has_prosthetic)->count(),
                    'needs_prosthetic' => $group->filter(fn (Serviceman $s) => $s->has_amputation && ! $s->has_prosthetic)->count(),
                    'severe_or_amputated_in_mou' => $group->filter(
                        fn (Serviceman $s) => $isSevereOrAmputated($s) && $s->facility_type === FacilityType::Mou,
                    )->count(),
                    'severe_or_amputated_in_moz' => $group->filter(
                        fn (Serviceman $s) => $isSevereOrAmputated($s) && $s->facility_type === FacilityType::Moz,
                    )->count(),
                    'gets_additional_remuneration' => $group->filter(
                        fn (Serviceman $s) => ! $s->paymentIssues->contains(fn ($issue) => $issue->type === PaymentIssueType::AdditionalRemuneration),
                    )->count(),
                    'awarded' => $group->filter(fn (Serviceman $s) => $s->awards->isNotEmpty())->count(),
                    'returned_to_duty' => $group->filter(fn (Serviceman $s) => $s->treatment_status === TreatmentStatus::OnDuty)->count(),
                ];
            })
            ->sortBy('brigade')
            ->values();

        $metricKeys = [
            'total',
            'severe',
            'amputated',
            'prosthetic',
            'needs_prosthetic',
            'severe_or_amputated_in_mou',
            'severe_or_amputated_in_moz',
            'gets_additional_remuneration',
            'awarded',
            'returned_to_duty',
        ];

        $totals = collect($metricKeys)
            ->mapWithKeys(fn (string $key) => [$key => $rows->sum($key)])
            ->put('brigade', 'Всього')
            ->all();

        return Inertia::render('Data/Report', [
            'rows' => $rows,
            'totals' => $totals,
        ]);
    }

    private function unitLabel(Serviceman $serviceman): string
    {
        $unit = $serviceman->unit;
        $battalion = $unit?->battalion;
        $brigade = $battalion?->brigade;

        return collect([$brigade?->name, $battalion?->name, $unit?->name])
            ->filter()
            ->implode(' / ');
    }
}
