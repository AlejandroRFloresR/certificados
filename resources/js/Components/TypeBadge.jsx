const TYPES = {
    asistio:  { label: 'Asistió',  className: 'bg-blue-100 text-blue-800' },
    dicto:    { label: 'Dictó',    className: 'bg-purple-100 text-purple-800' },
    aprobado: { label: 'Aprobado', className: 'bg-emerald-100 text-emerald-800' },
};

export const CERTIFICATE_TYPES = TYPES;

export default function TypeBadge({ type }) {
    if (!type) return null;
    const t = TYPES[type] ?? { label: type, className: 'bg-gray-100 text-gray-800' };
    return (
        <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${t.className}`}>
            {t.label}
        </span>
    );
}
