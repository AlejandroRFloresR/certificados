/** Casilla con su texto, del tamaño del kit (área clickeable de 44px de alto) */
export default function Checkbox({ id, label, checked, onChange }) {
    return (
        <label htmlFor={id} className="inline-flex min-h-[44px] cursor-pointer items-center gap-2.5 text-[15px] text-gray-800">
            <input id={id} type="checkbox" checked={checked} onChange={(e) => onChange(e.target.checked)}
                   className="h-[18px] w-[18px] rounded border-gray-400 text-hospitalblue focus:ring-4 focus:ring-hospitalblue/20" />
            {label}
        </label>
    );
}
