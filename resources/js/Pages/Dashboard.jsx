import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import TypeBadge from '@/Components/TypeBadge';
import DownloadLink from '@/Components/DownloadLink';
import { StatusBadge } from '@/Components/Catalog/CourseCard';
import { formatRange } from '@/Components/Catalog/utils';

function SectionTitle({ id, title, aside }) {
    return (
        <div className="flex flex-wrap items-baseline justify-between gap-3">
            <h2 id={id} className="text-xs font-bold uppercase tracking-widest text-hospitalgray">{title}</h2>
            {aside}
        </div>
    );
}

function MyCourse({ course, modalities }) {
    const modality = modalities[course.modality];
    const meta = [formatRange(course.start_date, course.end_date), modality]
        .filter(Boolean)
        .join(' · ');

    return (
        <li className="flex flex-wrap items-center justify-between gap-4 px-5 py-4">
            <div className="min-w-0">
                <div className="flex flex-wrap items-center gap-2">
                    <p className="font-semibold text-gray-900">{course.title}</p>
                    <StatusBadge status={course.status} />
                </div>
                <p className="mt-1 text-sm tabular-nums text-hospitalgray">{meta}</p>
            </div>
            {course.certificate ? (
                <div className="flex items-center gap-3">
                    <TypeBadge type={course.certificate.type} />
                    <DownloadLink href={course.certificate.download_url}>Descargar certificado</DownloadLink>
                </div>
            ) : (
                <span className="text-sm text-hospitalgray">
                    {course.status === 'finalizado' ? 'Certificado aún no emitido' : 'Certificado: se emite al finalizar'}
                </span>
            )}
        </li>
    );
}

function AvailableCourse({ course, modalities }) {
    const [sending, setSending] = useState(false);
    const modality = modalities[course.modality];

    const enroll = () => {
        router.post(route('courses.enroll', course.id), {}, {
            preserveScroll: true,
            onStart: () => setSending(true),
            onFinish: () => setSending(false),
        });
    };

    return (
        <article className="flex flex-col gap-3 rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
            <div className="flex items-center justify-between gap-2">
                <span className="inline-flex min-h-[1.25rem] items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-hospitalgray">
                    {course.category && (
                        <>
                            <span className="h-1.5 w-1.5 rounded-full bg-hospitalbrown" aria-hidden="true" />
                            {course.category}
                        </>
                    )}
                </span>
                <StatusBadge status={course.status} />
            </div>
            <h3 className="text-base font-semibold leading-snug text-gray-900">{course.title}</h3>
            <div className="space-y-1 text-sm text-hospitalgray">
                <p className="tabular-nums">{formatRange(course.start_date, course.end_date)}</p>
                {modality && <p>{modality}{course.location ? ` · ${course.location}` : ''}</p>}
            </div>
            <div className="mt-auto flex items-center justify-between gap-2 pt-1">
                <Link href={route('catalog.index', { curso: course.id })}
                      className="px-1 py-2.5 text-sm font-semibold text-hospitalblue hover:underline">
                    Ver detalle
                </Link>
                <button type="button" onClick={enroll} disabled={sending}
                        className="min-h-[44px] rounded-lg bg-hospitalblue px-4 text-sm font-semibold text-white hover:bg-hospitalblue-dark disabled:opacity-60">
                    {sending ? 'Inscribiendo…' : 'Inscribirme'}
                </button>
            </div>
        </article>
    );
}

export default function Dashboard({ studentCourses, availableCourses, availableTotal, modalities }) {
    const { auth } = usePage().props;
    const firstName = auth.user?.name?.split(' ')[0] ?? '';
    const issued = studentCourses.filter((c) => c.certificate).length;

    return (
        <AppLayout>
            <Head title="Inicio" />
            <div className="space-y-10">
                <header>
                    <h1 className="text-3xl font-bold tracking-tight text-gray-900">Hola, {firstName}</h1>
                    <p className="mt-1.5 text-sm text-hospitalgray">
                        Tus cursos, tus certificados y los cursos a los que todavía te podés inscribir, en un solo lugar.
                    </p>
                </header>

                <section aria-labelledby="mis-cursos" className="space-y-3">
                    <SectionTitle
                        id="mis-cursos"
                        title="Mis cursos"
                        aside={studentCourses.length > 0 && (
                            <span className="text-sm text-hospitalgray">
                                {studentCourses.length} {studentCourses.length === 1 ? 'curso' : 'cursos'} · {issued} {issued === 1 ? 'certificado' : 'certificados'}
                            </span>
                        )}
                    />
                    {studentCourses.length === 0 ? (
                        <div className="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center text-sm text-hospitalgray">
                            Todavía no estás inscripto en ningún curso. Elegí uno de los disponibles para empezar.
                        </div>
                    ) : (
                        <ul className="divide-y divide-gray-100 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                            {studentCourses.map((c) => <MyCourse key={c.id} course={c} modalities={modalities} />)}
                        </ul>
                    )}
                </section>

                <section aria-labelledby="disponibles" className="space-y-3">
                    <SectionTitle
                        id="disponibles"
                        title="Cursos disponibles para inscribirte"
                        aside={(
                            <Link href={route('catalog.index')} className="whitespace-nowrap text-sm font-semibold text-hospitalblue hover:underline">
                                Ver todos los cursos →
                            </Link>
                        )}
                    />
                    {availableCourses.length === 0 ? (
                        <div className="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-10 text-center text-sm text-hospitalgray">
                            Por ahora no hay otros cursos con inscripción abierta.
                        </div>
                    ) : (
                        <>
                            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                                {availableCourses.map((c) => <AvailableCourse key={c.id} course={c} modalities={modalities} />)}
                            </div>
                            {availableTotal > availableCourses.length && (
                                <p className="text-sm text-hospitalgray">
                                    Hay {availableTotal - availableCourses.length} cursos más en el{' '}
                                    <Link href={route('catalog.index')} className="font-semibold text-hospitalblue hover:underline">catálogo</Link>.
                                </p>
                            )}
                        </>
                    )}
                </section>
            </div>
        </AppLayout>
    );
}
