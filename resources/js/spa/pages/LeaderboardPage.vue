<script setup lang="ts">
import { onMounted } from 'vue';

import StateBlock from '@/spa/components/StateBlock.vue';
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
            <h1 class="text-2xl font-semibold">Leaderboard</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Rankings by total points.
            </p>
        </div>

        <StateBlock
            v-if="competition.leaderboard.length === 0"
            title="Leaderboard is empty"
        />

        <div v-else class="overflow-hidden rounded-md border bg-card">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-sm">
                    <thead class="bg-muted/60 text-left text-muted-foreground">
                        <tr>
                            <th class="px-4 py-3">Rank</th>
                            <th class="px-4 py-3">Participant</th>
                            <th class="px-4 py-3 text-right">Match</th>
                            <th class="px-4 py-3 text-right">Nomination</th>
                            <th class="px-4 py-3 text-right">Total</th>
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
                                #{{ entry.rank ?? '-' }}
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
