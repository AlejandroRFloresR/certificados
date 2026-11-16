import { Radio, RadioGroup } from '@headlessui/react';
import { useForm } from '@inertiajs/react';
import PublicLayout from '@/Layouts/PublicLayout';
import FieldLabel, { FieldMessage } from '@/Components/Form/FieldLabel';
import TextField from '@/Components/Form/TextField';
import SubmitButton from '@/Components/Form/SubmitButton';
import { focusFirstError } from '@/Components/Form/useUnsavedChanges';

/** Fila de opciones tipo botón (puntaje 1 a 5, o Sí / No) */
function ChoiceRow({ id, label, options, value, onChange, error, columns = 5 }) {
    const labelId = `${id}-label`;
    return (
        <div>
            <FieldLabel as="p" id={labelId} required>{label}</FieldLabel>
            <RadioGroup id={id} value={value ?? null} onChange={onChange} aria-labelledby={labelId}
                        aria-describedby={error ? `${id}-message` : undefined}
                        className={`mt-2.5 grid gap-2 ${columns === 5 ? 'grid-cols-5' : 'grid-cols-2 sm:max-w-xs'}`}>
                {options.map((o) => (
                    <Radio key={String(o.value)} value={o.value} aria-label={o.aria ?? o.label}
                           className="flex min-h-[64px] cursor-pointer flex-col items-center justify-center gap-0.5 rounded-xl border border-gray-300 bg-white px-1 py-2 text-center transition focus:outline-none data-[checked]:border-2 data-[checked]:border-hospitalblue data-[checked]:bg-hospitalblue/5 data-[focus]:ring-4 data-[focus]:ring-hospitalblue/20 data-[hover]:border-gray-400">
                        {o.big && <span className="text-xl font-bold tabular-nums text-gray-900">{o.big}</span>}
                        <span className={o.big ? 'text-[11px] leading-tight text-hospitalgray sm:text-xs' : 'text-[15px] font-bold text-gray-900'}>{o.label}</span>
                    </Radio>
                ))}
            </RadioGroup>
            <FieldMessage id={`${id}-message`} error={error} />
        </div>
    );
}

function Thanks({ firstName, downloadUrl }) {
    return (
        <div className="py-6 text-center">
            <div className="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-green-100 text-green-700">
                <svg aria-hidden="true" className="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M5 12l5 5 9-11" /></svg>
            </div>
            <h2 className="mt-4 text-xl font-bold text-gray-900">¡Gracias{firstName ? `, ${firstName}` : ''}!</h2>
            <p className="mt-2 text-gray-600">Recibimos tu opinión. Nos ayuda a mejorar los próximos cursos.</p>
            <a href={downloadUrl} className="mt-6 inline-flex min-h-[44px] items-center rounded-lg border border-gray-300 px-4 text-sm font-semibold text-hospitalblue hover:bg-gray-50">
                Descargar mi certificado
            </a>
        </div>
    );
}

export default function Survey({ token, course, firstName, answered, downloadUrl, questions }) {
    const form = useForm({
        ...Object.fromEntries(questions.ratings.map((q) => [q.key, null])),
        recommend: null,
        comment: '',
    });

    const scale = questions.scale.map((s) => ({ value: s.value, big: s.value, label: s.label, aria: `${s.value}: ${s.label}` }));

    const submit = (e) => {
        e.preventDefault();
        form.post(route('survey.store', token), { preserveScroll: true, onError: focusFirstError });
    };

    return (
        <PublicLayout title="Encuesta de satisfacción">
            <section className="bg-gradient-to-br from-hospitalblue to-hospitalblue-dark px-4 py-10 sm:px-8">
                <div className="mx-auto max-w-2xl">
                    <p className="text-xs font-bold uppercase tracking-wider text-white/70">Encuesta de satisfacción</p>
                    <h1 className="mt-2 text-2xl font-bold text-white sm:text-3xl">{course}</h1>
                </div>
            </section>

            <div className="mx-auto w-full max-w-2xl px-4 py-8 sm:px-8">
                {answered ? (
                    <Thanks firstName={firstName} downloadUrl={downloadUrl} />
                ) : (
                    <form onSubmit={submit} noValidate className="space-y-8">
                        <p className="text-gray-700">
                            {firstName ? `${firstName}, c` : 'C'}ontanos qué te pareció el curso. Son 5 preguntas y lleva menos de 2 minutos.
                            Las respuestas se muestran sin tu nombre.
                        </p>

                        {questions.ratings.map((q) => (
                            <ChoiceRow key={q.key} id={q.key} label={q.text} options={scale}
                                       value={form.data[q.key]} onChange={(v) => form.setData(q.key, v)} error={form.errors[q.key]} />
                        ))}

                        <ChoiceRow id="recommend" label={questions.recommend} columns={2}
                                   options={[{ value: true, label: 'Sí' }, { value: false, label: 'No' }]}
                                   value={form.data.recommend} onChange={(v) => form.setData('recommend', v)} error={form.errors.recommend} />

                        <TextField id="comment" label={questions.comment} multiline rows={4} maxLength={2000}
                                   value={form.data.comment} onChange={(v) => form.setData('comment', v)} error={form.errors.comment} />

                        <div className="space-y-4 border-t border-gray-100 pt-6 text-center">
                            <SubmitButton processing={form.processing}>Enviar respuestas</SubmitButton>
                            <a href={downloadUrl} className="inline-block py-2 text-sm font-semibold text-hospitalblue hover:underline">
                                Descargar mi certificado sin responder
                            </a>
                        </div>
                    </form>
                )}
            </div>
        </PublicLayout>
    );
}
