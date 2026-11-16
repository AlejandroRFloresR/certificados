import { router } from '@inertiajs/react';
import MoreMenu from '@/Components/MoreMenu';
import { confirmDialog } from '@/Components/ConfirmDialog';

export function PublicBadge({ isPublic }) {
    return isPublic
        ? <span className="inline-flex rounded-full bg-hospitalbrown/15 px-2 py-0.5 text-xs font-semibold text-[#6E4F22]">En catálogo</span>
        : <span className="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-hospitalgray">Oculto</span>;
}

/** Menú "⋯" con las acciones secundarias de un curso (listado y ficha) */
export default function CourseMenu({ course, withEdit = true }) {
    const destroy = async () => {
        const ok = await confirmDialog({
            title: '¿Eliminar el curso?',
            message: [
                `Se elimina "${course.title}". Esta acción no se puede deshacer.`,
                'Los certificados ya emitidos se conservan: se siguen descargando y verificando.',
            ],
            confirmLabel: 'Eliminar curso',
            danger: true,
        });
        if (!ok) return;
        router.delete(route('admin.courses.destroy', course.id));
    };

    const items = [
        withEdit && { label: 'Editar curso', href: route('admin.courses.edit', course.id) },
        course.is_public && { label: 'Ver en el catálogo', href: route('catalog.index', { curso: course.id }) },
        { label: 'Exportar Excel', href: route('admin.courses.users.export', course.id), download: true },
        { label: '-' },
        { label: 'Eliminar curso', onClick: destroy, danger: true },
    ].filter(Boolean);

    return <MoreMenu label={`Más acciones para ${course.title}`} items={items} />;
}
