import { useEffect, useRef, useState } from 'react';
import { Dialog, DialogBackdrop, DialogPanel, DialogTitle, Description } from '@headlessui/react';
import Button from '@/Components/Button';

/**
 * Reemplazo de window.confirm() con el estilo del sistema.
 *
 *   if (!(await confirmDialog({ title: '¿Eliminar el curso?', message: '…', confirmLabel: 'Eliminar', danger: true }))) return;
 *
 * Devuelve una promesa con true (confirmó) o false (canceló, Escape o click afuera).
 * `message` puede ser texto (los saltos de línea se respetan) o un array de párrafos.
 * Necesita un <ConfirmHost /> montado (está en AppLayout); si no lo hay, usa el confirm del navegador.
 */
let show = null;

export function confirmDialog(options) {
    if (!show) {
        const text = [options.title, ...[].concat(options.message ?? [])].join('\n\n');
        return Promise.resolve(window.confirm(text));
    }
    return new Promise((resolve) => show({ ...options, resolve }));
}

const Icon = ({ danger }) => (
    <div className={`flex h-11 w-11 shrink-0 items-center justify-center rounded-full ${danger ? 'bg-red-100 text-red-700' : 'bg-hospitalblue/10 text-hospitalblue'}`}>
        <svg aria-hidden="true" className="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
            {danger
                ? <><path d="M12 9v4" /><path d="M12 17h.01" /><path d="M10.3 3.9L1.8 18a2 2 0 001.7 3h17a2 2 0 001.7-3L13.7 3.9a2 2 0 00-3.4 0z" /></>
                : <><circle cx="12" cy="12" r="9" /><path d="M12 8h.01" /><path d="M11 12h1v4h1" /></>}
        </svg>
    </div>
);

export default function ConfirmHost() {
    const [current, setCurrent] = useState(null);
    const [open, setOpen] = useState(false);
    const cancelRef = useRef(null);
    const confirmRef = useRef(null);

    useEffect(() => {
        show = (options) => {
            // Si había otro abierto, se da por cancelado
            setCurrent((prev) => { prev?.resolve(false); return options; });
            setOpen(true);
        };
        return () => { show = null; };
    }, []);

    const answer = (value) => {
        if (!open) return;
        current?.resolve(value);
        setOpen(false);
    };

    const { title, message, confirmLabel = 'Aceptar', cancelLabel = 'Cancelar', danger = false } = current ?? {};
    const paragraphs = [].concat(message ?? []).filter(Boolean);

    return (
        <Dialog open={open} onClose={() => answer(false)} className="relative z-[60]"
                // En acciones peligrosas el foco arranca en "Cancelar": un Enter apurado no borra nada
                initialFocus={danger ? cancelRef : confirmRef}>
            <DialogBackdrop transition
                            className="fixed inset-0 bg-gray-900/50 transition-opacity duration-150 data-[closed]:opacity-0" />

            <div className="fixed inset-0 flex items-end justify-center p-4 sm:items-center">
                <DialogPanel transition
                             className="w-full max-w-md rounded-2xl bg-white p-6 shadow-xl transition duration-150 data-[closed]:translate-y-2 data-[closed]:opacity-0 sm:data-[closed]:translate-y-0 sm:data-[closed]:scale-95">
                    <div className="flex gap-4">
                        <Icon danger={danger} />
                        <div className="min-w-0 pt-1.5">
                            <DialogTitle className="text-lg font-bold leading-snug text-gray-900">{title}</DialogTitle>
                            {paragraphs.length > 0 && (
                                <Description as="div" className="mt-2 space-y-2 text-sm leading-relaxed text-gray-600">
                                    {paragraphs.map((p, i) => <p key={i} className="whitespace-pre-line break-words">{p}</p>)}
                                </Description>
                            )}
                        </div>
                    </div>

                    <div className="mt-6 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                        <Button ref={cancelRef} type="button" variant="secondary" className="min-h-[44px]" onClick={() => answer(false)}>
                            {cancelLabel}
                        </Button>
                        <Button ref={confirmRef} type="button" variant={danger ? 'danger' : 'primary'} className="min-h-[44px]" onClick={() => answer(true)}>
                            {confirmLabel}
                        </Button>
                    </div>
                </DialogPanel>
            </div>
        </Dialog>
    );
}
