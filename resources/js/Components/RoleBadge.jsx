export const ROLE_LABELS = { admin: 'Administrador', tutor: 'Tutor', user: 'Usuario' };

const COLORS = {
    admin: 'bg-hospitalblue/10 text-hospitalblue',
    tutor: 'bg-hospitalbrown/15 text-[#7a5a2b]',
    user:  'bg-gray-100 text-gray-700',
};

export default function RoleBadge({ role }) {
    if (!role) return <span className="text-xs text-gray-400">Sin rol</span>;
    return (
        <span className={`inline-flex rounded-full px-2 py-0.5 text-xs font-semibold ${COLORS[role] ?? COLORS.user}`}>
            {ROLE_LABELS[role] ?? role}
        </span>
    );
}
