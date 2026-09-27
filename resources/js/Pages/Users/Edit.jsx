import PrimaryButton from '@/Components/PrimaryButton';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';
import UserFormFields from './Partials/UserFormFields';

export default function Edit({ user, roles, brigades, battalions }) {
    const { data, setData, put, processing, errors } = useForm({
        name: user.name,
        email: user.email,
        password: '',
        role: user.role,
        brigade_id: user.brigade_id ?? '',
        battalion_id: user.battalion_id ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('users.update', user.id));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Редагування: {user.name}
                </h2>
            }
        >
            <Head title={`Редагування: ${user.name}`} />

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
                            isEdit
                        />

                        <div className="mt-6 flex justify-end">
                            <PrimaryButton disabled={processing}>Зберегти</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
