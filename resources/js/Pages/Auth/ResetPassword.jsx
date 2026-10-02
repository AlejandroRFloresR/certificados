import { useForm } from '@inertiajs/react';
import AuthLayout from '@/Layouts/AuthLayout';
import Field from '@/Components/Field';
import Button from '@/Components/Button';

export default function ResetPassword({ token, email }) {
    const form = useForm({ token, email, password: '', password_confirmation: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('password.store'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Nueva contraseña" subtitle="Elegí una contraseña de al menos 8 caracteres">
            <form onSubmit={submit} className="space-y-4">
                <Field id="email" type="email" label="Email" autoComplete="username" required
                       value={form.data.email} onChange={(e) => form.setData('email', e.target.value)}
                       error={form.errors.email} />
                <Field id="password" type="password" label="Nueva contraseña" autoComplete="new-password" autoFocus required
                       value={form.data.password} onChange={(e) => form.setData('password', e.target.value)}
                       error={form.errors.password} />
                <Field id="password_confirmation" type="password" label="Confirmar contraseña" autoComplete="new-password" required
                       value={form.data.password_confirmation} onChange={(e) => form.setData('password_confirmation', e.target.value)}
                       error={form.errors.password_confirmation} />
                <Button className="w-full" disabled={form.processing}>
                    {form.processing ? 'Guardando…' : 'Guardar contraseña'}
                </Button>
            </form>
        </AuthLayout>
    );
}
