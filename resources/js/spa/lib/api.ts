import axios from 'axios';

export const api = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
    },
});

api.interceptors.request.use((config) => {
    const token = localStorage.getItem('mundial_token');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

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
