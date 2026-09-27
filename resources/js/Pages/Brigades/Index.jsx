import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ brigades }) {
    const [editingId, setEditingId] = useState(null);

    const createForm = useForm({ name: '' });
    const editForm = useForm({ name: '' });

    const submitCreate = (e) => {
        e.preventDefault();
        createForm.post(route('brigades.store'), {
            preserveScroll: true,
            onSuccess: () => createForm.reset(),
        });
    };

    const startEdit = (brigade) => {
        setEditingId(brigade.id);
        editForm.setData({ name: brigade.name });
    };

    const submitEdit = (e, brigade) => {
        e.preventDefault();
        editForm.put(route('brigades.update', brigade.id), {
            preserveScroll: true,
            onSuccess: () => setEditingId(null),
        });
    };

    const destroy = (brigade) => {
        if (confirm(`Видалити бригаду "${brigade.name}"?`)) {
            router.delete(route('brigades.destroy', brigade.id), { preserveScroll: true });
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Бригади
                </h2>
            }
        >
            <Head title="Бригади" />

            <div className="py-8">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <form
                        onSubmit={submitCreate}
                        className="mb-6 flex flex-wrap items-start gap-3 bg-white p-4 shadow sm:rounded-lg"
                    >
                        <div className="flex-1 min-w-[200px]">
                            <TextInput
                                className="w-full"
                                placeholder="Назва бригади"
                                value={createForm.data.name}
                                onChange={(e) => createForm.setData('name', e.target.value)}
                            />
                            <InputError message={createForm.errors.name} className="mt-1" />
                        </div>
                        <PrimaryButton disabled={createForm.processing}>Додати</PrimaryButton>
                    </form>

                    <div className="overflow-x-auto bg-white shadow sm:rounded-lg">
                        <table className="min-w-full divide-y divide-gray-200">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Назва</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Батальйонів</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Користувачів</th>
                                    <th className="px-4 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {brigades.map((brigade) =>
                                    editingId === brigade.id ? (
                                        <tr key={brigade.id}>
                                            <td className="px-4 py-3" colSpan={4}>
                                                <form
                                                    onSubmit={(e) => submitEdit(e, brigade)}
                                                    className="flex flex-wrap items-center gap-3"
                                                >
                                                    <TextInput
                                                        value={editForm.data.name}
                                                        onChange={(e) => editForm.setData('name', e.target.value)}
                                                    />
                                                    <PrimaryButton disabled={editForm.processing}>Зберегти</PrimaryButton>
                                                    <SecondaryButton type="button" onClick={() => setEditingId(null)}>
                                                        Скасувати
                                                    </SecondaryButton>
                                                </form>
                                            </td>
                                        </tr>
                                    ) : (
                                        <tr key={brigade.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 text-sm font-medium text-gray-900">{brigade.name}</td>
                                            <td className="px-4 py-3 text-sm text-gray-600">{brigade.battalions_count}</td>
                                            <td className="px-4 py-3 text-sm text-gray-600">{brigade.users_count}</td>
                                            <td className="px-4 py-3 text-right">
                                                <button
                                                    type="button"
                                                    onClick={() => startEdit(brigade)}
                                                    className="mr-3 text-sm text-indigo-600 hover:underline"
                                                >
                                                    Редагувати
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => destroy(brigade)}
                                                    className="text-sm text-red-600 hover:underline"
                                                >
                                                    Видалити
                                                </button>
                                            </td>
                                        </tr>
                                    ),
                                )}

                                {brigades.length === 0 && (
                                    <tr>
                                        <td colSpan={4} className="px-4 py-8 text-center text-sm text-gray-500">
                                            Бригад ще немає.
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
