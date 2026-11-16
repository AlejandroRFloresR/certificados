import FieldLabel, { FieldMessage, controlClass } from './FieldLabel';

/**
 * Campo de texto del kit.
 * - `id` debe coincidir con la clave del error de Laravel (así focusFirstError lo encuentra).
 * - `multiline` lo convierte en textarea; con `maxLength` muestra el contador.
 * - `suffix` agrega una etiqueta fija a la derecha ("horas", "puntos"…).
 * - `trailing` agrega un elemento dentro del campo, a la derecha (ej.: botón mostrar contraseña).
 * - `label` vacío: el campo no muestra etiqueta propia (usar `aria-label`).
 */
export default function TextField({
    id, label, value, onChange, error, hint, required = false, disabled = false,
    multiline = false, rows = 4, maxLength, suffix, trailing, className = '', ...props
}) {
    const messageId = `${id}-message`;
    const shared = {
        id,
        value: value ?? '',
        onChange: (e) => onChange(e.target.value),
        required,
        disabled,
        maxLength,
        'aria-invalid': error ? true : undefined,
        'aria-describedby': error || hint ? messageId : undefined,
        ...props,
    };
    const length = (value ?? '').length;

    return (
        <div className={className}>
            {label && (
                <div className="mb-1.5 flex items-baseline justify-between gap-3">
                    <FieldLabel htmlFor={id} required={required}>{label}</FieldLabel>
                    {multiline && maxLength && (
                        <span className={`text-xs tabular-nums ${length > maxLength * 0.9 ? 'text-amber-800' : 'text-hospitalgray'}`}>
                            {length} / {maxLength}
                        </span>
                    )}
                </div>
            )}

            <div className={`flex overflow-hidden rounded-lg border focus-within:border-hospitalblue focus-within:ring-4 focus-within:ring-hospitalblue/15 ${controlClass({ error, disabled })}`}>
                {multiline ? (
                    <textarea {...shared} rows={rows}
                              className="block w-full resize-y border-0 bg-transparent px-3.5 py-2.5 text-[15px] leading-relaxed focus:ring-0" />
                ) : (
                    <input {...shared}
                           className="block min-h-[44px] w-full min-w-0 border-0 bg-transparent px-3.5 text-[15px] focus:ring-0 disabled:cursor-not-allowed" />
                )}
                {trailing}
                {suffix && (
                    <span className="flex items-center border-l border-gray-200 bg-gray-50 px-3.5 text-sm font-semibold text-hospitalgray">{suffix}</span>
                )}
            </div>

            <FieldMessage id={messageId} hint={hint} error={error} />
        </div>
    );
}
