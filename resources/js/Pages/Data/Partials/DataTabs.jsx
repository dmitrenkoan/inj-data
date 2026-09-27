import { Link } from '@inertiajs/react';

export default function DataTabs({ active }) {
    const tabs = [
        { key: 'map', label: 'Карта', route: 'data.index' },
        { key: 'statistics', label: 'Статистика', route: 'data.statistics' },
        { key: 'report', label: 'Звіт', route: 'data.report' },
    ];

    return (
        <div className="mb-4 flex gap-2 border-b border-gray-200">
            {tabs.map((t) => (
                <Link
                    key={t.key}
                    href={route(t.route)}
                    className={
                        'px-4 py-2 text-sm font-medium border-b-2 -mb-px ' +
                        (active === t.key
                            ? 'border-indigo-600 text-indigo-600'
                            : 'border-transparent text-gray-500 hover:text-gray-700')
                    }
                >
                    {t.label}
                </Link>
            ))}
        </div>
    );
}
