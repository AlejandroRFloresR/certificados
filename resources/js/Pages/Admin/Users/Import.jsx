import { Head, Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import FormSection from '@/Components/Form/FormSection';
import FileDrop from '@/Components/Form/FileDrop';
import RadioCards from '@/Components/Form/RadioCards';
import SelectField from '@/Components/Form/SelectField';
import FormActions from '@/Components/Form/FormActions';
import { focusFirstError } from '@/Components/Form/useUnsavedChanges';
import { ROLE_LABELS } from '@/Components/RoleBadge';

const ROLE_OPTIONS = [
    { value: 'user',  label: ROLE_LABELS.user,  description: 'Lo habitual para alumnos.' },
    { value: 'admin', label: ROLE_LABELS.admin, description: 'Acceso completo a la gestión.' },
];

export default function Import({ courses }) {
    const { flash } = usePage().props;

    // Sin columna "role" en el archivo, el importador usa "user": se muestra ese valor de entrada
    const form = useForm({ file: null, default_role: 'user', enroll_course_id: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('admin.users.import.store'), {
            forceFormData: true,     // necesario para enviar archivos
            preserveScroll: true,
            onError: focusFirstError,
            onSuccess: () => form.reset('file'),
        });
    };

    const failures = flash.failures ?? [];

    return (
        <AppLayout>
            <Head title="Importar usuarios" />

            <form onSubmit={submit} noValidate>
                <Link href={route('admin.users.index')} className="mb-3 inline-flex items-center gap-1.5 py-1.5 text-sm font-semibold text-hospitalblue hover:underline">
                    <svg aria-hidden="true" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                    Usuarios
                </Link>
                <h1 className="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">Importar usuarios</h1>
                <p className="mt-1.5 text-sm text-hospitalgray">
                    Crea las cuentas desde un Excel. Las personas que ya existen (mismo email) no se duplican.
                </p>

                {failures.length > 0 && (
                    <div className="mt-6 overflow-hidden rounded-xl border border-red-200 bg-white shadow-sm">
                        <h2 className="border-b border-red-100 bg-red-50 px-5 py-3 text-sm font-semibold text-red-800">
                            {failures.length} {failures.length === 1 ? 'fila no se importó' : 'filas no se importaron'}
                        </h2>
                        <ul className="divide-y divide-gray-100 text-sm">
                            {failures.map((f, i) => (
                                <li key={i} className="flex gap-3 px-5 py-2.5">
                                    <span className="shrink-0 font-semibold tabular-nums text-gray-900">Fila {f.row}</span>
                                    <span className="text-red-700">{f.errors.join(' · ')}</span>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                <div className="mt-8 space-y-8">
                    <FormSection title="Archivo"
                                 description="Columnas: nombre, apellido, correo_electronico y dni. Opcionales: telefono, role, password y course_ids (ej. 1|3|5).">
                        <FileDrop id="file" label="Planilla de usuarios" required file={form.data.file}
                                  onChange={(f) => form.setData('file', f)}
                                  accept=".xlsx,.xls,.csv" acceptLabel=".xlsx, .xls o .csv · hasta 10 MB"
                                  progress={form.progress} error={form.errors.file} />
                    </FormSection>

                    <FormSection title="Opciones" description="Se aplican a todas las filas del archivo." last>
                        <RadioCards id="default_role" label="Rol por defecto" required columns={2} options={ROLE_OPTIONS}
                                    value={form.data.default_role} onChange={(v) => form.setData('default_role', v)}
                                    error={form.errors.default_role} />
                        <p className="-mt-2 text-sm text-hospitalgray">Se usa en las filas que no traen la columna "role".</p>
                        <SelectField id="enroll_course_id" label="Inscribir a un curso" placeholder="No inscribir"
                                     hint="Además, cada fila puede indicar sus cursos en la columna course_ids."
                                     options={courses.map((c) => ({ value: c.id, label: c.title }))}
                                     value={form.data.enroll_course_id} onChange={(v) => form.setData('enroll_course_id', v)}
                                     error={form.errors.enroll_course_id} />
                    </FormSection>
                </div>

                <FormActions form={form} cancelHref={route('admin.users.index')}
                             submitLabel="Importar" processingLabel="Importando…" />
            </form>
        </AppLayout>
    );
}
