import { useRef, useState } from 'react';
import FieldLabel, { FieldMessage } from './FieldLabel';

function formatSize(bytes) {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 * 1024) return `${Math.round(bytes / 1024)} KB`;
    return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
}

/**
 * Zona para arrastrar o elegir un archivo.
 * - `accept`: extensiones permitidas, ej. ".xlsx,.xls,.csv" (también se valida en el servidor).
 * - `progress`: el form.progress de Inertia, para la barra de subida.
 */
export default function FileDrop({ id, label, file, onChange, accept, acceptLabel, error, hint, required = false, progress }) {
    const inputRef = useRef(null);
    const [dragging, setDragging] = useState(false);
    const [typeError, setTypeError] = useState(null);

    const allowed = (accept ?? '').split(',').map((e) => e.trim().toLowerCase()).filter(Boolean);

    const pick = (selected) => {
        if (!selected) return;
        const ext = `.${selected.name.split('.').pop().toLowerCase()}`;
        if (allowed.length && !allowed.includes(ext)) {
            setTypeError(`"${selected.name}" no es un formato válido. Usá ${acceptLabel ?? allowed.join(', ')}.`);
            return;
        }
        setTypeError(null);
        onChange(selected);
    };

    const clear = () => {
        onChange(null);
        if (inputRef.current) inputRef.current.value = '';
    };

    const ext = file?.name.split('.').pop().toUpperCase().slice(0, 4);

    return (
        <div>
            {label && <FieldLabel htmlFor={id} required={required} className="mb-1.5">{label}</FieldLabel>}

            {file ? (
                <div className="flex items-center gap-3 rounded-xl border border-gray-300 bg-white px-3.5 py-3">
                    <span aria-hidden="true" className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-green-100 text-[11px] font-bold text-green-900">{ext}</span>
                    <span className="min-w-0 flex-1">
                        <span className="block truncate text-sm font-semibold text-gray-900">{file.name}</span>
                        <span className="block text-sm text-hospitalgray">
                            {formatSize(file.size)} · {progress ? `subiendo ${progress.percentage}%` : 'listo para subir'}
                        </span>
                    </span>
                    {!progress && (
                        <button type="button" onClick={clear} aria-label="Quitar archivo"
                                className="flex h-10 w-10 items-center justify-center rounded-lg text-hospitalgray hover:bg-gray-100 hover:text-gray-900">
                            <svg aria-hidden="true" className="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round">
                                <path d="M6 6l12 12M18 6L6 18" />
                            </svg>
                        </button>
                    )}
                </div>
            ) : (
                <label
                    htmlFor={id}
                    onDragOver={(e) => { e.preventDefault(); setDragging(true); }}
                    onDragLeave={() => setDragging(false)}
                    onDrop={(e) => { e.preventDefault(); setDragging(false); pick(e.dataTransfer.files[0]); }}
                    className={`flex cursor-pointer flex-col items-center gap-2 rounded-xl border-2 border-dashed px-5 py-6 text-center transition focus-within:ring-4 focus-within:ring-hospitalblue/20 ${
                        dragging ? 'border-hospitalblue bg-hospitalblue/5' : error || typeError ? 'border-red-600 bg-red-50/40' : 'border-gray-300 bg-gray-50 hover:border-gray-400'
                    }`}
                >
                    <svg aria-hidden="true" className="h-7 w-7 text-hospitalblue" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M12 15V4m0 0L8 8m4-4l4 4M5 15v3a2 2 0 002 2h10a2 2 0 002-2v-3" />
                    </svg>
                    <span className="text-[15px] font-semibold text-gray-900">
                        Arrastrá el archivo acá o <span className="text-hospitalblue underline">elegilo</span>
                    </span>
                    {acceptLabel && <span className="text-sm text-hospitalgray">{acceptLabel}</span>}
                    <input ref={inputRef} id={id} type="file" accept={accept} className="sr-only"
                           onChange={(e) => pick(e.target.files[0])} />
                </label>
            )}

            {progress && (
                <div className="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-100" aria-hidden="true">
                    <div className="h-full bg-hospitalblue transition-all" style={{ width: `${progress.percentage}%` }} />
                </div>
            )}

            <FieldMessage id={`${id}-message`} hint={hint} error={typeError ?? error} />
        </div>
    );
}
