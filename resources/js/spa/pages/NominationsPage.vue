<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';

import StateBlock from '@/spa/components/StateBlock.vue';
import { api, validationErrors } from '@/spa/lib/api';
import { formatDateTime } from '@/spa/lib/dates';
import { useCompetitionStore } from '@/spa/stores/competition';

const competition = useCompetitionStore();
const values = reactive<Record<string, string | number | null>>({});
const errors = ref<Record<string, string>>({});
const saving = ref(false);
const message = ref('');
const isLocked = computed(() => competition.nominationMeta?.is_locked ?? false);

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

async function save(): Promise<void> {
    saving.value = true;
    errors.value = {};
    message.value = '';

    try {
        await api.post('/nominations/predictions', {
            predictions: competition.nominationCategories.map((category) => ({
                category_key: category.key,
                value_text:
                    category.type === 'number' ? null : values[category.key],
                value_number:
                    category.type === 'number' ? values[category.key] : null,
            })),
        });

        message.value = 'Nomination predictions saved.';
        await load();
    } catch (error) {
        errors.value = validationErrors(error);
        message.value = Object.keys(errors.value).length
            ? ''
            : 'Could not save nominations.';
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
                <h1 class="text-2xl font-semibold">Nominations</h1>
                <p class="mt-1 text-sm text-muted-foreground">
                    Locks at
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
                {{ isLocked ? 'Locked' : 'Open' }}
            </span>
        </div>

        <StateBlock
            v-if="competition.nominationCategories.length === 0"
            title="No nomination categories"
        />

        <form v-else class="grid gap-3" @submit.prevent="save">
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
                            ? 'Team name'
                            : category.type === 'number'
                              ? 'Number'
                              : 'Player name'
                    "
                />
            </div>

            <p v-if="errors.predictions" class="text-sm text-destructive">
                {{ errors.predictions }}
            </p>
            <p
                v-if="message"
                class="text-sm"
                :class="
                    message.includes('saved')
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
                {{ saving ? 'Saving...' : 'Save nominations' }}
            </button>
        </form>
    </div>
</template>
