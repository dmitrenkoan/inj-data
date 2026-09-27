import DangerButton from '@/Components/DangerButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AwardsManager from './Partials/AwardsManager';
import PaymentIssuesManager from './Partials/PaymentIssuesManager';
import TreatmentFacilityManager from './Partials/TreatmentFacilityManager';

function fmtDate(value) {
    return value ? String(value).slice(0, 10) : '—';
}

function fmtBool(value) {
    return value ? 'Так' : 'Ні';
}

function label(options, value) {
    return options.find((o) => o.value === value)?.label ?? value ?? '—';
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

function Row({ label: l, children }) {
    return (
        <div>
            <dt className="text-xs font-medium uppercase text-gray-500">{l}</dt>
            <dd className="mt-1 text-sm text-gray-900">{children}</dd>
        </div>
    );
}

const TABS = [
    { key: 'personal', label: 'Загальна інформація' },
    { key: 'treatment', label: 'Лікування' },
    { key: 'payments', label: 'Виплати' },
    { key: 'contacts', label: 'Дата контактів' },
    { key: 'awards', label: 'Нагороди' },
];

export default function Show({
    serviceman,
    statuses,
    severities,
    disabilityGroups,
    facilityTypes,
    remoteVlkStatuses,
    materialAidStatuses,
    paymentIssueTypes,
    militaryStatuses,
    treatmentStatuses,
}) {
    const [tab, setTab] = useState('personal');

    const destroy = () => {
        if (confirm('Видалити картку пораненого? Цю дію неможливо скасувати.')) {
            router.delete(route('servicemen.destroy', serviceman.id));
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <div className="flex items-center justify-between">
                    <h2 className="text-xl font-semibold leading-tight text-gray-800">
                        {serviceman.full_name}
                    </h2>
                    <div className="flex gap-2">
                        <Link
                            href={route('servicemen.edit', serviceman.id)}
                            className="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow ring-1 ring-gray-300 hover:bg-gray-50"
                        >
                            Редагувати
                        </Link>
                        <DangerButton onClick={destroy}>Видалити</DangerButton>
                    </div>
                </div>
            }
        >
            <Head title={serviceman.full_name} />

            <div className="py-8">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <div className="bg-white p-6 shadow sm:rounded-lg">
                        <AlertBadges serviceman={serviceman} />

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
                            <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <Row label="Підрозділ">
                                    {serviceman.unit ? unitLabel(serviceman.unit) : '—'}
                                </Row>
                                <Row label="Статус супроводження">{label(statuses, serviceman.status)}</Row>
                                <Row label="Статус військовослужбовця">{label(militaryStatuses, serviceman.military_status)}</Row>
                                <Row label="Статус лікування">{label(treatmentStatuses, serviceman.treatment_status)}</Row>
                                <Row label="Куратор">{serviceman.curator?.name || '—'}</Row>
                                <Row label="Статус УБД">{fmtBool(serviceman.is_combat_veteran)}</Row>
                                <Row label="Звання">{serviceman.rank || '—'}</Row>
                                <Row label="Посада">{serviceman.position || '—'}</Row>
                                <Row label="ІНН">{serviceman.tax_id || '—'}</Row>
                                <Row label="Телефон">{serviceman.phone || '—'}</Row>
                                <Row label="Дата народження">{fmtDate(serviceman.birth_date)}</Row>
                                <div className="sm:col-span-2">
                                    <Row label="Контакт членів сім'ї">{serviceman.family_contact || '—'}</Row>
                                </div>
                                <div className="sm:col-span-2">
                                    <Row label="Примітки">{serviceman.notes || '—'}</Row>
                                </div>
                            </dl>
                        )}

                        {tab === 'treatment' && (
                            <div>
                                <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <Row label="Дата евакуації">{fmtDate(serviceman.evacuation_date)}</Row>
                                    <Row label="Ступінь тяжкості">{label(severities, serviceman.severity)}</Row>
                                    <div className="sm:col-span-2">
                                        <Row label="Діагноз">{serviceman.diagnosis || '—'}</Row>
                                    </div>
                                    <Row label="Наявність довідки 5">{fmtBool(serviceman.has_certificate_5)}</Row>
                                </dl>

                                <div className="mt-6 border-t border-gray-200 pt-4">
                                    <h4 className="mb-2 text-sm font-semibold uppercase text-gray-700">ЕКОПФО</h4>
                                    <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <Row label="Ампутації">{fmtBool(serviceman.has_amputation)}</Row>
                                        <Row label="Наявність протезу">{fmtBool(serviceman.has_prosthetic)}</Row>
                                        <Row label="Дата проходження ЕКОПФО">{fmtDate(serviceman.ecopfo_date)}</Row>
                                        <Row label="Група інвалідності">{label(disabilityGroups, serviceman.disability_group)}</Row>
                                        <Row label="Відсоток втрати працездатності">
                                            {serviceman.work_capacity_loss_percent ?? '—'}
                                        </Row>
                                    </dl>
                                </div>

                                <div className="mt-6 rounded-md bg-indigo-50 p-4">
                                    <h4 className="mb-2 text-sm font-semibold uppercase text-indigo-900">
                                        Контрольні дати ВЛК
                                    </h4>
                                    <div className="grid grid-cols-3 gap-4 text-sm text-indigo-900">
                                        <div>4 міс.: {fmtDate(serviceman.vlk4_months_date)}</div>
                                        <div>8 міс.: {fmtDate(serviceman.vlk8_months_date)}</div>
                                        <div>12 міс.: {fmtDate(serviceman.vlk12_months_date)}</div>
                                    </div>
                                </div>

                                <TreatmentFacilityManager serviceman={serviceman} facilityTypes={facilityTypes} />

                                {serviceman.facility_type === 'foreign' && (
                                    <dl className="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                        <Row label="Дистанційне ВЛК">
                                            {label(remoteVlkStatuses, serviceman.remote_vlk_status)}
                                        </Row>
                                    </dl>
                                )}
                            </div>
                        )}

                        {tab === 'payments' && (
                            <div>
                                <dl className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <Row label="Виплата матдопомоги на вирішення соц.-поб. питань">
                                        {label(materialAidStatuses, serviceman.material_aid_status)}
                                    </Row>
                                </dl>

                                <PaymentIssuesManager serviceman={serviceman} paymentIssueTypes={paymentIssueTypes} />
                            </div>
                        )}

                        {tab === 'contacts' && (
                            <dl className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <Row label="Перший контакт після евакуації">
                                    {fmtDate(serviceman.first_contact_after_evacuation_date)}
                                </Row>
                                <Row label="Крайній контакт">{fmtDate(serviceman.last_contact_date)}</Row>
                                <Row label="Крайнє відвідування">{fmtDate(serviceman.last_visit_date)}</Row>
                            </dl>
                        )}

                        {tab === 'awards' && <AwardsManager serviceman={serviceman} />}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
