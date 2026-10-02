import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '@/Layouts/AuthLayout';
import Field from '@/Components/Field';
import Button from '@/Components/Button';

export default function ForgotPassword() {
    const { flash } = usePage().props;
    const form = useForm({ email: '' });

    return (
        <AuthLayout title="Recuperar contraseña" subtitle="Te enviamos un link para crear una nueva">
            {flash.status && (
                <div className="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{flash.status}</div>
            )}
            <form onSubmit={(e) => { e.preventDefault(); form.post(route('password.email')); }} className="space-y-4">
                <Field id="email" type="email" label="Email" autoComplete="username" autoFocus required
                       value={form.data.email} onChange={(e) => form.setData('email', e.target.value)}
                       error={form.errors.email} />
                <Button className="w-full" disabled={form.processing}>
                    {form.processing ? 'Enviando…' : 'Enviar link'}
                </Button>
            </form>
            <p className="mt-6 text-center text-sm">
                <Link href={route('login')} className="font-semibold text-hospitalblue hover:underline">← Volver a iniciar sesión</Link>
            </p>
        </AuthLayout>
    );
}
