<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';

import StateBlock from '@/spa/components/StateBlock.vue';
import { api, validationErrors } from '@/spa/lib/api';
import { formatDateTime } from '@/spa/lib/dates';
import { t } from '@/spa/lib/i18n';
import type { NominationCategory } from '@/spa/lib/types';
import { useCompetitionStore } from '@/spa/stores/competition';

const competition = useCompetitionStore();
const values = reactive<Record<string, string | number | null>>({});
const errors = ref<Record<string, string>>({});
const saving = ref(false);
const savedSuccessfully = ref(false);
const message = ref('');
const isLocked = computed(() => competition.nominationMeta?.is_locked ?? false);
const errorMessage = computed(
    () => errors.value.predictions ?? errors.value.tournament ?? '',
);

type NominationPayload = {
    category_key: string;
    value_text: string | null;
    value_number: number | null;
};

async function load(): Promise<void> {
    await competition.fetchNominations();
    await competition.fetchMyNominationPredictions();

    for (const category of competition.nominationCategories) {
        const prediction = competition.nominationPredictions.find(
            (item) => item.nomination_category_id === category.id,
        );
        values[category.key] =
            category.type === 'number'
                ? (prediction?.value_number ?? null)
                : (prediction?.value_text ?? '');
    }
}

function isFilled(category: NominationCategory): boolean {
    const value = values[category.key];

    if (category.type === 'number') {
        return value !== null && value !== '' && !Number.isNaN(Number(value));
    }

    return typeof value === 'string' && value.trim() !== '';
}

function payloadFor(category: NominationCategory): NominationPayload {
    const value = values[category.key];

    return {
        category_key: category.key,
        value_text: category.type === 'number' ? null : String(value).trim(),
        value_number: category.type === 'number' ? Number(value) : null,
    };
}

async function save(): Promise<void> {
    const categories = competition.nominationCategories.filter(isFilled);

    if (categories.length === 0) {
        errors.value = {
            predictions: t('fillAtLeastOneNominationBeforeSaving'),
        };

        return;
    }

    saving.value = true;
    savedSuccessfully.value = false;
    errors.value = {};
    message.value = '';

    try {
        await api.post('/nominations/predictions', {
            predictions: categories.map(payloadFor),
        });

        message.value = t('filledNominationsSaved');
        savedSuccessfully.value = true;
        await load();
    } catch (error) {
        errors.value = validationErrors(error);
        message.value = Object.keys(errors.value).length
            ? ''
            : t('couldNotSaveNominations');
    } finally {
        saving.value = false;
    }
}

onMounted(load);
</script>

<template>
    <div class="grid gap-5">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold">{{ t('nominations') }}</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    {{ t('locksAt') }}
                    {{ formatDateTime(competition.nominationMeta?.lock_at) }}
                </p>
            </div>
            <span
                class="rounded-md px-2 py-1 text-xs font-medium"
                :class="
                    isLocked
                        ? 'bg-muted text-muted-foreground'
                        : 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200'
                "
            >
                {{ isLocked ? t('lockedLabel') : t('openLabel') }}
            </span>
        </div>

        <StateBlock
            v-if="competition.nominationCategories.length === 0"
            :title="t('noNominationCategories')"
        />

        <form v-else class="grid gap-3" @submit.prevent="save()">
            <div
                v-for="category in competition.nominationCategories"
                :key="category.id"
                class="rounded-md border bg-card p-4"
            >
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <label
                        class="font-medium"
                        :for="`nomination-${category.key}`"
                        >{{ category.name }}</label
                    >
                    <span class="text-sm text-muted-foreground"
                        >{{ category.points }} points</span
                    >
                </div>
                    <input
                        :id="`nomination-${category.key}`"
                        v-model="values[category.key]"
                        class="mt-3 w-full rounded-md border bg-background px-3 py-2"
                        :type="category.type === 'number' ? 'number' : 'text'"
                        :disabled="isLocked"
                        :placeholder="
                            category.type === 'team'
                            ? t('teamName')
                            : category.type === 'number'
                              ? t('number')
                              : t('playerName')
                    "
                />
            </div>

            <p v-if="errorMessage" class="text-sm text-destructive">
                {{ errorMessage }}
            </p>
            <p
                v-if="message"
                class="text-sm"
                :class="
                    savedSuccessfully
                        ? 'text-emerald-600'
                        : 'text-destructive'
                "
            >
                {{ message }}
            </p>

            <button
                class="w-full rounded-md bg-primary px-4 py-2 font-medium text-primary-foreground disabled:opacity-50 sm:w-auto"
                type="submit"
                :disabled="saving || isLocked"
            >
                {{ saving ? t('saving') : t('saveFilledNominations') }}
            </button>
        </form>
    </div>
</template>
