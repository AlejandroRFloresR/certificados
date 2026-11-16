import { useMemo, useState } from 'react';
import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/Button';
import CourseMenu, { PublicBadge } from '@/Components/CourseMenu';
import { StatusBadge } from '@/Components/Catalog/CourseCard';
import { formatRange, normalize } from '@/Components/Catalog/utils';

const FILTERS = [
    { id: 'todos',      label: 'Todos',       match: () => true },
    { id: 'en_curso',   label: 'En curso',    match: (c) => c.status === 'en_curso' },
    { id: 'proximos',   label: 'Próximos',    match: (c) => c.status === 'proximo' || c.status === 'sin_fecha' },
    { id: 'finalizado', label: 'Finalizados', match: (c) => c.status === 'finalizado' },
];

function Progress({ course }) {
    const pct = course.students ? Math.round((course.certificates / course.students) * 100) : 0;
    const done = course.students > 0 && course.certificates >= course.students;

    return (
        <div className="w-full sm:w-56">
            <div className="flex justify-between text-sm text-hospitalgray">
                <span><strong className="text-gray-900">{course.students}</strong> {course.students === 1 ? 'alumno' : 'alumnos'}</span>
                <span><strong className="text-gray-900">{course.certificates}</strong> {course.certificates === 1 ? 'certificado' : 'certificados'}</span>
            </div>
            <div className="mt-1.5 h-1.5 overflow-hidden rounded-full bg-gray-100" aria-hidden="true">
                <div className={`h-full ${done ? 'bg-green-600' : 'bg-hospitalblue'}`} style={{ width: `${Math.min(pct, 100)}%` }} />
            </div>
        </div>
    );
}

export default function Index({ courses }) {
    const [query, setQuery] = useState('');
    const [filter, setFilter] = useState('todos');

    const searched = useMemo(() => {
        const q = normalize(query.trim());
        return q ? courses.filter((c) => normalize([c.title, c.category].join(' ')).includes(q)) : courses;
    }, [courses, query]);

    const counts = useMemo(
        () => Object.fromEntries(FILTERS.map((f) => [f.id, searched.filter(f.match).length])),
        [searched]
    );

    const visible = searched.filter(FILTERS.find((f) => f.id === filter).match);

    return (
        <AppLayout
            title="Cursos"
            actions={
                <Link href={route('admin.courses.create')}>
                    <Button type="button">+ Crear curso</Button>
                </Link>
            }
        >
            <p className="-mt-2 mb-5 text-sm text-hospitalgray">
                Entrá a un curso para ver sus alumnos, emitir certificados y sincronizar con Moodle.
            </p>

            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <input
                    type="search"
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    placeholder="Buscar por título o categoría"
                    aria-label="Buscar cursos"
                    className="min-h-[44px] w-full max-w-sm rounded-lg border-gray-300 text-sm focus:border-hospitalblue focus:ring-hospitalblue"
                />
                <div role="group" aria-label="Filtrar por estado" className="flex flex-wrap gap-1.5">
                    {FILTERS.map((f) => (
                        <button
                            key={f.id}
                            type="button"
                            aria-pressed={filter === f.id}
                            onClick={() => setFilter(f.id)}
                            className={`min-h-[36px] rounded-full border px-3.5 text-sm font-semibold ${
                                filter === f.id
                                    ? 'border-hospitalblue bg-hospitalblue text-white'
                                    : 'border-gray-300 bg-white text-gray-800 hover:border-gray-400'
                            }`}
                        >
                            {f.label} · {counts[f.id]}
                        </button>
                    ))}
                </div>
            </div>

            <div className="rounded-xl border border-gray-200 bg-white shadow-sm">
                {visible.length === 0 ? (
                    <p className="px-5 py-12 text-center text-sm text-hospitalgray">
                        {courses.length === 0 ? 'No hay cursos cargados aún.' : 'Ningún curso coincide con la búsqueda o el filtro.'}
                    </p>
                ) : (
                    <ul className="divide-y divide-gray-100">
                        {visible.map((c) => (
                            <li key={c.id} className="flex items-center gap-2 pr-3 hover:bg-gray-50">
                                {/* Toda la fila abre la ficha del curso */}
                                <Link href={route('admin.courses.users', c.id)}
                                      className="flex min-w-0 flex-1 flex-wrap items-center justify-between gap-4 py-4 pl-5 pr-2">
                                    <div className="min-w-0">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <span className="font-semibold text-gray-900">{c.title}</span>
                                            <StatusBadge status={c.status} />
                                            <PublicBadge isPublic={c.is_public} />
                                        </div>
                                        <p className="mt-1 text-sm tabular-nums text-hospitalgray">
                                            {[c.category, formatRange(c.start_date, c.end_date)].filter(Boolean).join(' · ')}
                                        </p>
                                    </div>
                                    <Progress course={c} />
                                </Link>
                                <CourseMenu course={c} />
                            </li>
                        ))}
                    </ul>
                )}
            </div>
        </AppLayout>
    );
}
