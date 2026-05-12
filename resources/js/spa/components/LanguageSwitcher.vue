<script setup lang="ts">
import { computed } from 'vue';

import { availableLocales, setLocale, useLocale } from '@/spa/lib/i18n';

const locale = useLocale();

const current = computed(() => locale.value);
</script>

<template>
    <div class="inline-flex rounded-md border p-1">
        <button
            v-for="item in availableLocales"
            :key="item.code"
            class="rounded px-2 py-1 text-xs font-medium transition"
            :class="
                current === item.code
                    ? 'bg-primary text-primary-foreground'
                    : 'text-muted-foreground hover:bg-accent hover:text-foreground'
            "
            type="button"
            @click="setLocale(item.code)"
        >
            {{ item.label }}
        </button>
    </div>
</template>
