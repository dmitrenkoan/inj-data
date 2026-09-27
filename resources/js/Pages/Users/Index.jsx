import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';

export default function Index({ users, roles }) {
    const destroy = (user) => {
        if (confirm(`Видалити користувача "${user.name}"?`)) {
            router.delete(route('users.destroy', user.id), { preserveScroll: true });
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        Користувачі
                    </h2>
                    <Link
                        href={route('users.create')}
                        className="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"
                    >
                        + Додати користувача
                    </Link>
                </div>
            }
        >
            <Head title="Користувачі" />

            <div className="py-8">
                <div className="mx-auto max-w-5xl sm:px-6 lg:px-8">
                    <div className="overflow-x-auto bg-white shadow sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Ім'я</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Email</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Роль</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Бригада</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Батальйон</th>
                                    <th className="px-4 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {users.map((u) => (
                                    <tr key={u.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 text-sm font-medium text-gray-900">{u.name}</td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{u.email}</td>
                                        <td className="px-4 py-3 text-sm text-gray-600">
                                            {roles.find((r) => r.value === u.role)?.label ?? u.role}
                                        </td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{u.brigade?.name ?? '—'}</td>
                                        <td className="px-4 py-3 text-sm text-gray-600">{u.battalion?.name ?? '—'}</td>
                                        <td className="px-4 py-3 text-right">
                                            <Link
                                                href={route('users.edit', u.id)}
                                                className="mr-3 text-sm text-indigo-600 hover:underline"
                                            >
                                                Редагувати
                                            </Link>
                                            <button
                                                type="button"
                                                onClick={() => destroy(u)}
                                                className="text-sm text-red-600 hover:underline"
                                            >
                                                Видалити
                                            </button>
                                        </td>
                                    </tr>
                                ))}

                                {users.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-4 py-8 text-center text-sm text-gray-500">
                                            Користувачів ще немає.
                                        </td>
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
