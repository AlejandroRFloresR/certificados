import { useMemo, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/Button';
import CourseHeader from '@/Components/CourseHeader';
import TypeBadge, { CERTIFICATE_TYPES } from '@/Components/TypeBadge';
import DownloadLink from '@/Components/DownloadLink';
import MoreMenu from '@/Components/MoreMenu';
import { confirmDialog } from '@/Components/ConfirmDialog';
import { normalize } from '@/Components/Catalog/utils';

function Stat({ label, value, of, highlight = false }) {
    return (
        <div className={`rounded-xl border p-4 ${highlight ? 'border-amber-200 bg-amber-50' : 'border-gray-200 bg-white'}`}>
            <p className={`text-xs font-bold uppercase tracking-wide ${highlight ? 'text-amber-800' : 'text-hospitalgray'}`}>{label}</p>
            <p className={`mt-1 text-2xl font-bold tabular-nums ${highlight ? 'text-amber-950' : 'text-gray-900'}`}>
                {value}
                {of !== undefined && <span className="ml-1 text-sm font-medium text-hospitalgray">de {of}</span>}
            </p>
        </div>
    );
}

/** Estado del correo y de la encuesta, debajo del certificado */
function MailStatus({ certificate: c }) {
    const mail = c.email_error
        ? <span className="font-semibold text-red-700" title={c.email_error}>No se pudo enviar el correo</span>
        : c.emailed_at
            ? <span>Enviado por correo el {c.emailed_at}</span>
            : <span>Sin enviar por correo</span>;
    const survey = { answered: 'encuesta respondida', pending: 'encuesta sin responder' }[c.survey];

    return (
        <p className="mt-1 text-xs text-hospitalgray">
            {mail}{survey && <> · {survey}</>}
        </p>
    );
}

function CertificateCell({ student }) {
    const c = student.certificate;
    if (!c) return <span className="text-sm text-hospitalgray">Pendiente</span>;

    const resend = async () => {
        const ok = await confirmDialog({
            title: '¿Enviar el certificado por correo?',
            message: `Le llega a ${student.email}, con el PDF adjunto${c.survey === 'pending' ? ' y el link a la encuesta' : ''}.`,
            confirmLabel: 'Enviar correo',
        });
        if (!ok) return;
        router.post(route('admin.certificates.email', c.id), {}, { preserveScroll: true });
    };

    return (
        <div className="flex items-center justify-end gap-1">
            <div className="text-right">
                <div className="flex items-center justify-end gap-2">
                    <TypeBadge type={c.type} />
                    <DownloadLink href={c.download_url} />
                </div>
                <MailStatus certificate={c} />
            </div>
            <MoreMenu label={`Más acciones para el certificado de ${student.name}`} items={[
                { label: c.emailed_at ? 'Reenviar por correo' : 'Enviar por correo', onClick: resend },
                { label: 'Ver el correo', href: route('admin.certificates.email.preview', c.id), download: true },
            ]} />
        </div>
    );
}

export default function Students({ course, students, types }) {
    const { errors } = usePage().props;
    const [query, setQuery] = useState('');
    const [selected, setSelected] = useState(() => new Set());
    const [type, setType] = useState(types.includes('aprobado') ? 'aprobado' : types[0]);
    const [sending, setSending] = useState(false);
    const [notify, setNotify] = useState(true);

    const pending = useMemo(() => students.filter((s) => !s.certificate), [students]);
    const issued = students.length - pending.length;

    const visible = useMemo(() => {
        const q = normalize(query.trim());
        return q ? students.filter((s) => normalize([s.name, s.email, s.dni].join(' ')).includes(q)) : students;
    }, [students, query]);

    const visiblePending = visible.filter((s) => !s.certificate);
    const allVisibleSelected = visiblePending.length > 0 && visiblePending.every((s) => selected.has(s.id));

    const toggle = (id) => setSelected((prev) => {
        const next = new Set(prev);
        next.has(id) ? next.delete(id) : next.add(id);
        return next;
    });

    const toggleAllVisible = () => setSelected((prev) => {
        const next = new Set(prev);
        visiblePending.forEach((s) => (allVisibleSelected ? next.delete(s.id) : next.add(s.id)));
        return next;
    });

    const issue = async () => {
        const label = CERTIFICATE_TYPES[type]?.label ?? type;
        const one = selected.size === 1;
        const ok = await confirmDialog({
            title: `¿Emitir ${selected.size} ${one ? 'certificado' : 'certificados'}?`,
            message: [
                `Tipo: ${label}.`,
                notify
                    ? `${one ? 'Se envía' : 'Se envían'} por correo a ${one ? 'el alumno' : 'cada alumno'}.`
                    : 'No se envían por correo: se pueden enviar después desde el menú ⋯ de cada alumno.',
            ],
            confirmLabel: 'Emitir',
        });
        if (!ok) return;

        router.post(route('admin.courses.certificates.issue', course.id), { user_ids: [...selected], type, notify }, {
            preserveScroll: true,
            onStart: () => setSending(true),
            onFinish: () => setSending(false),
            onSuccess: () => setSelected(new Set()),
        });
    };

    return (
        <AppLayout>
            <CourseHeader course={course} tab="alumnos" />

            <div className="mt-6 grid gap-3 sm:grid-cols-3">
                <Stat label="Alumnos" value={students.length} />
                <Stat label="Certificados emitidos" value={issued} of={students.length} />
                <Stat label="Pendientes de emitir" value={pending.length} highlight={pending.length > 0} />
            </div>

            <div className="mt-6 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div className="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 p-4">
                    <div className="flex flex-wrap items-center gap-2">
                        <input type="search" value={query} onChange={(e) => setQuery(e.target.value)}
                               placeholder="Buscar por nombre, email o DNI" aria-label="Buscar alumnos"
                               className="min-h-[44px] w-72 max-w-full rounded-lg border-gray-300 text-sm focus:border-hospitalblue focus:ring-hospitalblue" />
                        {pending.length > 0 && (
                            <button type="button" onClick={() => setSelected(new Set(pending.map((s) => s.id)))}
                                    className="min-h-[44px] px-2 text-sm font-semibold text-hospitalblue hover:underline">
                                Seleccionar pendientes ({pending.length})
                            </button>
                        )}
                    </div>
                    <Link href={route('admin.courses.users.edit', course.id)}>
                        <Button type="button" className="min-h-[44px]">+ Agregar alumnos</Button>
                    </Link>
                </div>

                {errors.user_ids && <p className="border-b border-red-100 bg-red-50 px-4 py-2.5 text-sm text-red-800">{errors.user_ids}</p>}

                {visible.length === 0 ? (
                    <p className="px-5 py-12 text-center text-sm text-hospitalgray">
                        {students.length === 0 ? 'Aún no hay alumnos inscriptos. Usá "Agregar alumnos" o la pestaña Moodle.' : 'Ningún alumno coincide con la búsqueda.'}
                    </p>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-gray-50 text-left text-xs font-bold uppercase tracking-wide text-hospitalgray">
                                    <th scope="col" className="w-14 px-4 py-2.5">
                                        {visiblePending.length > 0 && (
                                            <input type="checkbox" checked={allVisibleSelected} onChange={toggleAllVisible}
                                                   aria-label="Seleccionar todos los pendientes visibles"
                                                   className="h-[18px] w-[18px] rounded border-2 border-hospitalgray text-hospitalblue focus:ring-hospitalblue" />
                                        )}
                                    </th>
                                    <th scope="col" className="px-3 py-2.5">Alumno</th>
                                    <th scope="col" className="hidden px-3 py-2.5 md:table-cell">DNI</th>
                                    <th scope="col" className="hidden px-3 py-2.5 md:table-cell">Inscripción</th>
                                    <th scope="col" className="px-4 py-2.5 text-right">Certificado</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {visible.map((s) => {
                                    const checked = selected.has(s.id);
                                    return (
                                        <tr key={s.id} className={checked ? 'bg-hospitalblue/5' : ''}>
                                            <td className="px-4 py-3">
                                                {!s.certificate && (
                                                    <input type="checkbox" checked={checked} onChange={() => toggle(s.id)}
                                                           aria-label={`Seleccionar a ${s.name}`}
                                                           className="h-[18px] w-[18px] rounded border-2 border-hospitalgray text-hospitalblue focus:ring-hospitalblue" />
                                                )}
                                            </td>
                                            <td className="px-3 py-3">
                                                <p className="font-semibold text-gray-900">{s.name}</p>
                                                <p className="text-xs text-hospitalgray">{s.email}<span className="md:hidden"> · DNI {s.dni ?? '—'}</span></p>
                                            </td>
                                            <td className="hidden px-3 py-3 tabular-nums text-gray-600 md:table-cell">{s.dni ?? '—'}</td>
                                            <td className="hidden px-3 py-3 tabular-nums text-gray-600 md:table-cell">{s.enrolled_at ?? '—'}</td>
                                            <td className="px-4 py-3 text-right"><CertificateCell student={s} /></td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {/* Barra de emisión: aparece al seleccionar alumnos y queda pegada abajo al hacer scroll */}
            {selected.size > 0 && (
                <div className="sticky bottom-4 z-10 mt-4 flex flex-wrap items-center justify-between gap-3 rounded-xl bg-hospitalblue-dark px-4 py-3 text-white shadow-lg">
                    <span className="text-sm font-semibold">
                        {selected.size} {selected.size === 1 ? 'alumno seleccionado' : 'alumnos seleccionados'}
                    </span>
                    <div className="flex flex-wrap items-center gap-2">
                        <label htmlFor="bulk-type" className="text-sm text-white/85">Tipo de certificado</label>
                        <select id="bulk-type" value={type} onChange={(e) => setType(e.target.value)}
                                className="min-h-[44px] rounded-lg border-0 py-2 pl-3 pr-8 text-sm text-gray-900 focus:ring-2 focus:ring-hospitalbrown">
                            {types.map((t) => <option key={t} value={t}>{CERTIFICATE_TYPES[t]?.label ?? t}</option>)}
                        </select>
                        <label className="inline-flex min-h-[44px] cursor-pointer items-center gap-2 px-1 text-sm text-white/90">
                            <input type="checkbox" checked={notify} onChange={(e) => setNotify(e.target.checked)}
                                   className="h-[18px] w-[18px] rounded border-white/50 text-hospitalbrown focus:ring-2 focus:ring-hospitalbrown" />
                            Enviar por correo
                        </label>
                        <button type="button" onClick={issue} disabled={sending}
                                className="min-h-[44px] rounded-lg bg-hospitalbrown px-4 text-sm font-bold text-gray-900 hover:brightness-95 disabled:opacity-60">
                            {sending ? 'Emitiendo…' : `Emitir ${selected.size} ${selected.size === 1 ? 'certificado' : 'certificados'}`}
                        </button>
                        <button type="button" onClick={() => setSelected(new Set())}
                                className="min-h-[44px] px-2 text-sm font-semibold text-white/90 hover:text-white">
                            Cancelar
                        </button>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
