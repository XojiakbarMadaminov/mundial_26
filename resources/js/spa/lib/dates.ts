const timeZone = 'Asia/Tashkent';

export function formatDateTime(value?: string | null): string {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('uz-UZ', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        timeZone,
    }).format(new Date(value));
}

export function formatTime(value?: string | null): string {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('uz-UZ', {
        hour: '2-digit',
        minute: '2-digit',
        timeZone,
    }).format(new Date(value));
}

export function formatDateKey(value: string): string {
    return new Intl.DateTimeFormat('uz-UZ', {
        day: '2-digit',
        month: 'long',
        weekday: 'long',
        timeZone,
    }).format(new Date(value));
}
