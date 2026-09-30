import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '@/Layouts/AuthLayout';
import Field from '@/Components/Field';
import Button from '@/Components/Button';

export default function Login() {
    const { flash } = usePage().props;
    const form = useForm({ email: '', password: '', remember: false });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('login'), {
            onFinish: () => form.reset('password'), // no dejar la contraseña cargada si falla
        });
    };

    return (
        <AuthLayout title="Iniciar sesión" subtitle="Ingresá para ver tus cursos y certificados">
            {flash.status && (
                <div className="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                    {flash.status}
                </div>
            )}

            <form onSubmit={submit} className="space-y-4">
                <Field id="email" type="email" label="Email" autoComplete="username" autoFocus required
                       value={form.data.email}
                       onChange={(e) => form.setData('email', e.target.value)}
                       error={form.errors.email} />

                <Field id="password" type="password" label="Contraseña" autoComplete="current-password" required
                       value={form.data.password}
                       onChange={(e) => form.setData('password', e.target.value)}
                       error={form.errors.password} />

                <label className="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox"
                           checked={form.data.remember}
                           onChange={(e) => form.setData('remember', e.target.checked)}
                           className="rounded border-gray-300 text-hospitalblue focus:ring-hospitalblue" />
                    Recordarme
                </label>

                <Button className="w-full" disabled={form.processing}>
                    {form.processing ? 'Ingresando…' : 'Ingresar'}
                </Button>
            </form>

            <p className="mt-6 text-center text-sm text-hospitalgray">
                ¿No tenés cuenta?{' '}
                <Link href={route('register')} className="font-semibold text-hospitalblue hover:underline">
                    Registrate
                </Link>
            </p>
        </AuthLayout>
    );
}
