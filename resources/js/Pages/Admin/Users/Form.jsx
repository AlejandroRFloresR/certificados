import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import FormSection from '@/Components/Form/FormSection';
import TextField from '@/Components/Form/TextField';
import PasswordField, { PasswordRequirements } from '@/Components/Form/PasswordField';
import RadioCards from '@/Components/Form/RadioCards';
import FormActions from '@/Components/Form/FormActions';
import { focusFirstError } from '@/Components/Form/useUnsavedChanges';
import { ROLE_LABELS } from '@/Components/RoleBadge';

const ROLE_DESCRIPTIONS = {
    user:  'Ve sus cursos, se inscribe a los publicados y descarga sus certificados.',
    admin: 'Gestiona cursos, usuarios y certificados, y sincroniza con Moodle.',
};

export default function Form({ user, roles }) {
    const isEdit = Boolean(user);

    const form = useForm({
        name:     user?.name ?? '',
        email:    user?.email ?? '',
        dni:      user?.dni ?? '',
        telefono: user?.telefono ?? '',
        role:     user?.role ?? 'user',
        password: '',
        password_confirmation: '',
    });

    const set = (key) => (value) => form.setData(key, value);

    const submit = (e) => {
        e.preventDefault();
        // Al editarse a sí mismo no se envía el rol (el controlador tampoco lo cambiaría)
        form.transform((data) => (user?.is_self ? { ...data, role: null } : data));
        const options = {
            preserveScroll: true,
            onError: focusFirstError,
            onFinish: () => form.reset('password', 'password_confirmation'),
        };

        if (isEdit) form.put(route('admin.users.update', user.id), options);
        else form.post(route('admin.users.store'), options);
    };

    // "Usuario" primero: es el rol más común
    const roleOptions = [...roles]
        .sort((a, b) => (a === 'user' ? -1 : b === 'user' ? 1 : 0))
        .map((r) => ({ value: r, label: ROLE_LABELS[r] ?? r, description: ROLE_DESCRIPTIONS[r] }));

    return (
        <AppLayout>
            <Head title={isEdit ? `Editar ${user.name}` : 'Crear usuario'} />

            <form onSubmit={submit} noValidate>
                <Link href={route('admin.users.index')} className="mb-3 inline-flex items-center gap-1.5 py-1.5 text-sm font-semibold text-hospitalblue hover:underline">
                    <svg aria-hidden="true" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                    Usuarios
                </Link>
                <h1 className="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{isEdit ? 'Editar usuario' : 'Crear usuario'}</h1>
                <p className="mt-1.5 text-sm text-hospitalgray">
                    Los campos con <span className="font-bold text-red-700">*</span> son obligatorios.
                </p>

                <div className="mt-8 space-y-8">
                    <FormSection title="Datos personales"
                                 description="El nombre y el DNI se imprimen en los certificados. Con el DNI se verifican desde la página pública.">
                        <div className="grid gap-5 sm:grid-cols-2">
                            <TextField id="name" label="Nombre y apellido" required autoComplete="off" maxLength={255}
                                       value={form.data.name} onChange={set('name')} error={form.errors.name} />
                            <TextField id="email" type="email" label="Email" required autoComplete="off"
                                       value={form.data.email} onChange={set('email')} error={form.errors.email} />
                            <TextField id="dni" label="DNI" required inputMode="numeric" maxLength={20} hint="Solo números, sin puntos."
                                       value={form.data.dni} onChange={set('dni')} error={form.errors.dni} />
                            <TextField id="telefono" type="tel" label="Teléfono" required maxLength={20}
                                       value={form.data.telefono} onChange={set('telefono')} error={form.errors.telefono} />
                        </div>
                    </FormSection>

                    <FormSection title="Rol" description="Define qué puede hacer en el sistema.">
                        {user?.is_self ? (
                            <p className="text-sm text-hospitalgray">
                                Tu rol es <strong className="text-gray-900">{ROLE_LABELS[user.role] ?? user.role}</strong>. No podés cambiar tu propio rol.
                            </p>
                        ) : (
                            <RadioCards id="role" label="Rol" required columns={2} options={roleOptions}
                                        value={form.data.role} onChange={set('role')} error={form.errors.role} />
                        )}
                    </FormSection>

                    <FormSection title="Contraseña" description={isEdit ? 'Dejala vacía para no cambiarla.' : 'El usuario la puede cambiar después desde su perfil.'} last>
                        <div className="grid gap-5 sm:grid-cols-2">
                            <PasswordField id="password" label={isEdit ? 'Nueva contraseña' : 'Contraseña'} required={!isEdit} autoComplete="new-password"
                                           value={form.data.password} onChange={set('password')} error={form.errors.password} />
                            <PasswordField id="password_confirmation" label="Repetir contraseña" required={!isEdit} autoComplete="new-password"
                                           value={form.data.password_confirmation} onChange={set('password_confirmation')}
                                           error={form.errors.password_confirmation} />
                        </div>
                        <PasswordRequirements password={form.data.password} confirmation={form.data.password_confirmation} />
                    </FormSection>
                </div>

                <FormActions form={form} cancelHref={route('admin.users.index')}
                             submitLabel={isEdit ? 'Guardar cambios' : 'Crear usuario'} alwaysEnabled={!isEdit} />
            </form>
        </AppLayout>
    );
}
