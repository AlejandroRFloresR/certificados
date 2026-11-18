import { useMemo, useState } from 'react';
import { router, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import TextField from '@/Components/Form/TextField';
import FileDrop from '@/Components/Form/FileDrop';
import RadioCards from '@/Components/Form/RadioCards';
import Checkbox from '@/Components/Form/Checkbox';
import { focusFirstError } from '@/Components/Form/useUnsavedChanges';
import Button from '@/Components/Button';
import { CERTIFICATE_TYPES } from '@/Components/TypeBadge';
import CourseHeader from '@/Components/CourseHeader';
import { confirmDialog } from '@/Components/ConfirmDialog';

const STATUS = {
    issue:            { label: 'Se emite',                 className: 'bg-green-100 text-green-800' },
    enroll_and_issue: { label: 'Se inscribe y se emite',   className: 'bg-blue-100 text-blue-800' },
    already_issued:   { label: 'Ya tiene certificado',     className: 'bg-gray-100 text-gray-700' },
    type_conflict:    { label: 'Tiene otro tipo',          className: 'bg-amber-100 text-amber-800' },
    below_grade:      { label: 'No alcanza la nota',       className: 'bg-gray-100 text-gray-700' },
    not_found:        { label: 'No está en el sistema',    className: 'bg-red-100 text-red-800' },
};
const ACTIONABLE = ['issue', 'enroll_and_issue'];

const TYPE_DESCRIPTIONS = {
    aprobado: 'Cumplió la evaluación',
    asistio:  'Sin evaluación',
    dicto:    'Docente del curso',
};

function StepCard({ number, title, children }) {
    return (
        <section aria-labelledby={`paso-${number}`} className="flex flex-col gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
            <div className="flex items-center gap-3">
                <span aria-hidden="true" className="flex h-8 w-8 items-center justify-center rounded-full bg-hospitalblue/10 text-sm font-bold text-hospitalblue">
                    {number}
                </span>
                <h2 id={`paso-${number}`} className="text-base font-bold text-gray-900">{title}</h2>
            </div>
            {children}
        </section>
    );
}

/** Paso 1: pide el nombre corto de Moodle y descarga el CSV (el valor se recuerda para la próxima vez) */
function ExportForm({ course, exportable }) {
    const { errors } = usePage().props;
    const [shortname, setShortname] = useState(course.moodle_shortname ?? '');

    const download = (e) => {
        e.preventDefault();
        // Descarga de archivo: navegación normal del navegador, no Inertia
        window.location.href = route('admin.courses.moodle.export', { course: course.id, shortname: shortname.trim() });
    };

    if (exportable === 0) {
        return <p className="mt-auto text-sm text-hospitalgray">No hay alumnos para exportar todavía.</p>;
    }

    return (
        <form onSubmit={download} noValidate className="mt-auto space-y-4">
            <TextField id="shortname" label="Nombre corto del curso en Moodle" required maxLength={100}
                       placeholder="Ej: RCP-2026"
                       hint="Copialo de la configuración del curso en Moodle. Con él, Moodle inscribe a los alumnos en ese curso."
                       value={shortname} onChange={setShortname} error={errors.shortname} />
            <Button disabled={!shortname.trim()} className="min-h-[44px] gap-2">
                <svg aria-hidden="true" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M12 4v11m0 0l-4-4m4 4l4-4M5 19h14" />
                </svg>
                Descargar CSV para Moodle
            </Button>
        </form>
    );
}

function Steps({ course, exportable, types }) {
    const form = useForm({ file: null, type: types.includes('aprobado') ? 'aprobado' : types[0], min_grade: '' });
    const skipped = course.students - exportable;

    const submit = (e) => {
        e.preventDefault();
        form.post(route('admin.courses.moodle.preview', course.id), {
            forceFormData: true,
            preserveScroll: true,
            onError: focusFirstError,
        });
    };

    return (
        <div className="space-y-5">
            <div className="grid gap-5 lg:grid-cols-2">
                <StepCard number={1} title="Subir los alumnos a Moodle">
                    <p className="text-sm leading-relaxed text-gray-700">
                        Descargá el CSV con {exportable === 1 ? 'el alumno inscripto' : `los ${exportable} alumnos inscriptos`} y cargalo en Moodle desde{' '}
                        <strong>Administración del sitio › Usuarios › Subir usuarios</strong>. El DNI queda como usuario y como "Número de ID".
                    </p>
                    {skipped > 0 && (
                        <p className="text-sm text-hospitalgray">
                            {skipped === 1 ? '1 alumno sin email no se incluye.' : `${skipped} alumnos sin email no se incluyen.`}
                        </p>
                    )}
                    <ExportForm course={course} exportable={exportable} />
                </StepCard>

                <StepCard number={2} title="Emitir certificados a los aprobados">
                    <p className="text-sm leading-relaxed text-gray-700">
                        Al terminar el curso, exportá las calificaciones desde Moodle (<strong>Calificaciones › Exportar</strong>, en Excel o texto plano)
                        y subí el archivo. Vas a ver una vista previa antes de emitir.
                    </p>
                    <form onSubmit={submit} noValidate className="space-y-5">
                        <FileDrop id="file" label="Archivo de calificaciones" required file={form.data.file}
                                  onChange={(f) => form.setData('file', f)}
                                  accept=".xlsx,.xls,.csv" acceptLabel=".xlsx, .xls o .csv · hasta 10 MB"
                                  progress={form.progress} error={form.errors.file} />
                        <RadioCards id="type" label="Tipo de certificado" required
                                    options={types.map((t) => ({ value: t, label: CERTIFICATE_TYPES[t]?.label ?? t, description: TYPE_DESCRIPTIONS[t] }))}
                                    value={form.data.type} onChange={(v) => form.setData('type', v)} error={form.errors.type} />
                        <TextField id="min_grade" type="number" step="0.01" min="0" label="Nota mínima" className="sm:w-1/2"
                                   placeholder="Ej: 60" hint="Vacía: todos los del archivo. Se compara con el total del curso."
                                   value={form.data.min_grade} onChange={(v) => form.setData('min_grade', v)}
                                   error={form.errors.min_grade} />
                        <Button disabled={form.processing || !form.data.file} className="min-h-[44px]">
                            {form.processing ? 'Leyendo archivo…' : 'Ver vista previa'}
                        </Button>
                    </form>
                </StepCard>
            </div>
        </div>
    );
}

function Preview({ course, preview }) {
    const actionable = useMemo(() => preview.rows.filter((r) => ACTIONABLE.includes(r.status)), [preview]);
    const [selected, setSelected] = useState(() => new Set(actionable.map((r) => r.user_id)));
    const [sending, setSending] = useState(false);
    const [notify, setNotify] = useState(true);

    const counts = useMemo(() => preview.rows.reduce((acc, r) => ({ ...acc, [r.status]: (acc[r.status] ?? 0) + 1 }), {}), [preview]);
    const enrollCount = actionable.filter((r) => r.status === 'enroll_and_issue' && selected.has(r.user_id)).length;

    const toggle = (id) => setSelected((prev) => {
        const next = new Set(prev);
        next.has(id) ? next.delete(id) : next.add(id);
        return next;
    });

    const confirmIssue = async () => {
        const one = selected.size === 1;
        const ok = await confirmDialog({
            title: `¿Emitir ${selected.size} ${one ? 'certificado' : 'certificados'}?`,
            message: [
                `Tipo: ${CERTIFICATE_TYPES[preview.type]?.label}.`
                    + (enrollCount ? ` Además se ${enrollCount === 1 ? 'inscribe 1 alumno' : `inscriben ${enrollCount} alumnos`} al curso.` : ''),
                notify ? 'Cada certificado se envía por correo.' : 'No se envían por correo.',
            ],
            confirmLabel: 'Emitir',
        });
        if (!ok) return;

        router.post(route('admin.courses.moodle.confirm', course.id), { user_ids: [...selected], notify }, {
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
                                                   className="h-[18px] w-[18px] rounded border-2 border-hospitalgray text-hospitalblue focus:ring-hospitalblue" />
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

            <div className="flex flex-wrap items-center justify-end gap-2">
                <Checkbox id="moodle-notify" label="Enviar cada certificado por correo" checked={notify} onChange={setNotify} />
                <span className="flex-1" />
                <Button type="button" variant="secondary" onClick={cancel}>Cancelar</Button>
                <Button type="button" onClick={confirmIssue} disabled={sending || selected.size === 0}>
                    {sending ? 'Emitiendo…' : `Emitir ${selected.size} certificados`}
                </Button>
            </div>
        </div>
    );
}

export default function MoodleImport({ course, exportable, types, preview }) {
    return (
        <AppLayout>
            <CourseHeader course={course} tab="moodle" />
            <div className="mt-6">
                {preview
                    ? <Preview course={course} preview={preview} />
                    : <Steps course={course} exportable={exportable} types={types} />}
            </div>
        </AppLayout>
    );
}
