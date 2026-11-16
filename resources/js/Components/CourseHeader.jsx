import { Head, Link } from '@inertiajs/react';
import Button from '@/Components/Button';
import CourseMenu, { PublicBadge } from '@/Components/CourseMenu';
import { StatusBadge } from '@/Components/Catalog/CourseCard';
import { formatRange } from '@/Components/Catalog/utils';

/** Cabecera de la ficha del curso: datos, acciones y pestañas (tab: 'alumnos' | 'moodle' | 'encuesta') */
export default function CourseHeader({ course, tab }) {
    const meta = [course.category, formatRange(course.start_date, course.end_date), course.modality]
        .filter(Boolean)
        .join(' · ');

    const tabs = [
        { id: 'alumnos', label: 'Alumnos', count: course.students, href: route('admin.courses.users', course.id) },
        { id: 'moodle',  label: 'Moodle',  href: route('admin.courses.moodle', course.id) },
        { id: 'encuesta', label: 'Encuesta', href: route('admin.courses.survey', course.id) },
    ];

    return (
        <>
            <Head title={course.title} />

            <Link href={route('courses.index')}
                  className="mb-3 inline-flex items-center gap-1.5 py-1.5 text-sm font-semibold text-hospitalblue hover:underline">
                <svg aria-hidden="true" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                    <path d="M15 18l-6-6 6-6" />
                </svg>
                Cursos
            </Link>

            <div className="flex flex-wrap items-start justify-between gap-4">
                <div className="min-w-0">
                    <h1 className="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{course.title}</h1>
                    <div className="mt-2 flex flex-wrap items-center gap-2 text-sm text-hospitalgray">
                        <StatusBadge status={course.status} />
                        <PublicBadge isPublic={course.is_public} />
                        {meta && <span className="tabular-nums">{meta}</span>}
                    </div>
                </div>
                <div className="flex items-center gap-2">
                    <Link href={route('admin.courses.edit', course.id)}>
                        <Button type="button" variant="secondary" className="min-h-[44px]">Editar curso</Button>
                    </Link>
                    <CourseMenu course={course} withEdit={false} />
                </div>
            </div>

            <div role="tablist" aria-label="Secciones del curso" className="mt-6 flex gap-1 border-b border-gray-200">
                {tabs.map((t) => (
                    <Link
                        key={t.id}
                        href={t.href}
                        role="tab"
                        aria-selected={tab === t.id}
                        preserveScroll
                        className={`-mb-px border-b-2 px-3 py-3 text-sm font-semibold ${
                            tab === t.id ? 'border-hospitalblue text-hospitalblue' : 'border-transparent text-hospitalgray hover:text-gray-900'
                        }`}
                    >
                        {t.label}
                        {t.count !== undefined && (
                            <span className={`ml-1.5 rounded-full px-1.5 py-0.5 text-xs tabular-nums ${tab === t.id ? 'bg-hospitalblue/10' : 'bg-gray-100'}`}>
                                {t.count}
                            </span>
                        )}
                    </Link>
                ))}
            </div>
        </>
    );
}
