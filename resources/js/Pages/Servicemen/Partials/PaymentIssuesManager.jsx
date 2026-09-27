import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import SelectInput from '@/Components/SelectInput';
import TextareaInput from '@/Components/TextareaInput';
import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';

const STATUS_OPTIONS = [
    { value: 'in_progress', label: 'В роботі' },
    { value: 'completed', label: 'Завершено' },
];

function label(options, value) {
    return options.find((o) => o.value === value)?.label ?? value ?? '—';
}

function fmtDate(value) {
    return value ? String(value).slice(0, 10) : '—';
}

export default function PaymentIssuesManager({ serviceman, paymentIssueTypes, returnTo = 'show' }) {
    const [target, setTarget] = useState(null);
    const form = useForm({
        type: paymentIssueTypes[0]?.value ?? '',
        description: '',
        status: 'in_progress',
        return_to: returnTo,
    });

    const open = (t) => {
        form.clearErrors();
        form.setData({
            type: t === 'new' ? paymentIssueTypes[0]?.value ?? '' : t.type,
            description: t === 'new' ? '' : t.description,
            status: t === 'new' ? 'in_progress' : t.status,
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
            form.post(route('servicemen.payment-issues.store', serviceman.id), options);
        } else {
            form.patch(route('servicemen.payment-issues.update', [serviceman.id, target.id]), options);
        }
    };

    const toggleStatus = (issue) => {
        router.patch(
            route('servicemen.payment-issues.update', [serviceman.id, issue.id]),
            {
                status: issue.status === 'completed' ? 'in_progress' : 'completed',
                return_to: returnTo,
            },
            { preserveScroll: true, preserveState: true },
        );
    };

    const destroy = (issue) => {
        if (confirm('Видалити цю проблему з виплатами?')) {
            router.delete(route('servicemen.payment-issues.destroy', [serviceman.id, issue.id]), {
                data: { return_to: returnTo },
                preserveScroll: true,
                preserveState: true,
            });
        }
    };

    return (
        <div className="mt-6 border-t border-gray-200 pt-4">
            <div className="mb-2 flex items-center justify-between">
                <h4 className="text-sm font-semibold uppercase text-gray-700">Проблемні питання з виплатами</h4>
                <SecondaryButton type="button" onClick={() => open('new')}>
                    Так, є проблема
                </SecondaryButton>
            </div>

            {serviceman.payment_issues.length > 0 ? (
                <ul className="space-y-2">
                    {serviceman.payment_issues.map((issue) => (
                        <li key={issue.id} className="flex items-center justify-between rounded bg-gray-50 px-3 py-2 text-sm">
                            <div>
                                <span className="font-medium">{label(paymentIssueTypes, issue.type)}</span>
                                {' — '}
                                {issue.description}
                                <div className="mt-1 text-xs text-gray-500">Створено: {fmtDate(issue.created_at)}</div>
                            </div>
                            <div className="ml-3 flex shrink-0 items-center gap-3">
                                <button
                                    type="button"
                                    onClick={() => toggleStatus(issue)}
                                    className={
                                        'rounded-full px-2 py-1 text-xs font-medium ' +
                                        (issue.status === 'completed'
                                            ? 'bg-green-100 text-green-800'
                                            : 'bg-yellow-100 text-yellow-800')
                                    }
                                >
                                    {issue.status === 'completed' ? 'Завершено' : 'В роботі'}
                                </button>
                                <button
                                    type="button"
                                    onClick={() => open(issue)}
                                    className="text-xs text-indigo-600 hover:underline"
                                >
                                    Редагувати
                                </button>
                                <button
                                    type="button"
                                    onClick={() => destroy(issue)}
                                    className="text-xs text-red-600 hover:underline"
                                >
                                    Видалити
                                </button>
                            </div>
                        </li>
                    ))}
                </ul>
            ) : (
                <p className="text-sm text-gray-500">Проблем не зафіксовано.</p>
            )}

            <Modal show={target !== null} onClose={close}>
                <form onSubmit={submit} className="p-6">
                    <h3 className="text-lg font-medium text-gray-900">
                        {target === 'new' ? 'Проблема з виплатами' : 'Редагувати проблему з виплатами'}
                    </h3>

                    <div className="mt-4 space-y-4">
                        <div>
                            <InputLabel value="Вид" />
                            <SelectInput
                                className="mt-1 block w-full"
                                value={form.data.type}
                                onChange={(e) => form.setData('type', e.target.value)}
                            >
                                {paymentIssueTypes.map((o) => (
                                    <option key={o.value} value={o.value}>
                                        {o.label}
                                    </option>
                                ))}
                            </SelectInput>
                            <InputError message={form.errors.type} className="mt-1" />
                        </div>

                        <div>
                            <InputLabel value="Суть проблеми" />
                            <TextareaInput
                                className="mt-1 block w-full"
                                rows={3}
                                value={form.data.description}
                                onChange={(e) => form.setData('description', e.target.value)}
                            />
                            <InputError message={form.errors.description} className="mt-1" />
                        </div>

                        {target !== 'new' && (
                            <div>
                                <InputLabel value="Статус" />
                                <SelectInput
                                    className="mt-1 block w-full"
                                    value={form.data.status}
                                    onChange={(e) => form.setData('status', e.target.value)}
                                >
                                    {STATUS_OPTIONS.map((o) => (
                                        <option key={o.value} value={o.value}>
                                            {o.label}
                                        </option>
                                    ))}
                                </SelectInput>
                                <InputError message={form.errors.status} className="mt-1" />
                            </div>
                        )}
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
