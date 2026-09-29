import { Head, Link, usePage } from '@inertiajs/react';

export default function AuthLayout({ title, subtitle, children }) {
    const { imagesUrl } = usePage().props;

    return (
        <div className="flex min-h-screen flex-col items-center justify-center bg-gradient-to-br from-hospitalblue to-hospitalblue-dark px-4 py-10">
            <Head title={title} />

            <div className="w-full max-w-sm">
                <div className="rounded-2xl bg-white p-6 shadow-xl sm:p-8">
                    <Link href={route('home')} className="mb-6 flex justify-center">
                        <img src={`${imagesUrl}/logoInstitucional.png`} alt="Logo" className="h-12 w-auto" />
                    </Link>
                    {title && <h1 className="text-center text-lg font-semibold text-gray-900">{title}</h1>}
                    {subtitle && <p className="mt-1 text-center text-sm text-hospitalgray">{subtitle}</p>}
                    <div className="mt-6">{children}</div>
                </div>
            </div>
        </div>
    );
}
