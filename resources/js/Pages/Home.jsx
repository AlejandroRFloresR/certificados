import { useState } from 'react';
import { router } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';

function Alert({ ok, children }) {
    return (
        <div className={`mb-5 rounded-lg border p-3.5 text-sm ${ok
            ? 'border-green-200 bg-green-50 text-green-800'
            : 'border-red-200 bg-red-50 text-red-800'}`}>
            {children}
        </div>
    );
}

function TypeBadge({ type }) {
    if (!type) return <span>—</span>;
    return (
        <span className="inline-flex rounded-full bg-hospitalblue/10 px-2 py-0.5 text-xs font-semibold text-hospitalblue">
            {type.charAt(0).toUpperCase() + type.slice(1)}
        </span>
    );
}

// Descarga de PDF: <a> normal, NO <Link> (Inertia no puede navegar a un archivo)
function DownloadLink({ href }) {
    return (
        <a href={href} className="inline-flex items-center gap-1.5 rounded-md px-2.5 py-1.5 text-sm font-semibold text-hospitalblue hover:bg-hospitalblue/10">
            <svg className="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 4v11m0 0l-4-4m4 4l4-4M5 19h14" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
            </svg>
            Descargar
        </a>
    );
}

export default function Home({ q, searched, found, holder, certificates }) {
    const [query, setQuery] = useState(q);
    const [loading, setLoading] = useState(false);

    const submit = (e) => {
        e.preventDefault();
        // GET a la misma ruta: la URL queda como antes (/?q=...) y se puede compartir
        router.get(route('home'), { q: query }, {
            preserveState: true,
            onStart: () => setLoading(true),
            onFinish: () => setLoading(false),
        });
    };

    const count = certificates.length;

    return (
        <PublicLayout title="Verificar certificado">
            <section className="bg-gradient-to-br from-hospitalblue to-hospitalblue-dark px-4 py-12 sm:px-8 sm:py-16">
                <div className="mx-auto max-w-xl text-center">
                    <span className="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/70">
                        <span className="h-1.5 w-1.5 rounded-full bg-hospitalbrown" />
                        Verificación pública
                    </span>
                    <h1 className="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                        Verificá la validez de un certificado
                    </h1>
                    <p className="mx-auto mt-2 max-w-md text-sm text-white/80">
                        Ingresá el DNI del titular o el código del certificado para confirmar que fue emitido por el sistema.
                    </p>

                    <form onSubmit={submit} className="mt-6 flex gap-2 rounded-xl bg-white p-1.5 shadow-lg">
                        <input
                            type="text"
                            value={query}
                            onChange={(e) => setQuery(e.target.value)}
                            placeholder="DNI o código de certificado"
                            aria-label="DNI o código de certificado"
                            className="w-full border-0 p-2.5 text-sm text-gray-900 placeholder:text-gray-400 focus:ring-0"
                        />
                        <button disabled={loading}
                                className="shrink-0 rounded-lg bg-hospitalblue px-5 py-2.5 text-sm font-semibold text-white hover:bg-hospitalblue-dark disabled:opacity-60">
                            {loading ? 'Buscando…' : 'Buscar'}
                        </button>
                    </form>
                    <p className="mt-3 text-xs text-white/60">Ej: 30123456 (DNI) — o el código que figura en tu PDF</p>
                </div>
            </section>

            <div className="mx-auto max-w-3xl px-4 py-8 sm:px-8">
                {searched && (found ? (
                    <Alert ok>
                        <strong>{count === 1 ? 'Certificado válido.' : `${count} certificados encontrados.`}</strong>{' '}
                        {count === 1 && 'Este certificado fue emitido por el sistema.'}
                    </Alert>
                ) : (
                    <Alert>
                        <strong>No se encontraron resultados</strong> para “{q}”. Verificá que el DNI o el código estén escritos correctamente.
                    </Alert>
                ))}

                {found && (
                    <div className="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                        <div className="flex items-center justify-between gap-3 border-b border-gray-200 bg-gray-50 px-4 py-3 sm:px-5">
                            <div>
                                <p className="text-sm font-semibold text-gray-900">{holder.name}</p>
                                {holder.dni && <p className="text-xs tabular-nums text-hospitalgray">DNI {holder.dni}</p>}
                            </div>
                            <span className="text-xs text-hospitalgray">{count} {count === 1 ? 'certificado' : 'certificados'}</span>
                        </div>

                        {/* Escritorio: tabla */}
                        <table className="hidden w-full bg-white text-sm sm:table">
                            <thead>
                                <tr className="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-hospitalgray">
                                    <th className="px-5 py-2.5">Curso</th>
                                    <th className="px-5 py-2.5">Tipo</th>
                                    <th className="px-5 py-2.5">Emitido</th>
                                    <th className="px-5 py-2.5">Código</th>
                                    <th className="px-5 py-2.5 text-right">Acción</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {certificates.map((c) => (
                                    <tr key={c.code}>
                                        <td className="px-5 py-3 font-medium text-gray-900">{c.course}</td>
                                        <td className="px-5 py-3"><TypeBadge type={c.type} /></td>
                                        <td className="px-5 py-3 tabular-nums text-gray-600">{c.issued ?? '—'}</td>
                                        <td className="px-5 py-3 font-mono text-xs text-hospitalgray">{c.code}</td>
                                        <td className="px-5 py-3 text-right"><DownloadLink href={c.download_url} /></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        {/* Móvil: tarjetas */}
                        <div className="divide-y divide-gray-100 bg-white sm:hidden">
                            {certificates.map((c) => (
                                <div key={c.code} className="p-4">
                                    <div className="flex items-start justify-between gap-2">
                                        <p className="text-sm font-medium text-gray-900">{c.course}</p>
                                        <TypeBadge type={c.type} />
                                    </div>
                                    <p className="mt-1.5 text-xs text-hospitalgray">Emitido {c.issued ?? '—'}</p>
                                    <p className="mt-1 break-all font-mono text-xs text-hospitalgray">{c.code}</p>
                                    <div className="mt-2 -ml-2.5"><DownloadLink href={c.download_url} /></div>
                                </div>
                            ))}
                        </div>
                    </div>
                )}
            </div>
        </PublicLayout>
    );
}
