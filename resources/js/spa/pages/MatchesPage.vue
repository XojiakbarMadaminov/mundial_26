<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

import MatchCard from '@/spa/components/MatchCard.vue';
import StateBlock from '@/spa/components/StateBlock.vue';
import { formatDateKey } from '@/spa/lib/dates';
import { t } from '@/spa/lib/i18n';
import { useCompetitionStore } from '@/spa/stores/competition';

const competition = useCompetitionStore();
const filter = ref('all');

const filters = [
    { key: 'all', labelKey: 'all' },
    { key: 'today', labelKey: 'today' },
    { key: 'upcoming', labelKey: 'upcoming' },
    { key: 'finished', labelKey: 'finished' },
    { key: 'open', labelKey: 'predictionOpen' },
];

const filteredMatches = computed(() => {
    const today = new Intl.DateTimeFormat('en-CA', {
        timeZone: 'Asia/Tashkent',
    }).format(new Date());

    return competition.matches.filter((match) => {
        if (filter.value === 'today') {
            return (
                new Intl.DateTimeFormat('en-CA', {
                    timeZone: 'Asia/Tashkent',
                }).format(new Date(match.starts_at)) === today
            );
        }

        if (filter.value === 'upcoming') {
            return match.status === 'scheduled';
        }

        if (filter.value === 'finished') {
            return match.status === 'finished';
        }

        if (filter.value === 'open') {
            return !match.is_prediction_locked;
        }

        return true;
    });
});

const groups = computed(() => {
    return filteredMatches.value.reduce<
        Record<string, typeof filteredMatches.value>
    >((carry, match) => {
        const key = formatDateKey(match.starts_at);
        carry[key] ??= [];
        carry[key].push(match);

        return carry;
    }, {});
});

onMounted(() => {
    void competition.fetchMatches();
});
</script>

<template>
    <div class="grid gap-5">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h1 class="text-2xl font-semibold">{{ t('matches') }}</h1>
            <div class="flex flex-wrap gap-2">
                <button
                    v-for="item in filters"
                    :key="item.key"
                    class="rounded-md border px-3 py-2 text-sm font-medium"
                    :class="
                        filter === item.key
                            ? 'bg-primary text-primary-foreground'
                            : 'hover:bg-accent'
                    "
                    type="button"
                    @click="filter = item.key"
                >
                    {{ t(item.labelKey) }}
                </button>
            </div>
        </div>

        <StateBlock
            v-if="filteredMatches.length === 0"
            :title="t('noMatchesFound')"
        />
        <section
            v-for="(matches, date) in groups"
            :key="date"
            class="grid gap-3"
        >
            <h2
                class="text-sm font-semibold tracking-wide text-muted-foreground uppercase"
            >
                {{ date }}
            </h2>
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
                <MatchCard
                    v-for="match in matches"
                    :key="match.id"
                    :match="match"
                />
            </div>
        </section>
    </div>
</template>
