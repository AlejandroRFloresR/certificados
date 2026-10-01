export default function TypeBadge({ type }) {
    if (!type) return null;
    return (
        <span className="inline-flex rounded-full bg-hospitalblue/10 px-2 py-0.5 text-xs font-semibold text-hospitalblue">
            {type.charAt(0).toUpperCase() + type.slice(1)}
        </span>
    );
}
