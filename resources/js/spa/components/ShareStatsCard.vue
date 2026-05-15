<script setup lang="ts">
import { computed, ref } from 'vue';

import { t } from '@/spa/lib/i18n';

const props = defineProps<{
    userName: string;
    tournamentName: string;
    tournamentStatus?: string;
    totalPoints: number;
    rank: number | null;
}>();

const isWorking = ref(false);
const helperText = ref('');

const badge = computed(() => {
    const rank = props.rank;
    const isFinished = props.tournamentStatus === 'finished';

    if (rank === 1) {
        return isFinished
            ? { key: 'shareBadgeChampion', emoji: '🏆', color: '#f59e0b' }
            : { key: 'shareBadgeChampionContender', emoji: '🥇', color: '#f59e0b' };
    }

    if (rank !== null && rank <= 3) {
        return { key: 'shareBadgeTop3', emoji: '🥈', color: '#94a3b8' };
    }

    if (rank !== null && rank <= 10) {
        return { key: 'shareBadgeTop10', emoji: '🔥', color: '#22c55e' };
    }

    return { key: 'shareBadgeContender', emoji: '⚽', color: '#38bdf8' };
});

const safeRankText = computed(() => (props.rank ? `#${props.rank}` : '#-'));

function splitIntoLines(ctx: CanvasRenderingContext2D, text: string, maxWidth: number): string[] {
    const words = text.split(' ');
    const lines: string[] = [];
    let currentLine = '';

    for (const word of words) {
        const candidate = currentLine ? `${currentLine} ${word}` : word;

        if (ctx.measureText(candidate).width <= maxWidth) {
            currentLine = candidate;
            continue;
        }

        if (currentLine) {
            lines.push(currentLine);
        }

        currentLine = word;
    }

    if (currentLine) {
        lines.push(currentLine);
    }

    return lines;
}

function renderShareCard(): Promise<Blob> {
    return new Promise((resolve, reject) => {
        const width = 1200;
        const height = 630;
        const canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        const ctx = canvas.getContext('2d');

        if (!ctx) {
            reject(new Error('Canvas context unavailable'));
            return;
        }

        const bgGradient = ctx.createLinearGradient(0, 0, width, height);
        bgGradient.addColorStop(0, '#0f172a');
        bgGradient.addColorStop(1, '#1d4ed8');
        ctx.fillStyle = bgGradient;
        ctx.fillRect(0, 0, width, height);

        ctx.globalAlpha = 0.12;
        ctx.fillStyle = '#ffffff';
        ctx.beginPath();
        ctx.arc(180, 90, 180, 0, Math.PI * 2);
        ctx.fill();
        ctx.beginPath();
        ctx.arc(1040, 560, 220, 0, Math.PI * 2);
        ctx.fill();
        ctx.globalAlpha = 1;

        ctx.fillStyle = 'rgba(15, 23, 42, 0.6)';
        ctx.fillRect(64, 64, width - 128, height - 128);

        ctx.fillStyle = '#dbeafe';
        ctx.font = '600 34px ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif';
        ctx.fillText(props.tournamentName || 'Mundial 26 Predict', 100, 132);

        ctx.fillStyle = '#ffffff';
        ctx.font = '700 62px ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif';
        ctx.fillText(props.userName, 100, 230);

        ctx.fillStyle = '#bfdbfe';
        ctx.font = '500 28px ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif';
        ctx.fillText(t('shareCardSubtitle'), 100, 278);

        ctx.fillStyle = '#f8fafc';
        ctx.font = '600 30px ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif';
        ctx.fillText(t('totalPoints'), 100, 366);
        ctx.font = '700 74px ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif';
        ctx.fillText(String(props.totalPoints), 100, 448);

        ctx.fillStyle = '#f8fafc';
        ctx.font = '600 30px ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif';
        ctx.fillText(t('rank'), 430, 366);
        ctx.font = '700 74px ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif';
        ctx.fillText(safeRankText.value, 430, 448);

        ctx.fillStyle = badge.value.color;
        ctx.beginPath();
        ctx.arc(975, 250, 90, 0, Math.PI * 2);
        ctx.fill();

        ctx.fillStyle = '#ffffff';
        ctx.font = '900 62px ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif';
        ctx.textAlign = 'center';
        ctx.fillText(badge.value.emoji, 975, 274);
        ctx.textAlign = 'left';

        ctx.fillStyle = '#e2e8f0';
        ctx.font = '600 26px ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif';
        ctx.fillText(t('shareBadgeLabel'), 860, 390);
        ctx.fillStyle = '#ffffff';
        ctx.font = '700 36px ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif';
        const badgeText = t(badge.value.key);
        const badgeLines = splitIntoLines(ctx, badgeText, 300);

        if (badgeLines.length <= 1) {
            ctx.fillText(badgeText, 860, 436);
        } else {
            ctx.fillText(badgeLines[0], 860, 432);

            const secondLine = badgeLines.slice(1).join(' ');
            const lineToRender =
                ctx.measureText(secondLine).width > 300
                    ? `${secondLine.slice(0, 21).trimEnd()}...`
                    : secondLine;

            ctx.fillText(lineToRender, 860, 474);
        }

        ctx.fillStyle = 'rgba(248, 250, 252, 0.8)';
        ctx.font = '500 22px ui-sans-serif, system-ui, -apple-system, Segoe UI, sans-serif';
        ctx.fillText('Mundial 26 Predict', 100, 540);

        canvas.toBlob((blob) => {
            if (!blob) {
                reject(new Error('Could not create card image'));
                return;
            }

            resolve(blob);
        }, 'image/png');
    });
}

function downloadBlob(blob: Blob): void {
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = `mundial-share-${Date.now()}.png`;
    document.body.appendChild(link);
    link.click();
    link.remove();
    URL.revokeObjectURL(url);
}

async function downloadCard(): Promise<void> {
    isWorking.value = true;
    helperText.value = '';

    try {
        const blob = await renderShareCard();
        downloadBlob(blob);
        helperText.value = t('shareCardDownloaded');
    } catch {
        helperText.value = t('shareCardError');
    } finally {
        isWorking.value = false;
    }
}

</script>

<template>
    <section class="rounded-md border bg-card p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="text-base font-semibold">{{ t('shareCardTitle') }}</h2>
                <p class="text-sm text-muted-foreground">{{ t('shareCardDescription') }}</p>
            </div>
            <div class="flex gap-2">
                <button
                    type="button"
                    class="rounded-md border px-3 py-2 text-sm font-medium hover:bg-accent disabled:opacity-60"
                    :disabled="isWorking"
                    @click="downloadCard"
                >
                    {{ t('download') }}
                </button>
            </div>
        </div>
        <p v-if="helperText" class="mt-3 text-sm text-muted-foreground">{{ helperText }}</p>
    </section>
</template>
