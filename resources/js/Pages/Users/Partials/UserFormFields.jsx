import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import SelectInput from '@/Components/SelectInput';
import TextInput from '@/Components/TextInput';

export default function UserFormFields({ data, setData, errors, options, isEdit = false }) {
    const availableBattalions = options.battalions.filter(
        (b) => !data.brigade_id || String(b.brigade_id) === String(data.brigade_id),
    );

    return (
        <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <InputLabel value="Ім'я" />
                <TextInput
                    className="mt-1 block w-full"
                    value={data.name}
                    onChange={(e) => setData('name', e.target.value)}
                />
                <InputError message={errors.name} className="mt-1" />
            </div>

            <div>
                <InputLabel value="Email" />
                <TextInput
                    type="email"
                    className="mt-1 block w-full"
                    value={data.email}
                    onChange={(e) => setData('email', e.target.value)}
                />
                <InputError message={errors.email} className="mt-1" />
            </div>

            <div>
                <InputLabel value={isEdit ? 'Новий пароль (залиште порожнім, щоб не змінювати)' : 'Пароль'} />
                <TextInput
                    type="password"
                    className="mt-1 block w-full"
                    value={data.password}
                    onChange={(e) => setData('password', e.target.value)}
                />
                <InputError message={errors.password} className="mt-1" />
            </div>

            <div>
                <InputLabel value="Роль" />
                <SelectInput
                    className="mt-1 block w-full"
                    value={data.role}
                    onChange={(e) => {
                        setData('role', e.target.value);
                        if (e.target.value === 'super_admin') {
                            setData('battalion_id', '');
                        }
                    }}
                >
                    {options.roles.map((o) => (
                        <option key={o.value} value={o.value}>
                            {o.label}
                        </option>
                    ))}
                </SelectInput>
                <InputError message={errors.role} className="mt-1" />
            </div>

            {(data.role === 'brigade' || data.role === 'battalion') && (
                <div>
                    <InputLabel value="Військова частина" />
                    <SelectInput
                        className="mt-1 block w-full"
                        value={data.brigade_id ?? ''}
                        onChange={(e) => setData('brigade_id', e.target.value)}
                    >
                        <option value="">— Оберіть військову частину —</option>
                        {options.brigades.filter(Boolean).map((b) => (
                            <option key={b.id} value={b.id}>
                                {b.name}
                            </option>
                        ))}
                    </SelectInput>
                    <InputError message={errors.brigade_id} className="mt-1" />
                </div>
            )}

            {data.role === 'battalion' && (
                <div>
                    <InputLabel value="Батальйон" />
                    <SelectInput
                        className="mt-1 block w-full"
                        value={data.battalion_id ?? ''}
                        onChange={(e) => setData('battalion_id', e.target.value)}
                    >
                        <option value="">— Оберіть батальйон —</option>
                        {availableBattalions.map((b) => (
                            <option key={b.id} value={b.id}>
                                {b.name}
                            </option>
                        ))}
                    </SelectInput>
                    <InputError message={errors.battalion_id} className="mt-1" />
                </div>
            )}
        </div>
    );
}
