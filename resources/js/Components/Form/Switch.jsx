import { Description, Field, Label, Switch as HeadlessSwitch } from '@headlessui/react';

/** Interruptor sí/no con título y descripción (Headless UI se encarga del teclado y la accesibilidad) */
export default function Switch({ id, label, description, checked, onChange }) {
    return (
        <Field className="flex items-start justify-between gap-6">
            <div>
                <Label className="cursor-pointer text-[15px] font-bold text-gray-900">{label}</Label>
                {description && <Description className="mt-1 text-sm text-hospitalgray">{description}</Description>}
            </div>
            <HeadlessSwitch
                id={id}
                checked={checked}
                onChange={onChange}
                className="group relative inline-flex h-[30px] w-[52px] shrink-0 cursor-pointer items-center rounded-full bg-gray-300 p-[3px] transition-colors focus:outline-none data-[checked]:bg-hospitalblue data-[focus]:ring-4 data-[focus]:ring-hospitalblue/25"
            >
                <span aria-hidden="true"
                      className="h-6 w-6 rounded-full bg-white shadow transition-transform group-data-[checked]:translate-x-[22px]" />
            </HeadlessSwitch>
        </Field>
    );
}
