<script setup lang="ts">
import { Menu, Trophy } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router';

import LanguageSwitcher from '@/spa/components/LanguageSwitcher.vue';
import { t } from '@/spa/lib/i18n';
import { useAuthStore } from '@/spa/stores/auth';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const isOpen = ref(false);

const navigation = [
    { labelKey: 'dashboard', to: '/dashboard' },
    { labelKey: 'matches', to: '/matches' },
    { labelKey: 'myPredictions', to: '/predictions' },
    { labelKey: 'nominations', to: '/nominations' },
    { labelKey: 'leaderboard', to: '/leaderboard' },
    { labelKey: 'comparison', to: '/comparison' },
    { labelKey: 'rules', to: '/rules' },
];

const usesShell = computed(() => !['/login', '/register'].includes(route.path));

async function logout(): Promise<void> {
    await auth.logout();
    await router.push('/login');
}
</script>

<template>
    <RouterView v-if="!usesShell" />

    <div v-else class="min-h-screen bg-background text-foreground">
        <header
            class="sticky top-0 z-40 border-b bg-background/95 backdrop-blur"
        >
            <div
                class="mx-auto flex h-16 max-w-7xl items-center justify-between gap-4 px-4 sm:px-6 lg:px-8"
            >
                <RouterLink
                    to="/dashboard"
                    class="flex items-center gap-3 font-semibold"
                >
                    <span
                        class="flex size-9 items-center justify-center rounded-md bg-emerald-600 text-white"
                    >
                        <Trophy class="size-5" />
                    </span>
                    <span>{{ t('appName') }}</span>
                </RouterLink>

                <nav class="hidden items-center gap-1 lg:flex">
                    <RouterLink
                        v-for="item in navigation"
                        :key="item.to"
                        :to="item.to"
                        class="rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-foreground"
                        active-class="bg-accent text-foreground"
                    >
                        {{ t(item.labelKey) }}
                    </RouterLink>
                </nav>

                <div class="hidden items-center gap-3 lg:flex">
                    <LanguageSwitcher />
                    <span class="text-sm text-muted-foreground">{{
                        auth.user?.name
                    }}</span>
                    <button
                        class="rounded-md border px-3 py-2 text-sm font-medium hover:bg-accent"
                        type="button"
                        @click="logout"
                    >
                        {{ t('logout') }}
                    </button>
                </div>

                <button
                    class="rounded-md border p-2 lg:hidden"
                    type="button"
                    @click="isOpen = !isOpen"
                >
                    <Menu class="size-5" />
                </button>
            </div>

            <div v-if="isOpen" class="border-t px-4 py-3 lg:hidden">
                <nav class="grid gap-1">
                    <RouterLink
                        v-for="item in navigation"
                        :key="item.to"
                        :to="item.to"
                        class="rounded-md px-3 py-2 text-sm font-medium text-muted-foreground hover:bg-accent hover:text-foreground"
                        active-class="bg-accent text-foreground"
                        @click="isOpen = false"
                    >
                        {{ t(item.labelKey) }}
                    </RouterLink>
                    <div class="px-3 py-2">
                        <LanguageSwitcher />
                    </div>
                    <button
                        class="rounded-md px-3 py-2 text-left text-sm font-medium text-muted-foreground hover:bg-accent"
                        type="button"
                        @click="logout"
                    >
                        {{ t('logout') }}
                    </button>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <RouterView />
        </main>
    </div>
</template>
