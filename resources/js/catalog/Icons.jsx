const base = { viewBox: '0 0 24 24', fill: 'none', 'aria-hidden': true };
const stroke = { stroke: 'currentColor', strokeWidth: 1.8, strokeLinecap: 'round', strokeLinejoin: 'round' };

export const SearchIcon = (p) => (
    <svg {...base} {...p}>
        <circle cx="11" cy="11" r="7" {...stroke} strokeWidth="2" />
        <path d="M21 21l-4-4" {...stroke} strokeWidth="2" />
    </svg>
);

export const CalendarIcon = (p) => (
    <svg {...base} {...p}>
        <rect x="3.5" y="5" width="17" height="15" rx="2" {...stroke} />
        <path d="M3.5 10h17M8 3v4M16 3v4" {...stroke} />
    </svg>
);

export const ClockIcon = (p) => (
    <svg {...base} {...p}>
        <circle cx="12" cy="12" r="8.5" {...stroke} />
        <path d="M12 7.5V12l3 2" {...stroke} />
    </svg>
);

export const PinIcon = (p) => (
    <svg {...base} {...p}>
        <path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0113 0c0 5.4-6.5 11-6.5 11z" {...stroke} />
        <circle cx="12" cy="10" r="2.3" {...stroke} />
    </svg>
);

export const MonitorIcon = (p) => (
    <svg {...base} {...p}>
        <rect x="3" y="4.5" width="18" height="12" rx="2" {...stroke} />
        <path d="M8.5 20h7M12 16.5V20" {...stroke} />
    </svg>
);

export const UserIcon = (p) => (
    <svg {...base} {...p}>
        <circle cx="12" cy="8.5" r="3.5" {...stroke} />
        <path d="M5 20c.8-3.6 3.6-5.5 7-5.5s6.2 1.9 7 5.5" {...stroke} />
    </svg>
);

export const CheckIcon = (p) => (
    <svg {...base} {...p}>
        <path d="M6 12.5l4 4 8-9" {...stroke} strokeWidth="2" />
    </svg>
);

export const CloseIcon = (p) => (
    <svg {...base} {...p}>
        <path d="M6 6l12 12M18 6L6 18" {...stroke} strokeWidth="2" />
    </svg>
);
