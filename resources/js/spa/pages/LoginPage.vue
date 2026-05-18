<script setup lang="ts">
import { Send, Trophy } from 'lucide-vue-next';
import { computed } from 'vue';
import { useRoute } from 'vue-router';

import LanguageSwitcher from '@/spa/components/LanguageSwitcher.vue';
import { t } from '@/spa/lib/i18n';

const route = useRoute();

const message = computed(() => {
    if (route.query.telegram_status === 'pending') {
        return t('pendingApprovalLogin');
    }

    if (route.query.telegram_status === 'unconfigured') {
        return t('telegramLoginUnconfigured');
    }

    if (route.query.telegram_status === 'failed') {
        return t('telegramLoginFailed');
    }

    return '';
});

function loginWithTelegram(): void {
    window.location.href = '/auth/telegram/redirect';
}
</script>

<template>
    <main class="grid min-h-screen place-items-center bg-background px-4">
        <div class="fixed top-4 right-4 z-10">
            <LanguageSwitcher />
        </div>
        <section
            class="w-full max-w-md rounded-md border bg-card p-6 shadow-sm"
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
                        {{ t('telegramLoginSubtitle') }}
                    </p>
                </div>
            </div>

            <p class="mt-6 text-sm text-muted-foreground">
                {{ t('telegramLoginHint') }}
            </p>

            <p v-if="message" class="mt-4 rounded-md border px-3 py-2 text-sm">
                {{ message }}
            </p>

            <button
                class="mt-6 flex w-full items-center justify-center gap-2 rounded-md bg-primary px-4 py-2 font-medium text-primary-foreground hover:bg-primary/90"
                type="button"
                @click="loginWithTelegram"
            >
                <Send class="size-4" />
                {{ t('loginWithTelegram') }}
            </button>
        </section>
    </main>
</template>
