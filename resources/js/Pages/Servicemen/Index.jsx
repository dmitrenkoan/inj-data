import CityAutocomplete from '@/Components/CityAutocomplete';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

const STATUS_BADGE = {
    in_progress: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
    unreachable: 'bg-red-100 text-red-800',
};

const ALERT_BADGE = 'inline-block rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800';

function unitLabel(unit) {
    const brigade = unit.battalion?.brigade?.name;
    const battalion = unit.battalion?.name;

    return [brigade, battalion, unit.name].filter(Boolean).join(' / ');
}

function rowClassName(s) {
    const isRed = s.needs_first_contact || s.needs_attention || s.needs_visit;
    const isYellow = !isRed && s.needs_warning;

    if (isRed) {
        return 'bg-red-50 hover:bg-red-100';
    }

    if (isYellow) {
        return 'bg-yellow-50 hover:bg-yellow-100';
    }

    return 'hover:bg-gray-50';
}

function SortableHeader({ label, column, sort, direction, onSort }) {
    const isActive = sort === column;

    return (
        <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">
            <button
                type="button"
                onClick={() => onSort(column)}
                className="flex items-center gap-1 hover:text-gray-700"
            >
                {label}
                <span className="w-3 text-gray-400">{isActive ? (direction === 'asc' ? '▲' : '▼') : ''}</span>
            </button>
        </th>
    );
}

export default function Index({
    servicemen,
    units,
    brigades,
    curators,
    statuses,
    severities,
    militaryStatuses,
    treatmentStatuses,
    disabilityGroups,
    materialAidStatuses,
    facilityOblasts,
    facilitySettlementFilter,
    sort,
    direction,
    filters,
}) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [facilitySettlement, setFacilitySettlement] = useState(facilitySettlementFilter ?? null);

    const applyFilters = (next) => {
        router.get(route('servicemen.index'), { ...filters, sort, direction, ...next }, { preserveState: true, replace: true });
    };

    const submitSearch = (e) => {
        e.preventDefault();
        applyFilters({ search });
    };

    const resetFilters = () => {
        setSearch('');
        setFacilitySettlement(null);
        router.get(route('servicemen.index'), {}, { preserveState: true, replace: true });
    };

    const applySort = (column) => {
        const nextDirection = sort === column && direction === 'asc' ? 'desc' : 'asc';
        applyFilters({ sort: column, direction: nextDirection });
    };

    const hasActiveFilters = Object.values(filters).some((v) => v !== undefined && v !== null && v !== '');

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Поранені військовослужбовці
                    </h2>
                    <Link
                        href={route('servicemen.create')}
                        className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        + Додати картку
                    </Link>
                </div>
            }
        >
            <Head title="Поранені" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div className="mb-4 flex flex-wrap items-center gap-3 bg-white p-4 shadow sm:rounded-lg">
                        <form onSubmit={submitSearch} className="flex-1 min-w-[200px]">
                            <TextInput
                                className="w-full"
                                placeholder="Пошук за ПІБ..."
                                value={search}
                                onChange={(e) => setSearch(e.target.value)}
                            />
                        </form>

                        {brigades.length > 0 && (
                            <SelectInput
                                value={filters.brigade_id ?? ''}
                                onChange={(e) => applyFilters({ brigade_id: e.target.value })}
                            >
                                <option value="">Усі бригади</option>
                                {brigades.map((b) => (
                                    <option key={b.id} value={b.id}>
                                        {b.name}
                                    </option>
                                ))}
                            </SelectInput>
                        )}

                        {units.length > 1 && (
                            <SelectInput
                                value={filters.unit_id ?? ''}
                                onChange={(e) => applyFilters({ unit_id: e.target.value })}
                            >
                                <option value="">Усі підрозділи</option>
                                {units.map((u) => (
                                    <option key={u.id} value={u.id}>
                                        {unitLabel(u)}
                                    </option>
                                ))}
                            </SelectInput>
                        )}

                        <SelectInput
                            value={filters.status ?? ''}
                            onChange={(e) => applyFilters({ status: e.target.value })}
                        >
                            <option value="">Усі статуси супроводження</option>
                            {statuses.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </SelectInput>

                        <SelectInput
                            value={filters.severity ?? ''}
                            onChange={(e) => applyFilters({ severity: e.target.value })}
                        >
                            <option value="">Уся тяжкість</option>
                            {severities.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </SelectInput>

                        <SelectInput
                            value={filters.military_status ?? ''}
                            onChange={(e) => applyFilters({ military_status: e.target.value })}
                        >
                            <option value="">Усі статуси в/сл</option>
                            {militaryStatuses.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </SelectInput>

                        <SelectInput
                            value={filters.treatment_status ?? ''}
                            onChange={(e) => applyFilters({ treatment_status: e.target.value })}
                        >
                            <option value="">Усі статуси лікування</option>
                            {treatmentStatuses.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </SelectInput>

                        <SelectInput
                            value={filters.disability_group ?? ''}
                            onChange={(e) => applyFilters({ disability_group: e.target.value })}
                        >
                            <option value="">Усі групи інвалідності</option>
                            {disabilityGroups.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </SelectInput>

                        <SelectInput
                            value={filters.material_aid_status ?? ''}
                            onChange={(e) => applyFilters({ material_aid_status: e.target.value })}
                        >
                            <option value="">Уся матдопомога</option>
                            {materialAidStatuses.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </SelectInput>

                        <SelectInput
                            value={filters.facility_oblast ?? ''}
                            onChange={(e) => applyFilters({ facility_oblast: e.target.value })}
                        >
                            <option value="">Уся область лікування</option>
                            {facilityOblasts.map((o) => (
                                <option key={o} value={o}>
                                    {o}
                                </option>
                            ))}
                        </SelectInput>

                        <div className="min-w-[220px]">
                            <CityAutocomplete
                                className="w-full"
                                value={facilitySettlement}
                                onChange={(settlement) => {
                                    setFacilitySettlement(settlement);
                                    applyFilters({ facility_settlement_id: settlement?.id ?? '' });
                                }}
                                requireSelection={false}
                            />
                        </div>

                        {curators.length > 0 && (
                            <SelectInput
                                value={filters.curator_id ?? ''}
                                onChange={(e) => applyFilters({ curator_id: e.target.value })}
                            >
                                <option value="">Усі куратори</option>
                                {curators.map((c) => (
                                    <option key={c.id} value={c.id}>
                                        {c.name}
                                    </option>
                                ))}
                            </SelectInput>
                        )}

                        <SelectInput
                            value={filters.has_amputation ?? ''}
                            onChange={(e) => applyFilters({ has_amputation: e.target.value })}
                        >
                            <option value="">Ампутації: усі</option>
                            <option value="1">Ампутації: так</option>
                            <option value="0">Ампутації: ні</option>
                        </SelectInput>

                        <div className="flex items-center gap-2">
                            <label className="text-sm text-gray-500">Евакуація з</label>
                            <TextInput
                                type="date"
                                value={filters.evacuation_date_from ?? ''}
                                onChange={(e) => applyFilters({ evacuation_date_from: e.target.value })}
                            />
                            <label className="text-sm text-gray-500">по</label>
                            <TextInput
                                type="date"
                                value={filters.evacuation_date_to ?? ''}
                                onChange={(e) => applyFilters({ evacuation_date_to: e.target.value })}
                            />
                        </div>

                        {hasActiveFilters && (
                            <button
                                type="button"
                                onClick={resetFilters}
                                className="text-sm text-gray-500 hover:text-gray-700 hover:underline"
                            >
                                Скинути фільтри
                            </button>
                        )}
                    </div>

                    <div className="overflow-x-auto bg-white shadow sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <SortableHeader label="ПІБ" column="full_name" sort={sort} direction={direction} onSort={applySort} />
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Підрозділ</th>
                                    <SortableHeader label="Дата евакуації" column="evacuation_date" sort={sort} direction={direction} onSort={applySort} />
                                    <SortableHeader label="Тяжкість" column="severity" sort={sort} direction={direction} onSort={applySort} />
                                    <SortableHeader label="Статус супроводження" column="status" sort={sort} direction={direction} onSort={applySort} />
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Позначки</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {servicemen.data.map((s) => (
                                    <tr key={s.id} className={rowClassName(s)}>
                                        <td className="px-4 py-3">
                                            <Link
                                                href={route('servicemen.show', s.id)}
                                                className="font-medium text-indigo-600 hover:underline"
                                            >
                                                {s.full_name}
                                            </Link>
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-600">
                                            {s.unit ? unitLabel(s.unit) : '—'}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-600">
                                            {s.evacuation_date ? String(s.evacuation_date).slice(0, 10) : '—'}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-600">
                                            {severities.find((o) => o.value === s.severity)?.label ?? '—'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <span
                                                className={
                                                    'rounded-full px-2 py-1 text-xs font-medium ' +
                                                    (STATUS_BADGE[s.status] ?? 'bg-gray-100 text-gray-800')
                                                }
                                            >
                                                {statuses.find((o) => o.value === s.status)?.label ?? s.status}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3">
                                            <div className="flex flex-wrap gap-1">
                                                {s.needs_first_contact && (
                                                    <span className={ALERT_BADGE}>Потребує першого контакту</span>
                                                )}
                                                {s.needs_attention && (
                                                    <span className={ALERT_BADGE}>Потребує уваги</span>
                                                )}
                                                {s.needs_visit && (
                                                    <span className={ALERT_BADGE}>Потребує відвідування</span>
                                                )}
                                            </div>
                                        </td>
                                    </tr>
                                ))}

                                {servicemen.data.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-4 py-8 text-center text-sm text-gray-500">
                                            Записів не знайдено.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {servicemen.links.length > 3 && (
                        <div className="mt-4 flex flex-wrap gap-1">
                            {servicemen.links.map((link, i) => (
                                <Link
                                    key={i}
                                    href={link.url ?? '#'}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                    preserveScroll
                                    className={
                                        'rounded-md px-3 py-1 text-sm ' +
                                        (link.active
                                            ? 'bg-indigo-600 text-white'
                                            : 'bg-white text-gray-700 hover:bg-gray-100') +
                                        (!link.url ? ' pointer-events-none text-gray-300' : '')
                                    }
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
