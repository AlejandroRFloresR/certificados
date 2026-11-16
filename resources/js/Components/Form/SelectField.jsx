import FieldLabel, { FieldMessage, controlClass } from './FieldLabel';

/**
 * Lista desplegable nativa con el estilo del kit, para elegir por id (ej.: un curso).
 * options: [{ value, label }]. `placeholder` agrega una opción vacía al principio.
 */
export default function SelectField({ id, label, options, value, onChange, error, hint, required = false, placeholder }) {
    const messageId = `${id}-message`;

    return (
        <div>
            <FieldLabel htmlFor={id} required={required} className="mb-1.5">{label}</FieldLabel>
            <select
                id={id}
                value={value ?? ''}
                onChange={(e) => onChange(e.target.value)}
                aria-invalid={error ? true : undefined}
                aria-describedby={error || hint ? messageId : undefined}
                className={`block min-h-[44px] w-full rounded-lg border py-2 pl-3.5 pr-10 text-[15px] text-gray-900 focus:border-hospitalblue focus:ring-4 focus:ring-hospitalblue/15 ${controlClass({ error })}`}
            >
                {placeholder !== undefined && <option value="">{placeholder}</option>}
                {options.map((o) => <option key={o.value} value={o.value}>{o.label}</option>)}
            </select>
            <FieldMessage id={messageId} hint={hint} error={error} />
        </div>
    );
}
