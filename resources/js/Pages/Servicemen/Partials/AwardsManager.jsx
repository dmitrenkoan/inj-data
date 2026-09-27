import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import TextInput from '@/Components/TextInput';
import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';

function fmtDate(value) {
    return value ? String(value).slice(0, 10) : '—';
}

function toDateInput(value) {
    return value ? String(value).slice(0, 10) : '';
}

export default function AwardsManager({ serviceman, returnTo = 'show' }) {
    const [target, setTarget] = useState(null);
    const form = useForm({
        name: '',
        submission_date: '',
        awarded_date: '',
        return_to: returnTo,
    });

    const open = (t) => {
        form.clearErrors();
        form.setData({
            name: t === 'new' ? '' : t.name,
            submission_date: t === 'new' ? '' : toDateInput(t.submission_date),
            awarded_date: t === 'new' ? '' : toDateInput(t.awarded_date),
            return_to: returnTo,
        });
        setTarget(t);
    };

    const close = () => {
        setTarget(null);
        form.reset();
        form.clearErrors();
    };

    const submit = (e) => {
        e?.preventDefault();
        e?.stopPropagation();

        const options = { preserveScroll: true, preserveState: true, onSuccess: close };

        if (target === 'new') {
            form.post(route('servicemen.awards.store', serviceman.id), options);
        } else {
            form.patch(route('servicemen.awards.update', [serviceman.id, target.id]), options);
        }
    };

    const destroy = (award) => {
        if (confirm('Видалити цю нагороду?')) {
            router.delete(route('servicemen.awards.destroy', [serviceman.id, award.id]), {
                data: { return_to: returnTo },
                preserveScroll: true,
                preserveState: true,
            });
        }
    };

    return (
        <div>
            <div className="mb-4 flex items-center justify-between">
                <h4 className="text-sm font-semibold uppercase text-gray-700">Нагороди</h4>
                <SecondaryButton type="button" onClick={() => open('new')}>
                    + Додати нагороду
                </SecondaryButton>
            </div>

            {serviceman.awards.length > 0 ? (
                <ul className="space-y-2">
                    {serviceman.awards.map((award) => (
                        <li key={award.id} className="flex items-center justify-between rounded bg-gray-50 px-3 py-2 text-sm">
                            <div>
                                <span className="font-medium">{award.name}</span>
                                {' — подання: '}{fmtDate(award.submission_date)}
                                {', вручення: '}{fmtDate(award.awarded_date)}
                                <div className="mt-1 text-xs text-gray-500">Створено: {fmtDate(award.created_at)}</div>
                            </div>
                            <div className="ml-3 flex shrink-0 gap-3">
                                <button
                                    type="button"
                                    onClick={() => open(award)}
                                    className="text-xs text-indigo-600 hover:underline"
                                >
                                    Редагувати
                                </button>
                                <button
                                    type="button"
                                    onClick={() => destroy(award)}
                                    className="text-xs text-red-600 hover:underline"
                                >
                                    Видалити
                                </button>
                            </div>
                        </li>
                    ))}
                </ul>
            ) : (
                <p className="text-sm text-gray-500">Нагород не зафіксовано.</p>
            )}

            <Modal show={target !== null} onClose={close}>
                <form onSubmit={submit} className="p-6">
                    <h3 className="text-lg font-medium text-gray-900">
                        {target === 'new' ? 'Додати нагороду' : 'Редагувати нагороду'}
                    </h3>

                    <div className="mt-4 space-y-4">
                        <div>
                            <InputLabel value="Назва нагороди" />
                            <TextInput
                                className="mt-1 block w-full"
                                value={form.data.name}
                                onChange={(e) => form.setData('name', e.target.value)}
                            />
                            <InputError message={form.errors.name} className="mt-1" />
                        </div>

                        <div className="grid grid-cols-2 gap-3">
                            <div>
                                <InputLabel value="Дата подачі" />
                                <TextInput
                                    type="date"
                                    className="mt-1 block w-full"
                                    value={form.data.submission_date}
                                    onChange={(e) => form.setData('submission_date', e.target.value)}
                                />
                                <InputError message={form.errors.submission_date} className="mt-1" />
                            </div>
                            <div>
                                <InputLabel value="Дата вручення" />
                                <TextInput
                                    type="date"
                                    className="mt-1 block w-full"
                                    value={form.data.awarded_date}
                                    onChange={(e) => form.setData('awarded_date', e.target.value)}
                                />
                                <InputError message={form.errors.awarded_date} className="mt-1" />
                            </div>
                        </div>
                    </div>

                    <div className="mt-6 flex justify-end gap-2">
                        <SecondaryButton type="button" onClick={close}>
                            Скасувати
                        </SecondaryButton>
                        <PrimaryButton disabled={form.processing}>Зберегти</PrimaryButton>
                    </div>
                </form>
            </Modal>
        </div>
    );
}
