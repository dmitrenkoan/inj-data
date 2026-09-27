import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm } from '@inertiajs/react';

function Field({ label, hint, error, children }) {
    return (
        <div>
            <InputLabel value={label} />
            {hint && <p className="mt-1 text-xs text-gray-500">{hint}</p>}
            {children}
            <InputError message={error} className="mt-1" />
        </div>
    );
}

export default function Edit({ settings }) {
    const { data, setData, put, processing, errors } = useForm({
        warning_days: settings.warning_days ?? '',
        attention_days: settings.attention_days ?? '',
        first_contact_days: settings.first_contact_days ?? '',
        visit_days: settings.visit_days ?? '',
    });

    const submit = (e) => {
        e.preventDefault();
        put(route('settings.update'));
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Налаштування
                </h2>
            }
        >
            <Head title="Налаштування" />

            <div className="py-8">
                <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="space-y-6 bg-white p-6 shadow sm:rounded-lg">
                        <p className="text-sm text-gray-600">
                            Ці порогові значення визначають, коли картка пораненого позначається як
                            така, що потребує уваги, на сторінці списку карток. Залиште поле
                            порожнім, щоб вимкнути відповідну перевірку.
                        </p>

                        <Field
                            label="Днів з моменту останнього контакту до попередження"
                            hint="Картка підсвічується жовтим."
                            error={errors.warning_days}
                        >
                            <TextInput
                                type="number"
                                min="0"
                                className="mt-1 block w-full"
                                value={data.warning_days}
                                onChange={(e) => setData('warning_days', e.target.value)}
                            />
                        </Field>

                        <Field
                            label="Днів з моменту останнього контакту до статусу «Потребує уваги»"
                            hint="Картка підсвічується червоним."
                            error={errors.attention_days}
                        >
                            <TextInput
                                type="number"
                                min="0"
                                className="mt-1 block w-full"
                                value={data.attention_days}
                                onChange={(e) => setData('attention_days', e.target.value)}
                            />
                        </Field>

                        <Field
                            label="Днів після евакуації до статусу «Потребує першого контакту»"
                            hint="Застосовується, поки перший контакт після евакуації не зафіксовано."
                            error={errors.first_contact_days}
                        >
                            <TextInput
                                type="number"
                                min="0"
                                className="mt-1 block w-full"
                                value={data.first_contact_days}
                                onChange={(e) => setData('first_contact_days', e.target.value)}
                            />
                        </Field>

                        <Field
                            label="Днів з моменту останнього відвідування до статусу «Потребує відвідування»"
                            error={errors.visit_days}
                        >
                            <TextInput
                                type="number"
                                min="0"
                                className="mt-1 block w-full"
                                value={data.visit_days}
                                onChange={(e) => setData('visit_days', e.target.value)}
                            />
                        </Field>

                        <div className="flex justify-end">
                            <PrimaryButton disabled={processing}>Зберегти</PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
