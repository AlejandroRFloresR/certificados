import { usePage } from '@inertiajs/react';

export default function Flash() {
    const { flash } = usePage().props;
    const msg = flash.success || flash.status;

    return (
        <>
            {msg && (
                <div className="mb-5 rounded-lg border border-green-200 bg-green-50 p-3.5 text-sm text-green-800">{msg}</div>
            )}
            {flash.error && (
                <div className="mb-5 rounded-lg border border-red-200 bg-red-50 p-3.5 text-sm text-red-800">{flash.error}</div>
            )}
        </>
    );
}
