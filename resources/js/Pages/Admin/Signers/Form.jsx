import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import FormSection from '@/Components/Form/FormSection';
import TextField from '@/Components/Form/TextField';
import SignatureInput from '@/Components/Form/SignatureInput';
import FormActions from '@/Components/Form/FormActions';
import { focusFirstError } from '@/Components/Form/useUnsavedChanges';

export default function Form({ signer }) {
    const isEdit = Boolean(signer);

    const form = useForm({
        name: signer?.name ?? '',
        position: signer?.position ?? '',
        signature: null,
    });

    const set = (key) => (value) => form.setData(key, value);

    const submit = (e) => {
        e.preventDefault();
        const options = { forceFormData: true, preserveScroll: true, onError: focusFirstError };

        if (isEdit) {
            // PHP no lee archivos en PUT: se envía POST y Laravel lo trata como PUT
            form.transform((data) => ({ ...data, _method: 'put' }));
            form.post(route('admin.signers.update', signer.id), options);
        } else {
            form.post(route('admin.signers.store'), options);
        }
    };

    return (
        <AppLayout>
            <Head title={isEdit ? `Editar ${signer.name}` : 'Agregar firmante'} />

            <form onSubmit={submit} noValidate>
                <Link href={route('admin.signers.index')} className="mb-3 inline-flex items-center gap-1.5 py-1.5 text-sm font-semibold text-hospitalblue hover:underline">
                    <svg aria-hidden="true" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                    Firmantes
                </Link>
                <h1 className="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{isEdit ? 'Editar firmante' : 'Agregar firmante'}</h1>
                <p className="mt-1.5 text-sm text-hospitalgray">
                    Los campos con <span className="font-bold text-red-700">*</span> son obligatorios.
                </p>

                <div className="mt-8 space-y-8">
                    <FormSection title="Datos" description="El nombre y el cargo se imprimen debajo de la firma, en el certificado.">
                        <div className="grid gap-5 sm:grid-cols-2">
                            <TextField id="name" label="Nombre" required maxLength={255} placeholder="Ej: Dra. Ana López"
                                       value={form.data.name} onChange={set('name')} error={form.errors.name} />
                            <TextField id="position" label="Cargo" maxLength={255} placeholder="Ej: Directora"
                                       value={form.data.position} onChange={set('position')} error={form.errors.position} />
                        </div>
                    </FormSection>

                    <FormSection title="Firma" last
                                 description="Subí una foto o escaneo de la firma en hoja blanca: el sistema quita el fondo y la recorta. Revisá el resultado antes de guardar.">
                        <SignatureInput id="signature" label={isEdit ? 'Reemplazar firma' : 'Imagen de la firma'} required={!isEdit}
                                        currentUrl={signer?.signature_url} onChange={set('signature')}
                                        progress={form.progress} error={form.errors.signature} />
                        {isEdit && signer.courses > 0 && (
                            <p className="text-sm text-hospitalgray">
                                Si la cambiás, los próximos certificados de sus {signer.courses} {signer.courses === 1 ? 'curso' : 'cursos'} salen con la nueva.
                                Los ya emitidos conservan la firma anterior.
                            </p>
                        )}
                    </FormSection>
                </div>

                <FormActions form={form} cancelHref={route('admin.signers.index')}
                             submitLabel={isEdit ? 'Guardar cambios' : 'Agregar firmante'} alwaysEnabled={!isEdit} />
            </form>
        </AppLayout>
    );
}
