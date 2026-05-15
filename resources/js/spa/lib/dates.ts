import { currentLocale, localeToIntlTag } from '@/spa/lib/i18n';

const timeZone = 'Asia/Tashkent';
const uzMonths = [
    'yanvar',
    'fevral',
    'mart',
    'aprel',
    'may',
    'iyun',
    'iyul',
    'avgust',
    'sentabr',
    'oktabr',
    'noyabr',
    'dekabr',
];
const uzWeekdays = [
    'Yakshanba',
    'Dushanba',
    'Seshanba',
    'Chorshanba',
    'Payshanba',
    'Juma',
    'Shanba',
];

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
    const locale = currentLocale();
    const date = new Date(value);
    const day = new Intl.DateTimeFormat('en-GB', {
        day: 'numeric',
        timeZone,
    }).format(date);

    if (locale === 'uz') {
        const parts = getTashkentDateParts(date);

        return `${parts.day}-${uzMonths[parts.month - 1]}, ${uzWeekdays[parts.weekday]}`;
    }

    const month = new Intl.DateTimeFormat(localeToIntlTag(locale), {
        month: 'long',
        timeZone,
    }).format(date);
    const weekday = new Intl.DateTimeFormat(localeToIntlTag(locale), {
        weekday: 'long',
        timeZone,
    }).format(date);

    return `${day}-${month}, ${capitalizeFirstLetter(weekday)}`;
}

function capitalizeFirstLetter(value: string): string {
    return value.charAt(0).toLocaleUpperCase(localeToIntlTag(currentLocale())) + value.slice(1);
}

function getTashkentDateParts(date: Date): {
    day: number;
    month: number;
    weekday: number;
} {
    const parts = new Intl.DateTimeFormat('en-CA', {
        day: 'numeric',
        month: 'numeric',
        timeZone,
        weekday: 'short',
    }).formatToParts(date);
    const weekdayMap: Record<string, number> = {
        Sun: 0,
        Mon: 1,
        Tue: 2,
        Wed: 3,
        Thu: 4,
        Fri: 5,
        Sat: 6,
    };

    return {
        day: Number(parts.find((part) => part.type === 'day')?.value ?? 1),
        month: Number(parts.find((part) => part.type === 'month')?.value ?? 1),
        weekday: weekdayMap[parts.find((part) => part.type === 'weekday')?.value ?? 'Sun'] ?? 0,
    };
}
