import { Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Field from '@/Components/Field';
import Button from '@/Components/Button';
import { ROLE_LABELS } from '@/Components/RoleBadge';

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

    const bind = (key) => ({
        id: key,
        value: form.data[key],
        onChange: (e) => form.setData(key, e.target.value),
        error: form.errors[key],
    });

    const submit = (e) => {
        e.preventDefault();
        // Al editarse a sí mismo no se envía el rol (el controlador tampoco lo cambiaría)
        form.transform((data) => (user?.is_self ? { ...data, role: null } : data));
        const options = { onFinish: () => form.reset('password', 'password_confirmation') };

        if (isEdit) form.put(route('admin.users.update', user.id), options);
        else form.post(route('admin.users.store'), options);
    };

    const losesTutor = isEdit && user.role === 'tutor' && form.data.role !== 'tutor';

    return (
        <AppLayout title={isEdit ? `Editar usuario — ${user.name}` : 'Crear usuario'}>
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-6">

                <div className="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 className="text-sm font-semibold text-gray-900">Datos personales</h2>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label="Nombre y apellido" required {...bind('name')} />
                        <Field label="Email" type="email" required {...bind('email')} />
                        <Field label="DNI" inputMode="numeric" required {...bind('dni')} />
                        <Field label="Teléfono" type="tel" required {...bind('telefono')} />
                    </div>
                </div>

                <div className="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 className="text-sm font-semibold text-gray-900">Rol</h2>
                    {user?.is_self ? (
                        <p className="text-sm text-hospitalgray">
                            Tu rol es <strong className="text-gray-900">{ROLE_LABELS[user.role] ?? user.role}</strong>. No podés cambiar tu propio rol.
                        </p>
                    ) : (
                        <>
                            <Field as="select" label="Rol" className="sm:w-1/2" {...bind('role')}>
                                {roles.map((r) => <option key={r} value={r}>{ROLE_LABELS[r] ?? r}</option>)}
                            </Field>
                            {losesTutor && (
                                <p className="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                                    Al quitarle el rol de tutor se eliminan su firma y sus asignaciones como tutor en los cursos.
                                </p>
                            )}
                        </>
                    )}
                </div>

                <div className="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <div>
                        <h2 className="text-sm font-semibold text-gray-900">Contraseña</h2>
                        {isEdit && <p className="mt-0.5 text-xs text-hospitalgray">Dejala vacía para no cambiarla.</p>}
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field label={isEdit ? 'Nueva contraseña' : 'Contraseña'} type="password" autoComplete="new-password"
                               required={!isEdit} hint="Mínimo 8 caracteres" {...bind('password')} />
                        <Field label="Confirmar contraseña" type="password" autoComplete="new-password"
                               required={!isEdit} {...bind('password_confirmation')} />
                    </div>
                </div>

                <div className="flex items-center justify-end gap-2">
                    <Link href={route('admin.users.index')}>
                        <Button type="button" variant="secondary">Cancelar</Button>
                    </Link>
                    <Button disabled={form.processing}>
                        {form.processing ? 'Guardando…' : isEdit ? 'Guardar cambios' : 'Crear usuario'}
                    </Button>
                </div>
            </form>
        </AppLayout>
    );
}
