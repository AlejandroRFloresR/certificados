/** Etiqueta de campo con marca de obligatorio (*) u opcional, según `required` */
export default function FieldLabel({ htmlFor, required = false, children, as: Tag = 'label', className = '', ...props }) {
    return (
        <Tag htmlFor={Tag === 'label' ? htmlFor : undefined} className={`block text-sm font-semibold text-gray-900 ${className}`} {...props}>
            {children}{' '}
            {required
                ? <span className="text-red-700" aria-hidden="true">*</span>
                : <span className="font-normal text-hospitalgray">(opcional)</span>}
        </Tag>
    );
}

/** Ayuda o error debajo de un campo. El error reemplaza a la ayuda. */
export function FieldMessage({ id, hint, error }) {
    if (error) {
        return (
            <p id={id} className="mt-1.5 flex items-start gap-1.5 text-sm font-semibold text-red-700">
                <svg aria-hidden="true" className="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round">
                    <circle cx="12" cy="12" r="9" /><path d="M12 7.5v5.5M12 16.5v.5" />
                </svg>
                {error}
            </p>
        );
    }
    return hint ? <p id={id} className="mt-1.5 text-sm text-hospitalgray">{hint}</p> : null;
}

/** Clases del borde de un control según su estado (normal / error / deshabilitado) */
export function controlClass({ error, disabled } = {}) {
    if (disabled) return 'border-gray-200 bg-gray-100 text-hospitalgray';
    if (error) return 'border-red-600 bg-red-50/40 focus-within:ring-red-600/15 focus:ring-red-600/15';
    return 'border-gray-300 bg-white';
}
