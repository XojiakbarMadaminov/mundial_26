<script setup lang="ts">
import { computed, onMounted, reactive, ref } from 'vue';

import NominationOptionController from '@/actions/App/Http/Controllers/Api/NominationOptionController';
import StateBlock from '@/spa/components/StateBlock.vue';
import { api, validationErrors } from '@/spa/lib/api';
import { formatDateTime } from '@/spa/lib/dates';
import { t } from '@/spa/lib/i18n';
import type { NominationCategory } from '@/spa/lib/types';
import { useCompetitionStore } from '@/spa/stores/competition';

const competition = useCompetitionStore();
const values = reactive<Record<string, string | number | null>>({});
const optionSearches = reactive<Record<string, string>>({});
const selectedOptionLabels = reactive<Record<string, string>>({});
const options = reactive<Record<string, NominationOption[]>>({});
const openOptionKey = ref<string | null>(null);
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
    player_id: number | null;
    team_id: number | null;
    value_text: string | null;
    value_number: number | null;
};

type NominationOption = {
    id: number;
    name: string;
    code?: string | null;
    team_name?: string | null;
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
                : category.type === 'player'
                  ? (prediction?.player_id ?? null)
                  : category.type === 'team'
                    ? (prediction?.team_id ?? null)
                    : (prediction?.value_text ?? '');

        if (category.type === 'player' || category.type === 'team') {
            selectedOptionLabels[category.key] =
                category.type === 'player'
                    ? (prediction?.player?.name ?? '')
                    : (prediction?.team?.name ?? '');
            optionSearches[category.key] = selectedOptionLabels[category.key];
            await loadOptions(category);
        }
    }
}

function isFilled(category: NominationCategory): boolean {
    const value = values[category.key];

    if (category.type === 'number') {
        return value !== null && value !== '' && !Number.isNaN(Number(value));
    }

    if (category.type === 'player' || category.type === 'team') {
        return value !== null && value !== '' && Number(value) > 0;
    }

    return typeof value === 'string' && value.trim() !== '';
}

function payloadFor(category: NominationCategory): NominationPayload {
    const value = values[category.key];

    return {
        category_key: category.key,
        player_id: category.type === 'player' ? Number(value) : null,
        team_id: category.type === 'team' ? Number(value) : null,
        value_text:
            category.type === 'number' ||
            category.type === 'player' ||
            category.type === 'team'
                ? null
                : String(value).trim(),
        value_number: category.type === 'number' ? Number(value) : null,
    };
}

async function loadOptions(category: NominationCategory): Promise<void> {
    if (category.type !== 'player' && category.type !== 'team') {
        return;
    }

    const route =
        category.type === 'player'
            ? NominationOptionController.players({
                  query: { search: optionSearches[category.key] ?? '' },
              })
            : NominationOptionController.teams({
                  query: { search: optionSearches[category.key] ?? '' },
              });
    const response = await api.get<{ data: NominationOption[] }>(
        route.url.replace(/^\/api/, ''),
    );

    options[category.key] = response.data.data;
}

async function openOptions(category: NominationCategory): Promise<void> {
    openOptionKey.value = category.key;
    optionSearches[category.key] = selectedOptionLabels[category.key] ?? '';
    await loadOptions(category);
}

async function searchOptions(category: NominationCategory): Promise<void> {
    values[category.key] = null;
    selectedOptionLabels[category.key] = '';
    openOptionKey.value = category.key;
    await loadOptions(category);
}

function selectOption(
    category: NominationCategory,
    option: NominationOption,
): void {
    values[category.key] = option.id;
    selectedOptionLabels[category.key] = optionLabel(option);
    optionSearches[category.key] = selectedOptionLabels[category.key];
    options[category.key] = [
        option,
        ...(options[category.key] ?? []).filter((item) => item.id !== option.id),
    ];
    openOptionKey.value = null;
}

function closeOptions(category: NominationCategory, event: FocusEvent): void {
    const nextFocusedElement = event.relatedTarget;

    if (
        nextFocusedElement instanceof Node &&
        event.currentTarget instanceof HTMLElement &&
        event.currentTarget.contains(nextFocusedElement)
    ) {
        return;
    }

    window.setTimeout(() => {
        if (openOptionKey.value === category.key) {
            openOptionKey.value = null;
        }
    }, 150);
}

function selectPlaceholder(category: NominationCategory): string {
    return category.type === 'team' ? t('selectTeam') : t('selectPlayer');
}

function optionLabel(option: NominationOption): string {
    if (option.team_name) {
        return `${option.name} (${option.team_name})`;
    }

    if (option.code) {
        return `${option.name} (${option.code})`;
    }

    return option.name;
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
                <div
                    v-if="category.type === 'player' || category.type === 'team'"
                    class="relative mt-3"
                    @focusout="closeOptions(category, $event)"
                >
                    <button
                        :id="`nomination-${category.key}`"
                        class="flex w-full items-center justify-between gap-3 rounded-md border bg-background px-3 py-2 text-left"
                        type="button"
                        role="combobox"
                        :aria-expanded="openOptionKey === category.key"
                        :aria-controls="`nomination-options-${category.key}`"
                        :disabled="isLocked"
                        @click="openOptions(category)"
                        @keydown.escape="openOptionKey = null"
                    >
                        <span
                            class="truncate"
                            :class="
                                selectedOptionLabels[category.key]
                                    ? 'text-foreground'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{
                                selectedOptionLabels[category.key] ||
                                selectPlaceholder(category)
                            }}
                        </span>
                        <span class="text-muted-foreground">⌄</span>
                    </button>
                    <div
                        v-if="openOptionKey === category.key"
                        :id="`nomination-options-${category.key}`"
                        class="absolute z-20 mt-1 max-h-60 w-full overflow-auto rounded-md border bg-popover p-1 shadow-md"
                        role="listbox"
                    >
                        <input
                            v-model="optionSearches[category.key]"
                            class="mb-1 w-full rounded-sm border bg-background px-3 py-2 text-sm"
                            type="search"
                            autocomplete="off"
                            :placeholder="
                                category.type === 'team'
                                    ? t('searchTeam')
                                    : t('searchPlayer')
                            "
                            @input="searchOptions(category)"
                            @keydown.escape="openOptionKey = null"
                        />
                        <button
                            v-for="option in options[category.key] ?? []"
                            :key="option.id"
                            class="w-full rounded-sm px-3 py-2 text-left text-sm hover:bg-accent hover:text-accent-foreground"
                            type="button"
                            role="option"
                            :aria-selected="values[category.key] === option.id"
                            @mousedown.prevent="selectOption(category, option)"
                        >
                            {{ optionLabel(option) }}
                        </button>
                        <p
                            v-if="(options[category.key] ?? []).length === 0"
                            class="px-3 py-2 text-sm text-muted-foreground"
                        >
                            {{ t('noOptionsFound') }}
                        </p>
                    </div>
                </div>
                <input
                    v-else
                    :id="`nomination-${category.key}`"
                    v-model="values[category.key]"
                    class="mt-3 w-full rounded-md border bg-background px-3 py-2"
                    :type="category.type === 'number' ? 'number' : 'text'"
                    :disabled="isLocked"
                    :placeholder="
                        category.type === 'number' ? t('number') : t('value')
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
