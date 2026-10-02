import { Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/Button';

function SignaturePreview({ url }) {
    return url ? (
        <img src={url} alt="Firma" className="h-12 max-w-[10rem] rounded border border-gray-100 bg-white object-contain p-1" />
    ) : (
        <span className="inline-flex rounded-full bg-amber-50 px-2 py-0.5 text-xs font-semibold text-amber-700">Sin firma</span>
    );
}

function TutorActions({ tutor }) {
    const linkClass = 'rounded-md px-2.5 py-1.5 text-sm font-semibold text-hospitalblue hover:bg-hospitalblue/10';
    return (
        <div className="flex flex-wrap items-center justify-end gap-1">
            <Link href={route('tutors.editCourses', tutor.id)} className={linkClass}>Cursos</Link>
            <Link href={route('admin.tutors.signature.edit', tutor.id)} className={linkClass}>Firma</Link>
        </div>
    );
}

export default function Index({ tutors }) {
    const withoutSignature = tutors.filter((t) => !t.signature_url).length;

    return (
        <AppLayout
            title="Tutores"
            actions={
                // Un tutor es un usuario con rol tutor: se crea desde Usuarios
                <Link href={route('admin.users.create')}>
                    <Button type="button">+ Crear tutor</Button>
                </Link>
            }
        >
            {withoutSignature > 0 && (
                <div className="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">
                    {withoutSignature === 1 ? '1 tutor no tiene' : `${withoutSignature} tutores no tienen`} firma cargada.
                    Sus certificados se van a emitir sin firma.
                </div>
            )}

            <div className="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                {tutors.length === 0 ? (
                    <p className="px-5 py-12 text-center text-sm text-hospitalgray">
                        No hay tutores cargados aún. Creá un usuario con rol Tutor.
                    </p>
                ) : (
                    <>
                        <table className="hidden w-full text-sm md:table">
                            <thead>
                                <tr className="bg-gray-50 text-left text-xs font-semibold uppercase tracking-wide text-hospitalgray">
                                    <th className="px-5 py-2.5">Tutor</th>
                                    <th className="px-5 py-2.5">Firma</th>
                                    <th className="px-5 py-2.5">Cursos</th>
                                    <th className="px-5 py-2.5 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {tutors.map((t) => (
                                    <tr key={t.id} className="align-top">
                                        <td className="px-5 py-3">
                                            <p className="font-medium text-gray-900">{t.name}</p>
                                            <p className="text-xs text-hospitalgray">{t.email}</p>
                                        </td>
                                        <td className="px-5 py-3"><SignaturePreview url={t.signature_url} /></td>
                                        <td className="px-5 py-3 text-xs text-gray-700">
                                            {t.courses.length ? t.courses.join(', ') : <span className="text-gray-400">Sin cursos asignados</span>}
                                        </td>
                                        <td className="px-5 py-3"><TutorActions tutor={t} /></td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        <div className="divide-y divide-gray-100 md:hidden">
                            {tutors.map((t) => (
                                <div key={t.id} className="p-4">
                                    <div className="flex items-start justify-between gap-3">
                                        <div className="min-w-0">
                                            <p className="text-sm font-medium text-gray-900">{t.name}</p>
                                            <p className="truncate text-xs text-hospitalgray">{t.email}</p>
                                        </div>
                                        <SignaturePreview url={t.signature_url} />
                                    </div>
                                    <p className="mt-2 text-xs text-gray-600">
                                        {t.courses.length ? t.courses.join(', ') : 'Sin cursos asignados'}
                                    </p>
                                    <div className="mt-2 -mr-2.5"><TutorActions tutor={t} /></div>
                                </div>
                            ))}
                        </div>
                    </>
                )}
            </div>
        </AppLayout>
    );
}
