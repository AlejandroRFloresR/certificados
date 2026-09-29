import { Link } from '@inertiajs/react';

export default function NavLink({ href, active, children }) {
    return (
        <Link
            href={href}
            className={`rounded-md px-3 py-2 text-sm font-medium transition ${
                active ? 'bg-white/15 text-white' : 'text-white/75 hover:bg-white/10 hover:text-white'
            }`}
        >
            {children}
        </Link>
    );
}
