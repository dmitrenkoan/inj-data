import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

const TYPES = ['місто', 'селище', 'селище міського типу', 'село'];

export default function Index({ settlements, filters }) {
    const [search, setSearch] = useState(filters.search ?? '');
    const [editingId, setEditingId] = useState(null);

    const createForm = useForm({ name: '', raion: '', oblast: '', type: 'село' });
    const editForm = useForm({ name: '', raion: '', oblast: '', type: '' });

    const submitSearch = (e) => {
        e.preventDefault();
        router.get(route('settlements.index'), { search }, { preserveState: true, replace: true });
    };

    const submitCreate = (e) => {
        e.preventDefault();
        createForm.post(route('settlements.store'), {
            preserveScroll: true,
            onSuccess: () => createForm.reset('name'),
        });
    };

    const startEdit = (settlement) => {
        setEditingId(settlement.id);
        editForm.setData({
            name: settlement.name,
            raion: settlement.raion ?? '',
            oblast: settlement.oblast,
            type: settlement.type ?? '',
        });
    };

    const submitEdit = (e, settlement) => {
        e.preventDefault();
        editForm.put(route('settlements.update', settlement.id), {
            preserveScroll: true,
            onSuccess: () => setEditingId(null),
        });
    };

    const destroy = (settlement) => {
        if (confirm(`Видалити населений пункт "${settlement.name}"?`)) {
            router.delete(route('settlements.destroy', settlement.id), { preserveScroll: true });
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Населені пункти
                </h2>
            }
        >
            <Head title="Населені пункти" />

            <div className="py-8">
                <div className="mx-auto max-w-5xl sm:px-6 lg:px-8">
                    <form
                        onSubmit={submitCreate}
                        className="mb-4 flex flex-wrap items-start gap-3 bg-white p-4 shadow sm:rounded-lg"
                    >
                        <div className="min-w-[160px]">
                            <TextInput
                                className="w-full"
                                placeholder="Назва"
                                value={createForm.data.name}
                                onChange={(e) => createForm.setData('name', e.target.value)}
                            />
                            <InputError message={createForm.errors.name} className="mt-1" />
                        </div>
                        <div className="min-w-[180px]">
                            <TextInput
                                className="w-full"
                                placeholder="Область"
                                value={createForm.data.oblast}
                                onChange={(e) => createForm.setData('oblast', e.target.value)}
                            />
                            <InputError message={createForm.errors.oblast} className="mt-1" />
                        </div>
                        <div className="min-w-[180px]">
                            <TextInput
                                className="w-full"
                                placeholder="Район"
                                value={createForm.data.raion}
                                onChange={(e) => createForm.setData('raion', e.target.value)}
                            />
                            <InputError message={createForm.errors.raion} className="mt-1" />
                        </div>
                        <div className="min-w-[160px]">
                            <select
                                className="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                value={createForm.data.type}
                                onChange={(e) => createForm.setData('type', e.target.value)}
                            >
                                {TYPES.map((t) => (
                                    <option key={t} value={t}>
                                        {t}
                                    </option>
                                ))}
                            </select>
                            <InputError message={createForm.errors.type} className="mt-1" />
                        </div>
                        <PrimaryButton disabled={createForm.processing}>Додати</PrimaryButton>
                    </form>

                    <form onSubmit={submitSearch} className="mb-4">
                        <TextInput
                            className="w-full max-w-sm"
                            placeholder="Пошук за назвою..."
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                        />
                    </form>

                    <div className="overflow-x-auto bg-white shadow sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Назва</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Область</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Район</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Тип</th>
                                    <th className="px-4 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {settlements.data.map((s) =>
                                    editingId === s.id ? (
                                        <tr key={s.id}>
                                            <td className="px-4 py-3" colSpan={5}>
                                                <form
                                                    onSubmit={(e) => submitEdit(e, s)}
                                                    className="flex flex-wrap items-center gap-3"
                                                >
                                                    <TextInput
                                                        value={editForm.data.name}
                                                        onChange={(e) => editForm.setData('name', e.target.value)}
                                                    />
                                                    <TextInput
                                                        value={editForm.data.oblast}
                                                        onChange={(e) => editForm.setData('oblast', e.target.value)}
                                                    />
                                                    <TextInput
                                                        value={editForm.data.raion}
                                                        onChange={(e) => editForm.setData('raion', e.target.value)}
                                                    />
                                                    <select
                                                        className="block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        value={editForm.data.type}
                                                        onChange={(e) => editForm.setData('type', e.target.value)}
                                                    >
                                                        {TYPES.map((t) => (
                                                            <option key={t} value={t}>
                                                                {t}
                                                            </option>
                                                        ))}
                                                    </select>
                                                    <PrimaryButton disabled={editForm.processing}>Зберегти</PrimaryButton>
                                                    <SecondaryButton type="button" onClick={() => setEditingId(null)}>
                                                        Скасувати
                                                    </SecondaryButton>
                                                </form>
                                            </td>
                                        </tr>
                                    ) : (
                                        <tr key={s.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 text-sm font-medium text-gray-900">{s.name}</td>
                                            <td className="px-4 py-3 text-sm text-gray-600">{s.oblast}</td>
                                            <td className="px-4 py-3 text-sm text-gray-600">{s.raion || '—'}</td>
                                            <td className="px-4 py-3 text-sm text-gray-600">{s.type || '—'}</td>
                                            <td className="px-4 py-3 text-right">
                                                <button
                                                    type="button"
                                                    onClick={() => startEdit(s)}
                                                    className="mr-3 text-sm text-indigo-600 hover:underline"
                                                >
                                                    Редагувати
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => destroy(s)}
                                                    className="text-sm text-red-600 hover:underline"
                                                >
                                                    Видалити
                                                </button>
                                            </td>
                                        </tr>
                                    ),
                                )}

                                {settlements.data.length === 0 && (
                                    <tr>
                                        <td colSpan={5} className="px-4 py-8 text-center text-sm text-gray-500">
                                            Населених пунктів не знайдено.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    {settlements.links.length > 3 && (
                        <div className="mt-4 flex flex-wrap gap-1">
                            {settlements.links.map((link, i) => (
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
