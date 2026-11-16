import { useState } from 'react';
import TextField from './TextField';

/** Campo de contraseña con botón para mostrarla u ocultarla */
export default function PasswordField(props) {
    const [visible, setVisible] = useState(false);

    const toggle = (
        <button type="button" onClick={() => setVisible((v) => !v)}
                aria-label={visible ? 'Ocultar contraseña' : 'Mostrar contraseña'} aria-pressed={visible}
                className="flex h-11 w-11 shrink-0 items-center justify-center text-hospitalgray hover:text-gray-900 focus:outline-none focus-visible:text-hospitalblue">
            <svg aria-hidden="true" className="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                {visible
                    ? <path d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.2A10.4 10.4 0 0112 5c5 0 9 4.5 10 7a12.6 12.6 0 01-3 4.2M6.3 6.4C4.2 7.8 2.7 9.9 2 12c1 2.5 5 7 10 7a9.9 9.9 0 004.1-.9" />
                    : <><path d="M2 12c1-2.5 5-7 10-7s9 4.5 10 7c-1 2.5-5 7-10 7S3 14.5 2 12z" /><circle cx="12" cy="12" r="3" /></>}
            </svg>
        </button>
    );

    return <TextField {...props} type={visible ? 'text' : 'password'} trailing={toggle} />;
}

/**
 * Requisitos de una contraseña nueva, marcados en verde a medida que se cumplen.
 * No se muestra mientras ambos campos estén vacíos.
 */
export function PasswordRequirements({ password, confirmation, min = 8 }) {
    if (!password && !confirmation) return null;

    const rules = [
        { ok: password.length >= min, label: `Al menos ${min} caracteres` },
        { ok: password !== '' && password === confirmation, label: 'Las dos coinciden' },
    ];

    return (
        <ul aria-label="Requisitos de la contraseña" className="flex flex-wrap gap-x-5 gap-y-1.5 text-sm">
            {rules.map((r) => (
                <li key={r.label} className={`flex items-center gap-1.5 ${r.ok ? 'font-semibold text-green-800' : 'text-hospitalgray'}`}>
                    <svg aria-hidden="true" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth={r.ok ? 2.6 : 2} strokeLinecap="round" strokeLinejoin="round">
                        {r.ok ? <path d="M5 12.5l4.5 4.5L19 7.5" /> : <circle cx="12" cy="12" r="7" />}
                    </svg>
                    {r.label}
                    <span className="sr-only">{r.ok ? '(cumplido)' : '(pendiente)'}</span>
                </li>
            ))}
        </ul>
    );
}
