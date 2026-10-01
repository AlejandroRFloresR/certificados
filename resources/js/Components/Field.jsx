const inputClass =
    'mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-hospitalblue focus:ring-hospitalblue';

// as: 'input' (por defecto) | 'textarea' | 'select'
export default function Field({ label, error, hint, id, as: Tag = 'input', className = '', children, ...props }) {
    return (
        <div className={className}>
            {label && <label htmlFor={id} className="block text-sm font-medium text-gray-700">{label}</label>}
            <Tag id={id} {...props} className={`${inputClass} ${error ? 'border-red-400' : ''}`}>
                {children}
            </Tag>
            {hint && !error && <p className="mt-1 text-xs text-hospitalgray">{hint}</p>}
            {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
        </div>
    );
}