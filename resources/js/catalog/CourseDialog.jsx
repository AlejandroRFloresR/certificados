import { useEffect, useRef } from 'react';
import { StatusBadge } from './CourseCard';
import { formatRange } from './utils';
import { CalendarIcon, CheckIcon, ClockIcon, CloseIcon, MonitorIcon, PinIcon, UserIcon } from './Icons';

function Detail({ icon: Icon, label, children }) {
    return (
        <div className="flex gap-3">
            <Icon className="mt-0.5 h-[18px] w-[18px] shrink-0 text-hospitalblue" />
            <div>
                <dt className="text-xs font-semibold uppercase tracking-wide text-hospitalgray">{label}</dt>
                <dd className="mt-0.5 text-sm text-gray-900">{children}</dd>
            </div>
        </div>
    );
}

function EnrollAction({ course, auth, loginUrl, registerUrl, csrf }) {
    if (course.status === 'finalizado') {
        return <p className="text-sm text-hospitalgray">Este curso ya finalizó.</p>;
    }

    if (course.enrolled) {
        return (
            <span className="inline-flex items-center gap-1.5 rounded-lg bg-green-50 px-3 py-2 text-sm font-semibold text-green-800">
                <CheckIcon className="h-4 w-4" /> Ya estás inscripto
            </span>
        );
    }

    if (!auth) {
        return (
            <div className="flex flex-wrap items-center gap-3">
                <a href={loginUrl}
                   className="rounded-lg bg-hospitalblue px-4 py-2.5 text-sm font-semibold text-white hover:bg-hospitalblue-dark">
                    Iniciá sesión para inscribirte
                </a>
                {registerUrl && (
                    <a href={registerUrl} className="text-sm font-semibold text-hospitalblue hover:underline">
                        Crear cuenta
                    </a>
                )}
            </div>
        );
    }

    return (
        <form method="POST" action={course.enroll_url}>
            <input type="hidden" name="_token" value={csrf} />
            <button type="submit"
                    className="rounded-lg bg-hospitalblue px-4 py-2.5 text-sm font-semibold text-white hover:bg-hospitalblue-dark">
                Inscribirme
            </button>
        </form>
    );
}

export default function CourseDialog({ course, modalities, onClose, ...authProps }) {
    const ref = useRef(null);

    useEffect(() => {
        const dialog = ref.current;
        if (course && !dialog.open) dialog.showModal();
        if (!course && dialog.open) dialog.close();
    }, [course]);

    // Cerrar al hacer click en el fondo
    const handleClick = (e) => {
        if (e.target === ref.current) onClose();
    };

    const modality = course && modalities[course.modality];

    return (
        <dialog
            ref={ref}
            onClose={onClose}
            onClick={handleClick}
            aria-labelledby="course-dialog-title"
            className="w-[calc(100%-2rem)] max-w-lg rounded-xl p-0 shadow-2xl backdrop:bg-hospitalblue-dark/60"
        >
            {course && (
                <div>
                    <div className="bg-gradient-to-br from-hospitalblue to-hospitalblue-dark px-5 py-5 sm:px-6">
                        <div className="flex items-start justify-between gap-4">
                            <div>
                                {course.category && (
                                    <span className="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-white/70">
                                        <span className="h-1.5 w-1.5 rounded-full bg-hospitalbrown" />
                                        {course.category}
                                    </span>
                                )}
                                <h2 id="course-dialog-title" className="mt-1 text-lg font-bold leading-snug text-white">
                                    {course.title}
                                </h2>
                            </div>
                            <button type="button" onClick={onClose} aria-label="Cerrar"
                                    className="-mr-1 rounded-md p-1 text-white/70 hover:bg-white/10 hover:text-white">
                                <CloseIcon className="h-5 w-5" />
                            </button>
                        </div>
                        <div className="mt-3">
                            <StatusBadge status={course.status} onDark />
                        </div>
                    </div>

                    <div className="max-h-[60vh] overflow-y-auto px-5 py-5 sm:px-6">
                        {course.description && (
                            <p className="whitespace-pre-line text-sm leading-relaxed text-gray-700">
                                {course.description}
                            </p>
                        )}

                        <dl className={`grid gap-4 sm:grid-cols-2 ${course.description ? 'mt-5' : ''}`}>
                            <Detail icon={CalendarIcon} label="Fechas">
                                <span className="tabular-nums">{formatRange(course.start_date, course.end_date)}</span>
                            </Detail>
                            {course.hours && (
                                <Detail icon={ClockIcon} label="Carga horaria">
                                    <span className="tabular-nums">{course.hours} horas</span>
                                </Detail>
                            )}
                            {modality && (
                                <Detail icon={course.modality === 'virtual' ? MonitorIcon : PinIcon} label="Modalidad">
                                    {modality}
                                    {course.location && <span className="block text-hospitalgray">{course.location}</span>}
                                </Detail>
                            )}
                            {course.tutors.length > 0 && (
                                <Detail icon={UserIcon} label={course.tutors.length > 1 ? 'Docentes' : 'Docente'}>
                                    {course.tutors.join(', ')}
                                </Detail>
                            )}
                        </dl>
                    </div>

                    <div className="border-t border-gray-100 bg-gray-50 px-5 py-4 sm:px-6">
                        <EnrollAction course={course} {...authProps} />
                    </div>
                </div>
            )}
        </dialog>
    );
}
