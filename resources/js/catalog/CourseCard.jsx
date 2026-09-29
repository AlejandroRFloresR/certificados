import { STATUS, formatRange } from './utils';
import { CalendarIcon, ClockIcon, CheckIcon, MonitorIcon, PinIcon } from './Icons';

export function StatusBadge({ status, onDark = false }) {
    const s = STATUS[status] ?? STATUS.sin_fecha;
    const colors = onDark ? 'bg-white/15 text-white' : s.className;
    return (
        <span className={`inline-flex shrink-0 rounded-full px-2 py-0.5 text-xs font-semibold ${colors}`}>
            {s.label}
        </span>
    );
}

export default function CourseCard({ course, modalities, onOpen }) {
    const modality = modalities[course.modality];

    return (
        <article className="group flex flex-col rounded-xl border border-gray-200 bg-white shadow-sm transition hover:border-hospitalblue/40 hover:shadow-md">
            <button
                type="button"
                onClick={() => onOpen(course)}
                className="flex flex-1 flex-col p-5 text-left focus:outline-none focus-visible:ring-2 focus-visible:ring-hospitalblue focus-visible:ring-offset-2 rounded-xl"
            >
                <div className="flex items-start justify-between gap-3">
                    <span className="inline-flex min-h-[1.25rem] items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-hospitalgray">
                        {course.category && (
                            <>
                                <span className="h-1.5 w-1.5 rounded-full bg-hospitalbrown" />
                                {course.category}
                            </>
                        )}
                    </span>
                    <StatusBadge status={course.status} />
                </div>

                <h3 className="mt-2 text-base font-semibold leading-snug text-gray-900 group-hover:text-hospitalblue">
                    {course.title}
                </h3>

                {course.description && (
                    <p className="mt-1.5 line-clamp-2 text-sm text-gray-600">{course.description}</p>
                )}

                <dl className="mt-4 space-y-1.5 text-sm text-hospitalgray">
                    <div className="flex items-center gap-2">
                        <CalendarIcon className="h-4 w-4 shrink-0" />
                        <dt className="sr-only">Fechas</dt>
                        <dd className="tabular-nums">{formatRange(course.start_date, course.end_date)}</dd>
                    </div>
                    {course.hours && (
                        <div className="flex items-center gap-2">
                            <ClockIcon className="h-4 w-4 shrink-0" />
                            <dt className="sr-only">Carga horaria</dt>
                            <dd className="tabular-nums">{course.hours} h</dd>
                        </div>
                    )}
                    {modality && (
                        <div className="flex items-center gap-2">
                            {course.modality === 'virtual'
                                ? <MonitorIcon className="h-4 w-4 shrink-0" />
                                : <PinIcon className="h-4 w-4 shrink-0" />}
                            <dt className="sr-only">Modalidad</dt>
                            <dd className="truncate">
                                {modality}{course.location ? ` · ${course.location}` : ''}
                            </dd>
                        </div>
                    )}
                </dl>
            </button>

            <div className="flex items-center justify-between border-t border-gray-100 px-5 py-3">
                {course.enrolled ? (
                    <span className="inline-flex items-center gap-1 text-xs font-semibold text-green-700">
                        <CheckIcon className="h-3.5 w-3.5" /> Ya estás inscripto
                    </span>
                ) : <span />}
                <button
                    type="button"
                    onClick={() => onOpen(course)}
                    className="text-sm font-semibold text-hospitalblue hover:underline"
                >
                    Ver detalle →
                </button>
            </div>
        </article>
    );
}
