import { useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import NavLink from '@/Components/NavLink';
import Flash from '@/Components/Flash';

export default function AppLayout({ title, actions, children }) {
    const { auth, imagesUrl } = usePage().props;
    const isAdmin = auth.user?.roles.includes('admin');
    const [menuOpen, setMenuOpen] = useState(false);
    const [userOpen, setUserOpen] = useState(false);

    // Un solo lugar donde se definen los links: se usan en escritorio y en móvil
     const links = [
        { label: 'Mis cursos',        href: route('dashboard'),     active: route().current('dashboard') },
        { label: 'Listado de Cursos', href: route('courses.index'), active: route().current('courses.*') },
        ...(isAdmin ? [
            { label: 'Tutores',  href: route('tutors.index'),      active: route().current('tutors.*'),      native: true },
            { label: 'Usuarios', href: route('admin.users.index'), active: route().current('admin.users.*')},
        ] : []),
    ];

    return (
        <div className="min-h-screen bg-gray-50">
            <Head title={title} />

            <nav className="bg-hospitalblue">
                <div className="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-8">
                    <div className="flex items-center gap-6">
                        <Link href={route('dashboard')} className="shrink-0">
                            <img src={`${imagesUrl}/logoNav.png`} alt="Logo" className="h-10 w-auto" />
                        </Link>
                        <div className="hidden items-center gap-1 sm:flex">
                            {links.map((l) => (
                                <NavLink key={l.label} href={l.href} active={l.active} native={l.native}>{l.label}</NavLink>
                            ))}
                        </div>
                    </div>

                    {/* Menú del usuario (escritorio) */}
                    <div className="relative hidden sm:block">
                        <button
                            type="button"
                            onClick={() => setUserOpen(!userOpen)}
                            className="inline-flex items-center gap-1 rounded-md bg-white px-3 py-2 text-sm font-medium text-hospitalblue"
                        >
                            {auth.user?.name}
                            <svg className="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fillRule="evenodd" d="M5.3 7.3a1 1 0 011.4 0L10 10.6l3.3-3.3a1 1 0 111.4 1.4l-4 4a1 1 0 01-1.4 0l-4-4a1 1 0 010-1.4z" clipRule="evenodd" />
                            </svg>
                        </button>
                        {userOpen && (
                            <>
                                {/* Capa invisible: click afuera cierra el menú */}
                                <div className="fixed inset-0 z-10" onClick={() => setUserOpen(false)} />
                                <div className="absolute right-0 z-20 mt-2 w-48 overflow-hidden rounded-lg border border-gray-100 bg-white py-1 shadow-lg">
                                    <Link href={route('profile.edit')} className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        Perfil
                                    </Link>
                                    <Link href={route('logout')} method="post" as="button"
                                          className="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50">
                                        Cerrar sesión
                                    </Link>
                                </div>
                            </>
                        )}
                    </div>

                    {/* Botón hamburguesa (móvil) */}
                    <button
                        type="button"
                        onClick={() => setMenuOpen(!menuOpen)}
                        className="rounded-md p-2 text-white/80 hover:bg-white/10 sm:hidden"
                        aria-label="Menú"
                    >
                        <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                            {menuOpen
                                ? <path strokeLinecap="round" d="M6 18L18 6M6 6l12 12" />
                                : <path strokeLinecap="round" d="M4 6h16M4 12h16M4 18h16" />}
                        </svg>
                    </button>
                </div>

                {/* Menú desplegado (móvil) */}
                {menuOpen && (
                    <div className="space-y-1 border-t border-white/10 px-4 py-3 sm:hidden">
                        {links.map((l) => {
                            const Tag = l.native ? 'a' : Link;
                            return (
                                <Tag key={l.label} href={l.href}
                                    className={`block rounded-md px-3 py-2 text-sm font-medium ${l.active ? 'bg-white/15 text-white' : 'text-white/80'}`}>
                                    {l.label}
                                </Tag>
                            );
                        })}

                        <div className="mt-2 border-t border-white/10 pt-3">
                            <p className="px-3 text-sm font-medium text-white">{auth.user?.name}</p>
                            <p className="px-3 text-xs text-white/60">{auth.user?.email}</p>
                            <Link href={route('profile.edit')} className="mt-2 block rounded-md px-3 py-2 text-sm text-white/80">Perfil</Link>
                            <Link href={route('logout')} method="post" as="button"
                                  className="block w-full rounded-md px-3 py-2 text-left text-sm text-white/80">Cerrar sesión</Link>
                        </div>
                    </div>
                )}
            </nav>

            {title && (
                <header className="border-b border-gray-200 bg-white">
                    <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-5 sm:px-8">
                        <h1 className="text-xl font-semibold text-gray-900">{title}</h1>
                        {actions}
                    </div>
                </header>
            )}

            <main className="mx-auto max-w-7xl px-4 py-8 sm:px-8">
                <Flash />
                {children}
            </main>
        </div>
    );
}
