import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import DataTabs from './Partials/DataTabs';

const COLUMNS = [
    { key: 'total', label: 'Кількість поранених' },
    { key: 'severe', label: 'Важкопоранені' },
    { key: 'amputated', label: 'Ампутовані' },
    { key: 'prosthetic', label: 'Протезовані' },
    { key: 'needs_prosthetic', label: 'Потребують протезування' },
    { key: 'severe_or_amputated_in_mou', label: 'Важкопоранені/ампутовані в МОУ' },
    { key: 'severe_or_amputated_in_moz', label: 'Важкопоранені/ампутовані в МОЗ' },
    { key: 'gets_additional_remuneration', label: 'Отримують додаткову винагороду' },
    { key: 'awarded', label: 'Нагороджені' },
    { key: 'returned_to_duty', label: 'Повернулися до служби' },
];

export default function Report({ rows, totals }) {
    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Дані
                </h2>
            }
        >
            <Head title="Звіт" />

            <div className="py-8">
                <div className="mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <DataTabs active="report" />

                    <h3 className="mb-2 text-sm font-semibold uppercase text-gray-700">Звіт по бригадам</h3>

                    <div className="overflow-x-auto bg-white shadow sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Бригада</th>
                                    {COLUMNS.map((c) => (
                                        <th
                                            key={c.key}
                                            className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500"
                                        >
                                            {c.label}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {rows.map((r) => (
                                    <tr key={r.brigade} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 text-sm font-medium text-gray-900">{r.brigade}</td>
                                        {COLUMNS.map((c) => (
                                            <td key={c.key} className="px-4 py-3 text-sm text-gray-600">
                                                {r[c.key]}
                                            </td>
                                        ))}
                                    </tr>
                                ))}

                                {rows.length === 0 && (
                                    <tr>
                                        <td colSpan={COLUMNS.length + 1} className="px-4 py-8 text-center text-sm text-gray-500">
                                            Даних немає.
                                        </td>
                                    </tr>
                                )}

                                {rows.length > 0 && (
                                    <tr className="bg-gray-50 font-medium">
                                        <td className="px-4 py-3 text-sm text-gray-900">{totals.brigade}</td>
                                        {COLUMNS.map((c) => (
                                            <td key={c.key} className="px-4 py-3 text-sm text-gray-900">
                                                {totals[c.key]}
                                            </td>
                                        ))}
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
