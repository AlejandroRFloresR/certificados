import { useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Field from '@/Components/Field';
import Button from '@/Components/Button';

function Section({ title, description, children }) {
    return (
        <section className="grid gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6 md:grid-cols-3">
            <div>
                <h2 className="text-sm font-semibold text-gray-900">{title}</h2>
                <p className="mt-1 text-sm text-hospitalgray">{description}</p>
            </div>
            <div className="md:col-span-2">{children}</div>
        </section>
    );
}

function ProfileForm({ profile }) {
    const form = useForm({ name: profile.name, email: profile.email, telefono: profile.telefono ?? '' });

    const bind = (key) => ({
        id: key,
        value: form.data[key],
        onChange: (e) => form.setData(key, e.target.value),
        error: form.errors[key],
    });

    return (
        <form onSubmit={(e) => { e.preventDefault(); form.patch(route('profile.update'), { preserveScroll: true }); }}
              className="space-y-4">
            <Field label="Nombre y apellido" autoComplete="name" required {...bind('name')} />
            <Field label="Email" type="email" autoComplete="email" required {...bind('email')} />
            <div className="grid gap-4 sm:grid-cols-2">
                <Field id="dni" label="DNI" value={profile.dni ?? ''} disabled
                       hint="Para corregirlo, contactá a un administrador." />
                <Field label="Teléfono" type="tel" autoComplete="tel" required {...bind('telefono')} />
            </div>
            <div className="flex justify-end">
                <Button disabled={form.processing || !form.isDirty}>
                    {form.processing ? 'Guardando…' : 'Guardar cambios'}
                </Button>
            </div>
        </form>
    );
}

function PasswordForm() {
    const form = useForm({ current_password: '', password: '', password_confirmation: '' });

    const bind = (key) => ({
        id: key,
        type: 'password',
        value: form.data[key],
        onChange: (e) => form.setData(key, e.target.value),
        error: form.errors[key],
    });

    const submit = (e) => {
        e.preventDefault();
        form.put(route('password.update'), {
            errorBag: 'updatePassword',   // el controlador valida con validateWithBag('updatePassword')
            preserveScroll: true,
            onSuccess: () => form.reset(),
            onError: () => form.reset('password', 'password_confirmation'),
        });
    };

    return (
        <form onSubmit={submit} className="space-y-4">
            <Field label="Contraseña actual" autoComplete="current-password" required {...bind('current_password')} />
            <div className="grid gap-4 sm:grid-cols-2">
                <Field label="Nueva contraseña" autoComplete="new-password" required {...bind('password')} />
                <Field label="Confirmar nueva contraseña" autoComplete="new-password" required {...bind('password_confirmation')} />
            </div>
            <div className="flex justify-end">
                <Button disabled={form.processing}>{form.processing ? 'Guardando…' : 'Cambiar contraseña'}</Button>
            </div>
        </form>
    );
}

export default function Edit({ profile }) {
    return (
        <AppLayout title="Mi perfil">
            <div className="mx-auto max-w-4xl space-y-6">
                <Section title="Datos personales" description="Tu nombre aparece así en los certificados.">
                    <ProfileForm profile={profile} />
                </Section>
                <Section title="Contraseña" description="Usá al menos 8 caracteres.">
                    <PasswordForm />
                </Section>
            </div>
        </AppLayout>
    );
}
