import { defineStore } from 'pinia';

import { api } from '@/spa/lib/api';
import type { User } from '@/spa/lib/types';

type LoginPayload = {
    email: string;
    password: string;
};

type RegisterPayload = {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    telegram_username?: string;
    phone?: string;
};

export const useAuthStore = defineStore('auth', {
    state: () => ({
        token: localStorage.getItem('mundial_token'),
        user: null as User | null,
        loading: false,
    }),
    getters: {
        isAuthenticated: (state) => Boolean(state.token),
    },
    actions: {
        async boot(): Promise<void> {
            if (!this.token) {
                return;
            }

            await this.fetchUser();
        },
        async fetchUser(): Promise<void> {
            try {
                const response = await api.get<User>('/user');

                this.user = response.data;
            } catch {
                this.clearSession();
            }
        },
        async login(payload: LoginPayload): Promise<void> {
            const response = await api.post<{ token: string; user: User }>(
                '/login',
                payload,
            );

            this.setSession(response.data.token, response.data.user);
        },
        async register(payload: RegisterPayload): Promise<void> {
            await api.post<{ message: string }>(
                '/register',
                payload,
            );
        },
        async logout(): Promise<void> {
            try {
                await api.post('/logout');
            } finally {
                this.clearSession();
            }
        },
        setSession(token: string, user: User): void {
            this.token = token;
            this.user = user;
            localStorage.setItem('mundial_token', token);
        },
        clearSession(): void {
            this.token = null;
            this.user = null;
            localStorage.removeItem('mundial_token');
        },
    },
});
