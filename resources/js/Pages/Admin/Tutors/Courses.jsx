import { useMemo, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/Button';
import { normalize } from '@/Components/Catalog/utils';

export default function Courses({ tutor, courses, selected: initialSelected, max }) {
    const { errors } = usePage().props;
    const initial = useMemo(() => new Set(initialSelected), [initialSelected]);
    const [selected, setSelected] = useState(() => new Set(initialSelected));
    const [query, setQuery] = useState('');
    const [saving, setSaving] = useState(false);

    const visible = useMemo(() => {
        const q = normalize(query.trim());
        return q ? courses.filter((c) => normalize(c.title).includes(q)) : courses;
    }, [courses, query]);

    const toggle = (id) => setSelected((prev) => {
        const next = new Set(prev);
        next.has(id) ? next.delete(id) : next.add(id);
        return next;
    });

    const save = () => {
        router.put(route('tutors.updateCourses', tutor.id), { courses: [...selected] }, {
            onStart: () => setSaving(true),
            onFinish: () => setSaving(false),
        });
    };

    return (
        <AppLayout title={`Cursos de ${tutor.name}`}>
            <div className="mx-auto max-w-3xl">
                <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                    <input type="search" value={query} onChange={(e) => setQuery(e.target.value)}
                           placeholder="Buscar curso" aria-label="Buscar curso"
                           className="w-full max-w-sm rounded-lg border-gray-300 text-sm focus:border-hospitalblue focus:ring-hospitalblue" />
                    <span className="text-sm text-hospitalgray">{selected.size} seleccionados</span>
                </div>

                {errors.courses && (
                    <div className="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">{errors.courses}</div>
                )}

                <div className="divide-y divide-gray-100 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    {visible.length === 0 && (
                        <p className="px-5 py-12 text-center text-sm text-hospitalgray">Ningún curso coincide.</p>
                    )}
                    {visible.map((c) => {
                        const checked = selected.has(c.id);
                        // Cupo: si el tutor ya estaba, su lugar no cuenta como "nuevo"
                        const isFull = c.tutors_count >= max && !initial.has(c.id);
                        const disabled = isFull && !checked;

                        return (
                            <label key={c.id}
                                   className={`flex items-center gap-3 px-5 py-3 ${disabled ? 'opacity-50' : 'cursor-pointer hover:bg-gray-50'} ${checked ? 'bg-hospitalblue/5' : ''}`}>
                                <input type="checkbox" checked={checked} disabled={disabled} onChange={() => toggle(c.id)}
                                       className="rounded border-gray-300 text-hospitalblue focus:ring-hospitalblue" />
                                <span className="flex-1 text-sm text-gray-900">{c.title}</span>
                                <span className={`text-xs tabular-nums ${isFull ? 'font-semibold text-red-600' : 'text-hospitalgray'}`}>
                                    {isFull ? 'Cupo completo' : `${c.tutors_count}/${max} tutores`}
                                </span>
                            </label>
                        );
                    })}
                </div>

                <div className="mt-6 flex items-center justify-end gap-2">
                    <Link href={route('tutors.index')}>
                        <Button type="button" variant="secondary">Cancelar</Button>
                    </Link>
                    <Button type="button" onClick={save} disabled={saving}>
                        {saving ? 'Guardando…' : 'Guardar cambios'}
                    </Button>
                </div>
            </div>
        </AppLayout>
    );
}
