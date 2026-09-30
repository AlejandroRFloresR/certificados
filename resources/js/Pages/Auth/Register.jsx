import { Link, useForm } from '@inertiajs/react';
import AuthLayout from '@/Layouts/AuthLayout';
import Field from '@/Components/Field';
import Button from '@/Components/Button';

export default function Register() {
    const form = useForm({
        name: '', email: '', dni: '', telefono: '',
        password: '', password_confirmation: '',
    });

    // Evita repetir value / onChange / error en cada campo
    const bind = (key) => ({
        id: key,
        value: form.data[key],
        onChange: (e) => form.setData(key, e.target.value),
        error: form.errors[key],
    });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('register'), {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    };

    return (
        <AuthLayout title="Crear cuenta" subtitle="Registrate para inscribirte a cursos y obtener tus certificados">
            <form onSubmit={submit} className="space-y-4">
                <Field label="Nombre y apellido" autoComplete="name" autoFocus required {...bind('name')} />
                <Field label="Email" type="email" autoComplete="email" required {...bind('email')} />

                <div className="grid grid-cols-2 gap-3">
                    <Field label="DNI" inputMode="numeric" required {...bind('dni')} />
                    <Field label="Teléfono" type="tel" autoComplete="tel" required {...bind('telefono')} />
                </div>

                <Field label="Contraseña" type="password" autoComplete="new-password" required {...bind('password')} />
                <Field label="Confirmar contraseña" type="password" autoComplete="new-password" required
                       {...bind('password_confirmation')} />

                <Button className="w-full" disabled={form.processing}>
                    {form.processing ? 'Creando cuenta…' : 'Registrarme'}
                </Button>
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
