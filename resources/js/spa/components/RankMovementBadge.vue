<script setup lang="ts">
import { computed } from 'vue';

import { t } from '@/spa/lib/i18n';

const props = defineProps<{
    rank?: number | null;
    previousRank?: number | null;
    changedAt?: string | null;
}>();

const movement = computed(() => {
    if (!props.rank || !props.previousRank || !props.changedAt) {
        return null;
    }

    const changedAt = new Date(props.changedAt).getTime();
    const oneDay = 24 * 60 * 60 * 1000;

    if (Date.now() - changedAt > oneDay) {
        return null;
    }

    if (props.previousRank === props.rank) {
        return null;
    }

    const delta = Math.abs(props.previousRank - props.rank);

    return {
        isUp: props.previousRank > props.rank,
        text: t(props.previousRank > props.rank ? 'rankUp' : 'rankDown', {
            count: delta,
        }),
    };
});
</script>

<template>
    <span
        v-if="movement"
        class="inline-flex items-center rounded-md px-2 py-0.5 text-xs font-semibold"
        :class="
            movement.isUp
                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-300'
                : 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-300'
        "
    >
        {{ movement.text }}
    </span>
</template>
