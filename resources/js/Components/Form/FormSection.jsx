/** Bloque de un formulario: título y explicación a la izquierda, campos a la derecha */
export default function FormSection({ title, description, children, last = false }) {
    return (
        <section className={`grid gap-4 lg:grid-cols-[18rem_minmax(0,1fr)] lg:gap-8 ${last ? '' : 'border-b border-gray-200 pb-8'}`}>
            <div>
                <h2 className="text-base font-bold text-gray-900">{title}</h2>
                {description && <p className="mt-1.5 text-sm leading-relaxed text-hospitalgray">{description}</p>}
            </div>
            <div className="space-y-5 rounded-xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                {children}
            </div>
        </section>
    );
}
