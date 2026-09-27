import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';

function battalionLabel(battalion) {
    return battalion.brigade ? `${battalion.brigade.name} / ${battalion.name}` : battalion.name;
}

export default function Index({ units, battalions }) {
    const { auth } = usePage().props;
    const [editingId, setEditingId] = useState(null);

    const createForm = useForm({
        name: '',
        battalion_id: auth.user.role === 'battalion' ? auth.user.battalion_id : '',
    });

    const editForm = useForm({ name: '', battalion_id: '' });

    const submitCreate = (e) => {
        e.preventDefault();
        createForm.post(route('units.store'), {
            preserveScroll: true,
            onSuccess: () => createForm.reset('name'),
        });
    };

    const startEdit = (unit) => {
        setEditingId(unit.id);
        editForm.setData({ name: unit.name, battalion_id: unit.battalion_id });
    };

    const submitEdit = (e, unit) => {
        e.preventDefault();
        editForm.put(route('units.update', unit.id), {
            preserveScroll: true,
            onSuccess: () => setEditingId(null),
        });
    };

    const destroy = (unit) => {
        if (confirm(`Видалити підрозділ "${unit.name}"?`)) {
            router.delete(route('units.destroy', unit.id), { preserveScroll: true });
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Підрозділи
                </h2>
            }
        >
            <Head title="Підрозділи" />

            <div className="py-8">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <form
                        onSubmit={submitCreate}
                        className="mb-6 flex flex-wrap items-start gap-3 bg-white p-4 shadow sm:rounded-lg"
                    >
                        {battalions.length > 0 && (
                            <div>
                                <SelectInput
                                    value={createForm.data.battalion_id}
                                    onChange={(e) => createForm.setData('battalion_id', e.target.value)}
                                >
                                    <option value="">— Батальйон —</option>
                                    {battalions.map((b) => (
                                        <option key={b.id} value={b.id}>
                                            {battalionLabel(b)}
                                        </option>
                                    ))}
                                </SelectInput>
                                <InputError message={createForm.errors.battalion_id} className="mt-1" />
                            </div>
                        )}
                        <div className="flex-1 min-w-[200px]">
                            <TextInput
                                className="w-full"
                                placeholder="Назва підрозділу"
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
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Батальйон</th>
                                    <th className="px-4 py-3 text-left text-xs font-medium uppercase text-gray-500">Поранених</th>
                                    <th className="px-4 py-3" />
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-200">
                                {units.map((unit) =>
                                    editingId === unit.id ? (
                                        <tr key={unit.id}>
                                            <td className="px-4 py-3" colSpan={4}>
                                                <form
                                                    onSubmit={(e) => submitEdit(e, unit)}
                                                    className="flex flex-wrap items-center gap-3"
                                                >
                                                    {battalions.length > 0 && (
                                                        <SelectInput
                                                            value={editForm.data.battalion_id}
                                                            onChange={(e) => editForm.setData('battalion_id', e.target.value)}
                                                        >
                                                            {battalions.map((b) => (
                                                                <option key={b.id} value={b.id}>
                                                                    {battalionLabel(b)}
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
                                        <tr key={unit.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 text-sm font-medium text-gray-900">{unit.name}</td>
                                            <td className="px-4 py-3 text-sm text-gray-600">
                                                {unit.battalion ? battalionLabel(unit.battalion) : '—'}
                                            </td>
                                            <td className="px-4 py-3 text-sm text-gray-600">{unit.servicemen_count}</td>
                                            <td className="px-4 py-3 text-right">
                                                <button
                                                    type="button"
                                                    onClick={() => startEdit(unit)}
                                                    className="mr-3 text-sm text-indigo-600 hover:underline"
                                                >
                                                    Редагувати
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => destroy(unit)}
                                                    className="text-sm text-red-600 hover:underline"
                                                >
                                                    Видалити
                                                </button>
                                            </td>
                                        </tr>
                                    ),
                                )}

                                {units.length === 0 && (
                                    <tr>
                                        <td colSpan={4} className="px-4 py-8 text-center text-sm text-gray-500">
                                            Підрозділів ще немає.
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
