import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import ServicemanFormFields from './Partials/ServicemanFormFields';

function toDateInput(value) {
    return value ? String(value).slice(0, 10) : '';
}

export default function Edit({
    serviceman,
    units,
    curators,
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
    const { data, setData, put, processing, errors } = useForm({
        unit_id: serviceman.unit_id ?? '',
        full_name: serviceman.full_name ?? '',
        rank: serviceman.rank ?? '',
        position: serviceman.position ?? '',
        tax_id: serviceman.tax_id ?? '',
        phone: serviceman.phone ?? '',
        birth_date: toDateInput(serviceman.birth_date),
        notes: serviceman.notes ?? '',
        family_contact: serviceman.family_contact ?? '',
        status: serviceman.status ?? 'in_progress',
        military_status: serviceman.military_status ?? 'active',
        is_combat_veteran: !!serviceman.is_combat_veteran,
        treatment_status: serviceman.treatment_status ?? '',
        curator_id: serviceman.curator_id ?? '',
        evacuation_date: toDateInput(serviceman.evacuation_date),
        diagnosis: serviceman.diagnosis ?? '',
        severity: serviceman.severity ?? '',
        has_amputation: !!serviceman.has_amputation,
        has_prosthetic: !!serviceman.has_prosthetic,
        has_certificate_5: !!serviceman.has_certificate_5,
        ecopfo_date: toDateInput(serviceman.ecopfo_date),
        disability_group: serviceman.disability_group ?? 'none',
        work_capacity_loss_percent: serviceman.work_capacity_loss_percent ?? '',
        remote_vlk_status: serviceman.remote_vlk_status ?? '',
        material_aid_status: serviceman.material_aid_status ?? 'not_applicable',
        first_contact_after_evacuation_date: toDateInput(serviceman.first_contact_after_evacuation_date),
        last_contact_date: toDateInput(serviceman.last_contact_date),
        last_visit_date: toDateInput(serviceman.last_visit_date),
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('servicemen.update', serviceman.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Редагування картки: {serviceman.full_name}
                </h2>
            }
        >
            <Head title={`Редагування: ${serviceman.full_name}`} />

            <div className="py-8">
                <div className="mx-auto max-w-4xl sm:px-6 lg:px-8">
                    <form
                        onSubmit={submit}
                        className="bg-white p-6 shadow sm:rounded-lg"
                    >
                        <ServicemanFormFields
                            data={data}
                            setData={setData}
                            errors={errors}
                            serviceman={serviceman}
                            options={{
                                units,
                                curators,
                                statuses,
                                severities,
                                disabilityGroups,
                                facilityTypes,
                                remoteVlkStatuses,
                                materialAidStatuses,
                                paymentIssueTypes,
                                militaryStatuses,
                                treatmentStatuses,
                            }}
                        />

                        <div className="mt-6 flex justify-end">
                            <PrimaryButton disabled={processing}>
                                Зберегти
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
