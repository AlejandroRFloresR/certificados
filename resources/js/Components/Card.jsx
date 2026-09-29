export default function Card({ title, actions, children, className = '' }) {
    return (
        <div className={`overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm ${className}`}>
            {(title || actions) && (
                <div className="flex items-center justify-between gap-3 border-b border-gray-100 px-5 py-3">
                    <h3 className="text-sm font-semibold text-gray-900">{title}</h3>
                    {actions}
                </div>
            )}
            <div className="p-5">{children}</div>
        </div>
    );
}
