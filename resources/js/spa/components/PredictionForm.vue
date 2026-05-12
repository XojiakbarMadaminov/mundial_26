<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';

import { api, validationErrors } from '@/spa/lib/api';
import { t } from '@/spa/lib/i18n';
import type { Match } from '@/spa/lib/types';

const props = defineProps<{
    match: Match;
}>();

const emit = defineEmits<{
    saved: [];
}>();

const form = reactive({
    home_score: props.match.my_prediction?.home_score ?? 0,
    away_score: props.match.my_prediction?.away_score ?? 0,
    home_penalty_score:
        props.match.my_prediction?.home_penalty_score ??
        (null as number | null),
    away_penalty_score:
        props.match.my_prediction?.away_penalty_score ??
        (null as number | null),
});

const saving = ref(false);
const savedSuccessfully = ref(false);
const errors = ref<Record<string, string>>({});
const message = ref('');
const isPlayoff = computed(() => props.match.stage !== 'group');
const homeTeamName = computed(() => props.match.home_team?.name ?? t('homeTeam'));
const awayTeamName = computed(() => props.match.away_team?.name ?? t('awayTeam'));

watch(
    () => props.match.my_prediction,
    (prediction) => {
        form.home_score = prediction?.home_score ?? 0;
        form.away_score = prediction?.away_score ?? 0;
        form.home_penalty_score = prediction?.home_penalty_score ?? null;
        form.away_penalty_score = prediction?.away_penalty_score ?? null;
    },
);

async function submit(): Promise<void> {
    saving.value = true;
    savedSuccessfully.value = false;
    errors.value = {};
    message.value = '';

    try {
        const payload = {
            home_score: form.home_score,
            away_score: form.away_score,
            home_penalty_score: isPlayoff.value
                ? form.home_penalty_score
                : null,
            away_penalty_score: isPlayoff.value
                ? form.away_penalty_score
                : null,
        };

        if (props.match.my_prediction) {
            await api.put(`/matches/${props.match.id}/prediction`, payload);
        } else {
            await api.post(`/matches/${props.match.id}/prediction`, payload);
        }

        message.value = t('predictionSaved');
        savedSuccessfully.value = true;
        emit('saved');
    } catch (error) {
        errors.value = validationErrors(error);
        message.value = Object.keys(errors.value).length
            ? ''
            : t('couldNotSavePrediction');
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <form class="rounded-md border bg-card p-4" @submit.prevent="submit">
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-1 text-sm font-medium">
                {{ homeTeamName }}
                <input
                    v-model.number="form.home_score"
                    class="rounded-md border bg-background px-3 py-2"
                    min="0"
                    max="30"
                    type="number"
                    :disabled="match.is_prediction_locked"
                />
                <span
                    v-if="errors.home_score"
                    class="text-xs text-destructive"
                    >{{ errors.home_score }}</span
                >
            </label>
            <label class="grid gap-1 text-sm font-medium">
                {{ awayTeamName }}
                <input
                    v-model.number="form.away_score"
                    class="rounded-md border bg-background px-3 py-2"
                    min="0"
                    max="30"
                    type="number"
                    :disabled="match.is_prediction_locked"
                />
                <span
                    v-if="errors.away_score"
                    class="text-xs text-destructive"
                    >{{ errors.away_score }}</span
                >
            </label>
        </div>

        <div v-if="isPlayoff" class="mt-4 grid gap-4 sm:grid-cols-2">
            <label class="grid gap-1 text-sm font-medium">
                {{ homeTeamName }} penalties
                <input
                    v-model.number="form.home_penalty_score"
                    class="rounded-md border bg-background px-3 py-2"
                    min="0"
                    max="30"
                    type="number"
                    :disabled="match.is_prediction_locked"
                />
                <span
                    v-if="errors.home_penalty_score"
                    class="text-xs text-destructive"
                    >{{ errors.home_penalty_score }}</span
                >
            </label>
            <label class="grid gap-1 text-sm font-medium">
                {{ awayTeamName }} penalties
                <input
                    v-model.number="form.away_penalty_score"
                    class="rounded-md border bg-background px-3 py-2"
                    min="0"
                    max="30"
                    type="number"
                    :disabled="match.is_prediction_locked"
                />
                <span
                    v-if="errors.away_penalty_score"
                    class="text-xs text-destructive"
                    >{{ errors.away_penalty_score }}</span
                >
            </label>
        </div>

        <p
            v-if="message"
            class="mt-3 text-sm"
            :class="
                savedSuccessfully
                    ? 'text-emerald-600'
                    : 'text-destructive'
            "
        >
            {{ message }}
        </p>

                <button
                class="mt-4 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground disabled:opacity-50"
                type="submit"
                :disabled="saving || match.is_prediction_locked"
            >
                {{
                    saving
                    ? t('saving')
                    : match.my_prediction
                      ? t('updatePrediction')
                      : t('submitPrediction')
                }}
        </button>
    </form>
</template>
