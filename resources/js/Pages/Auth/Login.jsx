import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '@/Layouts/AuthLayout';
import TextField from '@/Components/Form/TextField';
import PasswordField from '@/Components/Form/PasswordField';
import Checkbox from '@/Components/Form/Checkbox';
import SubmitButton from '@/Components/Form/SubmitButton';
import { focusFirstError } from '@/Components/Form/useUnsavedChanges';

export default function Login() {
    const { flash } = usePage().props;
    const form = useForm({ email: '', password: '', remember: false });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('login'), {
            onError: focusFirstError,
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

            <form onSubmit={submit} noValidate className="space-y-4">
                <TextField id="email" type="email" label="Email" required autoComplete="username" autoFocus
                           value={form.data.email} onChange={(v) => form.setData('email', v)} error={form.errors.email} />
                <PasswordField id="password" label="Contraseña" required autoComplete="current-password"
                               value={form.data.password} onChange={(v) => form.setData('password', v)} error={form.errors.password} />
                <Checkbox id="remember" label="Recordarme en este equipo"
                          checked={form.data.remember} onChange={(v) => form.setData('remember', v)} />
                <SubmitButton processing={form.processing} processingLabel="Ingresando…">Ingresar</SubmitButton>
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
