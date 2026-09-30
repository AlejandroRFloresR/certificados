import { useMemo, useState } from 'react';
import { usePage } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import CourseCard from '@/Components/Catalog/CourseCard';
import CourseDialog from '@/Components/Catalog/CourseDialog';
import { SearchIcon, CheckIcon } from '@/Components/Catalog/Icons';
import { TABS, normalize, sortCourses } from '@/Components/Catalog/utils';

const selectClass =
    'rounded-lg border-gray-300 py-2 pl-3 pr-8 text-sm text-gray-700 focus:border-hospitalblue focus:ring-hospitalblue';

export default function Catalog({ courses = [], modalities = {} }) {
    const { auth, flash } = usePage().props;
    const [query, setQuery] = useState('');
    const [tab, setTab] = useState('disponibles');
    const [category, setCategory] = useState('');
    const [modality, setModality] = useState('');
    // Se guarda solo el id: cuando te inscribís, Inertia recarga `courses`
    // y el diálogo muestra automáticamente "Ya estás inscripto"
    const [selectedId, setSelectedId] = useState(
        () => Number(new URLSearchParams(window.location.search).get('curso')) || null
    );
    const selected = courses.find((c) => c.id === selectedId) ?? null;

    const openCourse = (course) => {
        setSelectedId(course?.id ?? null);
        const url = new URL(window.location.href);
        if (course) url.searchParams.set('curso', course.id);
        else url.searchParams.delete('curso');
        // Mantener window.history.state: Inertia guarda ahí la página, y sin eso el botón "Atrás" falla
        window.history.replaceState(window.history.state, '', url);
    };

    const categories = useMemo(
        () => [...new Set(courses.map((c) => c.category).filter(Boolean))].sort((a, b) => a.localeCompare(b, 'es')),
        [courses]
    );
    const usedModalities = useMemo(
        () => Object.entries(modalities).filter(([key]) => courses.some((c) => c.modality === key)),
        [courses, modalities]
    );

    // Filtros que no dependen de la pestaña, para poder mostrar el conteo por pestaña
    const baseFiltered = useMemo(() => {
        const q = normalize(query.trim());
        return courses.filter((c) => {
            if (category && c.category !== category) return false;
            if (modality && c.modality !== modality) return false;
            if (!q) return true;
            return normalize([c.title, c.description, c.category, c.location, ...c.tutors].join(' ')).includes(q);
        });
    }, [courses, query, category, modality]);

    const counts = useMemo(
        () => Object.fromEntries(TABS.map((t) => [t.id, baseFiltered.filter(t.match).length])),
        [baseFiltered]
    );

    const visible = useMemo(() => {
        const current = TABS.find((t) => t.id === tab);
        return baseFiltered.filter(current.match).sort(sortCourses);
    }, [baseFiltered, tab]);

    const hasFilters = query || category || modality;
    const clearFilters = () => {
        setQuery('');
        setCategory('');
        setModality('');
    };

    return (
        <PublicLayout title="Cursos">
            <section className="bg-gradient-to-br from-hospitalblue to-hospitalblue-dark px-4 py-12 sm:px-8 sm:py-16">
                <div className="mx-auto max-w-xl text-center">
                    <span className="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/70">
                        <span className="h-1.5 w-1.5 rounded-full bg-hospitalbrown" />
                        Capacitación
                    </span>
                    <h1 className="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Cursos del hospital
                    </h1>
                    <p className="mx-auto mt-2 max-w-md text-sm text-white/80">
                        Conocé la oferta de cursos y capacitaciones, con sus fechas, carga horaria y docentes.
                    </p>

                    <label className="mt-6 flex items-center gap-2 rounded-xl bg-white p-1.5 px-3 shadow-lg">
                        <SearchIcon className="h-[17px] w-[17px] shrink-0 text-gray-400" />
                        <span className="sr-only">Buscar cursos</span>
                        <input
                            type="search"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="Buscar por nombre, tema o docente"
                            className="w-full border-0 p-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-0"
                        />
                    </label>
                </div>
            </section>

            <div className="mx-auto max-w-6xl px-4 py-8 sm:px-8">
                  {flash.success && (
                    <div className="mb-6 flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 p-3.5 text-sm text-green-800">
                        <CheckIcon className="mt-0.5 h-[18px] w-[18px] shrink-0" />
                        <span>{flash.success}</span>
                    </div>
                )}
                <div className="flex flex-col gap-4 border-b border-gray-200 pb-4 md:flex-row md:items-end md:justify-between">
                    <div role="tablist" aria-label="Estado del curso" className="-mb-4 flex gap-1 overflow-x-auto">
                        {TABS.map((t) => (
                            <button
                                key={t.id}
                                role="tab"
                                aria-selected={tab === t.id}
                                onClick={() => setTab(t.id)}
                                className={`whitespace-nowrap border-b-2 px-3 pb-3 text-sm font-semibold transition ${
                                    tab === t.id
                                        ? 'border-hospitalblue text-hospitalblue'
                                        : 'border-transparent text-hospitalgray hover:text-gray-900'
                                }`}
                            >
                                {t.label}
                                <span className={`ml-1.5 rounded-full px-1.5 py-0.5 text-xs tabular-nums ${
                                    tab === t.id ? 'bg-hospitalblue/10' : 'bg-gray-100'
                                }`}>
                                    {counts[t.id]}
                                </span>
                            </button>
                        ))}
                    </div>

                    {(categories.length > 0 || usedModalities.length > 0) && (
                        <div className="flex flex-wrap gap-2">
                            {categories.length > 0 && (
                                <select aria-label="Categoría" value={category}
                                        onChange={(e) => setCategory(e.target.value)} className={selectClass}>
                                    <option value="">Todas las categorías</option>
                                    {categories.map((c) => <option key={c} value={c}>{c}</option>)}
                                </select>
                            )}
                            {usedModalities.length > 0 && (
                                <select aria-label="Modalidad" value={modality}
                                        onChange={(e) => setModality(e.target.value)} className={selectClass}>
                                    <option value="">Todas las modalidades</option>
                                    {usedModalities.map(([key, label]) => <option key={key} value={key}>{label}</option>)}
                                </select>
                            )}
                        </div>
                    )}
                </div>

                {visible.length > 0 ? (
                    <div className="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {visible.map((course) => (
                            <CourseCard key={course.id} course={course} modalities={modalities} onOpen={openCourse} />
                        ))}
                    </div>
                ) : (
                    <div className="mt-6 rounded-xl border border-dashed border-gray-300 px-6 py-14 text-center">
                        <p className="text-sm font-semibold text-gray-900">
                            {courses.length === 0 ? 'Todavía no hay cursos publicados' : 'No hay cursos que coincidan'}
                        </p>
                        <p className="mt-1 text-sm text-hospitalgray">
                            {courses.length === 0
                                ? 'Volvé a consultar pronto.'
                                : hasFilters
                                    ? 'Probá con otra búsqueda o quitá los filtros.'
                                    : 'Probá mirando otra pestaña.'}
                        </p>
                        {hasFilters && (
                            <button type="button" onClick={clearFilters}
                                    className="mt-4 text-sm font-semibold text-hospitalblue hover:underline">
                                Limpiar filtros
                            </button>
                        )}
                    </div>
                )}
            </div>

            <CourseDialog
                course={selected}
                modalities={modalities}
                onClose={() => openCourse(null)}
                auth={!!auth.user}
            />
        </PublicLayout>
    );
}
