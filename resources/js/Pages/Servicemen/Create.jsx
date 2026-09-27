import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import ServicemanFormFields from './Partials/ServicemanFormFields';

export default function Create({
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
    const { data, setData, post, processing, errors } = useForm({
        unit_id: units.length === 1 ? units[0].id : '',
        full_name: '',
        rank: '',
        position: '',
        tax_id: '',
        phone: '',
        birth_date: '',
        notes: '',
        family_contact: '',
        status: 'in_progress',
        military_status: 'active',
        is_combat_veteran: false,
        treatment_status: '',
        curator_id: '',
        evacuation_date: '',
        diagnosis: '',
        severity: '',
        has_amputation: false,
        has_prosthetic: false,
        has_certificate_5: false,
        ecopfo_date: '',
        disability_group: 'none',
        work_capacity_loss_percent: '',
        facility_type: '',
        facility_settlement_id: '',
        facility_name: '',
        remote_vlk_status: '',
        material_aid_status: 'not_applicable',
        first_contact_after_evacuation_date: '',
        last_contact_date: '',
        last_visit_date: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('servicemen.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Нова картка пораненого
                </h2>
            }
        >
            <Head title="Нова картка" />

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
                                Створити
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
