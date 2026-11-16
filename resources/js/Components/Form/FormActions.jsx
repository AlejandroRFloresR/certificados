import { Link } from '@inertiajs/react';
import useUnsavedChanges from './useUnsavedChanges';

/**
 * Barra de acciones del formulario, con el estado de los cambios.
 * Recibe el `form` de useForm y activa el aviso de cambios sin guardar.
 * - Por defecto queda fija abajo de la pantalla; con `inline` va al pie de la tarjeta
 *   (para páginas con más de un formulario, como el perfil).
 * - `cancelHref` vuelve a otra página; `onCancel` ejecuta una acción (ej.: descartar cambios).
 */
export default function FormActions({
    form, cancelHref, onCancel, submitLabel = 'Guardar cambios', processingLabel = 'Guardando…',
    alwaysEnabled = false, inline = false,
}) {
    useUnsavedChanges(form.isDirty && !form.processing);

    const errorCount = Object.keys(form.errors).length;
    const canSubmit = !form.processing && (alwaysEnabled || form.isDirty);

    let status;
    if (form.processing) {
        status = <span className="text-sm text-hospitalgray">{processingLabel}</span>;
    } else if (form.isDirty || errorCount > 0) {
        status = (
            <span className="inline-flex items-center gap-2 text-sm font-semibold text-[#6E4F22]">
                <span aria-hidden="true" className="h-2 w-2 rounded-full bg-hospitalbrown" />
                {form.isDirty ? 'Cambios sin guardar' : 'Revisá los campos marcados'}
                {errorCount > 0 && ` · ${errorCount} ${errorCount === 1 ? 'campo con error' : 'campos con error'}`}
            </span>
        );
    } else {
        status = <span className="text-sm text-hospitalgray">Sin cambios</span>;
    }

    const cancelClass = 'inline-flex min-h-[44px] items-center rounded-lg border border-gray-300 bg-white px-4 text-sm font-semibold text-gray-800 hover:bg-gray-50 disabled:opacity-50';

    const content = (
        <>
            <div aria-live="polite">{status}</div>
            <div className="flex gap-2.5">
                {cancelHref && <Link href={cancelHref} className={cancelClass}>Cancelar</Link>}
                {onCancel && (
                    <button type="button" onClick={onCancel} disabled={!form.isDirty || form.processing} className={cancelClass}>
                        Descartar
                    </button>
                )}
                <button type="submit" disabled={!canSubmit}
                        className="inline-flex min-h-[44px] items-center gap-2 rounded-lg bg-hospitalblue px-5 text-sm font-semibold text-white hover:bg-hospitalblue-dark disabled:cursor-not-allowed disabled:bg-gray-300 disabled:text-gray-700">
                    {form.processing && (
                        <svg aria-hidden="true" className="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round">
                            <path d="M21 12a9 9 0 11-6.2-8.6" />
                        </svg>
                    )}
                    {form.processing ? processingLabel : submitLabel}
                </button>
            </div>
        </>
    );

    if (inline) {
        return <div className="flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">{content}</div>;
    }

    return (
        <div className="sticky bottom-0 z-10 -mx-4 mt-10 border-t border-gray-200 bg-white/95 shadow-[0_-6px_18px_rgba(0,34,68,0.06)] backdrop-blur sm:-mx-8">
            <div className="mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-3 px-4 py-3.5 sm:px-8">
                {content}
            </div>
        </div>
    );
}
