import SelectInput from '@/Components/SelectInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import DataTabs from './Partials/DataTabs';

function battalionLabel(battalion) {
    return battalion.brigade ? `${battalion.brigade.name} / ${battalion.name}` : battalion.name;
}

const STATUS_BADGE = {
    in_progress: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
    unreachable: 'bg-red-100 text-red-800',
};

export default function Statistics({
    rows,
    treatmentRows,
    controls,
    additionalRows,
    remoteVlkRows,
    remoteVlkTotal,
    total,
    filters,
    brigades,
    battalions,
}) {
    const applyFilters = (next) => {
        router.get(
            route('data.statistics'),
            {
                brigade_id: filters.brigade_id ?? '',
                battalion_id: filters.battalion_id ?? '',
                ...next,
            },
            { preserveState: true, replace: true },
        );
    };

    const availableBattalions = battalions.filter(
        (b) => !filters.brigade_id || String(b.brigade_id) === String(filters.brigade_id),
    );

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Дані
                </h2>
            }
        >
            <Head title="Статистика" />

            <div className="py-8">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <DataTabs active="statistics" />

                    <div className="mb-4 flex flex-wrap items-center gap-3 bg-white p-4 shadow sm:rounded-lg">
                        <span className="text-sm text-gray-500">
                            Всього поранених: <span className="font-semibold text-gray-900">{total}</span>
                        </span>

                        {brigades.length > 0 && (
                            <SelectInput
                                value={filters.brigade_id ?? ''}
                                onChange={(e) => applyFilters({ brigade_id: e.target.value, battalion_id: '' })}
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
                                onChange={(e) => applyFilters({ battalion_id: e.target.value })}
                            >
                                <option value="">Усі батальйони</option>
                                {availableBattalions.map((b) => (
                                    <option key={b.id} value={b.id}>{battalionLabel(b)}</option>
                                ))}
                            </SelectInput>
                        )}
                    </div>

                    <h3 className="mb-2 text-sm font-semibold uppercase text-gray-700">Статус супроводження</h3>

                    <div className="overflow-x-auto bg-white shadow sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Статус</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Кількість</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Відсоток</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {rows.map((r) => (
                                    <tr key={r.status} className="hover:bg-gray-50">
                                        <td className="px-4 py-3">
                                            <span
                                                className={
                                                    'rounded-full px-2 py-1 text-xs font-medium ' +
                                                    (STATUS_BADGE[r.status] ?? 'bg-gray-100 text-gray-800')
                                                }
                                            >
                                                {r.label}
                                            </span>
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{r.count}</td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{r.percentage}%</td>
                                    </tr>
                                ))}

                                <tr className="bg-gray-50 font-medium">
                                    <td className="px-4 py-3 text-sm text-gray-900">Всього</td>
                                    <td className="px-4 py-3 text-sm text-gray-900">{total}</td>
                                    <td className="px-4 py-3 text-sm text-gray-900">{total > 0 ? '100' : '0'}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h3 className="mb-2 mt-6 text-sm font-semibold uppercase text-gray-700">Статус лікування</h3>

                    <div className="overflow-x-auto bg-white shadow sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Статус</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Кількість</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Відсоток</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {treatmentRows.map((r) => (
                                    <tr key={r.status ?? 'none'} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 text-sm text-gray-900">{r.label}</td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{r.count}</td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{r.percentage}%</td>
                                    </tr>
                                ))}

                                <tr className="bg-gray-50 font-medium">
                                    <td className="px-4 py-3 text-sm text-gray-900">Всього</td>
                                    <td className="px-4 py-3 text-sm text-gray-900">{total}</td>
                                    <td className="px-4 py-3 text-sm text-gray-900">{total > 0 ? '100' : '0'}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h3 className="mb-2 mt-6 text-sm font-semibold uppercase text-gray-700">Контроль</h3>

                    <div className="overflow-x-auto bg-white shadow sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Категорія контролю</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Супроводжується, кільк.</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Супроводжується, %</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Потребує уваги, кільк.</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Потребує уваги, %</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {controls.map((c) => (
                                    <tr key={c.key} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 text-sm font-medium text-gray-900">{c.label}</td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{c.ok_count}</td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{c.ok_percentage}%</td>
                                        <td className="px-4 py-3 text-sm text-red-700">{c.needs_count}</td>
                                        <td className="px-4 py-3 text-sm text-red-700">{c.needs_percentage}%</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <h3 className="mb-2 mt-6 text-sm font-semibold uppercase text-gray-700">Додаткові показники</h3>

                    <div className="overflow-x-auto bg-white shadow sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Показник</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Кількість</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Відсоток</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {additionalRows.map((r) => (
                                    <tr key={r.key} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 text-sm text-gray-900">{r.label}</td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{r.count}</td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{r.percentage}%</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>

                    <h3 className="mb-2 mt-6 text-sm font-semibold uppercase text-gray-700">Статус дистанційного ВЛК</h3>

                    <div className="overflow-x-auto bg-white shadow sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Статус</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Кількість</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Відсоток</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {remoteVlkRows.map((r) => (
                                    <tr key={r.status} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 text-sm text-gray-900">{r.label}</td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{r.count}</td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{r.percentage}%</td>
                                    </tr>
                                ))}

                                <tr className="bg-gray-50 font-medium">
                                    <td className="px-4 py-3 text-sm text-gray-900">Всього (лікування за кордоном)</td>
                                    <td className="px-4 py-3 text-sm text-gray-900">{remoteVlkTotal}</td>
                                    <td className="px-4 py-3 text-sm text-gray-900">{remoteVlkTotal > 0 ? '100' : '0'}%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
