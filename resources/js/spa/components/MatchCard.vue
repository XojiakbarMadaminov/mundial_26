<script setup lang="ts">
import { Clock } from 'lucide-vue-next';
import { RouterLink } from 'vue-router';

import { formatDateTime, formatTime } from '@/spa/lib/dates';
import type { Match } from '@/spa/lib/types';

defineProps<{
    match: Match;
}>();
</script>

<template>
    <article class="rounded-md border bg-card p-4 shadow-sm">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p
                    class="text-xs font-medium tracking-wide text-muted-foreground uppercase"
                >
                    {{ match.stage.replaceAll('_', ' ') }}
                    <span v-if="match.group_name">
                        · Group {{ match.group_name }}</span
                    >
                </p>
                <div class="mt-3 grid gap-2 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="min-w-0 flex-1 font-medium">{{
                            match.home_team?.name ?? 'TBD'
                        }}</span>
                        <span
                            v-if="match.status === 'finished'"
                            class="font-semibold"
                            >{{ match.result?.home_score }}</span
                        >
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="min-w-0 flex-1 font-medium">{{
                            match.away_team?.name ?? 'TBD'
                        }}</span>
                        <span
                            v-if="match.status === 'finished'"
                            class="font-semibold"
                            >{{ match.result?.away_score }}</span
                        >
                    </div>
                </div>
            </div>

            <span
                class="rounded-md px-2 py-1 text-xs font-medium"
                :class="
                    match.is_prediction_locked
                        ? 'bg-muted text-muted-foreground'
                        : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                "
            >
                {{ match.is_prediction_locked ? 'Locked' : 'Open' }}
            </span>
        </div>

        <div class="mt-4 grid gap-2 text-xs text-muted-foreground">
            <p class="flex items-center gap-2">
                <Clock class="size-4" />
                Start: {{ formatDateTime(match.starts_at) }}
            </p>
            <p>Lock: {{ formatTime(match.lock_at) }}</p>
            <p v-if="match.my_prediction">
                My prediction: {{ match.my_prediction.home_score }} -
                {{ match.my_prediction.away_score }}
            </p>
            <p v-else>No prediction yet</p>
            <p v-if="match.points">Points: {{ match.points.total_points }}</p>
        </div>

        <RouterLink
            :to="`/matches/${match.id}`"
            class="mt-4 inline-flex w-full items-center justify-center rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground hover:bg-primary/90 sm:w-auto"
        >
            {{
                match.is_prediction_locked
                    ? 'View match'
                    : match.my_prediction
                      ? 'Edit prediction'
                      : 'Submit prediction'
            }}
        </RouterLink>
    </article>
</template>
