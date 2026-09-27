import CityAutocomplete from '@/Components/CityAutocomplete';
import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';
import TextareaInput from '@/Components/TextareaInput';
import { useState } from 'react';
import AwardsManager from './AwardsManager';
import PaymentIssuesManager from './PaymentIssuesManager';
import TreatmentFacilityManager from './TreatmentFacilityManager';

const BASE_TABS = [
    { key: 'personal', label: 'Загальна інформація' },
    { key: 'treatment', label: 'Лікування' },
    { key: 'payments', label: 'Виплати' },
    { key: 'contacts', label: 'Дата контактів' },
];

function Field({ label, error, children }) {
    return (
        <div>
            <InputLabel value={label} />
            {children}
            <InputError message={error} className="mt-1" />
        </div>
    );
}

function unitLabel(unit) {
    const brigade = unit.battalion?.brigade?.name ?? unit.brigade?.name;
    const battalion = unit.battalion?.name;

    return [brigade, battalion, unit.name].filter(Boolean).join(' / ');
}

const ALERT_BADGE = 'inline-block rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-800';

function AlertBadges({ serviceman }) {
    if (!serviceman.needs_first_contact && !serviceman.needs_attention && !serviceman.needs_visit) {
        return null;
    }

    return (
        <div className="mb-4 flex flex-wrap gap-2">
            {serviceman.needs_first_contact && <span className={ALERT_BADGE}>Потребує першого контакту</span>}
            {serviceman.needs_attention && <span className={ALERT_BADGE}>Потребує уваги</span>}
            {serviceman.needs_visit && <span className={ALERT_BADGE}>Потребує відвідування</span>}
        </div>
    );
}

export default function ServicemanFormFields({ data, setData, errors, options, serviceman = null }) {
    const [tab, setTab] = useState('personal');
    const [facilitySettlement, setFacilitySettlement] = useState(null);
    const TABS = serviceman ? [...BASE_TABS, { key: 'awards', label: 'Нагороди' }] : BASE_TABS;
    const currentFacilityType = serviceman ? serviceman.facility_type : data.facility_type;
    const isForeignFacility = currentFacilityType === 'foreign';

    return (
        <div>
            {serviceman && <AlertBadges serviceman={serviceman} />}
            <div className="mb-6 flex gap-2 border-b border-gray-200">
                {TABS.map((t) => (
                    <button
                        key={t.key}
                        type="button"
                        onClick={() => setTab(t.key)}
                        className={
                            'px-4 py-2 text-sm font-medium border-b-2 -mb-px ' +
                            (tab === t.key
                                ? 'border-indigo-600 text-indigo-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700')
                        }
                    >
                        {t.label}
                    </button>
                ))}
            </div>

            {tab === 'personal' && (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <Field label="Підрозділ" error={errors.unit_id}>
                        <SelectInput
                            className="mt-1 block w-full"
                            value={data.unit_id ?? ''}
                            onChange={(e) => setData('unit_id', e.target.value)}
                        >
                            <option value="">— Оберіть підрозділ —</option>
                            {options.units.map((u) => (
                                <option key={u.id} value={u.id}>
                                    {unitLabel(u)}
                                </option>
                            ))}
                        </SelectInput>
                    </Field>

                    <Field label="Статус супроводження" error={errors.status}>
                        <SelectInput
                            className="mt-1 block w-full"
                            value={data.status ?? ''}
                            onChange={(e) => setData('status', e.target.value)}
                        >
                            {options.statuses.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </SelectInput>
                    </Field>

                    <Field label="Статус військовослужбовця" error={errors.military_status}>
                        <SelectInput
                            className="mt-1 block w-full"
                            value={data.military_status ?? ''}
                            onChange={(e) => setData('military_status', e.target.value)}
                        >
                            {options.militaryStatuses.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </SelectInput>
                    </Field>

                    <Field label="Статус лікування" error={errors.treatment_status}>
                        <SelectInput
                            className="mt-1 block w-full"
                            value={data.treatment_status ?? ''}
                            onChange={(e) => setData('treatment_status', e.target.value)}
                        >
                            <option value="">—</option>
                            {options.treatmentStatuses.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </SelectInput>
                    </Field>

                    <Field label="Куратор" error={errors.curator_id}>
                        <SelectInput
                            className="mt-1 block w-full"
                            value={data.curator_id ?? ''}
                            onChange={(e) => setData('curator_id', e.target.value)}
                        >
                            <option value="">— Не призначено —</option>
                            {options.curators.map((c) => (
                                <option key={c.id} value={c.id}>
                                    {c.name}
                                </option>
                            ))}
                        </SelectInput>
                    </Field>

                    <label className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            checked={!!data.is_combat_veteran}
                            onChange={(e) => setData('is_combat_veteran', e.target.checked)}
                        />
                        <span className="text-sm text-gray-700">Статус УБД</span>
                    </label>

                    <Field label="ПІБ" error={errors.full_name}>
                        <TextInput
                            className="mt-1 block w-full"
                            value={data.full_name ?? ''}
                            onChange={(e) => setData('full_name', e.target.value)}
                        />
                    </Field>

                    <Field label="Звання" error={errors.rank}>
                        <TextInput
                            className="mt-1 block w-full"
                            value={data.rank ?? ''}
                            onChange={(e) => setData('rank', e.target.value)}
                        />
                    </Field>

                    <Field label="Посада" error={errors.position}>
                        <TextInput
                            className="mt-1 block w-full"
                            value={data.position ?? ''}
                            onChange={(e) => setData('position', e.target.value)}
                        />
                    </Field>

                    <Field label="ІНН" error={errors.tax_id}>
                        <TextInput
                            className="mt-1 block w-full"
                            value={data.tax_id ?? ''}
                            onChange={(e) => setData('tax_id', e.target.value)}
                        />
                    </Field>

                    <Field label="Телефон" error={errors.phone}>
                        <TextInput
                            className="mt-1 block w-full"
                            value={data.phone ?? ''}
                            onChange={(e) => setData('phone', e.target.value)}
                        />
                    </Field>

                    <Field label="Дата народження" error={errors.birth_date}>
                        <TextInput
                            type="date"
                            className="mt-1 block w-full"
                            value={data.birth_date ?? ''}
                            onChange={(e) => setData('birth_date', e.target.value)}
                        />
                    </Field>

                    <div className="sm:col-span-2">
                        <Field label="Контакт членів сім'ї" error={errors.family_contact}>
                            <TextareaInput
                                className="mt-1 block w-full"
                                rows={2}
                                value={data.family_contact ?? ''}
                                onChange={(e) => setData('family_contact', e.target.value)}
                            />
                        </Field>
                    </div>

                    <div className="sm:col-span-2">
                        <Field label="Примітки" error={errors.notes}>
                            <TextareaInput
                                className="mt-1 block w-full"
                                rows={3}
                                value={data.notes ?? ''}
                                onChange={(e) => setData('notes', e.target.value)}
                            />
                        </Field>
                    </div>
                </div>
            )}

            {tab === 'treatment' && (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <Field label="Дата евакуації" error={errors.evacuation_date}>
                        <TextInput
                            type="date"
                            className="mt-1 block w-full"
                            value={data.evacuation_date ?? ''}
                            onChange={(e) => setData('evacuation_date', e.target.value)}
                        />
                    </Field>

                    <Field label="Ступінь тяжкості" error={errors.severity}>
                        <SelectInput
                            className="mt-1 block w-full"
                            value={data.severity ?? ''}
                            onChange={(e) => setData('severity', e.target.value)}
                        >
                            <option value="">—</option>
                            {options.severities.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </SelectInput>
                    </Field>

                    <div className="sm:col-span-2">
                        <Field label="Діагноз" error={errors.diagnosis}>
                            <TextareaInput
                                className="mt-1 block w-full"
                                rows={2}
                                value={data.diagnosis ?? ''}
                                onChange={(e) => setData('diagnosis', e.target.value)}
                            />
                        </Field>
                    </div>

                    <label className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            checked={!!data.has_certificate_5}
                            onChange={(e) => setData('has_certificate_5', e.target.checked)}
                        />
                        <span className="text-sm text-gray-700">Наявність довідки 5</span>
                    </label>

                    <div className="sm:col-span-2 border-t border-gray-200 pt-4">
                        <h4 className="mb-2 text-sm font-semibold uppercase text-gray-700">ЕКОПФО</h4>
                    </div>

                    <label className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            checked={!!data.has_amputation}
                            onChange={(e) => setData('has_amputation', e.target.checked)}
                        />
                        <span className="text-sm text-gray-700">Ампутації</span>
                    </label>

                    <label className="flex items-center gap-2">
                        <input
                            type="checkbox"
                            className="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            checked={!!data.has_prosthetic}
                            onChange={(e) => setData('has_prosthetic', e.target.checked)}
                        />
                        <span className="text-sm text-gray-700">Наявність протезу</span>
                    </label>

                    <Field label="Дата проходження ЕКОПФО" error={errors.ecopfo_date}>
                        <TextInput
                            type="date"
                            className="mt-1 block w-full"
                            value={data.ecopfo_date ?? ''}
                            onChange={(e) => setData('ecopfo_date', e.target.value)}
                        />
                    </Field>

                    <Field label="Група інвалідності" error={errors.disability_group}>
                        <SelectInput
                            className="mt-1 block w-full"
                            value={data.disability_group ?? ''}
                            onChange={(e) => setData('disability_group', e.target.value)}
                        >
                            {options.disabilityGroups.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </SelectInput>
                    </Field>

                    <Field label="Відсоток втрати працездатності" error={errors.work_capacity_loss_percent}>
                        <TextInput
                            type="number"
                            min="0"
                            max="100"
                            className="mt-1 block w-full"
                            value={data.work_capacity_loss_percent ?? ''}
                            onChange={(e) => setData('work_capacity_loss_percent', e.target.value)}
                        />
                    </Field>

                    {serviceman ? (
                        <TreatmentFacilityManager serviceman={serviceman} facilityTypes={options.facilityTypes} returnTo="edit" />
                    ) : (
                        <>
                            <div className="sm:col-span-2 border-t border-gray-200 pt-4">
                                <h4 className="mb-2 text-sm font-semibold uppercase text-gray-700">Заклад лікування</h4>
                            </div>

                            <Field label="Тип медичного закладу" error={errors.facility_type}>
                                <SelectInput
                                    className="mt-1 block w-full"
                                    value={data.facility_type ?? ''}
                                    onChange={(e) => {
                                        setData('facility_type', e.target.value);
                                        if (e.target.value !== 'foreign') {
                                            setData('remote_vlk_status', '');
                                        }
                                    }}
                                >
                                    <option value="">—</option>
                                    {options.facilityTypes.map((o) => (
                                        <option key={o.value} value={o.value}>
                                            {o.label}
                                        </option>
                                    ))}
                                </SelectInput>
                            </Field>

                            <Field label="Населений пункт" error={errors.facility_settlement_id}>
                                <CityAutocomplete
                                    className="mt-1 block w-full"
                                    value={facilitySettlement}
                                    onChange={(settlement) => {
                                        setFacilitySettlement(settlement);
                                        setData('facility_settlement_id', settlement?.id ?? '');
                                    }}
                                />
                            </Field>

                            <Field label="Назва медичного закладу" error={errors.facility_name}>
                                <TextInput
                                    className="mt-1 block w-full"
                                    value={data.facility_name ?? ''}
                                    onChange={(e) => setData('facility_name', e.target.value)}
                                />
                            </Field>
                        </>
                    )}

                    <Field label="Дистанційне ВЛК" error={errors.remote_vlk_status}>
                        <SelectInput
                            className="mt-1 block w-full"
                            value={isForeignFacility ? (data.remote_vlk_status ?? '') : ''}
                            disabled={!isForeignFacility}
                            onChange={(e) => setData('remote_vlk_status', e.target.value)}
                        >
                            <option value="">—</option>
                            {options.remoteVlkStatuses.map((o) => (
                                <option key={o.value} value={o.value}>
                                    {o.label}
                                </option>
                            ))}
                        </SelectInput>
                        {!isForeignFacility && (
                            <p className="mt-1 text-xs text-gray-500">
                                Доступно лише для типу закладу «Іноземний».
                            </p>
                        )}
                    </Field>
                </div>
            )}

            {tab === 'payments' && (
                <div>
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <Field label="Виплата матдопомоги на вирішення соц.-поб. питань" error={errors.material_aid_status}>
                            <SelectInput
                                className="mt-1 block w-full"
                                value={data.material_aid_status ?? ''}
                                onChange={(e) => setData('material_aid_status', e.target.value)}
                            >
                                {options.materialAidStatuses.map((o) => (
                                    <option key={o.value} value={o.value}>
                                        {o.label}
                                    </option>
                                ))}
                            </SelectInput>
                        </Field>
                    </div>

                    {serviceman && (
                        <PaymentIssuesManager
                            serviceman={serviceman}
                            paymentIssueTypes={options.paymentIssueTypes}
                            returnTo="edit"
                        />
                    )}
                </div>
            )}

            {tab === 'contacts' && (
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <Field label="Перший контакт після евакуації" error={errors.first_contact_after_evacuation_date}>
                        <TextInput
                            type="date"
                            className="mt-1 block w-full"
                            value={data.first_contact_after_evacuation_date ?? ''}
                            onChange={(e) => setData('first_contact_after_evacuation_date', e.target.value)}
                        />
                    </Field>

                    <Field label="Крайній контакт" error={errors.last_contact_date}>
                        <TextInput
                            type="date"
                            className="mt-1 block w-full"
                            value={data.last_contact_date ?? ''}
                            onChange={(e) => setData('last_contact_date', e.target.value)}
                        />
                    </Field>

                    <Field label="Крайнє відвідування" error={errors.last_visit_date}>
                        <TextInput
                            type="date"
                            className="mt-1 block w-full"
                            value={data.last_visit_date ?? ''}
                            onChange={(e) => setData('last_visit_date', e.target.value)}
                        />
                    </Field>
                </div>
            )}

            {tab === 'awards' && serviceman && (
                <AwardsManager serviceman={serviceman} returnTo="edit" />
            )}
        </div>
    );
}
