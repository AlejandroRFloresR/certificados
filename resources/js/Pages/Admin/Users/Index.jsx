import { useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/Button';
import Pagination from '@/Components/Pagination';
import RoleBadge, { ROLE_LABELS } from '@/Components/RoleBadge';

export default function Index({ users, filters, roles }) {
    const { auth } = usePage().props;
    const [q, setQ] = useState(filters.q);

    // Búsqueda en el servidor: los usuarios pueden ser miles y están paginados
    const applyFilters = (next) => {
        router.get(route('admin.users.index'), { ...filters, q, ...next }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const destroy = (user) => {
        if (!confirm(`¿Eliminar a ${user.name}? Esta acción no se puede deshacer.`)) return;
        router.delete(route('admin.users.destroy', user.id), { preserveScroll: true });
    };

    const hasFilters = filters.q || filters.role;

    return (
        <AppLayout
            title="Usuarios"
            actions={
                <div className="flex gap-2">
                    <Link href={route('admin.users.import.create')}>
                        <Button type="button" variant="secondary">Importar</Button>
                    </Link>
                    <Link href={route('admin.users.create')}>
                        <Button type="button">+ Crear usuario</Button>
                    </Link>
                </div>
            }
        >
            <form onSubmit={(e) => { e.preventDefault(); applyFilters({}); }}
                  className="mb-4 flex flex-wrap items-center gap-2">
                <input type="search" value={q} onChange={(e) => setQ(e.target.value)}
                       placeholder="Buscar por nombre, email o DNI" aria-label="Buscar usuarios"
                       className="w-full max-w-sm rounded-lg border-gray-300 text-sm focus:border-hospitalblue focus:ring-hospitalblue" />
                <select value={filters.role} onChange={(e) => applyFilters({ role: e.target.value })} aria-label="Filtrar por rol"
                        className="rounded-lg border-gray-300 py-2 pl-3 pr-8 text-sm focus:border-hospitalblue focus:ring-hospitalblue">
                    <option value="">Todos los roles</option>
                    {roles.map((r) => <option key={r} value={r}>{ROLE_LABELS[r] ?? r}</option>)}
                </select>
                <Button>Buscar</Button>
                {hasFilters && (
                    <Button type="button" variant="ghost" onClick={() => { setQ(''); applyFilters({ q: '', role: '' }); }}>
                        Limpiar
                    </Button>
                )}
            </form>

            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                {users.data.length === 0 ? (
                    <p className="px-5 py-12 text-center text-sm text-hospitalgray">
                        {hasFilters ? 'Ningún usuario coincide con la búsqueda.' : 'No hay usuarios para mostrar.'}
                    </p>
                ) : (
                    <>
                        <table className="hidden w-full text-sm md:table">
                            <thead>
                                <tr className="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-hospitalgray">
                                    <th className="px-5 py-2.5">Usuario</th>
                                    <th className="px-5 py-2.5">DNI</th>
                                    <th className="px-5 py-2.5">Teléfono</th>
                                    <th className="px-5 py-2.5">Rol</th>
                                    <th className="px-5 py-2.5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {users.data.map((u) => (
                                    <tr key={u.id}>
                                        <td className="px-5 py-3">
                                            <p className="font-medium text-gray-900">
                                                {u.name}
                                                {u.id === auth.user.id && <span className="ml-1.5 text-xs font-normal text-hospitalgray">(vos)</span>}
                                            </p>
                                            <p className="text-xs text-hospitalgray">{u.email}</p>
                                        </td>
                                        <td className="px-5 py-3 tabular-nums text-gray-600">{u.dni ?? '—'}</td>
                                        <td className="px-5 py-3 tabular-nums text-gray-600">{u.telefono ?? '—'}</td>
                                        <td className="px-5 py-3"><RoleBadge role={u.role} /></td>
                                        <td className="px-5 py-3">
                                            <UserActions user={u} isSelf={u.id === auth.user.id} onDelete={destroy} />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        <div className="divide-y divide-gray-100 md:hidden">
                            {users.data.map((u) => (
                                <div key={u.id} className="p-4">
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="min-w-0">
                                            <p className="text-sm font-medium text-gray-900">{u.name}</p>
                                            <p className="truncate text-xs text-hospitalgray">{u.email}</p>
                                        </div>
                                        <RoleBadge role={u.role} />
                                    </div>
                                    <p className="mt-1 text-xs tabular-nums text-gray-600">DNI {u.dni ?? '—'} · Tel. {u.telefono ?? '—'}</p>
                                    <div className="mt-2 -mr-2.5">
                                        <UserActions user={u} isSelf={u.id === auth.user.id} onDelete={destroy} />
                                    </div>
                                </div>
                            ))}
                        </div>
                    </>
                )}
            </div>

            <Pagination paginator={users} />
        </AppLayout>
    );
}

function UserActions({ user, isSelf, onDelete }) {
    return (
        <div className="flex items-center justify-end gap-1">
            <Link href={route('admin.users.edit', user.id)}
                  className="rounded-md px-2.5 py-1.5 text-sm font-semibold text-hospitalblue hover:bg-hospitalblue/10">
                Editar
            </Link>
            <button type="button" onClick={() => onDelete(user)} disabled={isSelf}
                    title={isSelf ? 'No podés eliminar tu propio usuario' : undefined}
                    className="rounded-md px-2.5 py-1.5 text-sm font-semibold text-red-600 hover:bg-red-50 disabled:cursor-not-allowed disabled:opacity-40">
                Eliminar
            </button>
        </div>
    );
}
