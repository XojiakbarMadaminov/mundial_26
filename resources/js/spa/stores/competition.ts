import { defineStore } from 'pinia';

import { api } from '@/spa/lib/api';
import type {
    LeaderboardEntry,
    Match,
    NominationCategory,
    NominationPrediction,
    Tournament,
} from '@/spa/lib/types';

export const useCompetitionStore = defineStore('competition', {
    state: () => ({
        tournament: null as Tournament | null,
        matches: [] as Match[],
        todayMatches: [] as Match[],
        leaderboard: [] as LeaderboardEntry[],
        nominationCategories: [] as NominationCategory[],
        nominationPredictions: [] as NominationPrediction[],
        nominationMeta: null as {
            lock_at: string | null;
            is_locked: boolean;
        } | null,
        loading: false,
    }),
    actions: {
        async fetchTournament(): Promise<void> {
            const response = await api.get<{ data: Tournament }>(
                '/tournaments/current',
            );

            this.tournament = response.data.data;
        },
        async fetchMatches(): Promise<void> {
            const response = await api.get<{ data: Match[] }>('/matches');

            this.matches = response.data.data;
        },
        async fetchTodayMatches(): Promise<void> {
            const response = await api.get<{ data: Match[] }>('/matches/today');

            this.todayMatches = response.data.data;
        },
        async fetchLeaderboard(): Promise<void> {
            const response = await api.get<{ data: LeaderboardEntry[] }>(
                '/leaderboard',
            );

            this.leaderboard = response.data.data;
        },
        async fetchNominations(): Promise<void> {
            const response = await api.get<{
                data: NominationCategory[];
                meta: { lock_at: string | null; is_locked: boolean };
            }>('/nominations');

            this.nominationCategories = response.data.data;
            this.nominationMeta = response.data.meta;
        },
        async fetchMyNominationPredictions(): Promise<void> {
            const response = await api.get<{ data: NominationPrediction[] }>(
                '/my-nomination-predictions',
            );

            this.nominationPredictions = response.data.data;
        },
        async loadDashboard(): Promise<void> {
            this.loading = true;

            try {
                await Promise.all([
                    this.fetchTournament(),
                    this.fetchMatches(),
                    this.fetchTodayMatches(),
                    this.fetchLeaderboard(),
                ]);
            } finally {
                this.loading = false;
            }
        },
    },
});
