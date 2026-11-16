import { Link, useForm, usePage } from '@inertiajs/react';
import AuthLayout from '@/Layouts/AuthLayout';
import TextField from '@/Components/Form/TextField';
import SubmitButton from '@/Components/Form/SubmitButton';
import { focusFirstError } from '@/Components/Form/useUnsavedChanges';

export default function ForgotPassword() {
    const { flash } = usePage().props;
    const form = useForm({ email: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('password.email'), { onError: focusFirstError });
    };

    return (
        <AuthLayout title="Recuperar contraseña" subtitle="Te enviamos un link para crear una nueva">
            {flash.status && (
                <div className="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">{flash.status}</div>
            )}
            <form onSubmit={submit} noValidate className="space-y-4">
                <TextField id="email" type="email" label="Email" required autoComplete="username" autoFocus
                           value={form.data.email} onChange={(v) => form.setData('email', v)} error={form.errors.email} />
                <SubmitButton processing={form.processing} processingLabel="Enviando…">Enviar link</SubmitButton>
            </form>
            <p className="mt-6 text-center text-sm">
                <Link href={route('login')} className="font-semibold text-hospitalblue hover:underline">← Volver a iniciar sesión</Link>
            </p>
        </AuthLayout>
    );
}
