import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/Button';

const CONFIG = {
    users: {
        title: 'Asignar alumnos',
        field: 'users',
        noun: ['alumno', 'alumnos'],
        placeholder: 'Buscar por nombre, email o DNI',
        columns: [['name', 'Nombre'], ['email', 'Email'], ['dni', 'DNI'], ['telefono', 'Teléfono']],
        editRoute: 'admin.courses.users.edit',
        updateRoute: 'admin.courses.users.update',
        max: null,
    },
    tutors: {
        title: 'Asignar tutores',
        field: 'tutors',
        noun: ['tutor', 'tutores'],
        placeholder: 'Buscar tutor por nombre o email',
        columns: [['name', 'Nombre'], ['email', 'Email']],
        editRoute: 'admin.courses.tutors.edit',
        updateRoute: 'admin.courses.tutors.update',
        max: 3,
    },
};

export default function Assign({ mode, course, items, selected: initialSelected, q }) {
    const cfg = CONFIG[mode];
    const { errors } = usePage().props;

    // La selección vive acá y NO se pierde al buscar (preserveState).
    // Así se guardan también los seleccionados que no aparecen en la búsqueda actual.
    const [selected, setSelected] = useState(() => new Set(initialSelected));
    const [query, setQuery] = useState(q);
    const [saving, setSaving] = useState(false);

    const full = cfg.max !== null && selected.size >= cfg.max;

    const toggle = (id) => {
        setSelected((prev) => {
            const next = new Set(prev);
            next.has(id) ? next.delete(id) : next.add(id);
            return next;
        });
    };

    const search = (e) => {
        e.preventDefault();
        router.get(route(cfg.editRoute, course.id), { q: query }, {
            preserveState: true,   // mantiene `selected`
            preserveScroll: true,
            replace: true,
        });
    };

    const clear = () => {
        setQuery('');
        router.get(route(cfg.editRoute, course.id), {}, { preserveState: true, replace: true });
    };

    const save = () => {
        router.put(route(cfg.updateRoute, course.id), { [cfg.field]: [...selected] }, {
            onStart: () => setSaving(true),
            onFinish: () => setSaving(false),
        });
    };

    const hiddenSelected = [...selected].filter((id) => !items.some((i) => i.id === id)).length;
    const [one, many] = cfg.noun;

    return (
        <AppLayout title={`${cfg.title} — ${course.title}`}>
            <form onSubmit={search} className="mb-4 flex flex-wrap items-center gap-2">
                <input type="search" value={query} onChange={(e) => setQuery(e.target.value)}
                       placeholder={cfg.placeholder} aria-label={cfg.placeholder}
                       className="w-full max-w-md rounded-lg border-gray-300 text-sm focus:border-hospitalblue focus:ring-hospitalblue" />
                <Button>Buscar</Button>
                {q && <Button type="button" variant="secondary" onClick={clear}>Limpiar</Button>}
            </form>

            {errors[cfg.field] && (
                <div className="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800">{errors[cfg.field]}</div>
            )}

            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div className="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 px-5 py-3 text-sm">
                    <span className="font-medium text-gray-900">
                        {selected.size} {selected.size === 1 ? one : many} seleccionados
                        {cfg.max && <span className="text-hospitalgray"> (máximo {cfg.max})</span>}
                    </span>
                    {hiddenSelected > 0 && (
                        <span className="text-xs text-hospitalgray">
                            {hiddenSelected} seleccionados no aparecen en esta búsqueda y se mantienen
                        </span>
                    )}
                </div>

                {items.length === 0 ? (
                    <p className="px-5 py-12 text-center text-sm text-hospitalgray">No se encontraron {many}.</p>
                ) : (
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead>
                                <tr className="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-hospitalgray">
                                    <th className="w-12 px-5 py-2.5"><span className="sr-only">Seleccionar</span></th>
                                    {cfg.columns.map(([key, label]) => <th key={key} className="px-5 py-2.5">{label}</th>)}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {items.map((item) => {
                                    const checked = selected.has(item.id);
                                    const disabled = !checked && full;
                                    return (
                                        <tr key={item.id}
                                            onClick={() => !disabled && toggle(item.id)}
                                            className={`${disabled ? 'opacity-50' : 'cursor-pointer hover:bg-gray-50'} ${checked ? 'bg-hospitalblue/5' : ''}`}>
                                            <td className="px-5 py-3">
                                                <input type="checkbox" checked={checked} disabled={disabled}
                                                       onChange={() => toggle(item.id)} onClick={(e) => e.stopPropagation()}
                                                       aria-label={`Seleccionar ${item.name}`}
                                                       className="rounded border-gray-300 text-hospitalblue focus:ring-hospitalblue" />
                                            </td>
                                            {cfg.columns.map(([key]) => (
                                                <td key={key} className={`px-5 py-3 ${key === 'name' ? 'font-medium text-gray-900' : 'text-gray-600'}`}>
                                                    {item[key] ?? '—'}
                                                </td>
                                            ))}
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                )}
            </div>

            {items.length === 300 && (
                <p className="mt-2 text-xs text-hospitalgray">Se muestran los primeros 300 resultados. Usá el buscador para encontrar a alguien en particular.</p>
            )}

            <div className="mt-6 flex items-center justify-end gap-2">
                <Link href={route('admin.courses.users', course.id)}>
                    <Button type="button" variant="secondary">Cancelar</Button>
                </Link>
                <Button type="button" onClick={save} disabled={saving}>
                    {saving ? 'Guardando…' : 'Guardar cambios'}
                </Button>
            </div>
        </AppLayout>
    );
}
