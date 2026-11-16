import { Radio, RadioGroup } from '@headlessui/react';
import FieldLabel, { FieldMessage } from './FieldLabel';

/**
 * Opciones como tarjetas seleccionables.
 * options: [{ value, label, description?, icon? }]
 * `clearable`: muestra "Quitar selección" (para campos opcionales).
 * El grupo se nombra con la etiqueta; cada tarjeta, con su propio texto.
 */
export default function RadioCards({ id, label, options, value, onChange, error, required = false, clearable = false, columns = 3 }) {
    const gridCols = { 2: 'sm:grid-cols-2', 3: 'sm:grid-cols-3' }[columns] ?? 'sm:grid-cols-3';
    const labelId = `${id}-label`;

    return (
        <div>
            <div className="flex items-baseline justify-between gap-3">
                <FieldLabel as="p" id={labelId} required={required}>{label}</FieldLabel>
                {clearable && value && (
                    <button type="button" onClick={() => onChange('')} className="text-sm font-semibold text-hospitalblue hover:underline">
                        Quitar selección
                    </button>
                )}
            </div>

            <RadioGroup id={id} value={value || null} onChange={onChange} aria-labelledby={labelId}
                        className={`mt-2.5 grid gap-3 ${gridCols}`}>
                {options.map((option) => (
                    <Radio
                        key={option.value}
                        value={option.value}
                        aria-label={option.label}
                        aria-description={option.description}
                        className="group flex cursor-pointer flex-col gap-1.5 rounded-xl border border-gray-300 bg-white p-4 transition focus:outline-none data-[checked]:border-2 data-[checked]:border-hospitalblue data-[checked]:bg-hospitalblue/5 data-[checked]:p-[15px] data-[focus]:ring-4 data-[focus]:ring-hospitalblue/20 data-[hover]:border-gray-400"
                    >
                        <span className="flex items-center justify-between">
                            {option.icon
                                ? <option.icon className="h-[22px] w-[22px] text-hospitalgray group-data-[checked]:text-hospitalblue" aria-hidden="true" />
                                : <span />}
                            {/* Marca circular, como un radio nativo */}
                            <span aria-hidden="true"
                                  className="flex h-[18px] w-[18px] items-center justify-center rounded-full border-2 border-gray-400 group-data-[checked]:border-hospitalblue">
                                <span className="h-2 w-2 rounded-full bg-hospitalblue opacity-0 group-data-[checked]:opacity-100" />
                            </span>
                        </span>
                        <span className="text-[15px] font-bold text-gray-900">{option.label}</span>
                        {option.description && <span className="text-sm text-hospitalgray">{option.description}</span>}
                    </Radio>
                ))}
            </RadioGroup>

            <FieldMessage id={`${id}-message`} error={error} />
        </div>
    );
}
