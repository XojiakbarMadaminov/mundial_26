import { createRouter, createWebHistory } from 'vue-router';

import DashboardPage from '@/spa/pages/DashboardPage.vue';
import LeaderboardPage from '@/spa/pages/LeaderboardPage.vue';
import ComparisonPage from '@/spa/pages/ComparisonPage.vue';
import LoginPage from '@/spa/pages/LoginPage.vue';
import MatchDetailPage from '@/spa/pages/MatchDetailPage.vue';
import MatchesPage from '@/spa/pages/MatchesPage.vue';
import MyPredictionsPage from '@/spa/pages/MyPredictionsPage.vue';
import NominationsPage from '@/spa/pages/NominationsPage.vue';
import RulesPage from '@/spa/pages/RulesPage.vue';
import { useAuthStore } from '@/spa/stores/auth';

export const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/', redirect: '/dashboard' },
        { path: '/login', component: LoginPage, meta: { guest: true } },
        { path: '/register', redirect: '/login' },
        { path: '/dashboard', component: DashboardPage, meta: { auth: true } },
        { path: '/matches', component: MatchesPage, meta: { auth: true } },
        {
            path: '/matches/:id',
            component: MatchDetailPage,
            meta: { auth: true },
        },
        {
            path: '/predictions',
            component: MyPredictionsPage,
            meta: { auth: true },
        },
        {
            path: '/nominations',
            component: NominationsPage,
            meta: { auth: true },
        },
        {
            path: '/leaderboard',
            component: LeaderboardPage,
            meta: { auth: true },
        },
        {
            path: '/comparison',
            component: ComparisonPage,
            meta: { auth: true },
        },
        { path: '/rules', component: RulesPage, meta: { auth: true } },
    ],
});

router.beforeEach((to) => {
    const auth = useAuthStore();

    if (to.meta.auth && !auth.isAuthenticated) {
        return '/login';
    }

    if (to.meta.guest && auth.isAuthenticated) {
        return '/dashboard';
    }
});
