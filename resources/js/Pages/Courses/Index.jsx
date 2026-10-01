import { useMemo, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/Button';
import { StatusBadge } from '@/Components/Catalog/CourseCard';
import { formatRange, normalize } from '@/Components/Catalog/utils';

function PublicBadge({ isPublic }) {
    return isPublic
        ? <span className="rounded-full bg-hospitalbrown/15 px-2 py-0.5 text-xs font-semibold text-[#7a5a2b]">En catálogo</span>
        : <span className="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-hospitalgray">Oculto</span>;
}

function Actions({ course, isAdmin }) {
    const [enrolling, setEnrolling] = useState(false);

    const destroy = () => {
        if (!confirm(`¿Eliminar el curso "${course.title}"? Esta acción no se puede deshacer.`)) return;
        router.delete(route('admin.courses.destroy', course.id), { preserveScroll: true });
    };

    const enroll = () => {
        router.post(route('courses.enroll', course.id), {}, {
            preserveScroll: true,
            onStart: () => setEnrolling(true),
            onFinish: () => setEnrolling(false),
        });
    };

    const linkClass = 'rounded-md px-2.5 py-1.5 text-sm font-semibold text-hospitalblue hover:bg-hospitalblue/10';

    return (
        <div className="flex flex-wrap items-center justify-end gap-1">
            {isAdmin && (
                <>
                    <Link href={route('admin.courses.edit', course.id)} className={linkClass}>Editar</Link>
                    <Link href={route('admin.courses.users', course.id)} className={linkClass}>Alumnos</Link>
                    <Link href={route('admin.courses.tutors.edit', course.id)} className={linkClass}>Tutores</Link>
                    <button type="button" onClick={destroy}
                            className="rounded-md px-2.5 py-1.5 text-sm font-semibold text-red-600 hover:bg-red-50">
                        Eliminar
                    </button>
                </>
            )}
            {course.enrolled ? (
                <span className="ml-1 rounded-full bg-green-50 px-2 py-0.5 text-xs font-semibold text-green-700">Inscripto</span>
            ) : (
                <button type="button" onClick={enroll} disabled={enrolling}
                        className="ml-1 rounded-md bg-green-600 px-2.5 py-1.5 text-sm font-semibold text-white hover:bg-green-700 disabled:opacity-60">
                    {enrolling ? '…' : 'Inscribirme'}
                </button>
            )}
        </div>
    );
}

export default function Index({ courses }) {
    const { auth } = usePage().props;
    const isAdmin = auth.user.roles.includes('admin');
    const [query, setQuery] = useState('');

    const visible = useMemo(() => {
        const q = normalize(query.trim());
        if (!q) return courses;
        return courses.filter((c) => normalize([c.title, c.category, ...c.tutors].join(' ')).includes(q));
    }, [courses, query]);

    return (
        <AppLayout
            title="Listado de cursos"
            actions={isAdmin && (
                <Link href={route('admin.courses.create')}>
                    <Button>+ Crear curso</Button>
                </Link>
            )}
        >
            <div className="mb-4 flex items-center justify-between gap-3">
                <input
                    type="search"
                    value={query}
                    onChange={(e) => setQuery(e.target.value)}
                    placeholder="Buscar por título, categoría o tutor"
                    aria-label="Buscar cursos"
                    className="w-full max-w-sm rounded-lg border-gray-300 text-sm focus:border-hospitalblue focus:ring-hospitalblue"
                />
                <span className="shrink-0 text-xs text-hospitalgray">{visible.length} de {courses.length}</span>
            </div>

            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                {visible.length === 0 ? (
                    <p className="px-5 py-12 text-center text-sm text-hospitalgray">
                        {courses.length === 0 ? 'No hay cursos cargados aún.' : 'Ningún curso coincide con la búsqueda.'}
                    </p>
                ) : (
                    <>
                        {/* Escritorio */}
                        <table className="hidden w-full text-sm lg:table">
                            <thead>
                                <tr className="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-hospitalgray">
                                    <th className="px-5 py-2.5">Curso</th>
                                    <th className="px-5 py-2.5">Tutores</th>
                                    {isAdmin && <th className="px-5 py-2.5 text-right">Alumnos</th>}
                                    <th className="px-5 py-2.5">Estado</th>
                                    <th className="px-5 py-2.5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {visible.map((c) => (
                                    <tr key={c.id} className="align-top">
                                        <td className="px-5 py-3">
                                            <p className="font-medium text-gray-900">{c.title}</p>
                                            <p className="mt-0.5 text-xs tabular-nums text-hospitalgray">
                                                {c.category && `${c.category} · `}
                                                {formatRange(c.start_date, c.end_date)}
                                                {c.hours && ` · ${c.hours} h`}
                                            </p>
                                        </td>
                                        <td className="px-5 py-3 text-xs text-gray-700">
                                            {c.tutors.length ? c.tutors.join(', ') : <span className="text-gray-400">Sin tutores</span>}
                                        </td>
                                        {isAdmin && <td className="px-5 py-3 text-right tabular-nums">{c.students}</td>}
                                        <td className="px-5 py-3">
                                            <div className="flex flex-wrap gap-1">
                                                <StatusBadge status={c.status} />
                                                {isAdmin && <PublicBadge isPublic={c.is_public} />}
                                            </div>
                                        </td>
                                        <td className="px-5 py-3"><Actions course={c} isAdmin={isAdmin} /></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        {/* Móvil y tablet */}
                        <div className="divide-y divide-gray-100 lg:hidden">
                            {visible.map((c) => (
                                <div key={c.id} className="p-4">
                                    <div className="flex items-start justify-between gap-2">
                                        <p className="text-sm font-medium text-gray-900">{c.title}</p>
                                        <StatusBadge status={c.status} />
                                    </div>
                                    <p className="mt-0.5 text-xs tabular-nums text-hospitalgray">
                                        {formatRange(c.start_date, c.end_date)}
                                        {c.hours && ` · ${c.hours} h`}
                                    </p>
                                    <p className="mt-1 text-xs text-gray-600">
                                        {c.tutors.length ? `Tutores: ${c.tutors.join(', ')}` : 'Sin tutores'}
                                        {isAdmin && ` · ${c.students} alumnos`}
                                    </p>
                                    {isAdmin && <div className="mt-2"><PublicBadge isPublic={c.is_public} /></div>}
                                    <div className="mt-3 -mr-2.5"><Actions course={c} isAdmin={isAdmin} /></div>
                                </div>
                            ))}
                        </div>
                    </>
                )}
            </div>
        </AppLayout>
    );
}
