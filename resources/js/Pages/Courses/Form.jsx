import { Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Field from '@/Components/Field';
import Button from '@/Components/Button';

const empty = {
    title: '', description: '', start_date: '', end_date: '', hours: '',
    category: '', modality: '', location: '', is_public: false,
};

export default function Form({ course, modalities, categories }) {
    const isEdit = Boolean(course);

    // Los null de la base pasan a '' porque los inputs de React no aceptan null
    const form = useForm(
        isEdit
            ? Object.fromEntries(Object.keys(empty).map((k) => [k, course[k] ?? empty[k]]))
            : empty
    );

    const bind = (key) => ({
        id: key,
        value: form.data[key],
        onChange: (e) => form.setData(key, e.target.value),
        error: form.errors[key],
    });

    const submit = (e) => {
        e.preventDefault();
        if (isEdit) form.put(route('admin.courses.update', course.id));
        else form.post(route('admin.courses.store'));
    };

    return (
        <AppLayout title={isEdit ? 'Editar curso' : 'Crear curso'}>
            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-6">

                <div className="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 className="text-sm font-semibold text-gray-900">Datos del curso</h2>
                    <Field label="Título" required {...bind('title')} />
                    <Field as="textarea" rows={5} label="Descripción" {...bind('description')} />

                    <div className="grid gap-4 sm:grid-cols-2">
                        <Field type="date" label="Fecha de inicio" {...bind('start_date')} />
                        <Field type="date" label="Fecha de finalización" {...bind('end_date')} />
                    </div>
                    <Field type="number" min="1" max="2000" label="Carga horaria (horas)" className="sm:w-1/2"
                           {...bind('hours')} />
                </div>

                <div className="space-y-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 className="text-sm font-semibold text-gray-900">Catálogo público</h2>

                    <div className="grid gap-4 sm:grid-cols-2">
                        <div>
                            <Field label="Categoría" list="course-categories" maxLength={100}
                                   placeholder="Ej: Enfermería, Emergencias…" {...bind('category')} />
                            <datalist id="course-categories">
                                {categories.map((c) => <option key={c} value={c} />)}
                            </datalist>
                        </div>
                        <Field as="select" label="Modalidad" {...bind('modality')}>
                            <option value="">—</option>
                            {Object.entries(modalities).map(([value, label]) => (
                                <option key={value} value={value}>{label}</option>
                            ))}
                        </Field>
                    </div>
                    <Field label="Lugar / plataforma" placeholder="Ej: Aula magna, Zoom…" {...bind('location')} />

                    <label className="flex items-start gap-3 rounded-lg bg-gray-50 p-3">
                        <input type="checkbox"
                               checked={form.data.is_public}
                               onChange={(e) => form.setData('is_public', e.target.checked)}
                               className="mt-0.5 rounded border-gray-300 text-hospitalblue focus:ring-hospitalblue" />
                        <span className="text-sm">
                            <span className="font-medium text-gray-900">Publicar en el catálogo de cursos</span>
                            <span className="block text-hospitalgray">Si está marcado, el curso se muestra en la página pública /cursos.</span>
                        </span>
                    </label>
                </div>

                <div className="flex items-center justify-end gap-2">
                    <Link href={route('courses.index')}>
                        <Button type="button" variant="secondary">Cancelar</Button>
                    </Link>
                    <Button disabled={form.processing}>
                        {form.processing ? 'Guardando…' : isEdit ? 'Guardar cambios' : 'Crear curso'}
                    </Button>
                </div>
            </form>
        </AppLayout>
    );
}
