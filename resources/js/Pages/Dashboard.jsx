import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import TypeBadge from '@/Components/TypeBadge';
import DownloadLink from '@/Components/DownloadLink';
import { StatusBadge } from '@/Components/Catalog/CourseCard';
import { formatRange } from '@/Components/Catalog/utils';

function Section({ title, count, children }) {
    return (
        <section className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
            <div className="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3">
                <h2 className="text-sm font-semibold text-gray-900">{title}</h2>
                {count > 0 && <span className="text-xs text-hospitalgray">{count} {count === 1 ? 'curso' : 'cursos'}</span>}
            </div>
            {children}
        </section>
    );
}

function Empty({ children }) {
    return <div className="px-5 py-12 text-center text-sm text-hospitalgray">{children}</div>;
}

function CourseMeta({ course }) {
    return (
        <p className="mt-0.5 text-xs tabular-nums text-hospitalgray">
            {formatRange(course.start_date, course.end_date)}
            {course.hours && ` · ${course.hours} h`}
        </p>
    );
}

function CertificateStatus({ certificate }) {
    if (!certificate) return <span className="text-xs text-gray-400">Aún no emitido</span>;
    return (
        <div className="flex flex-wrap items-center gap-1.5">
            <span className="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800">Emitido</span>
            <TypeBadge type={certificate.type} />
        </div>
    );
}

export default function Dashboard({ studentCourses, tutorCourses, isTutor }) {
    return (
        <AppLayout title="Mis cursos">
            <div className="space-y-8">

                {/* Como alumno */}
                <Section title="Cursos en los que estoy inscripto" count={studentCourses.length}>
                    {studentCourses.length === 0 ? (
                        <Empty>
                            Todavía no estás inscripto en ningún curso.
                            <Link href={route('catalog.index')} className="mt-3 block font-semibold text-hospitalblue hover:underline">
                                Ver cursos disponibles →
                            </Link>
                        </Empty>
                    ) : (
                        <>
                            {/* Escritorio: tabla */}
                            <table className="hidden w-full text-sm md:table">
                                <thead>
                                    <tr className="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-hospitalgray">
                                        <th className="px-5 py-2.5">Curso</th>
                                        <th className="px-5 py-2.5">Estado</th>
                                        <th className="px-5 py-2.5">Certificado</th>
                                        <th className="px-5 py-2.5 text-right">Acción</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {studentCourses.map((c) => (
                                        <tr key={c.id}>
                                            <td className="px-5 py-3">
                                                <p className="font-medium text-gray-900">{c.title}</p>
                                                <CourseMeta course={c} />
                                            </td>
                                            <td className="px-5 py-3"><StatusBadge status={c.status} /></td>
                                            <td className="px-5 py-3"><CertificateStatus certificate={c.certificate} /></td>
                                            <td className="px-5 py-3 text-right">
                                                {c.certificate
                                                    ? <DownloadLink href={c.certificate.download_url} />
                                                    : <span className="text-xs text-gray-400">—</span>}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>

                            {/* Móvil: tarjetas */}
                            <div className="divide-y divide-gray-100 md:hidden">
                                {studentCourses.map((c) => (
                                    <div key={c.id} className="p-4">
                                        <div className="flex items-start justify-between gap-2">
                                            <p className="text-sm font-medium text-gray-900">{c.title}</p>
                                            <StatusBadge status={c.status} />
                                        </div>
                                        <CourseMeta course={c} />
                                        <div className="mt-3 flex items-center justify-between gap-2">
                                            <CertificateStatus certificate={c.certificate} />
                                            {c.certificate && <DownloadLink href={c.certificate.download_url} />}
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </>
                    )}
                </Section>

                {/* Como tutor */}
                {isTutor && (
                    <Section title="Mis cursos como tutor" count={tutorCourses.length}>
                        {tutorCourses.length === 0 ? (
                            <Empty>No tenés cursos asignados como tutor por el momento.</Empty>
                        ) : (
                            <ul className="divide-y divide-gray-100">
                                {tutorCourses.map((c) => (
                                    <li key={c.id} className="flex items-center justify-between gap-4 px-5 py-3">
                                        <div className="min-w-0">
                                            <p className="text-sm font-medium text-gray-900">{c.title}</p>
                                            <CourseMeta course={c} />
                                        </div>
                                        <div className="flex shrink-0 items-center gap-3">
                                            <span className="text-xs tabular-nums text-hospitalgray">
                                                {c.students} {c.students === 1 ? 'alumno' : 'alumnos'}
                                            </span>
                                            <StatusBadge status={c.status} />
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                    </Section>
                )}
            </div>
        </AppLayout>
    );
}
