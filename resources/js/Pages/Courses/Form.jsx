import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import FormSection from '@/Components/Form/FormSection';
import TextField from '@/Components/Form/TextField';
import Combobox from '@/Components/Form/Combobox';
import RadioCards from '@/Components/Form/RadioCards';
import Switch from '@/Components/Form/Switch';
import FormActions from '@/Components/Form/FormActions';
import SignerPicker from '@/Components/Form/SignerPicker';
import { focusFirstError } from '@/Components/Form/useUnsavedChanges';
import { StatusBadge } from '@/Components/Catalog/CourseCard';
import { MonitorIcon, PinIcon } from '@/Components/Catalog/Icons';
import { formatRange } from '@/Components/Catalog/utils';

const empty = {
    title: '', description: '', start_date: '', end_date: '',
    category: '', modality: '', location: '', is_public: false,
};

function HybridIcon(props) {
    return (
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" {...props}>
            <path d="M4 12h6M14 12h6M10 9l-3 3 3 3M14 9l3 3-3 3" />
        </svg>
    );
}

const MODALITY_CARDS = [
    { value: 'presencial', icon: PinIcon,     description: 'En el hospital o un aula' },
    { value: 'virtual',    icon: MonitorIcon, description: 'Por videollamada o campus' },
    { value: 'hibrido',    icon: HybridIcon,  description: 'Parte presencial y parte virtual' },
];

// Mismo criterio que Course::getStatusAttribute, para la vista previa
function statusFromDates(start, end) {
    const today = new Date().toLocaleDateString('sv-SE'); // AAAA-MM-DD en hora local
    if (!start) return 'sin_fecha';
    if (start > today) return 'proximo';
    if (!end || end >= today) return 'en_curso';
    return 'finalizado';
}

/** Cómo se va a ver la tarjeta del curso en el catálogo, con los datos del formulario */
function CatalogPreview({ data, modalities }) {
    const meta = [formatRange(data.start_date, data.end_date), modalities[data.modality], data.location]
        .filter(Boolean)
        .join(' · ');

    return (
        <div>
            <p className="text-xs font-bold uppercase tracking-widest text-hospitalgray">Así se va a ver</p>
            <div className="mt-2 max-w-xs rounded-xl border border-gray-200 bg-white p-4 shadow-sm">
                <div className="flex items-center justify-between gap-2">
                    <span className="inline-flex min-h-[1.25rem] items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-hospitalgray">
                        {data.category && <><span aria-hidden="true" className="h-1.5 w-1.5 rounded-full bg-hospitalbrown" />{data.category}</>}
                    </span>
                    <StatusBadge status={statusFromDates(data.start_date, data.end_date)} />
                </div>
                <p className="mt-2 font-semibold text-gray-900">{data.title || 'Título del curso'}</p>
                <p className="mt-1 text-sm tabular-nums text-hospitalgray">{meta}</p>
            </div>
        </div>
    );
}

export default function Form({ course, modalities, categories, signers, selectedSigners, maxSigners }) {
    const isEdit = Boolean(course);

    // Los null de la base pasan a '' porque los inputs de React no aceptan null
    const form = useForm({
        ...(isEdit
            ? Object.fromEntries(Object.keys(empty).map((k) => [k, course[k] ?? empty[k]]))
            : empty),
        signers: selectedSigners, // ids en el orden en que se imprimen
    });

    const set = (key) => (value) => form.setData(key, value);

    const submit = (e) => {
        e.preventDefault();
        const options = { preserveScroll: true, onError: focusFirstError };
        if (isEdit) form.put(route('admin.courses.update', course.id), options);
        else form.post(route('admin.courses.store'), options);
    };

    const backHref = isEdit ? route('admin.courses.users', course.id) : route('courses.index');
    const isVirtual = form.data.modality === 'virtual';

    return (
        <AppLayout>
            <Head title={isEdit ? `Editar ${course.title}` : 'Crear curso'} />

            <form onSubmit={submit} noValidate>
                <Link href={backHref} className="mb-3 inline-flex items-center gap-1.5 py-1.5 text-sm font-semibold text-hospitalblue hover:underline">
                    <svg aria-hidden="true" className="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                    {isEdit ? course.title : 'Cursos'}
                </Link>
                <h1 className="text-2xl font-bold tracking-tight text-gray-900 sm:text-3xl">{isEdit ? 'Editar curso' : 'Crear curso'}</h1>
                <p className="mt-1.5 text-sm text-hospitalgray">
                    Los campos con <span className="font-bold text-red-700">*</span> son obligatorios.
                </p>

                <div className="mt-8 space-y-8">
                    <FormSection title="Datos del curso" description="El título y la descripción aparecen en el catálogo y en el certificado.">
                        <TextField id="title" label="Título" required maxLength={255}
                                   value={form.data.title} onChange={set('title')} error={form.errors.title} />
                        <TextField id="description" label="Descripción" multiline rows={4} maxLength={2000}
                                   hint="Se imprime en el certificado debajo del nombre del alumno."
                                   value={form.data.description} onChange={set('description')} error={form.errors.description} />
                        <div className="sm:w-2/3">
                            <Combobox id="category" label="Categoría" options={categories} creatable createLabel="Crear categoría"
                                      placeholder="Elegí o escribí una categoría"
                                      value={form.data.category} onChange={set('category')} error={form.errors.category} />
                        </div>
                    </FormSection>

                    <FormSection title="Fechas" description="Con las fechas, el sistema calcula solo si el curso está próximo, en curso o finalizado.">
                        <div className="grid gap-4 sm:grid-cols-2">
                            <TextField id="start_date" type="date" label="Inicio"
                                       value={form.data.start_date} onChange={set('start_date')} error={form.errors.start_date} />
                            <TextField id="end_date" type="date" label="Finalización" min={form.data.start_date || undefined}
                                       value={form.data.end_date} onChange={set('end_date')} error={form.errors.end_date} />
                        </div>
                    </FormSection>

                    <FormSection title="Modalidad y lugar" description="Cómo y dónde se dicta. Se muestra en el catálogo.">
                        <RadioCards id="modality" label="Modalidad" clearable
                                    options={MODALITY_CARDS.map((m) => ({ ...m, label: modalities[m.value] }))}
                                    value={form.data.modality} onChange={set('modality')} error={form.errors.modality} />
                        <TextField id="location" label={isVirtual ? 'Plataforma' : 'Lugar'} maxLength={255}
                                   placeholder={isVirtual ? 'Ej: Zoom, Campus virtual' : 'Ej: Aula magna'}
                                   value={form.data.location} onChange={set('location')} error={form.errors.location} />
                    </FormSection>

                    <FormSection title="Firmas del certificado"
                                 description="Quiénes firman los certificados de este curso. Los certificados ya emitidos conservan las firmas que tenían.">
                        {signers.length === 0 ? (
                            <p className="text-sm text-hospitalgray">
                                Todavía no hay firmantes cargados. Agregalos en{' '}
                                <Link href={route('admin.signers.index')} className="font-semibold text-hospitalblue hover:underline">Firmantes</Link>
                                {' '}y después elegilos acá. Sin firmantes, el certificado sale sin firmas.
                            </p>
                        ) : (
                            <SignerPicker id="signers" label="Firmantes" signers={signers} max={maxSigners}
                                          value={form.data.signers} onChange={set('signers')}
                                          error={form.errors.signers ?? form.errors['signers.0']} />
                        )}
                    </FormSection>

                    <FormSection title="Catálogo público" description="Decidí si el curso se muestra en la página pública de cursos y los alumnos se pueden inscribir." last>
                        <Switch id="is_public" label="Publicar en el catálogo"
                                description="Visible en /cursos y en el inicio de los alumnos mientras no haya terminado."
                                checked={form.data.is_public} onChange={set('is_public')} />
                        {form.data.is_public && <CatalogPreview data={form.data} modalities={modalities} />}
                    </FormSection>
                </div>

                <FormActions form={form} cancelHref={backHref} submitLabel={isEdit ? 'Guardar cambios' : 'Crear curso'} alwaysEnabled={!isEdit} />
            </form>
        </AppLayout>
    );
}
