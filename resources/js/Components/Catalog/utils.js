export const STATUS = {
    proximo:    { label: 'Próximo',     className: 'bg-hospitalblue/10 text-hospitalblue' },
    en_curso:   { label: 'En curso',    className: 'bg-green-100 text-green-800' },
    finalizado: { label: 'Finalizado',  className: 'bg-gray-100 text-hospitalgray' },
    sin_fecha:  { label: 'Fecha a confirmar', className: 'bg-hospitalbrown/15 text-[#7a5a2b]' },
};

// "Disponibles" = todo lo que todavía no terminó
export const TABS = [
    { id: 'disponibles', label: 'Disponibles', match: (c) => c.status !== 'finalizado' },
    { id: 'finalizados', label: 'Finalizados', match: (c) => c.status === 'finalizado' },
    { id: 'todos',       label: 'Todos',       match: () => true },
];

const STATUS_ORDER = { en_curso: 0, proximo: 1, sin_fecha: 2, finalizado: 3 };

export function sortCourses(a, b) {
    const byStatus = STATUS_ORDER[a.status] - STATUS_ORDER[b.status];
    if (byStatus !== 0) return byStatus;

    // Finalizados: los más recientes primero. El resto: el que empieza antes primero.
    const da = a.start_date || '9999-12-31';
    const db = b.start_date || '9999-12-31';
    return a.status === 'finalizado' ? db.localeCompare(da) : da.localeCompare(db);
}

const dateFmt = new Intl.DateTimeFormat('es-AR', { day: 'numeric', month: 'short', year: 'numeric' });

export function formatDate(iso) {
    if (!iso) return null;
    const [y, m, d] = iso.slice(0, 10).split('-').map(Number);
    return dateFmt.format(new Date(y, m - 1, d));
}

export function formatRange(start, end) {
    const s = formatDate(start);
    const e = formatDate(end);
    if (!s) return 'Fecha a confirmar';
    if (!e || s === e) return s;
    return `${s} – ${e}`;
}

// Búsqueda sin distinguir mayúsculas ni tildes
export function normalize(text) {
    return (text || '')
        .toString()
        .normalize('NFD')
        .replace(/[̀-ͯ]/g, '')
        .toLowerCase();
}
