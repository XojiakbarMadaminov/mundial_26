<script setup lang="ts">
import { onMounted } from 'vue';

import RankMovementBadge from '@/spa/components/RankMovementBadge.vue';
import StateBlock from '@/spa/components/StateBlock.vue';
import { t } from '@/spa/lib/i18n';
import { useAuthStore } from '@/spa/stores/auth';
import { useCompetitionStore } from '@/spa/stores/competition';

const auth = useAuthStore();
const competition = useCompetitionStore();

onMounted(() => {
    void competition.fetchLeaderboard();
});
</script>

<template>
    <div class="grid gap-5">
        <div>
            <h1 class="text-2xl font-semibold">{{ t('leaderboard') }}</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ t('leaderboardIntro') }}
            </p>
        </div>

        <StateBlock
            v-if="competition.leaderboard.length === 0"
            :title="t('leaderboardEmpty')"
        />

        <div v-else class="overflow-hidden rounded-md border bg-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead class="bg-muted/60 text-left text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3">{{ t('rankColumn') }}</th>
                            <th class="px-4 py-3">
                                {{ t('participantColumn') }}
                            </th>
                            <th class="px-4 py-3 text-right">
                                {{ t('matchColumn') }}
                            </th>
                            <th class="px-4 py-3 text-right">
                                {{ t('nominationColumn') }}
                            </th>
                            <th class="px-4 py-3 text-right">
                                {{ t('totalColumn') }}
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr
                            v-for="entry in competition.leaderboard"
                            :key="entry.id"
                            :class="
                                entry.user.id === auth.user?.id
                                    ? 'bg-emerald-50 dark:bg-emerald-950/30'
                                    : ''
                            "
                        >
                            <td class="px-4 py-3 font-semibold">
                                <div class="flex items-center gap-2">
                                    <span>#{{ entry.rank ?? '-' }}</span>
                                    <RankMovementBadge
                                        :changed-at="entry.rank_changed_at"
                                        :previous-rank="entry.previous_rank"
                                        :rank="entry.rank"
                                    />
                                </div>
                            </td>
                            <td class="px-4 py-3">{{ entry.user.name }}</td>
                            <td class="px-4 py-3 text-right">
                                {{ entry.match_points }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                {{ entry.nomination_points }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold">
                                {{ entry.total_points }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>
