import SelectInput from '@/Components/SelectInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import ukraineMapSvg from '../../data/ukraine-map.svg?raw';
import DataTabs from './Partials/DataTabs';

// Sequential single-hue ramp (blue, light -> dark), steps 100-700.
const COLOR_STEPS = [
    '#cde2fb', '#b7d3f6', '#9ec5f4', '#86b6ef', '#6da7ec', '#5598e7',
    '#3987e5', '#2a78d6', '#256abf', '#1c5cab', '#184f95', '#104281', '#0d366b',
];

function colorForRatio(ratio) {
    if (ratio <= 0) {
        return null;
    }

    const idx = Math.min(COLOR_STEPS.length - 1, Math.max(0, Math.round(ratio * (COLOR_STEPS.length - 1))));

    return COLOR_STEPS[idx];
}

function battalionLabel(battalion) {
    return battalion.brigade ? `${battalion.brigade.name} / ${battalion.name}` : battalion.name;
}

function unitLabel(unit) {
    const brigade = unit.battalion?.brigade?.name;
    const battalion = unit.battalion?.name;

    return [brigade, battalion, unit.name].filter(Boolean).join(' / ');
}

export default function Index({ oblasts, totalCount, filters, brigades, battalions, units }) {
    const [view, setView] = useState({ level: 'oblasts' });
    const mapContainerRef = useRef(null);

    const maxCount = Math.max(1, ...oblasts.map((o) => o.count));

    useEffect(() => {
        const svg = mapContainerRef.current?.querySelector('svg');

        if (!svg) {
            return;
        }

        svg.querySelectorAll('path.ukr-region').forEach((path) => {
            const name = path.getAttribute('data-name');
            const entry = oblasts.find((o) => o.oblast === name);
            const color = entry ? colorForRatio(entry.count / maxCount) : null;

            if (color) {
                path.style.setProperty('--c', color);
            } else {
                path.style.removeProperty('--c');
            }

            path.style.cursor = entry ? 'pointer' : 'default';

            const titleEl = path.querySelector('title');
            if (titleEl) {
                titleEl.textContent = entry ? `${name}: ${entry.count}` : name;
            }

            path.onclick = entry
                ? () => setView({ level: 'cities', oblast: entry.oblast, cities: entry.cities })
                : null;
        });
    }, [oblasts, view.level]);

    const applyFilters = (next) => {
        router.get(
            route('data.index'),
            {
                brigade_id: filters.brigade_id ?? '',
                battalion_id: filters.battalion_id ?? '',
                unit_id: filters.unit_id ?? '',
                ...next,
            },
            { preserveState: true, replace: true },
        );
    };

    const availableBattalions = battalions.filter(
        (b) => !filters.brigade_id || String(b.brigade_id) === String(filters.brigade_id),
    );
    const availableUnits = units.filter((u) => {
        if (filters.battalion_id) {
            return String(u.battalion_id) === String(filters.battalion_id);
        }
        if (filters.brigade_id) {
            return String(u.battalion?.brigade_id) === String(filters.brigade_id);
        }
        return true;
    });

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Дані
                </h2>
            }
        >
            <Head title="Дані" />

            <div className="py-8">
                <div className="mx-auto max-w-6xl sm:px-6 lg:px-8">
                    <DataTabs active="map" />

                    <div className="mb-4 flex flex-wrap items-center gap-3 bg-white p-4 shadow sm:rounded-lg">
                        <span className="text-sm text-gray-500">
                            Всього на лікуванні: <span className="font-semibold text-gray-900">{totalCount}</span>
                        </span>

                        {brigades.length > 0 && (
                            <SelectInput
                                value={filters.brigade_id ?? ''}
                                onChange={(e) => applyFilters({ brigade_id: e.target.value, battalion_id: '', unit_id: '' })}
                            >
                                <option value="">Усі військові частини</option>
                                {brigades.map((b) => (
                                    <option key={b.id} value={b.id}>{b.name}</option>
                                ))}
                            </SelectInput>
                        )}

                        {battalions.length > 0 && (
                            <SelectInput
                                value={filters.battalion_id ?? ''}
                                onChange={(e) => applyFilters({ battalion_id: e.target.value, unit_id: '' })}
                            >
                                <option value="">Усі батальйони</option>
                                {availableBattalions.map((b) => (
                                    <option key={b.id} value={b.id}>{battalionLabel(b)}</option>
                                ))}
                            </SelectInput>
                        )}

                        {units.length > 1 && (
                            <SelectInput
                                value={filters.unit_id ?? ''}
                                onChange={(e) => applyFilters({ unit_id: e.target.value })}
                            >
                                <option value="">Усі підрозділи</option>
                                {availableUnits.map((u) => (
                                    <option key={u.id} value={u.id}>{unitLabel(u)}</option>
                                ))}
                            </SelectInput>
                        )}
                    </div>

                    {view.level === 'oblasts' && (
                        <div className="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div className="bg-white p-4 shadow sm:rounded-lg">
                                <div
                                    ref={mapContainerRef}
                                    className="[&_svg]:h-auto [&_svg]:w-full"
                                    dangerouslySetInnerHTML={{ __html: ukraineMapSvg }}
                                />
                            </div>

                            <div className="overflow-x-auto bg-white shadow sm:rounded-lg">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Область</th>
                                            <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Поранених</th>
                                        </tr>
                                    </thead>
                                    <tbody className="divide-y divide-gray-200">
                                        {oblasts.map((o) => (
                                            <tr key={o.oblast} className="hover:bg-gray-50">
                                                <td className="px-4 py-3 text-sm">
                                                    <button
                                                        type="button"
                                                        className="font-medium text-indigo-600 hover:underline"
                                                        onClick={() => setView({ level: 'cities', oblast: o.oblast, cities: o.cities })}
                                                    >
                                                        {o.oblast}
                                                    </button>
                                                </td>
                                                <td className="px-4 py-3 text-sm text-gray-600">{o.count}</td>
                                            </tr>
                                        ))}

                                        {oblasts.length === 0 && (
                                            <tr>
                                                <td colSpan={2} className="px-4 py-8 text-center text-sm text-gray-500">
                                                    Даних немає.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    )}

                    {view.level === 'cities' && (
                        <div className="bg-white p-4 shadow sm:rounded-lg">
                            <button
                                type="button"
                                onClick={() => setView({ level: 'oblasts' })}
                                className="mb-4 text-sm text-indigo-600 hover:underline"
                            >
                                ← Назад до областей
                            </button>
                            <h3 className="mb-4 text-lg font-medium text-gray-900">{view.oblast}</h3>

                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Місто</th>
                                        <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Поранених</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-200">
                                    {view.cities.map((c) => (
                                        <tr key={c.city} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 text-sm">
                                                <button
                                                    type="button"
                                                    className="font-medium text-indigo-600 hover:underline"
                                                    onClick={() => setView({
                                                        level: 'servicemen',
                                                        oblast: view.oblast,
                                                        city: c.city,
                                                        servicemen: c.servicemen,
                                                    })}
                                                >
                                                    {c.city}
                                                </button>
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-600">{c.count}</td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}

                    {view.level === 'servicemen' && (
                        <div className="bg-white p-4 shadow sm:rounded-lg">
                            <button
                                type="button"
                                onClick={() => setView({ level: 'cities', oblast: view.oblast, cities: oblasts.find((o) => o.oblast === view.oblast)?.cities ?? [] })}
                                className="mb-4 text-sm text-indigo-600 hover:underline"
                            >
                                ← Назад до міст
                            </button>
                            <h3 className="mb-4 text-lg font-medium text-gray-900">
                                {view.city}, {view.oblast}
                            </h3>

                            <ul className="divide-y divide-gray-200">
                                {view.servicemen.map((s) => (
                                    <li key={s.id} className="flex items-center justify-between py-2">
                                        <div>
                                            <Link
                                                href={route('servicemen.show', s.id)}
                                                className="font-medium text-indigo-600 hover:underline"
                                            >
                                                {s.full_name}
                                            </Link>
                                            <div className="text-xs text-gray-500">{s.unit_label}</div>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
