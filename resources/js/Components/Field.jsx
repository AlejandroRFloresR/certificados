export default function Field({ label, error, id, className = '', ...props }) {
    return (
        <div className={className}>
            {label && <label htmlFor={id} className="block text-sm font-medium text-gray-700">{label}</label>}
            <input
                id={id}
                {...props}
                className={`mt-1 block w-full rounded-lg border-gray-300 text-sm shadow-sm focus:border-hospitalblue focus:ring-hospitalblue ${error ? 'border-red-400' : ''}`}
            />
            {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
        </div>
    );
}
