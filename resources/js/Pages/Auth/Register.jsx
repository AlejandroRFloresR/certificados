import { Link, useForm } from '@inertiajs/react';
import AuthLayout from '@/Layouts/AuthLayout';
import TextField from '@/Components/Form/TextField';
import PasswordField, { PasswordRequirements } from '@/Components/Form/PasswordField';
import SubmitButton from '@/Components/Form/SubmitButton';
import { focusFirstError } from '@/Components/Form/useUnsavedChanges';

export default function Register() {
    const form = useForm({
        name: '', email: '', dni: '', telefono: '',
        password: '', password_confirmation: '',
    });

    const set = (key) => (value) => form.setData(key, value);

    const submit = (e) => {
        e.preventDefault();
        form.post(route('register'), {
            onError: focusFirstError,
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Crear cuenta" subtitle="Registrate para inscribirte a cursos y obtener tus certificados">
            <form onSubmit={submit} noValidate className="space-y-4">
                <TextField id="name" label="Nombre y apellido" required autoComplete="name" autoFocus maxLength={255}
                           hint="Así va a aparecer en tus certificados."
                           value={form.data.name} onChange={set('name')} error={form.errors.name} />
                <TextField id="email" type="email" label="Email" required autoComplete="email"
                           value={form.data.email} onChange={set('email')} error={form.errors.email} />

                <div className="grid grid-cols-2 gap-3">
                    <TextField id="dni" label="DNI" required inputMode="numeric" maxLength={20}
                               value={form.data.dni} onChange={set('dni')} error={form.errors.dni} />
                    <TextField id="telefono" type="tel" label="Teléfono" required autoComplete="tel" maxLength={20}
                               value={form.data.telefono} onChange={set('telefono')} error={form.errors.telefono} />
                </div>

                <PasswordField id="password" label="Contraseña" required autoComplete="new-password"
                               value={form.data.password} onChange={set('password')} error={form.errors.password} />
                <PasswordField id="password_confirmation" label="Repetir contraseña" required autoComplete="new-password"
                               value={form.data.password_confirmation} onChange={set('password_confirmation')}
                               error={form.errors.password_confirmation} />
                <PasswordRequirements password={form.data.password} confirmation={form.data.password_confirmation} />

                <SubmitButton processing={form.processing} processingLabel="Creando cuenta…">Registrarme</SubmitButton>
            </form>

            <p className="mt-6 text-center text-sm text-hospitalgray">
                ¿Ya tenés cuenta?{' '}
                <Link href={route('login')} className="font-semibold text-hospitalblue hover:underline">
                    Iniciá sesión
                </Link>
            </p>
        </AuthLayout>
    );
}
