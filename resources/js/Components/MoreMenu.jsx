import { useEffect, useLayoutEffect, useRef, useState } from 'react';
import { createPortal } from 'react-dom';
import { Link } from '@inertiajs/react';

/**
 * Botón "⋯" con un menú de acciones secundarias.
 * items: [{ label, href?, onClick?, download?: true (archivo: <a>), danger?: true, disabled?: true, note? }]
 * Un item con label '-' dibuja un separador.
 * El menú se dibuja en <body> con posición fija: así no lo recortan las tablas con scroll
 * (abre hacia arriba si no entra abajo, y se cierra al hacer scroll).
 */
export default function MoreMenu({ label, items, className = '' }) {
    const [open, setOpen] = useState(false);
    const [position, setPosition] = useState(null);
    const buttonRef = useRef(null);
    const menuRef = useRef(null);
    const close = () => setOpen(false);

    // Ubica el menú junto al botón, alineado a su derecha
    useLayoutEffect(() => {
        if (!open) { setPosition(null); return; }
        const button = buttonRef.current.getBoundingClientRect();
        const height = menuRef.current?.offsetHeight ?? 0;
        const below = button.bottom + 6;
        const top = below + height > window.innerHeight - 8 && button.top - 6 - height > 8
            ? button.top - 6 - height
            : below;
        setPosition({ top, right: Math.max(8, window.innerWidth - button.right) });
    }, [open]);

    // Scroll o cambio de tamaño: se cierra (el botón se movió)
    useEffect(() => {
        if (!open) return undefined;
        const onMove = (e) => { if (!menuRef.current?.contains(e.target)) close(); };
        window.addEventListener('scroll', onMove, true);
        window.addEventListener('resize', close);
        return () => {
            window.removeEventListener('scroll', onMove, true);
            window.removeEventListener('resize', close);
        };
    }, [open]);

    const itemClass = (danger) =>
        `block w-full rounded-md px-3 py-2.5 text-left text-sm ${danger ? 'text-red-700 hover:bg-red-50' : 'text-gray-800 hover:bg-gray-50'}`;

    return (
        <div className={`relative ${className}`} onKeyDown={(e) => e.key === 'Escape' && close()}>
            <button
                ref={buttonRef}
                type="button"
                aria-label={label}
                aria-haspopup="menu"
                aria-expanded={open}
                onClick={(e) => { e.preventDefault(); e.stopPropagation(); setOpen(!open); }}
                className={`flex h-11 w-11 items-center justify-center rounded-lg border text-gray-600 hover:border-gray-300 hover:bg-white hover:text-gray-900 ${open ? 'border-gray-300 bg-white text-gray-900' : 'border-transparent'}`}
            >
                <svg aria-hidden="true" className="h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                    <circle cx="5" cy="12" r="1.8" /><circle cx="12" cy="12" r="1.8" /><circle cx="19" cy="12" r="1.8" />
                </svg>
            </button>

            {open && createPortal(
                <>
                    {/* Capa invisible: click afuera cierra el menú */}
                    <div className="fixed inset-0 z-40" onClick={(e) => { e.preventDefault(); e.stopPropagation(); close(); }} />
                    <div ref={menuRef} role="menu"
                         className="fixed z-50 w-56 rounded-xl border border-gray-200 bg-white p-1.5 shadow-lg"
                         style={position ? { top: position.top, right: position.right } : { top: -9999, right: 0, visibility: 'hidden' }}
                         onClick={(e) => e.stopPropagation()}>
                        {items.map((item, i) => {
                            if (item.label === '-') return <div key={i} className="mx-1.5 my-1 h-px bg-gray-100" />;

                            // Deshabilitado: se ve, y `note` explica por qué no se puede
                            if (item.disabled) {
                                return (
                                    <div key={i} role="menuitem" aria-disabled="true" className="cursor-not-allowed rounded-md px-3 py-2.5 text-sm">
                                        <span className="block text-gray-400">{item.label}</span>
                                        {item.note && <span className="mt-0.5 block text-xs text-hospitalgray">{item.note}</span>}
                                    </div>
                                );
                            }

                            if (item.onClick) {
                                return (
                                    <button key={i} type="button" role="menuitem" className={itemClass(item.danger)}
                                            onClick={() => { close(); item.onClick(); }}>
                                        {item.label}
                                    </button>
                                );
                            }

                            // Descargas: <a> normal; páginas: <Link> de Inertia
                            const Tag = item.download ? 'a' : Link;
                            return (
                                <Tag key={i} href={item.href} role="menuitem" className={itemClass(item.danger)} onClick={close}>
                                    {item.label}
                                </Tag>
                            );
                        })}
                    </div>
                </>,
                document.body
            )}
        </div>
    );
}
