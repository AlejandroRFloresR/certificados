import { useEffect, useRef, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/Button';
import MoreMenu from '@/Components/MoreMenu';
import { confirmDialog } from '@/Components/ConfirmDialog';
import Pagination from '@/Components/Pagination';
import RoleBadge from '@/Components/RoleBadge';

// Plural de los chips de rol
const ROLE_CHIPS = { admin: 'Administradores', user: 'Usuarios' };

export default function Index({ users, filters, roles, counts }) {
    const { auth } = usePage().props;
    const [q, setQ] = useState(filters.q);

    // Búsqueda en el servidor (los usuarios pueden ser miles y están paginados)
    const visit = (params) =>
        router.get(route('admin.users.index'), { q: q.trim(), role: filters.role, ...params }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });

    // Busca mientras se escribe, con una pequeña espera (no en la primera carga)
    const firstRender = useRef(true);
    useEffect(() => {
        if (firstRender.current) { firstRender.current = false; return undefined; }
        if (q.trim() === filters.q) return undefined;
        const timer = setTimeout(() => visit({ q: q.trim() }), 350);
        return () => clearTimeout(timer);
    }, [q]); // eslint-disable-line react-hooks/exhaustive-deps

    const destroy = async (user) => {
        const ok = await confirmDialog({
            title: `¿Eliminar a ${user.name}?`,
            message: [
                'Se elimina su cuenta y ya no va a poder ingresar. Esta acción no se puede deshacer.',
                user.certificates > 0 && `Sus ${user.certificates} ${user.certificates === 1 ? 'certificado se conserva' : 'certificados se conservan'}: se siguen descargando y verificando por código o DNI.`,
            ],
            confirmLabel: 'Eliminar usuario',
            danger: true,
        });
        if (!ok) return;
        router.delete(route('admin.users.destroy', user.id), { preserveScroll: true });
    };

    const chips = [{ id: '', label: 'Todos' }, ...roles.map((r) => ({ id: r, label: ROLE_CHIPS[r] ?? r }))];

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
            <div className="mb-4 flex flex-wrap items-center justify-between gap-3">
                <input
                    type="search"
                    value={q}
                    onChange={(e) => setQ(e.target.value)}
                    onKeyDown={(e) => e.key === 'Enter' && visit({ q: q.trim() })}
                    placeholder="Buscar por nombre, email o DNI"
                    aria-label="Buscar usuarios"
                    className="min-h-[44px] w-full max-w-sm rounded-lg border-gray-300 text-sm focus:border-hospitalblue focus:ring-hospitalblue"
                />
                <div role="group" aria-label="Filtrar por rol" className="flex flex-wrap gap-1.5">
                    {chips.map((c) => (
                        <button
                            key={c.id}
                            type="button"
                            aria-pressed={filters.role === c.id}
                            onClick={() => visit({ role: c.id, page: undefined })}
                            className={`min-h-[36px] rounded-full border px-3.5 text-sm font-semibold ${
                                filters.role === c.id
                                    ? 'border-hospitalblue bg-hospitalblue text-white'
                                    : 'border-gray-300 bg-white text-gray-800 hover:border-gray-400'
                            }`}
                        >
                            {c.label} · {counts[c.id] ?? 0}
                        </button>
                    ))}
                </div>
            </div>

            <div className="rounded-xl border border-gray-200 bg-white shadow-sm">
                {users.data.length === 0 ? (
                    <div className="px-5 py-12 text-center text-sm text-hospitalgray">
                        {filters.q || filters.role ? (
                            <>
                                <p>Ningún usuario coincide con la búsqueda o el filtro.</p>
                                <button type="button" onClick={() => { setQ(''); visit({ q: '', role: '' }); }}
                                        className="mt-2 font-semibold text-hospitalblue hover:underline">
                                    Ver todos
                                </button>
                            </>
                        ) : 'No hay usuarios para mostrar.'}
                    </div>
                ) : (
                    <ul className="divide-y divide-gray-100">
                        {users.data.map((u) => (
                            <UserRow key={u.id} user={u} isSelf={u.id === auth.user.id} onDelete={destroy} />
                        ))}
                    </ul>
                )}
            </div>

            <Pagination paginator={users} />
        </AppLayout>
    );
}

function UserRow({ user: u, isSelf, onDelete }) {
    const deleteItem = isSelf
        ? { label: 'Eliminar', disabled: true, note: 'No podés eliminar tu propio usuario.' }
        : { label: 'Eliminar', danger: true, onClick: () => onDelete(u) };

    return (
        <li className="flex items-center gap-2 pr-3 hover:bg-gray-50">
            {/* Toda la fila abre el usuario */}
            <Link href={route('admin.users.edit', u.id)}
                  className="flex min-w-0 flex-1 flex-wrap items-center justify-between gap-x-4 gap-y-1 py-4 pl-5 pr-2">
                <div className="min-w-0">
                    <div className="flex flex-wrap items-center gap-2">
                        <span className="font-semibold text-gray-900">{u.name}</span>
                        {isSelf && <span className="text-sm text-hospitalgray">(vos)</span>}
                        <RoleBadge role={u.role} />
                    </div>
                    <p className="mt-0.5 truncate text-sm text-hospitalgray">{u.email}</p>
                </div>
                <p className="text-sm tabular-nums text-hospitalgray">
                    {[u.dni && `DNI ${u.dni}`, u.telefono && `Tel. ${u.telefono}`].filter(Boolean).join(' · ')}
                </p>
            </Link>
            <MoreMenu label={`Más acciones para ${u.name}`} items={[
                { label: 'Editar', href: route('admin.users.edit', u.id) },
                { label: '-' },
                deleteItem,
            ]} />
        </li>
    );
}
