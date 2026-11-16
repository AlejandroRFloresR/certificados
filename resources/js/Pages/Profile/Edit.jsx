import { Head, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import FormSection from '@/Components/Form/FormSection';
import TextField from '@/Components/Form/TextField';
import PasswordField, { PasswordRequirements } from '@/Components/Form/PasswordField';
import FormActions from '@/Components/Form/FormActions';
import { focusFirstError } from '@/Components/Form/useUnsavedChanges';

function ProfileForm({ profile }) {
    const form = useForm({ name: profile.name, email: profile.email, telefono: profile.telefono ?? '' });
    const set = (key) => (value) => form.setData(key, value);

    const submit = (e) => {
        e.preventDefault();
        form.patch(route('profile.update'), {
            preserveScroll: true,
            onError: focusFirstError,
            // Lo guardado pasa a ser el nuevo punto de partida: "Sin cambios"
            onSuccess: () => form.setDefaults(),
        });
    };

    return (
        <form onSubmit={submit} noValidate className="space-y-5">
            <TextField id="name" label="Nombre y apellido" required autoComplete="name" maxLength={255}
                       value={form.data.name} onChange={set('name')} error={form.errors.name} />
            <TextField id="email" type="email" label="Email" required autoComplete="email"
                       value={form.data.email} onChange={set('email')} error={form.errors.email} />
            <div className="grid gap-5 sm:grid-cols-2">
                <TextField id="dni" label="DNI" value={profile.dni ?? ''} disabled onChange={() => {}}
                           hint="Para corregirlo, contactá a un administrador." />
                <TextField id="telefono" type="tel" label="Teléfono" required autoComplete="tel" maxLength={20}
                           value={form.data.telefono} onChange={set('telefono')} error={form.errors.telefono} />
            </div>
            <FormActions form={form} inline onCancel={() => { form.reset(); form.clearErrors(); }} />
        </form>
    );
}

function PasswordForm() {
    const form = useForm({ current_password: '', password: '', password_confirmation: '' });
    const set = (key) => (value) => form.setData(key, value);

    const submit = (e) => {
        e.preventDefault();
        form.put(route('password.update'), {
            errorBag: 'updatePassword',   // el controlador valida con validateWithBag('updatePassword')
            preserveScroll: true,
            onSuccess: () => form.reset(),
            onError: (errors) => {
                form.reset('password', 'password_confirmation');
                focusFirstError(errors);
            },
        });
    };

    return (
        <form onSubmit={submit} noValidate className="space-y-5">
            <PasswordField id="current_password" label="Contraseña actual" required autoComplete="current-password"
                           value={form.data.current_password} onChange={set('current_password')} error={form.errors.current_password} />
            <div className="grid gap-5 sm:grid-cols-2">
                <PasswordField id="password" label="Nueva contraseña" required autoComplete="new-password"
                               value={form.data.password} onChange={set('password')} error={form.errors.password} />
                <PasswordField id="password_confirmation" label="Repetir nueva contraseña" required autoComplete="new-password"
                               value={form.data.password_confirmation} onChange={set('password_confirmation')}
                               error={form.errors.password_confirmation} />
            </div>
            <PasswordRequirements password={form.data.password} confirmation={form.data.password_confirmation} />
            <FormActions form={form} inline submitLabel="Cambiar contraseña" onCancel={() => { form.reset(); form.clearErrors(); }} />
        </form>
    );
}

export default function Edit({ profile }) {
    return (
        <AppLayout>
            <Head title="Mi perfil" />
            <h1 className="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Mi perfil</h1>
            <p className="mt-1.5 text-sm text-hospitalgray">
                Los campos con <span className="font-bold text-red-700">*</span> son obligatorios.
            </p>

            <div className="mt-8 space-y-8">
                <FormSection title="Datos personales" description="Tu nombre aparece así en los certificados.">
                    <ProfileForm profile={profile} />
                </FormSection>
                <FormSection title="Contraseña" description="Para cambiarla, primero ingresá la actual." last>
                    <PasswordForm />
                </FormSection>
            </div>
        </AppLayout>
    );
}
