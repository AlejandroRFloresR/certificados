const variants = {
    primary:   'bg-hospitalblue text-white hover:bg-hospitalblue-dark',
    secondary: 'border border-gray-300 bg-white text-gray-700 hover:bg-gray-50',
    danger:    'bg-red-600 text-white hover:bg-red-700',
    ghost:     'text-hospitalblue hover:bg-hospitalblue/10',
};

export default function Button({ variant = 'primary', className = '', ...props }) {
    return (
        <button
            {...props}
            className={`inline-flex items-center justify-center gap-1.5 rounded-lg px-4 py-2 text-sm font-semibold transition disabled:opacity-50 ${variants[variant]} ${className}`}
        />
    );
}