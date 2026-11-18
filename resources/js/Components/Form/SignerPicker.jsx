import FieldLabel, { FieldMessage } from './FieldLabel';

/**
 * Elegir hasta `max` firmantes y su orden de impresión.
 * signers: [{ id, name, position, signature_url }]; value: ids elegidos, en orden.
 */
export default function SignerPicker({ id, label, signers, value, onChange, max = 3, error }) {
    const full = value.length >= max;

    const toggle = (signerId) =>
        onChange(value.includes(signerId) ? value.filter((v) => v !== signerId) : [...value, signerId]);

    const move = (signerId, delta) => {
        const i = value.indexOf(signerId);
        const j = i + delta;
        if (j < 0 || j >= value.length) return;
        const next = [...value];
        [next[i], next[j]] = [next[j], next[i]];
        onChange(next);
    };

    // Elegidos primero (en su orden) y después el resto
    const ordered = [
        ...value.map((v) => signers.find((s) => s.id === v)).filter(Boolean),
        ...signers.filter((s) => !value.includes(s.id)),
    ];

    return (
        <fieldset id={id}>
            <legend className="w-full">
                <span className="flex items-baseline justify-between gap-3">
                    <FieldLabel as="span">{label}</FieldLabel>
                    <span className="text-sm tabular-nums text-hospitalgray">{value.length} de {max}</span>
                </span>
            </legend>

            <ul className="mt-2.5 space-y-2">
                {ordered.map((s) => {
                    const position = value.indexOf(s.id);
                    const checked = position !== -1;
                    const disabled = !checked && full;

                    return (
                        <li key={s.id}
                            className={`flex items-center gap-3 rounded-xl border p-3 ${checked ? 'border-2 border-hospitalblue bg-hospitalblue/5 p-[11px]' : 'border-gray-300 bg-white'} ${disabled ? 'opacity-50' : ''}`}>
                            <input id={`${id}-${s.id}`} type="checkbox" checked={checked} disabled={disabled} onChange={() => toggle(s.id)}
                                   className="h-[18px] w-[18px] shrink-0 rounded border-2 border-hospitalgray text-hospitalblue focus:ring-4 focus:ring-hospitalblue/20" />
                            <label htmlFor={`${id}-${s.id}`} className={`flex min-w-0 flex-1 items-center gap-3 ${disabled ? 'cursor-not-allowed' : 'cursor-pointer'}`}>
                                <span className="flex h-12 w-28 shrink-0 items-center justify-center rounded-lg border border-gray-100 bg-white p-1">
                                    <img src={s.signature_url} alt="" className="max-h-full max-w-full object-contain" />
                                </span>
                                <span className="min-w-0">
                                    <span className="block truncate font-semibold text-gray-900">{s.name}</span>
                                    {s.position && <span className="block truncate text-sm text-hospitalgray">{s.position}</span>}
                                </span>
                            </label>

                            {checked && (
                                <span className="flex shrink-0 items-center gap-1">
                                    <span className="mr-1 rounded-full bg-hospitalblue px-2 py-0.5 text-xs font-bold tabular-nums text-white" aria-label={`Se imprime en el lugar ${position + 1}`}>
                                        {position + 1}°
                                    </span>
                                    {value.length > 1 && (
                                        <>
                                            <button type="button" onClick={() => move(s.id, -1)} disabled={position === 0}
                                                    aria-label={`Subir a ${s.name}`}
                                                    className="flex h-9 w-9 items-center justify-center rounded-lg text-gray-600 hover:bg-white disabled:opacity-30">
                                                <svg aria-hidden="true" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round"><path d="M6 15l6-6 6 6" /></svg>
                                            </button>
                                            <button type="button" onClick={() => move(s.id, 1)} disabled={position === value.length - 1}
                                                    aria-label={`Bajar a ${s.name}`}
                                                    className="flex h-9 w-9 items-center justify-center rounded-lg text-gray-600 hover:bg-white disabled:opacity-30">
                                                <svg aria-hidden="true" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round"><path d="M6 9l6 6 6-6" /></svg>
                                            </button>
                                        </>
                                    )}
                                </span>
                            )}
                        </li>
                    );
                })}
            </ul>

            {full && signers.length > max && (
                <p className="mt-2 text-sm text-hospitalgray">Ya elegiste {max}: para cambiar uno, primero desmarcá otro.</p>
            )}
            <FieldMessage id={`${id}-message`} error={error} />
        </fieldset>
    );
}
