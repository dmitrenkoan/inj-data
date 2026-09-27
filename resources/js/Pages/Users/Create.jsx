import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import UserFormFields from './Partials/UserFormFields';

export default function Create({ roles, brigades, battalions }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        role: roles[0]?.value ?? 'battalion',
        brigade_id: brigades.length === 1 ? brigades[0]?.id : '',
        battalion_id: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('users.store'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Новий користувач
                </h2>
            }
        >
            <Head title="Новий користувач" />

            <div className="py-8">
                <div className="mx-auto max-w-3xl sm:px-6 lg:px-8">
                    <form
                        onSubmit={submit}
                        className="bg-white p-6 shadow sm:rounded-lg"
                    >
                        <UserFormFields
                            data={data}
                            setData={setData}
                            errors={errors}
                            options={{ roles, brigades, battalions }}
                        />

                        <div className="mt-6 flex justify-end">
                            <PrimaryButton disabled={processing}>Створити</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
