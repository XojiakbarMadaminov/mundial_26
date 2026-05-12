import axios from 'axios';

import { currentLocale, localeToIntlTag } from '@/spa/lib/i18n';

export const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token =
        typeof window !== 'undefined'
            ? window.localStorage.getItem('mundial_token')
            : null;
    const locale = currentLocale();

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    config.headers['X-Locale'] = locale;
    config.headers['Accept-Language'] = localeToIntlTag(locale);

    return config;
});

export function validationErrors(error: unknown): Record<string, string> {
    if (!axios.isAxiosError(error)) {
        return {};
    }

    const errors = error.response?.data?.errors;

    if (!errors || typeof errors !== 'object') {
        return {};
    }

    return Object.fromEntries(
        Object.entries(errors).map(([key, value]) => [
            key,
            Array.isArray(value) ? String(value[0]) : String(value),
        ]),
    );
}
