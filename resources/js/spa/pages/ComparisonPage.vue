<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';

import ComparisonController from '@/actions/App/Http/Controllers/Api/ComparisonController';
import StateBlock from '@/spa/components/StateBlock.vue';
import { api } from '@/spa/lib/api';
import { formatDateTime } from '@/spa/lib/dates';
import { t, translateStage } from '@/spa/lib/i18n';
import { useAuthStore } from '@/spa/stores/auth';
import { useCompetitionStore } from '@/spa/stores/competition';
import type { ComparisonParticipant, ComparisonResponse } from '@/spa/lib/types';

const auth = useAuthStore();
const competition = useCompetitionStore();
const comparison = ref<ComparisonResponse | null>(null);
const selectedOpponentId = ref<number | null>(null);
const loadingCompetition = ref(false);
const loadedLeaderboard = ref(false);

const opponentOptions = computed(() =>
    competition.leaderboard.filter((entry) => entry.user?.id !== auth.user?.id),
);

const emptyParticipant: ComparisonParticipant = {
    id: 0,
    name: '',
    rank: null,
    match_points: 0,
    nomination_points: 0,
    total_points: 0,
    exact_scores_count: 0,
    goal_difference_count: 0,
    result_count: 0,
};

const me = computed(() => comparison.value?.me ?? emptyParticipant);
const opponent = computed(() => comparison.value?.opponent ?? emptyParticipant);
const comparisonMatches = computed(() => comparison.value?.matches ?? []);
const comparisonNominations = computed(() => comparison.value?.nominations ?? []);

function predictionLabel(
    prediction: {
        home_score?: number | null;
        away_score?: number | null;
        home_penalty_score?: number | null;
        away_penalty_score?: number | null;
    } | null | undefined,
): string {
    if (!prediction) {
        return t('tbd');
    }

    const base = `${prediction.home_score ?? 0} - ${prediction.away_score ?? 0}`;

    if (
        prediction.home_penalty_score !== null &&
        prediction.home_penalty_score !== undefined &&
        prediction.away_penalty_score !== null &&
        prediction.away_penalty_score !== undefined
    ) {
        return `${base} (${prediction.home_penalty_score} - ${prediction.away_penalty_score})`;
    }

    return base;
}

function nominationLabel(
    prediction:
        | {
              player?: { name: string } | null;
              team?: { name: string } | null;
              value_text?: string | null;
              value_number?: number | null;
          }
        | null
        | undefined,
): string {
    if (!prediction) {
        return t('tbd');
    }

    if (prediction.player) {
        return prediction.player.name;
    }

    if (prediction.team) {
        return prediction.team.name;
    }

    if (prediction.value_text) {
        return prediction.value_text;
    }

    if (prediction.value_number !== null && prediction.value_number !== undefined) {
        return String(prediction.value_number);
    }

    return t('tbd');
}

async function loadComparison(opponentId: number): Promise<void> {
    loadingCompetition.value = true;

    try {
        const comparisonUrl = ComparisonController.show(opponentId).url.replace(
            /^\/api/,
            '',
        );
        const response = await api.get<ComparisonResponse | { data: ComparisonResponse }>(
            comparisonUrl,
        );

        comparison.value = 'data' in response.data ? response.data.data : response.data;
    } finally {
        loadingCompetition.value = false;
    }
}

onMounted(async () => {
    await competition.fetchLeaderboard();
    loadedLeaderboard.value = true;

    if (!selectedOpponentId.value) {
        selectedOpponentId.value = opponentOptions.value[0]?.user?.id ?? null;
    }
});

watch(selectedOpponentId, async (next) => {
    if (!loadedLeaderboard.value || !next) {
        comparison.value = null;
        return;
    }

    await loadComparison(next);
}, { immediate: true });
</script>

<template>
    <div class="grid gap-6">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold">{{ t('comparisonTitle') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ t('comparisonSubtitle') }}
                </p>
            </div>

            <label class="grid gap-1 text-sm font-medium">
                <span>{{ t('comparisonOpponent') }}</span>
                <select
                    v-model.number="selectedOpponentId"
                    class="min-w-64 rounded-md border bg-background px-3 py-2"
                >
                    <option :value="null" disabled>
                        {{ t('comparisonSelectPlaceholder') }}
                    </option>
                    <option
                        v-for="entry in opponentOptions"
                        :key="entry.user?.id ?? entry.id"
                        :value="entry.user?.id ?? entry.id"
                    >
                        {{ entry.user?.name ?? t('tbd') }}
                    </option>
                </select>
            </label>
        </div>

        <StateBlock
            v-if="!loadedLeaderboard || loadingCompetition"
            :title="t('comparisonTitle')"
            :message="t('comparisonSubtitle')"
        />

        <StateBlock
            v-else-if="opponentOptions.length === 0"
            :title="t('noComparisonOpponents')"
        />

        <template v-else-if="comparison">
            <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <article class="rounded-md border bg-card p-4">
                    <p class="text-xs font-medium uppercase text-muted-foreground">
                        {{ t('yourSide') }}
                    </p>
                    <h2 class="mt-2 text-lg font-semibold">
                        {{ me.name || t('tbd') }}
                    </h2>
                    <div class="mt-3 grid gap-2 text-sm text-muted-foreground">
                        <p>{{ t('rank') }}: #{{ me.rank ?? '-' }}</p>
                        <p>{{ t('matchColumn') }}: {{ me.match_points }}</p>
                        <p>
                            {{ t('nominationColumn') }}:
                            {{ me.nomination_points }}
                        </p>
                        <p>{{ t('totalColumn') }}: {{ me.total_points }}</p>
                    </div>
                </article>

                <article class="rounded-md border bg-card p-4">
                    <p class="text-xs font-medium uppercase text-muted-foreground">
                        {{ t('opponentSide') }}
                    </p>
                    <h2 class="mt-2 text-lg font-semibold">
                        {{ opponent.name || t('tbd') }}
                    </h2>
                    <div class="mt-3 grid gap-2 text-sm text-muted-foreground">
                        <p>{{ t('rank') }}: #{{ opponent.rank ?? '-' }}</p>
                        <p>{{ t('matchColumn') }}: {{ opponent.match_points }}</p>
                        <p>
                            {{ t('nominationColumn') }}:
                            {{ opponent.nomination_points }}
                        </p>
                        <p>{{ t('totalColumn') }}: {{ opponent.total_points }}</p>
                    </div>
                </article>

                <article class="rounded-md border bg-card p-4">
                    <p class="text-xs font-medium uppercase text-muted-foreground">
                        {{ t('exactScoresCount') }}
                    </p>
                    <p class="mt-2 text-2xl font-semibold">
                        {{ me.exact_scores_count }} / {{ opponent.exact_scores_count }}
                    </p>
                    <p class="mt-1 text-sm text-muted-foreground">
                        {{ t('goalDifferenceCount') }}:
                        {{ me.goal_difference_count }} / {{ opponent.goal_difference_count }}
                    </p>
                </article>
            </section>

            <section class="grid gap-3">
                <div>
                    <h2 class="text-lg font-semibold">{{ t('comparisonMatches') }}</h2>
                </div>
                <StateBlock
                    v-if="comparisonMatches.length === 0"
                    :title="t('noComparisonData')"
                />
                <div v-else class="overflow-hidden rounded-md border bg-card">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[820px] text-sm">
                            <thead class="bg-muted/60 text-left text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">{{ t('matchColumn') }}</th>
                                    <th class="px-4 py-3">{{ t('yourSide') }}</th>
                                    <th class="px-4 py-3">{{ t('opponentSide') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="match in comparisonMatches" :key="match.id">
                                    <td class="px-4 py-3">
                                        <p class="font-medium">
                                            {{
                                                match.home_team?.name ?? t('tbd')
                                            }} - {{
                                                match.away_team?.name ?? t('tbd')
                                            }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ translateStage(match.stage) }}
                                            <span v-if="match.group_name">· {{ match.group_name }}</span>
                                            · {{ formatDateTime(match.starts_at) }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium">
                                            {{ predictionLabel(match.me_prediction) }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{
                                                match.me_prediction?.points?.total_points ??
                                                t('tbd')
                                            }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium">
                                            {{ predictionLabel(match.opponent_prediction) }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{
                                                match.opponent_prediction?.points?.total_points ??
                                                t('tbd')
                                            }}
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

            <section class="grid gap-3">
                <div>
                    <h2 class="text-lg font-semibold">
                        {{ t('comparisonNominations') }}
                    </h2>
                </div>
                <StateBlock
                    v-if="comparisonNominations.length === 0"
                    :title="t('noComparisonData')"
                />
                <div v-else class="overflow-hidden rounded-md border bg-card">
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[760px] text-sm">
                            <thead class="bg-muted/60 text-left text-muted-foreground">
                                <tr>
                                    <th class="px-4 py-3">{{ t('nominationColumn') }}</th>
                                    <th class="px-4 py-3">{{ t('yourSide') }}</th>
                                    <th class="px-4 py-3">{{ t('opponentSide') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="item in comparisonNominations" :key="item.id">
                                    <td class="px-4 py-3">
                                        <p class="font-medium">{{ item.name }}</p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ item.points }} {{ t('pointsUnit') }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium">
                                            {{ nominationLabel(item.me_prediction) }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ item.me_prediction?.points ?? t('tbd') }}
                                        </p>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium">
                                            {{ nominationLabel(item.opponent_prediction) }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ item.opponent_prediction?.points ?? t('tbd') }}
                                        </p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </template>
    </div>
</template>
