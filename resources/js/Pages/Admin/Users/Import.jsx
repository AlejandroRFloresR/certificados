import { useState } from 'react';
import { Link, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Field from '@/Components/Field';
import Button from '@/Components/Button';
import { ROLE_LABELS } from '@/Components/RoleBadge';

export default function Import({ courses }) {
    const { flash } = usePage().props;
    const [inputKey, setInputKey] = useState(0); // para vaciar el <input type="file"> después de importar

    const form = useForm({ file: null, default_role: '', enroll_course_id: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('admin.users.import.store'), {
            forceFormData: true,     // necesario para enviar archivos
            preserveScroll: true,
            onSuccess: () => {
                form.reset('file');
                setInputKey((k) => k + 1);
            },
        });
    };

    const failures = flash.failures ?? [];

    return (
        <AppLayout title="Importar usuarios">
            <div className="mx-auto max-w-2xl space-y-6">
                <form onSubmit={submit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <div>
                        <label htmlFor="file" className="block text-sm font-medium text-gray-700">Archivo (.xlsx, .xls o .csv)</label>
                        <input key={inputKey} id="file" type="file" accept=".xlsx,.xls,.csv" required
                               onChange={(e) => form.setData('file', e.target.files[0] ?? null)}
                               className="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-hospitalblue/10 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hospitalblue hover:file:bg-hospitalblue/20" />
                        {form.errors.file && <p className="mt-1 text-xs text-red-600">{form.errors.file}</p>}
                        {form.progress && (
                            <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100">
                                <div className="h-full bg-hospitalblue transition-all" style={{ width: `${form.progress.percentage}%` }} />
                            </div>
                        )}
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field as="select" id="default_role" label="Rol por defecto"
                               hint="Se usa si el archivo no trae la columna role."
                               value={form.data.default_role}
                               onChange={(e) => form.setData('default_role', e.target.value)}
                               error={form.errors.default_role}>
                            <option value="">— Ninguno —</option>
                            {['user', 'tutor', 'admin'].map((r) => <option key={r} value={r}>{ROLE_LABELS[r]}</option>)}
                        </Field>

                        <Field as="select" id="enroll_course_id" label="Inscribir a un curso (opcional)"
                               hint="También podés usar la columna course_ids en el archivo."
                               value={form.data.enroll_course_id}
                               onChange={(e) => form.setData('enroll_course_id', e.target.value)}
                               error={form.errors.enroll_course_id}>
                            <option value="">— Ninguno —</option>
                            {courses.map((c) => <option key={c.id} value={c.id}>{c.title}</option>)}
                        </Field>
                    </div>

                    <div className="flex items-center justify-end gap-2">
                        <Link href={route('admin.users.index')}>
                            <Button type="button" variant="secondary">Volver</Button>
                        </Link>
                        <Button disabled={form.processing}>{form.processing ? 'Importando…' : 'Importar'}</Button>
                    </div>
                </form>

                {failures.length > 0 && (
                    <div className="overflow-hidden rounded-xl border border-red-200 bg-white shadow-sm">
                        <h3 className="border-b border-red-100 bg-red-50 px-5 py-3 text-sm font-semibold text-red-800">
                            {failures.length} {failures.length === 1 ? 'fila con errores' : 'filas con errores'}
                        </h3>
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
            </div>
        </AppLayout>
    );
}
