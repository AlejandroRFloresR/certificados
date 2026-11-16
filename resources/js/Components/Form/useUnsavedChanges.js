import { useEffect } from 'react';
import { router } from '@inertiajs/react';
import { confirmDialog } from '@/Components/ConfirmDialog';

/**
 * Avisa antes de salir de un formulario con cambios sin guardar:
 * - al navegar dentro del sistema (links de Inertia), con el diálogo del sistema;
 * - al cerrar la pestaña o recargar, con el aviso nativo del navegador
 *   (ese no se puede personalizar: los navegadores no lo permiten).
 * El envío del propio formulario (POST/PUT/PATCH) no pregunta.
 */
export default function useUnsavedChanges(isDirty) {
    useEffect(() => {
        if (!isDirty) return undefined;

        let leaving = false;
        const removeInertiaListener = router.on('before', (event) => {
            const { visit } = event.detail;
            if (leaving || visit.method !== 'get') return; // ya confirmó, o es el envío del formulario

            // El diálogo es asíncrono: se frena la visita y, si confirma, se repite
            event.preventDefault();
            confirmDialog({
                title: '¿Salir sin guardar?',
                message: 'Tenés cambios sin guardar en este formulario. Si salís, se pierden.',
                confirmLabel: 'Salir sin guardar',
                cancelLabel: 'Seguir editando',
                danger: true,
            }).then((ok) => {
                if (!ok) return;
                leaving = true;
                router.visit(visit.url, {
                    data: visit.data,
                    replace: visit.replace,
                    preserveScroll: visit.preserveScroll,
                    preserveState: visit.preserveState,
                });
            });
        });

        const onBeforeUnload = (event) => {
            event.preventDefault();
            event.returnValue = '';
        };
        window.addEventListener('beforeunload', onBeforeUnload);

        return () => {
            removeInertiaListener();
            window.removeEventListener('beforeunload', onBeforeUnload);
        };
    }, [isDirty]);
}

/** Lleva el foco (y el scroll) al primer campo con error. Usar en onError de form.post/put. */
export function focusFirstError(errors) {
    const firstKey = Object.keys(errors)[0];
    const element = firstKey && document.getElementById(firstKey);
    if (!element) return;
    element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    element.focus({ preventScroll: true });
}
