<script setup lang="ts">
import { computed, onMounted } from 'vue';
import { RouterLink } from 'vue-router';

import MatchCard from '@/spa/components/MatchCard.vue';
import StateBlock from '@/spa/components/StateBlock.vue';
import { useAuthStore } from '@/spa/stores/auth';
import { useCompetitionStore } from '@/spa/stores/competition';

const auth = useAuthStore();
const competition = useCompetitionStore();

const myEntry = computed(() =>
    competition.leaderboard.find((entry) => entry.user.id === auth.user?.id),
);
const missingPredictions = computed(() =>
    competition.matches
        .filter((match) => !match.is_prediction_locked && !match.my_prediction)
        .slice(0, 6),
);
const topFive = computed(() => competition.leaderboard.slice(0, 5));

onMounted(() => {
    void competition.loadDashboard();
});
</script>

<template>
    <div class="grid gap-6">
        <section class="grid gap-4 lg:grid-cols-[1fr_22rem]">
            <div class="rounded-md border bg-card p-5">
                <p class="text-sm text-muted-foreground">Current tournament</p>
                <h1 class="mt-1 text-2xl font-semibold">
                    {{ competition.tournament?.name ?? 'Tournament' }}
                </h1>
                <p class="mt-2 text-sm text-muted-foreground">
                    Track your predictions, nominations, and leaderboard
                    position in one place.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="rounded-md border bg-card p-4">
                    <p class="text-sm text-muted-foreground">Total points</p>
                    <p class="mt-2 text-3xl font-semibold">
                        {{ myEntry?.total_points ?? 0 }}
                    </p>
                </div>
                <div class="rounded-md border bg-card p-4">
                    <p class="text-sm text-muted-foreground">Rank</p>
                    <p class="mt-2 text-3xl font-semibold">
                        {{ myEntry?.rank ?? '-' }}
                    </p>
                </div>
            </div>
        </section>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="grid gap-3">
                <div class="flex items-center justify-between gap-3">
                    <h2 class="text-lg font-semibold">Today matches</h2>
                    <RouterLink
                        class="text-sm font-medium underline underline-offset-4"
                        to="/matches"
                        >All matches</RouterLink
                    >
                </div>
                <StateBlock
                    v-if="
                        !competition.loading &&
                        competition.todayMatches.length === 0
                    "
                    title="No matches today"
                />
                <div class="grid gap-3">
                    <MatchCard
                        v-for="match in competition.todayMatches"
                        :key="match.id"
                        :match="match"
                    />
                </div>
            </div>

            <div class="grid gap-3">
                <h2 class="text-lg font-semibold">Prediction needed</h2>
                <StateBlock
                    v-if="
                        !competition.loading && missingPredictions.length === 0
                    "
                    title="All open matches have predictions"
                />
                <div class="grid gap-3">
                    <MatchCard
                        v-for="match in missingPredictions"
                        :key="match.id"
                        :match="match"
                    />
                </div>
            </div>
        </section>

        <section class="rounded-md border bg-card">
            <div class="border-b p-4">
                <h2 class="text-lg font-semibold">Top 5 leaderboard</h2>
            </div>
            <div class="divide-y">
                <div
                    v-for="entry in topFive"
                    :key="entry.id"
                    class="grid grid-cols-[3rem_1fr_5rem] items-center gap-3 p-4 text-sm"
                >
                    <span class="font-semibold">#{{ entry.rank ?? '-' }}</span>
                    <span>{{ entry.user.name }}</span>
                    <span class="text-right font-semibold">{{
                        entry.total_points
                    }}</span>
                </div>
            </div>
        </section>
    </div>
</template>
