/** Botón principal de ancho completo, para formularios cortos (login, registro…) */
export default function SubmitButton({ processing = false, processingLabel = 'Enviando…', children }) {
    return (
        <button type="submit" disabled={processing}
                className="inline-flex min-h-[44px] w-full items-center justify-center gap-2 rounded-lg bg-hospitalblue px-5 text-[15px] font-semibold text-white hover:bg-hospitalblue-dark disabled:cursor-not-allowed disabled:opacity-70">
            {processing && (
                <svg aria-hidden="true" className="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round">
                    <path d="M21 12a9 9 0 11-6.2-8.6" />
                </svg>
            )}
            {processing ? processingLabel : children}
        </button>
    );
}
