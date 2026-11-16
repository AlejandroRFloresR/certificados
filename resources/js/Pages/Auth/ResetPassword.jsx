import { useForm } from '@inertiajs/react';
import AuthLayout from '@/Layouts/AuthLayout';
import TextField from '@/Components/Form/TextField';
import PasswordField, { PasswordRequirements } from '@/Components/Form/PasswordField';
import SubmitButton from '@/Components/Form/SubmitButton';
import { focusFirstError } from '@/Components/Form/useUnsavedChanges';

export default function ResetPassword({ token, email }) {
    const form = useForm({ token, email, password: '', password_confirmation: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('password.store'), {
            onError: focusFirstError,
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Nueva contraseña" subtitle="Elegí una contraseña de al menos 8 caracteres">
            <form onSubmit={submit} noValidate className="space-y-4">
                <TextField id="email" type="email" label="Email" required autoComplete="username"
                           value={form.data.email} onChange={(v) => form.setData('email', v)} error={form.errors.email} />
                <PasswordField id="password" label="Nueva contraseña" required autoComplete="new-password" autoFocus
                               value={form.data.password} onChange={(v) => form.setData('password', v)} error={form.errors.password} />
                <PasswordField id="password_confirmation" label="Repetir contraseña" required autoComplete="new-password"
                               value={form.data.password_confirmation} onChange={(v) => form.setData('password_confirmation', v)}
                               error={form.errors.password_confirmation} />
                <PasswordRequirements password={form.data.password} confirmation={form.data.password_confirmation} />
                <SubmitButton processing={form.processing} processingLabel="Guardando…">Guardar contraseña</SubmitButton>
            </form>
        </AuthLayout>
    );
}
