import { useMemo, useState } from 'react';
import { Link, router, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Field from '@/Components/Field';
import Button from '@/Components/Button';
import { CERTIFICATE_TYPES } from '@/Components/TypeBadge';

const STATUS = {
    issue:            { label: 'Se emite',                 className: 'bg-green-100 text-green-800' },
    enroll_and_issue: { label: 'Se inscribe y se emite',   className: 'bg-blue-100 text-blue-800' },
    already_issued:   { label: 'Ya tiene certificado',     className: 'bg-gray-100 text-gray-700' },
    type_conflict:    { label: 'Tiene otro tipo',          className: 'bg-amber-100 text-amber-800' },
    below_grade:      { label: 'No alcanza la nota',       className: 'bg-gray-100 text-gray-700' },
    not_found:        { label: 'No está en el sistema',    className: 'bg-red-100 text-red-800' },
};
const ACTIONABLE = ['issue', 'enroll_and_issue'];

function UploadForm({ course, types }) {
    const form = useForm({ file: null, type: 'aprobado', min_grade: '' });

    const submit = (e) => {
        e.preventDefault();
        form.post(route('admin.courses.moodle.preview', course.id), { forceFormData: true });
    };

    return (
        <div className="mx-auto max-w-2xl space-y-6">
            <div className="rounded-xl border border-blue-100 bg-blue-50 p-4 text-sm text-blue-900">
                <p className="font-semibold">Cómo obtener el archivo en Moodle</p>
                <ol className="mt-2 list-decimal space-y-1 pl-5">
                    <li>Entrá al curso → <strong>Calificaciones</strong> → <strong>Exportar</strong>.</li>
                    <li>Elegí <strong>Hoja de cálculo Excel</strong> o <strong>Archivo de texto plano</strong>.</li>
                    <li>Si querés filtrar por nota, incluí el <strong>Total del curso</strong>.</li>
                    <li>Si querés subir solo a los aprobados, filtralos en el Excel antes de subirlo y dejá la nota mínima vacía.</li>
                </ol>
            </div>

            <form onSubmit={submit} className="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div>
                    <label htmlFor="file" className="block text-sm font-medium text-gray-700">Archivo de calificaciones (.xlsx, .xls o .csv)</label>
                    <input id="file" type="file" accept=".xlsx,.xls,.csv" required
                           onChange={(e) => form.setData('file', e.target.files[0] ?? null)}
                           className="mt-1 block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-hospitalblue/10 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hospitalblue hover:file:bg-hospitalblue/20" />
                    {form.errors.file && <p className="mt-1 text-xs text-red-600">{form.errors.file}</p>}
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    <Field as="select" id="type" label="Tipo de certificado"
                           value={form.data.type} onChange={(e) => form.setData('type', e.target.value)}
                           error={form.errors.type}>
                        {types.map((t) => <option key={t} value={t}>{CERTIFICATE_TYPES[t]?.label ?? t}</option>)}
                    </Field>
                    <Field id="min_grade" type="number" step="0.01" min="0" label="Nota mínima para aprobar (opcional)"
                           placeholder="Ej: 60" hint="Vacío = se consideran aprobados todos los del archivo."
                           value={form.data.min_grade} onChange={(e) => form.setData('min_grade', e.target.value)}
                           error={form.errors.min_grade} />
                </div>

                <div className="flex items-center justify-end gap-2">
                    <Link href={route('admin.courses.users', course.id)}>
                        <Button type="button" variant="secondary">Volver</Button>
                    </Link>
                    <Button disabled={form.processing}>{form.processing ? 'Leyendo archivo…' : 'Ver vista previa'}</Button>
                </div>
            </form>
        </div>
    );
}

function Preview({ course, preview }) {
    const actionable = useMemo(() => preview.rows.filter((r) => ACTIONABLE.includes(r.status)), [preview]);
    const [selected, setSelected] = useState(() => new Set(actionable.map((r) => r.user_id)));
    const [sending, setSending] = useState(false);

    const counts = useMemo(() => preview.rows.reduce((acc, r) => ({ ...acc, [r.status]: (acc[r.status] ?? 0) + 1 }), {}), [preview]);
    const enrollCount = actionable.filter((r) => r.status === 'enroll_and_issue' && selected.has(r.user_id)).length;

    const toggle = (id) => setSelected((prev) => {
        const next = new Set(prev);
        next.has(id) ? next.delete(id) : next.add(id);
        return next;
    });

    const confirmIssue = () => {
        const msg = `Se van a emitir ${selected.size} certificados de tipo "${CERTIFICATE_TYPES[preview.type]?.label}"`
            + (enrollCount ? ` y se van a inscribir ${enrollCount} alumnos al curso` : '') + '. ¿Continuar?';
        if (!confirm(msg)) return;

        router.post(route('admin.courses.moodle.confirm', course.id), { user_ids: [...selected] }, {
            onStart: () => setSending(true),
            onFinish: () => setSending(false),
        });
    };

    const cancel = () => router.delete(route('admin.courses.moodle.cancel', course.id));

    return (
        <div className="space-y-6">
            <div className="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-4 text-sm">
                <p className="text-gray-700">
                    <strong>{preview.file}</strong> · Tipo: <strong>{CERTIFICATE_TYPES[preview.type]?.label}</strong>
                    {preview.min_grade !== null && <> · Nota mínima: <strong>{preview.min_grade}</strong></>}
                </p>
                <Button type="button" variant="ghost" onClick={cancel}>Subir otro archivo</Button>
            </div>

            {preview.min_grade !== null && !preview.has_grade && (
                <div className="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    El archivo no tiene la columna "Total del curso", así que nadie alcanza la nota mínima.
                    Exportalo de nuevo incluyendo el total, o subilo sin nota mínima.
                </div>
            )}

            <div className="flex flex-wrap gap-2">
                {Object.entries(STATUS).filter(([key]) => counts[key]).map(([key, s]) => (
                    <span key={key} className={`rounded-full px-3 py-1 text-xs font-semibold ${s.className}`}>
                        {s.label}: {counts[key]}
                    </span>
                ))}
            </div>

            <div className="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm">
                <table className="w-full text-sm">
                    <thead>
                        <tr className="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-hospitalgray">
                            <th className="w-12 px-4 py-2.5"><span className="sr-only">Emitir</span></th>
                            <th className="px-4 py-2.5">Fila</th>
                            <th className="px-4 py-2.5">Alumno</th>
                            <th className="px-4 py-2.5">DNI</th>
                            <th className="px-4 py-2.5 text-right">Nota</th>
                            <th className="px-4 py-2.5">Resultado</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {preview.rows.map((r) => {
                            const canIssue = ACTIONABLE.includes(r.status);
                            return (
                                <tr key={r.row} className={canIssue ? '' : 'text-gray-500'}>
                                    <td className="px-4 py-2.5">
                                        {canIssue && (
                                            <input type="checkbox" checked={selected.has(r.user_id)} onChange={() => toggle(r.user_id)}
                                                   aria-label={`Emitir a ${r.name}`}
                                                   className="rounded border-gray-300 text-hospitalblue focus:ring-hospitalblue" />
                                        )}
                                    </td>
                                    <td className="px-4 py-2.5 tabular-nums">{r.row}</td>
                                    <td className="px-4 py-2.5">
                                        <p className={canIssue ? 'font-medium text-gray-900' : ''}>{r.name}</p>
                                        {r.email && <p className="text-xs text-hospitalgray">{r.email}</p>}
                                    </td>
                                    <td className="px-4 py-2.5 tabular-nums">{r.dni ?? '—'}</td>
                                    <td className="px-4 py-2.5 text-right tabular-nums">{r.grade ?? '—'}</td>
                                    <td className="px-4 py-2.5">
                                        <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${STATUS[r.status].className}`}>
                                            {STATUS[r.status].label}
                                        </span>
                                    </td>
                                </tr>
                            );
                        })}
                    </tbody>
                </table>
            </div>

            {counts.not_found > 0 && (
                <p className="text-xs text-hospitalgray">
                    Los alumnos "No está en el sistema" no tienen cuenta con ese DNI ni email. Podés crearlos desde Usuarios
                    (o con la importación de usuarios) y volver a subir el archivo.
                </p>
            )}

            <div className="flex items-center justify-end gap-2">
                <Button type="button" variant="secondary" onClick={cancel}>Cancelar</Button>
                <Button type="button" onClick={confirmIssue} disabled={sending || selected.size === 0}>
                    {sending ? 'Emitiendo…' : `Emitir ${selected.size} certificados`}
                </Button>
            </div>
        </div>
    );
}

export default function MoodleImport({ course, types, preview }) {
    return (
        <AppLayout title={`Importar aprobados de Moodle — ${course.title}`}>
            {preview ? <Preview course={course} preview={preview} /> : <UploadForm course={course} types={types} />}
        </AppLayout>
    );
}
