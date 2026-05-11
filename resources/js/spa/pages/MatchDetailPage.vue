<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';

import PredictionForm from '@/spa/components/PredictionForm.vue';
import StateBlock from '@/spa/components/StateBlock.vue';
import { api } from '@/spa/lib/api';
import { formatDateTime } from '@/spa/lib/dates';
import type { Match } from '@/spa/lib/types';

const route = useRoute();
const match = ref<Match | null>(null);
const loading = ref(false);

async function loadMatch(): Promise<void> {
    loading.value = true;

    try {
        const response = await api.get<{ data: Match }>(
            `/matches/${route.params.id}`,
        );
        match.value = response.data.data;
    } finally {
        loading.value = false;
    }
}

onMounted(loadMatch);
</script>

<template>
    <StateBlock v-if="loading" title="Loading match" />
    <div v-else-if="match" class="grid gap-6 lg:grid-cols-[1fr_24rem]">
        <section class="rounded-md border bg-card p-5">
            <p
                class="text-sm font-medium tracking-wide text-muted-foreground uppercase"
            >
                {{ match.stage.replaceAll('_', ' ') }}
            </p>
            <div class="mt-5 grid gap-4 text-lg font-semibold">
                <div class="flex items-center justify-between gap-4">
                    <span>{{ match.home_team?.name ?? 'TBD' }}</span>
                    <span v-if="match.status === 'finished'">{{
                        match.result?.home_score
                    }}</span>
                </div>
                <div class="flex items-center justify-between gap-4">
                    <span>{{ match.away_team?.name ?? 'TBD' }}</span>
                    <span v-if="match.status === 'finished'">{{
                        match.result?.away_score
                    }}</span>
                </div>
            </div>
            <div class="mt-5 grid gap-2 text-sm text-muted-foreground">
                <p>Start: {{ formatDateTime(match.starts_at) }}</p>
                <p>Lock: {{ formatDateTime(match.lock_at) }}</p>
                <p>Status: {{ match.status }}</p>
                <p v-if="match.points">
                    Your points: {{ match.points.total_points }}
                </p>
            </div>
        </section>

        <PredictionForm :match="match" @saved="loadMatch" />
    </div>
    <StateBlock v-else title="Match not found" />
</template>
