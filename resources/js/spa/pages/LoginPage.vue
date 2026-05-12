<script setup lang="ts">
import { Trophy } from 'lucide-vue-next';
import { reactive, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';

import LanguageSwitcher from '@/spa/components/LanguageSwitcher.vue';
import { validationErrors } from '@/spa/lib/api';
import { t } from '@/spa/lib/i18n';
import { useAuthStore } from '@/spa/stores/auth';

const auth = useAuthStore();
const router = useRouter();
const form = reactive({ email: '', password: '' });
const errors = ref<Record<string, string>>({});
const message = ref('');
const loading = ref(false);

async function submit(): Promise<void> {
    loading.value = true;
    errors.value = {};
    message.value = '';

    try {
        await auth.login(form);
        await router.push('/dashboard');
    } catch (error) {
        errors.value = validationErrors(error);
        message.value = Object.keys(errors.value).length ? '' : t('loginFailed');
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <main class="grid min-h-screen place-items-center bg-background px-4">
        <div class="fixed right-4 top-4 z-10">
            <LanguageSwitcher />
        </div>
        <form
            class="w-full max-w-md rounded-md border bg-card p-6 shadow-sm"
            @submit.prevent="submit"
        >
            <div class="flex items-center gap-3">
                <span
                    class="flex size-10 items-center justify-center rounded-md bg-emerald-600 text-white"
                >
                    <Trophy class="size-5" />
                </span>
                <div>
                    <h1 class="text-xl font-semibold">{{ t('loginTitle') }}</h1>
                    <p class="text-sm text-muted-foreground">
                        {{ t('loginSubtitle') }}
                    </p>
                </div>
            </div>

            <div class="mt-6 grid gap-4">
                <label class="grid gap-1 text-sm font-medium">
                    {{ t('email') }}
                    <input
                        v-model="form.email"
                        class="rounded-md border bg-background px-3 py-2"
                        type="email"
                        autocomplete="email"
                        required
                    />
                    <span
                        v-if="errors.email"
                        class="text-xs text-destructive"
                        >{{ errors.email }}</span
                    >
                </label>
                <label class="grid gap-1 text-sm font-medium">
                    {{ t('password') }}
                    <input
                        v-model="form.password"
                        class="rounded-md border bg-background px-3 py-2"
                        type="password"
                        autocomplete="current-password"
                        required
                    />
                    <span
                        v-if="errors.password"
                        class="text-xs text-destructive"
                        >{{ errors.password }}</span
                    >
                </label>
            </div>

            <p v-if="message" class="mt-3 text-sm text-destructive">
                {{ message }}
            </p>

            <button
                class="mt-6 w-full rounded-md bg-primary px-4 py-2 font-medium text-primary-foreground disabled:opacity-50"
                type="submit"
                :disabled="loading"
            >
                {{ loading ? t('signingIn') : t('login') }}
            </button>

            <p class="mt-4 text-center text-sm text-muted-foreground">
                {{ t('noAccount') }}
                <RouterLink
                    class="font-medium text-foreground underline underline-offset-4"
                    to="/register"
                    >{{ t('register') }}</RouterLink
                >
            </p>
        </form>
    </main>
</template>
