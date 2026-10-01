import { useMemo, useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/Button';
import TypeBadge, { CERTIFICATE_TYPES } from '@/Components/TypeBadge';
import DownloadLink from '@/Components/DownloadLink';
import { normalize } from '@/Components/Catalog/utils';

function EmitForm({ student, course, types }) {
    const [type, setType] = useState(types[0]);
    const [sending, setSending] = useState(false);

    const emit = () => {
        router.post(route('certificates.emit'), {
            user_id: student.id,
            course_id: course.id,
            type,
        }, {
            preserveScroll: true,
            onStart: () => setSending(true),
            onFinish: () => setSending(false),
        });
    };

    return (
        <div className="flex items-center justify-end gap-2">
            <select value={type} onChange={(e) => setType(e.target.value)} aria-label="Tipo de certificado"
                    className="rounded-lg border-gray-300 py-1.5 pl-2 pr-8 text-sm focus:border-hospitalblue focus:ring-hospitalblue">
                {types.map((t) => <option key={t} value={t}>{CERTIFICATE_TYPES[t]?.label ?? t}</option>)}
            </select>
            <Button type="button" onClick={emit} disabled={sending} className="!px-3 !py-1.5">
                {sending ? 'Emitiendo…' : 'Emitir'}
            </Button>
        </div>
    );
}

function CertificateCell({ student, course, types }) {
    if (student.certificate) {
        return (
            <div className="flex items-center justify-end gap-2">
                <TypeBadge type={student.certificate.type} />
                <DownloadLink href={student.certificate.download_url} />
            </div>
        );
    }
    return <EmitForm student={student} course={course} types={types} />;
}

export default function Students({ course, students, types }) {
    const [query, setQuery] = useState('');

    const visible = useMemo(() => {
        const q = normalize(query.trim());
        if (!q) return students;
        return students.filter((s) => normalize([s.name, s.email, s.dni].join(' ')).includes(q));
    }, [students, query]);

    const issued = students.filter((s) => s.certificate).length;

    return (
        <AppLayout
            title={course.title}
            actions={
                <div className="flex flex-wrap gap-2">
                    {/* Archivo: <a>, no <Link> */}
                    <a href={route('admin.courses.users.export', course.id)}>
                        <Button type="button" variant="secondary">Exportar Excel</Button>
                    </a>
                    <Link href={route('admin.courses.users.edit', course.id)}>
                        <Button type="button" variant="secondary">Asignar alumnos</Button>
                    </Link>
                    <Link href={route('admin.courses.tutors.edit', course.id)}>
                        <Button type="button" variant="secondary">Asignar tutores</Button>
                    </Link>
                </div>
            }
        >
            <Link href={route('courses.index')} className="mb-4 inline-block text-sm font-semibold text-hospitalblue hover:underline">
                ← Volver al listado de cursos
            </Link>

            {/* Resumen */}
            <div className="mb-6 grid gap-3 sm:grid-cols-3">
                <div className="rounded-xl border border-gray-200 bg-white p-4">
                    <p className="text-xs font-semibold uppercase tracking-wide text-hospitalgray">Alumnos</p>
                    <p className="mt-1 text-2xl font-semibold tabular-nums">{students.length}</p>
                </div>
                <div className="rounded-xl border border-gray-200 bg-white p-4">
                    <p className="text-xs font-semibold uppercase tracking-wide text-hospitalgray">Certificados emitidos</p>
                    <p className="mt-1 text-2xl font-semibold tabular-nums">{issued} <span className="text-sm font-normal text-hospitalgray">de {students.length}</span></p>
                </div>
                <div className="rounded-xl border border-gray-200 bg-white p-4">
                    <p className="text-xs font-semibold uppercase tracking-wide text-hospitalgray">Tutores</p>
                    <p className="mt-1 text-sm text-gray-900">{course.tutors.length ? course.tutors.join(', ') : <span className="text-gray-400">Sin tutores</span>}</p>
                </div>
            </div>

            <div className="mb-4">
                <input type="search" value={query} onChange={(e) => setQuery(e.target.value)}
                       placeholder="Buscar por nombre, email o DNI" aria-label="Buscar alumnos"
                       className="w-full max-w-sm rounded-lg border-gray-300 text-sm focus:border-hospitalblue focus:ring-hospitalblue" />
            </div>

            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                {visible.length === 0 ? (
                    <p className="px-5 py-12 text-center text-sm text-hospitalgray">
                        {students.length === 0 ? 'Aún no hay alumnos inscriptos en este curso.' : 'Ningún alumno coincide con la búsqueda.'}
                    </p>
                ) : (
                    <>
                        <table className="hidden w-full text-sm md:table">
                            <thead>
                                <tr className="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-hospitalgray">
                                    <th className="px-5 py-2.5">Alumno</th>
                                    <th className="px-5 py-2.5">DNI</th>
                                    <th className="px-5 py-2.5">Inscripción</th>
                                    <th className="px-5 py-2.5 text-right">Certificado</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {visible.map((s) => (
                                    <tr key={s.id}>
                                        <td className="px-5 py-3">
                                            <p className="font-medium text-gray-900">{s.name}</p>
                                            <p className="text-xs text-hospitalgray">{s.email}</p>
                                        </td>
                                        <td className="px-5 py-3 tabular-nums text-gray-600">{s.dni ?? '—'}</td>
                                        <td className="px-5 py-3 tabular-nums text-gray-600">{s.enrolled_at ?? '—'}</td>
                                        <td className="px-5 py-3"><CertificateCell student={s} course={course} types={types} /></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        <div className="divide-y divide-gray-100 md:hidden">
                            {visible.map((s) => (
                                <div key={s.id} className="p-4">
                                    <p className="text-sm font-medium text-gray-900">{s.name}</p>
                                    <p className="text-xs text-hospitalgray">{s.email} · DNI {s.dni ?? '—'}</p>
                                    <div className="mt-3"><CertificateCell student={s} course={course} types={types} /></div>
                                </div>
                            ))}
                        </div>
                    </>
                )}
            </div>
        </AppLayout>
    );
}
