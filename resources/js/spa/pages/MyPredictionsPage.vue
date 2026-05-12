<script setup lang="ts">
import { computed, onMounted } from 'vue';

import MatchCard from '@/spa/components/MatchCard.vue';
import StateBlock from '@/spa/components/StateBlock.vue';
import { t } from '@/spa/lib/i18n';
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
            <h1 class="text-2xl font-semibold">{{ t('myPredictions') }}</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ t('predictionsFromMatches') }}
            </p>
        </div>

        <StateBlock
            v-if="predictedMatches.length === 0"
            :title="t('noPredictionsYet')"
            :message="t('openMatchesHint')"
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
