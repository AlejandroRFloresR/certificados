import { Link } from '@inertiajs/react';

export default function Pagination({ paginator }) {
    if (paginator.last_page <= 1) return null;

    // Laravel manda "« Previous" y "Next »": los reemplazamos por flechas
    const links = paginator.links.map((l, i, all) => ({
        ...l,
        label: i === 0 ? '‹' : i === all.length - 1 ? '›' : l.label,
    }));

    return (
        <div className="mt-4 flex flex-wrap items-center justify-between gap-3">
            <p className="text-xs text-hospitalgray">
                Mostrando {paginator.from}–{paginator.to} de {paginator.total}
            </p>
            <nav className="flex flex-wrap gap-1" aria-label="Paginación">
                {links.map((l, i) =>
                    l.url ? (
                        <Link key={i} href={l.url} preserveScroll
                              className={`min-w-[2rem] rounded-md px-2.5 py-1.5 text-center text-sm ${
                                  l.active ? 'bg-hospitalblue font-semibold text-white' : 'text-gray-700 hover:bg-gray-100'
                              }`}>
                            {l.label}
                        </Link>
                    ) : (
                        <span key={i} className="min-w-[2rem] px-2.5 py-1.5 text-center text-sm text-gray-300">{l.label}</span>
                    )
                )}
            </nav>
        </div>
    );
}
