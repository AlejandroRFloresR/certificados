import { Head, Link, usePage } from '@inertiajs/react';

export default function PublicLayout({ title, children }) {
    const { auth, appName, imagesUrl } = usePage().props;

    const navClass = (active) =>
        `rounded-md px-3 py-2 text-sm font-medium ${active ? 'text-hospitalblue' : 'text-gray-600 hover:text-hospitalblue'}`;

    return (
        <div className="flex min-h-screen flex-col bg-white">
            <Head title={title} />

            <header className="flex items-center justify-between gap-2 border-b border-gray-100 px-4 py-4 sm:px-8">
                <Link href={route('home')}>
                    <img src={`${imagesUrl}/logoInstitucional.png`} alt="Logo" className="h-10 w-auto sm:h-[50px]" />
                </Link>

                <div className="flex items-center gap-1 sm:gap-2">
                    <Link href={route('home')} className={`hidden sm:inline-block ${navClass(route().current('home'))}`}>
                        Verificar certificado
                    </Link>
                    <Link href={route('catalog.index')} className={navClass(route().current('catalog.index'))}>
                        Cursos
                    </Link>

                    {auth.user ? (
                        <Link href={route('dashboard')}
                              className="rounded-md bg-hospitalblue px-3 py-2 text-sm font-medium text-white hover:bg-hospitalblue-dark">
                            Ir al panel
                        </Link>
                    ) : (
                        <>
                            <Link href={route('login')}
                                  className="rounded-md border border-gray-300 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">
                                Iniciar sesión
                            </Link>
                            <Link href={route('register')}
                                  className="hidden rounded-md px-3 py-2 text-sm font-medium text-hospitalblue hover:bg-gray-50 sm:inline-block">
                                Registrarme
                            </Link>
                        </>
                    )}
                </div>
            </header>

            <main className="flex-1">{children}</main>

            <footer className="border-t border-gray-100 py-6 text-center text-xs text-hospitalgray">
                {appName}
            </footer>
        </div>
    );
}
