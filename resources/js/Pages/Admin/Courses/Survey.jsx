import AppLayout from '@/Layouts/AppLayout';
import CourseHeader from '@/Components/CourseHeader';

function Stat({ label, value, detail }) {
    return (
        <div className="rounded-xl border border-gray-200 bg-white p-4">
            <p className="text-xs font-bold uppercase tracking-wide text-hospitalgray">{label}</p>
            <p className="mt-1 text-2xl font-bold tabular-nums text-gray-900">
                {value}
                {detail && <span className="ml-1 text-sm font-medium text-hospitalgray">{detail}</span>}
            </p>
        </div>
    );
}

const pct = (n, total) => (total ? Math.round((n / total) * 100) : 0);

/** Pregunta de puntaje: promedio y barras por respuesta */
function RatingCard({ rating, total }) {
    const max = Math.max(...rating.distribution.map((d) => d.count), 1);
    return (
        <div className="rounded-xl border border-gray-200 bg-white p-5">
            <div className="flex items-start justify-between gap-4">
                <p className="font-semibold text-gray-900">{rating.text}</p>
                <p className="shrink-0 text-right">
                    <span className="text-2xl font-bold tabular-nums text-gray-900">{rating.average?.toLocaleString('es-AR') ?? '—'}</span>
                    <span className="text-sm text-hospitalgray"> / 5</span>
                </p>
            </div>
            <dl className="mt-4 space-y-1.5">
                {rating.distribution.map((d) => (
                    <div key={d.value} className="grid grid-cols-[6.5rem_1fr_4.5rem] items-center gap-3 text-sm">
                        <dt className="text-gray-700"><span className="tabular-nums">{d.value}</span> · {d.label}</dt>
                        <dd className="h-2.5 overflow-hidden rounded-full bg-gray-100" aria-hidden="true">
                            <div className="h-full rounded-full bg-hospitalblue" style={{ width: `${(d.count / max) * 100}%` }} />
                        </dd>
                        <dd className="text-right tabular-nums text-hospitalgray">{d.count} <span className="text-xs">({pct(d.count, total)}%)</span></dd>
                    </div>
                ))}
            </dl>
        </div>
    );
}

export default function Survey({ course, invited, total, ratings, recommend, comments }) {
    return (
        <AppLayout>
            <CourseHeader course={course} tab="encuesta" />

            {invited === 0 ? (
                <p className="mt-6 rounded-xl border border-gray-200 bg-white px-5 py-12 text-center text-sm text-hospitalgray">
                    Todavía no se emitieron certificados con encuesta en este curso.
                    La encuesta llega por correo junto con el certificado.
                </p>
            ) : (
                <>
                    <div className="mt-6 grid gap-3 sm:grid-cols-3">
                        <Stat label="Respuestas" value={total} detail={`de ${invited} (${pct(total, invited)}%)`} />
                        <Stat label="Calificación general" value={ratings[0]?.average?.toLocaleString('es-AR') ?? '—'} detail="/ 5" />
                        <Stat label="Lo recomendarían" value={total ? `${pct(recommend.yes, total)}%` : '—'}
                              detail={total ? `${recommend.yes} de ${total}` : undefined} />
                    </div>

                    {total === 0 ? (
                        <p className="mt-6 rounded-xl border border-gray-200 bg-white px-5 py-12 text-center text-sm text-hospitalgray">
                            Nadie respondió todavía. Los resultados aparecen acá a medida que llegan las respuestas.
                        </p>
                    ) : (
                        <>
                            <div className="mt-6 grid gap-4 lg:grid-cols-2">
                                {ratings.map((r) => <RatingCard key={r.key} rating={r} total={total} />)}
                            </div>

                            <section className="mt-8">
                                <h2 className="text-lg font-bold text-gray-900">Comentarios <span className="text-hospitalgray">· {comments.length}</span></h2>
                                <p className="mt-1 text-sm text-hospitalgray">Se muestran sin el nombre de quien respondió.</p>
                                {comments.length === 0 ? (
                                    <p className="mt-3 text-sm text-hospitalgray">Nadie dejó comentarios.</p>
                                ) : (
                                    <ul className="mt-3 divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white">
                                        {comments.map((c) => (
                                            <li key={c.id} className="px-5 py-4">
                                                <p className="whitespace-pre-line text-gray-800">{c.comment}</p>
                                                <p className="mt-1.5 text-xs tabular-nums text-hospitalgray">{c.date} · Calificación general {c.overall}/5</p>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </section>
                        </>
                    )}
                </>
            )}
        </AppLayout>
    );
}
