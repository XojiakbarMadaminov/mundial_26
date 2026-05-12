import { currentLocale, localeToIntlTag } from '@/spa/lib/i18n';

const timeZone = 'Asia/Tashkent';

export function formatDateTime(value?: string | null): string {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat(localeToIntlTag(currentLocale()), {
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

    return new Intl.DateTimeFormat(localeToIntlTag(currentLocale()), {
        hour: '2-digit',
        minute: '2-digit',
        timeZone,
    }).format(new Date(value));
}

export function formatDateKey(value: string): string {
    return new Intl.DateTimeFormat(localeToIntlTag(currentLocale()), {
        day: '2-digit',
        month: 'long',
        weekday: 'long',
        timeZone,
    }).format(new Date(value));
}
