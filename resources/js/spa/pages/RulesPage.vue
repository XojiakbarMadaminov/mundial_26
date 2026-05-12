<script setup lang="ts">
import { t } from '@/spa/lib/i18n';
</script>

<template>
    <article class="mx-auto grid max-w-3xl gap-6">
        <div>
            <h1 class="text-2xl font-semibold">{{ t('rulesTitle') }}</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                {{ t('rulesSubtitle') }}
            </p>
        </div>

        <section class="rounded-md border bg-card p-5">
            <h2 class="font-semibold">{{ t('scorePredictionRule') }}</h2>
            <div class="mt-4 grid gap-3 text-sm leading-6 text-muted-foreground">
                <p>
                    {{ t('exactScore') }}
                    <strong class="text-foreground">10 {{ t('pointsUnit') }}</strong>.
                </p>
                <p>
                    {{ t('goalDifference') }}
                    <strong class="text-foreground">4 {{ t('pointsUnit') }}</strong>.
                </p>
                <p>
                    {{ t('correctResult') }}
                    <strong class="text-foreground">1 {{ t('pointsUnit') }}</strong>.
                </p>
                <p>{{ t('exactPriority') }}</p>
            </div>
        </section>

        <section class="rounded-md border bg-card p-5">
            <h2 class="font-semibold">{{ t('penalties') }}</h2>
            <div class="mt-4 grid gap-3 text-sm leading-6 text-muted-foreground">
                <p>{{ t('playoffOnly') }}</p>
                <p>
                    {{ t('penaltyExact') }}
                    <strong class="text-foreground">10 {{ t('pointsUnit') }}</strong>.
                </p>
                <p>
                    {{ t('penaltyWinner') }}
                    <strong class="text-foreground">2 {{ t('pointsUnit') }}</strong>.
                </p>
                <p>{{ t('penaltyAdd') }}</p>
            </div>
        </section>

        <section class="rounded-md border bg-card p-5">
            <h2 class="font-semibold">{{ t('nominationRules') }}</h2>
            <div class="mt-4 grid gap-3 text-sm leading-6 text-muted-foreground">
                <p>
                    {{ t('nominationRulesText') }}
                    <strong class="text-foreground">30 {{ t('pointsUnit') }}</strong>.
                </p>
                <p>{{ t('nominationRulesChange') }}</p>
                <p>{{ t('nominationRulesClose') }}</p>
            </div>
        </section>

        <section class="rounded-md border bg-card p-5">
            <h2 class="font-semibold">{{ t('closingTime') }}</h2>
            <p class="mt-4 text-sm leading-6 text-muted-foreground">
                {{ t('closingTimeText') }}
            </p>
        </section>
    </article>
</template>
