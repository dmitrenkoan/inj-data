import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ battalions, brigades }) {
    const { auth } = usePage().props;
    const [editingId, setEditingId] = useState(null);

    const createForm = useForm({
        name: '',
        brigade_id: auth.user.role === 'super_admin' ? '' : auth.user.brigade_id,
    });

    const editForm = useForm({ name: '', brigade_id: '' });

    const submitCreate = (e) => {
        e.preventDefault();
        createForm.post(route('battalions.store'), {
            preserveScroll: true,
            onSuccess: () => createForm.reset('name'),
        });
    };

    const startEdit = (battalion) => {
        setEditingId(battalion.id);
        editForm.setData({ name: battalion.name, brigade_id: battalion.brigade_id });
    };

    const submitEdit = (e, battalion) => {
        e.preventDefault();
        editForm.put(route('battalions.update', battalion.id), {
            preserveScroll: true,
            onSuccess: () => setEditingId(null),
        });
    };

    const destroy = (battalion) => {
        if (confirm(`Видалити батальйон "${battalion.name}"?`)) {
            router.delete(route('battalions.destroy', battalion.id), { preserveScroll: true });
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Батальйони
                </h2>
            }
        >
            <Head title="Батальйони" />

            <div className="py-8">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <form
                        onSubmit={submitCreate}
                        className="mb-6 flex flex-wrap items-start gap-3 bg-white p-4 shadow sm:rounded-lg"
                    >
                        {brigades.length > 0 && (
                            <div>
                                <SelectInput
                                    value={createForm.data.brigade_id}
                                    onChange={(e) => createForm.setData('brigade_id', e.target.value)}
                                >
                                    <option value="">— Бригада —</option>
                                    {brigades.map((b) => (
                                        <option key={b.id} value={b.id}>
                                            {b.name}
                                        </option>
                                    ))}
                                </SelectInput>
                                <InputError message={createForm.errors.brigade_id} className="mt-1" />
                            </div>
                        )}
                        <div className="flex-1 min-w-[200px]">
                            <TextInput
                                className="w-full"
                                placeholder="Назва батальйону"
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
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Бригада</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Підрозділів</th>
                                    <th className="px-4 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {battalions.map((battalion) =>
                                    editingId === battalion.id ? (
                                        <tr key={battalion.id}>
                                            <td className="px-4 py-3" colSpan={4}>
                                                <form
                                                    onSubmit={(e) => submitEdit(e, battalion)}
                                                    className="flex flex-wrap items-center gap-3"
                                                >
                                                    {brigades.length > 0 && (
                                                        <SelectInput
                                                            value={editForm.data.brigade_id}
                                                            onChange={(e) => editForm.setData('brigade_id', e.target.value)}
                                                        >
                                                            {brigades.map((b) => (
                                                                <option key={b.id} value={b.id}>
                                                                    {b.name}
                                                                </option>
                                                            ))}
                                                        </SelectInput>
                                                    )}
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
                                        <tr key={battalion.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 text-sm font-medium text-gray-900">{battalion.name}</td>
                                            <td className="px-4 py-3 text-sm text-gray-600">{battalion.brigade?.name ?? '—'}</td>
                                            <td className="px-4 py-3 text-sm text-gray-600">{battalion.units_count}</td>
                                            <td className="px-4 py-3 text-right">
                                                <button
                                                    type="button"
                                                    onClick={() => startEdit(battalion)}
                                                    className="mr-3 text-sm text-indigo-600 hover:underline"
                                                >
                                                    Редагувати
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => destroy(battalion)}
                                                    className="text-sm text-red-600 hover:underline"
                                                >
                                                    Видалити
                                                </button>
                                            </td>
                                        </tr>
                                    ),
                                )}

                                {battalions.length === 0 && (
                                    <tr>
                                        <td colSpan={4} className="px-4 py-8 text-center text-sm text-gray-500">
                                            Батальйонів ще немає.
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
