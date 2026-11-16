import { useState } from 'react';
import {
    Combobox as HeadlessCombobox, ComboboxButton, ComboboxInput, ComboboxOption, ComboboxOptions, Field, Label,
} from '@headlessui/react';
import FieldLabel, { FieldMessage, controlClass } from './FieldLabel';
import { normalize } from '@/Components/Catalog/utils';

/**
 * Lista con buscador sobre una lista de textos.
 * Con `creatable`, si lo escrito no existe ofrece «Crear …» y devuelve ese texto nuevo.
 * El valor es un string (o '' si no hay selección).
 */
export default function Combobox({
    id, label, options, value, onChange, error, hint, required = false,
    creatable = false, createLabel = 'Crear', placeholder, emptyText = 'No hay coincidencias.',
}) {
    const [query, setQuery] = useState('');

    const q = normalize(query.trim());
    const filtered = q ? options.filter((o) => normalize(o).includes(q)) : options;
    // "enfermeria" coincide con "Enfermería": en ese caso no se ofrece crear otra
    const exists = options.some((o) => normalize(o) === q);
    const canCreate = creatable && q !== '' && !exists;

    const select = (selected) => {
        onChange(selected ?? '');
        setQuery('');
    };

    return (
        <Field className="relative">
            <FieldLabel as={Label} required={required}>{label}</FieldLabel>

            <HeadlessCombobox value={value || null} onChange={select} onClose={() => setQuery('')}>
                <div className={`mt-1.5 flex items-center rounded-lg border focus-within:border-hospitalblue focus-within:ring-4 focus-within:ring-hospitalblue/15 ${controlClass({ error })}`}>
                    <ComboboxInput
                        id={id}
                        placeholder={placeholder}
                        displayValue={(v) => v ?? ''}
                        onChange={(e) => setQuery(e.target.value)}
                        aria-invalid={error ? true : undefined}
                        className="block min-h-[44px] w-full min-w-0 border-0 bg-transparent px-3.5 text-[15px] focus:ring-0"
                    />
                    {value && (
                        <button type="button" onClick={() => select(null)} aria-label={`Quitar ${label.toLowerCase()}`}
                                className="flex h-11 w-9 items-center justify-center text-hospitalgray hover:text-gray-900">
                            <svg aria-hidden="true" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round">
                                <path d="M6 6l12 12M18 6L6 18" />
                            </svg>
                        </button>
                    )}
                    <ComboboxButton className="flex h-11 w-10 items-center justify-center text-hospitalgray" aria-label={`Ver ${label.toLowerCase()}`}>
                        <svg aria-hidden="true" className="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                            <path d="M8 9l4-4 4 4M8 15l4 4 4-4" />
                        </svg>
                    </ComboboxButton>
                </div>

                <ComboboxOptions
                    anchor="bottom start"
                    className="z-30 mt-1 max-h-72 w-[var(--input-width)] min-w-64 overflow-auto rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg empty:invisible focus:outline-none [--anchor-gap:4px]"
                >
                    {filtered.map((option) => (
                        <ComboboxOption key={option} value={option}
                                        className="group flex cursor-pointer items-center justify-between rounded-lg px-3 py-2.5 text-[15px] text-gray-900 data-[focus]:bg-hospitalblue/10">
                            {option}
                            <svg aria-hidden="true" className="invisible h-4 w-4 text-hospitalblue group-data-[selected]:visible" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.6" strokeLinecap="round" strokeLinejoin="round">
                                <path d="M5 12.5l4.5 4.5L19 7.5" />
                            </svg>
                        </ComboboxOption>
                    ))}

                    {filtered.length === 0 && !canCreate && (
                        <div className="px-3 py-2.5 text-sm text-hospitalgray">{emptyText}</div>
                    )}

                    {canCreate && (
                        <>
                            {filtered.length > 0 && <div className="mx-1.5 my-1 h-px bg-gray-100" />}
                            <ComboboxOption value={query.trim()}
                                            className="flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2.5 text-[15px] font-semibold text-hospitalblue data-[focus]:bg-hospitalblue/10">
                                <svg aria-hidden="true" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round">
                                    <path d="M12 5v14M5 12h14" />
                                </svg>
                                {createLabel} «{query.trim()}»
                            </ComboboxOption>
                        </>
                    )}
                </ComboboxOptions>
            </HeadlessCombobox>

            <FieldMessage id={`${id}-message`} hint={hint} error={error} />
        </Field>
    );
}
