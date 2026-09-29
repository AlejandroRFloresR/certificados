import { useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import Card from '@/Components/Card';
import Field from '@/Components/Field';
import Button from '@/Components/Button';

export default function Demo({ mensaje }) {
    const form = useForm({ nombre: '' });

    return (
        <AppLayout title="Demo Inertia" actions={<Button variant="secondary">Acción</Button>}>
            <Card title={mensaje}>
                <form onSubmit={(e) => { e.preventDefault(); form.post(route('demo.store')); }} className="space-y-4">
                    <Field id="nombre" label="Nombre" value={form.data.nombre}
                           onChange={(e) => form.setData('nombre', e.target.value)}
                           error={form.errors.nombre} />
                    <Button disabled={form.processing}>Guardar</Button>
                </form>
            </Card>
        </AppLayout>
    );
}
