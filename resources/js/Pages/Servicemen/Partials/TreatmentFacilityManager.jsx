import CityAutocomplete from '@/Components/CityAutocomplete';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import Modal from '@/Components/Modal';
import PrimaryButton from '@/Components/PrimaryButton';
import SecondaryButton from '@/Components/SecondaryButton';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';

function fmtDate(value) {
    return value ? String(value).slice(0, 10) : '—';
}

function label(options, value) {
    return options.find((o) => o.value === value)?.label ?? value ?? '—';
}

export default function TreatmentFacilityManager({ serviceman, facilityTypes, returnTo = 'show' }) {
    const [target, setTarget] = useState(null);
    const [facilitySettlement, setFacilitySettlement] = useState(null);
    const form = useForm({ facility_type: '', facility_settlement_id: '', city: '', facility_name: '', return_to: returnTo });

    const open = (t) => {
        if (t === 'current') {
            const settlement = serviceman.facility_settlement_id
                ? {
                    id: serviceman.facility_settlement_id,
                    name: serviceman.facility_city,
                    oblast: serviceman.facility_oblast,
                    raion: serviceman.facility_raion,
                }
                : null;

            form.clearErrors();
            form.setData({
                facility_type: serviceman.facility_type ?? '',
                facility_settlement_id: settlement?.id ?? '',
                city: '',
                facility_name: serviceman.facility_name ?? '',
                return_to: returnTo,
            });
            setFacilitySettlement(settlement);
        } else {
            form.clearErrors();
            form.setData({
                facility_type: t.facility_type ?? '',
                facility_settlement_id: '',
                city: t.city ?? '',
                facility_name: t.facility_name ?? '',
                return_to: returnTo,
            });
            setFacilitySettlement(null);
        }

        setTarget(t);
    };

    const close = () => {
        setTarget(null);
        setFacilitySettlement(null);
        form.reset();
        form.clearErrors();
    };

    const submit = (e) => {
        e.preventDefault();
        e.stopPropagation();

        const url = target === 'current'
            ? route('servicemen.facility.update', serviceman.id)
            : route('servicemen.treatment-facilities.update', [serviceman.id, target.id]);

        form.patch(url, { preserveScroll: true, preserveState: true, onSuccess: close });
    };

    const destroyHistory = (entry) => {
        if (confirm('Видалити цей запис історії закладів лікування?')) {
            router.delete(route('servicemen.treatment-facilities.destroy', [serviceman.id, entry.id]), {
                data: { return_to: returnTo },
                preserveScroll: true,
                preserveState: true,
            });
        }
    };

    return (
        <div className="sm:col-span-2 border-t border-gray-200 pt-4">
            <div className="mb-2 flex items-center justify-between">
                <h4 className="text-sm font-semibold uppercase text-gray-700">Заклад лікування</h4>
                <SecondaryButton type="button" onClick={() => open('current')}>
                    Змінити
                </SecondaryButton>
            </div>

            <dl className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <dt className="text-xs font-medium uppercase text-gray-500">Тип</dt>
                    <dd className="mt-1 text-sm text-gray-900">{label(facilityTypes, serviceman.facility_type)}</dd>
                </div>
                <div>
                    <dt className="text-xs font-medium uppercase text-gray-500">Населений пункт</dt>
                    <dd className="mt-1 text-sm text-gray-900">
                        {serviceman.facility_city || '—'}
                        {(serviceman.facility_oblast || serviceman.facility_raion) && (
                            <span className="text-gray-400">
                                {' '}({[serviceman.facility_oblast, serviceman.facility_raion].filter(Boolean).join(', ')})
                            </span>
                        )}
                    </dd>
                </div>
                <div>
                    <dt className="text-xs font-medium uppercase text-gray-500">Назва</dt>
                    <dd className="mt-1 text-sm text-gray-900">{serviceman.facility_name || '—'}</dd>
                </div>
            </dl>

            {serviceman.treatment_facility_history?.length > 0 && (
                <div className="mt-4">
                    <h5 className="mb-2 text-xs font-semibold uppercase text-gray-500">
                        Історія закладів
                    </h5>
                    <ul className="space-y-1 text-sm text-gray-700">
                        {serviceman.treatment_facility_history.map((h) => (
                            <li key={h.id} className="flex items-center justify-between rounded bg-gray-50 px-3 py-2">
                                <span>
                                    {fmtDate(h.changed_at)} — {label(facilityTypes, h.facility_type)}, {h.city}, {h.facility_name}
                                </span>
                                <span className="ml-3 flex shrink-0 gap-3">
                                    <button
                                        type="button"
                                        onClick={() => open(h)}
                                        className="text-xs text-indigo-600 hover:underline"
                                    >
                                        Редагувати
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => destroyHistory(h)}
                                        className="text-xs text-red-600 hover:underline"
                                    >
                                        Видалити
                                    </button>
                                </span>
                            </li>
                        ))}
                    </ul>
                </div>
            )}

            <Modal show={target !== null} onClose={close}>
                <form onSubmit={submit} className="p-6">
                    <h3 className="text-lg font-medium text-gray-900">
                        {target === 'current' ? 'Змінити заклад лікування' : 'Редагувати запис історії'}
                    </h3>

                    <div className="mt-4 space-y-4">
                        <div>
                            <InputLabel value="Тип медичного закладу" />
                            <SelectInput
                                className="mt-1 block w-full"
                                value={form.data.facility_type}
                                onChange={(e) => form.setData('facility_type', e.target.value)}
                            >
                                <option value="">—</option>
                                {facilityTypes.map((o) => (
                                    <option key={o.value} value={o.value}>
                                        {o.label}
                                    </option>
                                ))}
                            </SelectInput>
                            <InputError message={form.errors.facility_type} className="mt-1" />
                        </div>

                        <div>
                            <InputLabel value="Населений пункт" />
                            {target === 'current' ? (
                                <CityAutocomplete
                                    className="mt-1 block w-full"
                                    value={facilitySettlement}
                                    onChange={(settlement) => {
                                        setFacilitySettlement(settlement);
                                        form.setData('facility_settlement_id', settlement?.id ?? '');
                                    }}
                                />
                            ) : (
                                <CityAutocomplete
                                    className="mt-1 block w-full"
                                    value={form.data.city}
                                    requireSelection={false}
                                    onChange={(settlement, text) => form.setData('city', settlement ? settlement.name : text)}
                                />
                            )}
                            <InputError message={form.errors.facility_settlement_id ?? form.errors.city} className="mt-1" />
                        </div>

                        <div>
                            <InputLabel value="Назва медичного закладу" />
                            <TextInput
                                className="mt-1 block w-full"
                                value={form.data.facility_name}
                                onChange={(e) => form.setData('facility_name', e.target.value)}
                            />
                            <InputError message={form.errors.facility_name} className="mt-1" />
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
