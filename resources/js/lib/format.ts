type DateInput = string | Date | null | undefined;

function parseDateInput(value: DateInput): Date | null {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    if (value instanceof Date) {
        return Number.isNaN(value.getTime()) ? null : value;
    }

    const trimmed = value.trim();

    if (!trimmed) {
        return null;
    }

    const dateOnlyMatch = trimmed.match(/^(\d{4})-(\d{2})-(\d{2})$/);

    if (dateOnlyMatch) {
        const [, year, month, day] = dateOnlyMatch;

        return new Date(Number(year), Number(month) - 1, Number(day));
    }

    const parsed = new Date(trimmed);

    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

function pad(value: number): string {
    return String(value).padStart(2, '0');
}

function formatYearMonthDay(date: Date): string {
    return `${date.getFullYear()}-${pad(date.getMonth() + 1)}-${pad(date.getDate())}`;
}

function formatTime12Hour(date: Date): string {
    const hours = date.getHours();
    const meridiem = hours >= 12 ? 'PM' : 'AM';
    const hour12 = hours % 12 || 12;

    return `${pad(hour12)}:${pad(date.getMinutes())} ${meridiem}`;
}

/** Formats as `Y-m-d h:i A` (e.g. 2026-06-24 03:30 PM). */
export function formatDateTime(value: DateInput): string {
    const parsed = parseDateInput(value);

    if (!parsed) {
        return '—';
    }

    return `${formatYearMonthDay(parsed)} ${formatTime12Hour(parsed)}`;
}

/** @alias formatDateTime */
export function formatDate(value: DateInput): string {
    return formatDateTime(value);
}

export function formatCurrency(amount: number | string, currency = 'KES'): string {
    const value = typeof amount === 'string' ? parseFloat(amount) : amount;
    return new Intl.NumberFormat('en-KE', { style: 'currency', currency }).format(value || 0);
}

export function titleCase(value: string): string {
    return value.replace(/_/g, ' ').replace(/\b\w/g, (c) => c.toUpperCase());
}
