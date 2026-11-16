import { useEffect, useMemo, useRef, useState } from 'react';
import FileDrop from './FileDrop';
import Checkbox from './Checkbox';
import { cleanSignature } from './signatureCleaner';

// Cuadriculado que deja ver qué quedó transparente
const CHECKERBOARD = {
    backgroundImage: 'repeating-conic-gradient(#e5e7eb 0% 25%, #ffffff 0% 50%)',
    backgroundSize: '16px 16px',
};

const INK_OPTIONS = [
    { value: 'original', label: 'Original' },
    { value: 'black', label: 'Negra' },
    { value: 'blue', label: 'Azul' },
];

function Preview({ title, url, emptyText, transparent = false, highlight = false, children }) {
    return (
        <div>
            <p className="text-xs font-bold uppercase tracking-widest text-hospitalgray">{title}</p>
            <div className={`mt-2 flex h-36 items-center justify-center overflow-hidden rounded-xl border p-3 ${highlight ? 'border-2 border-hospitalblue/60' : 'border-gray-200'}`}
                 style={transparent ? CHECKERBOARD : { background: '#F9FAFB' }}>
                {url
                    ? <img src={url} alt={title} className="max-h-full max-w-full object-contain" />
                    : <span className="text-sm text-hospitalgray">{emptyText}</span>}
            </div>
            {children}
        </div>
    );
}

/**
 * Subida de firma con limpieza de fondo en el navegador.
 * `onChange` recibe el File que se va a subir (ya procesado) o null.
 */
export default function SignatureInput({ id, label, required, currentUrl, onChange, error, progress }) {
    const [original, setOriginal] = useState(null);
    const [enabled, setEnabled] = useState(true);
    const [strength, setStrength] = useState(50);
    const [ink, setInk] = useState('original');
    const [result, setResult] = useState(null);
    const [working, setWorking] = useState(false);
    const [warning, setWarning] = useState(null);

    // onChange cambia en cada render (viene de setData): se lee desde una ref
    const onChangeRef = useRef(onChange);
    onChangeRef.current = onChange;

    const originalUrl = useMemo(() => (original ? URL.createObjectURL(original) : null), [original]);
    const resultUrl = useMemo(() => (result ? URL.createObjectURL(result) : null), [result]);
    useEffect(() => () => originalUrl && URL.revokeObjectURL(originalUrl), [originalUrl]);
    useEffect(() => () => resultUrl && URL.revokeObjectURL(resultUrl), [resultUrl]);

    // Reprocesa al cambiar la imagen o los ajustes (con una pequeña espera para el deslizador)
    useEffect(() => {
        if (!original) {
            setResult(null);
            setWarning(null);
            onChangeRef.current(null);
            return undefined;
        }
        if (!enabled) {
            setResult(original);
            setWarning(null);
            onChangeRef.current(original);
            return undefined;
        }

        let cancelled = false; // descarta resultados viejos si el deslizador se movió mientras procesaba
        const timer = setTimeout(async () => {
            setWorking(true);
            try {
                const cleaned = await cleanSignature(original, { strength, ink });
                if (cancelled) return;
                setResult(cleaned);
                setWarning(cleaned ? null : 'No quedó ningún trazo: bajá la limpieza o probá con otra imagen.');
                onChangeRef.current(cleaned);
            } catch {
                if (cancelled) return;
                setResult(null);
                setWarning('No se pudo procesar la imagen. Probá con otro archivo.');
                onChangeRef.current(null);
            } finally {
                if (!cancelled) setWorking(false);
            }
        }, 120);

        return () => { cancelled = true; clearTimeout(timer); };
    }, [original, enabled, strength, ink]);

    return (
        <div className="space-y-5">
            <FileDrop id={id} label={label} required={required} file={original} onChange={setOriginal}
                      accept=".png,.jpg,.jpeg" acceptLabel="PNG o JPG · foto o escaneo de la firma en hoja blanca"
                      progress={progress} error={error} />

            {original ? (
                <>
                    <div className="space-y-4 rounded-xl border border-gray-200 bg-gray-50 p-4">
                        <Checkbox id={`${id}-clean`} label="Quitar el fondo automáticamente" checked={enabled} onChange={setEnabled} />

                        {enabled && (
                            <div className="grid gap-5 sm:grid-cols-2">
                                <div>
                                    <div className="flex items-baseline justify-between">
                                        <label htmlFor={`${id}-strength`} className="text-sm font-semibold text-gray-900">Intensidad de limpieza</label>
                                        <span className="text-sm tabular-nums text-hospitalgray">{strength}</span>
                                    </div>
                                    <input id={`${id}-strength`} type="range" min="0" max="100" value={strength}
                                           onChange={(e) => setStrength(Number(e.target.value))}
                                           className="mt-2 w-full accent-hospitalblue" />
                                    <p className="mt-1 text-sm text-hospitalgray">Subila si queda suciedad del papel; bajala si se borra parte de la firma.</p>
                                </div>
                                <fieldset>
                                    <legend className="text-sm font-semibold text-gray-900">Color de la tinta</legend>
                                    <div className="mt-2 inline-flex rounded-lg border border-gray-300 bg-white p-1">
                                        {INK_OPTIONS.map((o) => (
                                            <label key={o.value}
                                                   className={`cursor-pointer rounded-md px-3.5 py-2 text-sm font-semibold focus-within:ring-2 focus-within:ring-hospitalblue ${ink === o.value ? 'bg-hospitalblue text-white' : 'text-gray-700 hover:bg-gray-100'}`}>
                                                <input type="radio" name={`${id}-ink`} value={o.value} checked={ink === o.value}
                                                       onChange={() => setInk(o.value)} className="sr-only" />
                                                {o.label}
                                            </label>
                                        ))}
                                    </div>
                                    <p className="mt-1 text-sm text-hospitalgray">Negra o azul unifican la tinta si la foto salió grisácea.</p>
                                </fieldset>
                            </div>
                        )}
                    </div>

                    <div className={`grid gap-4 ${currentUrl ? 'sm:grid-cols-3' : 'sm:grid-cols-2'}`}>
                        {currentUrl && <Preview title="Actual" url={currentUrl} transparent />}
                        <Preview title="Original" url={originalUrl} />
                        <Preview title={working ? 'Resultado (procesando…)' : 'Resultado'} url={resultUrl}
                                 emptyText="Sin trazos" transparent highlight>
                            {result && !warning && (
                                <p className="mt-1.5 text-sm text-hospitalgray">
                                    {enabled ? 'Los cuadritos son la parte transparente.' : 'Se sube la imagen tal cual.'}
                                </p>
                            )}
                        </Preview>
                    </div>

                    {warning && <p role="alert" className="text-sm font-semibold text-amber-800">{warning}</p>}
                </>
            ) : (
                currentUrl && (
                    <div className="sm:w-1/2">
                        <Preview title="Firma actual" url={currentUrl} transparent />
                    </div>
                )
            )}
        </div>
    );
}
