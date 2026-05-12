<script setup lang="ts">
import { reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';

import LanguageSwitcher from '@/spa/components/LanguageSwitcher.vue';
import { validationErrors } from '@/spa/lib/api';
import { t } from '@/spa/lib/i18n';
import { useAuthStore } from '@/spa/stores/auth';

const auth = useAuthStore();
const form = reactive({
    name: '',
    email: '',
    telegram_username: '',
    phone: '',
    password: '',
    password_confirmation: '',
});
const errors = ref<Record<string, string>>({});
const message = ref('');
const loading = ref(false);

async function submit(): Promise<void> {
    loading.value = true;
    errors.value = {};
    message.value = '';

    try {
        await auth.register(form);
        form.name = '';
        form.email = '';
        form.telegram_username = '';
        form.phone = '';
        form.password = '';
        form.password_confirmation = '';
        message.value = t('moderationMessage');
    } catch (error) {
        errors.value = validationErrors(error);
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <main class="grid min-h-screen place-items-center bg-background px-4 py-8">
        <div class="fixed right-4 top-4 z-10">
            <LanguageSwitcher />
        </div>
        <form
            class="w-full max-w-lg rounded-md border bg-card p-6 shadow-sm"
            @submit.prevent="submit"
        >
            <h1 class="text-xl font-semibold">{{ t('registerTitle') }}</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ t('registerSubtitle') }}
            </p>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <label class="grid gap-1 text-sm font-medium sm:col-span-2">
                    {{ t('name') }}
                    <input
                        v-model="form.name"
                        class="rounded-md border bg-background px-3 py-2"
                        required
                    />
                    <span v-if="errors.name" class="text-xs text-destructive">{{
                        errors.name
                    }}</span>
                </label>
                <label class="grid gap-1 text-sm font-medium sm:col-span-2">
                    {{ t('email') }}
                    <input
                        v-model="form.email"
                        class="rounded-md border bg-background px-3 py-2"
                        type="email"
                        required
                    />
                    <span
                        v-if="errors.email"
                        class="text-xs text-destructive"
                        >{{ errors.email }}</span
                    >
                </label>
                <label class="grid gap-1 text-sm font-medium">
                    {{ t('telegram') }}
                    <input
                        v-model="form.telegram_username"
                        class="rounded-md border bg-background px-3 py-2"
                    />
                    <span
                        v-if="errors.telegram_username"
                        class="text-xs text-destructive"
                        >{{ errors.telegram_username }}</span
                    >
                </label>
                <label class="grid gap-1 text-sm font-medium">
                    {{ t('phone') }}
                    <input
                        v-model="form.phone"
                        class="rounded-md border bg-background px-3 py-2"
                    />
                    <span
                        v-if="errors.phone"
                        class="text-xs text-destructive"
                        >{{ errors.phone }}</span
                    >
                </label>
                <label class="grid gap-1 text-sm font-medium">
                    {{ t('password') }}
                    <input
                        v-model="form.password"
                        class="rounded-md border bg-background px-3 py-2"
                        type="password"
                        required
                    />
                    <span
                        v-if="errors.password"
                        class="text-xs text-destructive"
                        >{{ errors.password }}</span
                    >
                </label>
                <label class="grid gap-1 text-sm font-medium">
                    {{ t('confirmPassword') }}
                    <input
                        v-model="form.password_confirmation"
                        class="rounded-md border bg-background px-3 py-2"
                        type="password"
                        required
                    />
                </label>
            </div>

            <button
                class="mt-6 w-full rounded-md bg-primary px-4 py-2 font-medium text-primary-foreground disabled:opacity-50"
                type="submit"
                :disabled="loading"
            >
                {{ loading ? t('creating') : t('register') }}
            </button>

            <p
                v-if="message"
                class="mt-4 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-800 dark:border-emerald-900 dark:bg-emerald-950 dark:text-emerald-200"
            >
                {{ message }}
            </p>

            <p class="mt-4 text-center text-sm text-muted-foreground">
                {{ t('alreadyRegistered') }}
                <RouterLink
                    class="font-medium text-foreground underline underline-offset-4"
                    to="/login"
                    >{{ t('login') }}</RouterLink
                >
            </p>
        </form>
    </main>
</template>
