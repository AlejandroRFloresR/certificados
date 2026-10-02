import { useState } from 'react';
import { Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Button from '@/Components/Button';

export default function Signature({ mode, tutorName, signature_url, updateUrl, backUrl, rules }) {
    const [preview, setPreview] = useState(null);
    const [inputKey, setInputKey] = useState(0);

    // _method: 'put' → PHP no lee archivos en PUT, así que se envía POST y Laravel lo trata como PUT
    const form = useForm({ signature: null, _method: 'put' });

    const pick = (e) => {
        const file = e.target.files[0] ?? null;
        form.setData('signature', file);
        setPreview(file ? URL.createObjectURL(file) : null);
    };

    const submit = (e) => {
        e.preventDefault();
        form.post(updateUrl, {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.reset('signature');
                setPreview(null);
                setInputKey((k) => k + 1);
            },
        });
    };

    return (
        <AppLayout title={mode === 'self' ? 'Mi firma' : `Firma de ${tutorName}`}>
            <form onSubmit={submit} className="mx-auto max-w-xl space-y-6 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <p className="text-sm text-hospitalgray">
                    Esta firma se imprime en los certificados de los cursos {mode === 'self' ? 'que dictás' : 'de este tutor'}.
                    Para mejores resultados usá una imagen PNG con fondo transparente.
                </p>

                <div className="grid gap-4 sm:grid-cols-2">
                    <div>
                        <p className="mb-1 text-xs font-semibold uppercase tracking-wide text-hospitalgray">Actual</p>
                        <div className="flex h-28 items-center justify-center rounded-lg border border-dashed border-gray-300 bg-gray-50 p-2">
                            {signature_url
                                ? <img src={signature_url} alt="Firma actual" className="max-h-full max-w-full object-contain" />
                                : <span className="text-sm text-gray-400">Sin firma cargada</span>}
                        </div>
                    </div>
                    <div>
                        <p className="mb-1 text-xs font-semibold uppercase tracking-wide text-hospitalgray">Nueva</p>
                        <div className="flex h-28 items-center justify-center rounded-lg border border-dashed border-hospitalblue/40 bg-hospitalblue/5 p-2">
                            {preview
                                ? <img src={preview} alt="Vista previa" className="max-h-full max-w-full object-contain" />
                                : <span className="text-sm text-gray-400">Elegí un archivo</span>}
                        </div>
                    </div>
                </div>

                <div>
                    <input key={inputKey} type="file" accept={rules.accept} required onChange={pick}
                           aria-label="Archivo de firma"
                           className="block w-full text-sm text-gray-700 file:mr-3 file:rounded-lg file:border-0 file:bg-hospitalblue/10 file:px-3 file:py-2 file:text-sm file:font-semibold file:text-hospitalblue hover:file:bg-hospitalblue/20" />
                    {form.errors.signature
                        ? <p className="mt-1 text-xs text-red-600">{form.errors.signature}</p>
                        : <p className="mt-1 text-xs text-hospitalgray">{rules.hint}</p>}
                </div>

                <div className="flex items-center justify-end gap-2">
                    <Link href={backUrl}>
                        <Button type="button" variant="secondary">Volver</Button>
                    </Link>
                    <Button disabled={form.processing || !form.data.signature}>
                        {form.processing ? 'Subiendo…' : 'Guardar firma'}
                    </Button>
                </div>
            </form>
        </AppLayout>
    );
}
