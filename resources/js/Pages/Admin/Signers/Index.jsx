import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/Button';
import MoreMenu from '@/Components/MoreMenu';
import { confirmDialog } from '@/Components/ConfirmDialog';

function SignaturePreview({ url, name }) {
    return (
        <div className="flex h-16 w-40 shrink-0 items-center justify-center rounded-lg border border-gray-100 bg-white p-1.5">
            <img src={url} alt={`Firma de ${name}`} className="max-h-full max-w-full object-contain" />
        </div>
    );
}

export default function Index({ signers }) {
    const destroy = async (signer) => {
        const usage = signer.courses
            ? `Se va a quitar de ${signer.courses} ${signer.courses === 1 ? 'curso' : 'cursos'}. `
            : '';
        const ok = await confirmDialog({
            title: `¿Eliminar a ${signer.name} de los firmantes?`,
            message: `${usage}Los certificados ya emitidos no cambian.`,
            confirmLabel: 'Eliminar firmante',
            danger: true,
        });
        if (!ok) return;
        router.delete(route('admin.signers.destroy', signer.id), { preserveScroll: true });
    };

    return (
        <AppLayout
            title="Firmantes"
            actions={
                <Link href={route('admin.signers.create')}>
                    <Button type="button" className="min-h-[44px]">+ Agregar firmante</Button>
                </Link>
            }
        >
            <Head title="Firmantes" />
            <p className="-mt-2 mb-5 text-sm text-hospitalgray">
                Las personas cuya firma se imprime en los certificados. En cada curso se eligen hasta 3.
            </p>

            {signers.length === 0 ? (
                <div className="rounded-xl border border-dashed border-gray-300 bg-white px-6 py-14 text-center">
                    <p className="font-semibold text-gray-900">Todavía no hay firmantes</p>
                    <p className="mt-1 text-sm text-hospitalgray">Agregá a quienes firman los certificados (dirección, coordinación de docencia…).</p>
                    <Link href={route('admin.signers.create')} className="mt-4 inline-block">
                        <Button type="button" className="min-h-[44px]">+ Agregar firmante</Button>
                    </Link>
                </div>
            ) : (
                <ul className="divide-y divide-gray-100 rounded-xl border border-gray-200 bg-white shadow-sm">
                    {signers.map((s) => (
                        <li key={s.id} className="flex items-center gap-2 pr-3 hover:bg-gray-50">
                            <Link href={route('admin.signers.edit', s.id)}
                                  className="flex min-w-0 flex-1 flex-wrap items-center gap-4 py-4 pl-5 pr-2">
                                <SignaturePreview url={s.signature_url} name={s.name} />
                                <div className="min-w-0">
                                    <span className="font-semibold text-gray-900">{s.name}</span>
                                    {s.position && <p className="mt-0.5 text-sm text-hospitalgray">{s.position}</p>}
                                    <p className="mt-0.5 text-sm text-hospitalgray">
                                        {s.courses ? `Firma ${s.courses} ${s.courses === 1 ? 'curso' : 'cursos'}` : 'Todavía no firma ningún curso'}
                                    </p>
                                </div>
                            </Link>
                            <MoreMenu label={`Más acciones para ${s.name}`} items={[
                                { label: 'Editar', href: route('admin.signers.edit', s.id) },
                                { label: '-' },
                                { label: 'Eliminar', onClick: () => destroy(s), danger: true },
                            ]} />
                        </li>
                    ))}
                </ul>
            )}
        </AppLayout>
    );
}
