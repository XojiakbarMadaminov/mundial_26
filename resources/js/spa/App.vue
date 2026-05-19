<script setup lang="ts">
import { Menu, Monitor, Moon, Sun, Trophy } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { RouterLink, RouterView, useRoute, useRouter } from 'vue-router';

import { useAppearance } from '@/composables/useAppearance';
import LanguageSwitcher from '@/spa/components/LanguageSwitcher.vue';
import { t } from '@/spa/lib/i18n';
import { useAuthStore } from '@/spa/stores/auth';

const auth = useAuthStore();
const route = useRoute();
const router = useRouter();
const isOpen = ref(false);
const { appearance, updateAppearance } = useAppearance();

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

const userInitials = computed(() =>
    (auth.user?.name ?? 'U')
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part[0]?.toUpperCase())
        .join(''),
);

const appearanceTabs = [
    { value: 'light', icon: Sun, label: 'Light' },
    { value: 'dark', icon: Moon, label: 'Dark' },
    { value: 'system', icon: Monitor, label: 'System' },
] as const;

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
                    <div class="flex rounded-md border bg-muted/40 p-0.5">
                        <button
                            v-for="{
                                value,
                                icon: Icon,
                                label,
                            } in appearanceTabs"
                            :key="value"
                            :class="[
                                'flex size-8 items-center justify-center rounded-sm text-muted-foreground transition-colors hover:text-foreground',
                                appearance === value
                                    ? 'bg-background text-foreground shadow-sm'
                                    : '',
                            ]"
                            type="button"
                            :aria-label="label"
                            :title="label"
                            @click="updateAppearance(value)"
                        >
                            <component :is="Icon" class="size-4" />
                        </button>
                    </div>
                    <div
                        class="flex min-w-0 items-center gap-2 text-sm text-muted-foreground"
                    >
                        <img
                            v-if="auth.user?.telegram_photo_url"
                            :src="auth.user.telegram_photo_url"
                            :alt="auth.user.name"
                            class="size-8 rounded-full object-cover"
                            referrerpolicy="no-referrer"
                        />
                        <span
                            v-else
                            class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-semibold text-foreground"
                        >
                            {{ userInitials }}
                        </span>
                        <span class="max-w-40 truncate">
                            {{ auth.user?.name }}
                        </span>
                    </div>
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
                    <div
                        class="flex items-center justify-between gap-3 px-3 py-2"
                    >
                        <div class="flex min-w-0 items-center gap-2">
                            <img
                                v-if="auth.user?.telegram_photo_url"
                                :src="auth.user.telegram_photo_url"
                                :alt="auth.user.name"
                                class="size-8 rounded-full object-cover"
                                referrerpolicy="no-referrer"
                            />
                            <span
                                v-else
                                class="flex size-8 shrink-0 items-center justify-center rounded-full bg-muted text-xs font-semibold"
                            >
                                {{ userInitials }}
                            </span>
                            <span class="truncate text-sm font-medium">
                                {{ auth.user?.name }}
                            </span>
                        </div>
                    </div>
                    <div class="flex gap-1 px-3 py-2">
                        <button
                            v-for="{
                                value,
                                icon: Icon,
                                label,
                            } in appearanceTabs"
                            :key="value"
                            :class="[
                                'flex h-9 flex-1 items-center justify-center rounded-md border text-muted-foreground',
                                appearance === value
                                    ? 'bg-accent text-foreground'
                                    : 'hover:bg-accent hover:text-foreground',
                            ]"
                            type="button"
                            :aria-label="label"
                            @click="updateAppearance(value)"
                        >
                            <component :is="Icon" class="size-4" />
                        </button>
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
