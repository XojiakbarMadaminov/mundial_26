<script setup lang="ts">
import { computed, onMounted } from 'vue';

import MatchCard from '@/spa/components/MatchCard.vue';
import StateBlock from '@/spa/components/StateBlock.vue';
import { useCompetitionStore } from '@/spa/stores/competition';

const competition = useCompetitionStore();
const predictedMatches = computed(() =>
    competition.matches.filter((match) => match.my_prediction),
);

onMounted(() => {
    void competition.fetchMatches();
});
</script>

<template>
    <div class="grid gap-5">
        <div>
            <h1 class="text-2xl font-semibold">My Predictions</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Submitted match predictions and calculated points.
            </p>
        </div>

        <StateBlock
            v-if="predictedMatches.length === 0"
            title="No predictions yet"
            message="Open matches from the Matches page and submit your scores."
        />

        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            <MatchCard
                v-for="match in predictedMatches"
                :key="match.id"
                :match="match"
            />
        </div>
    </div>
</template>
