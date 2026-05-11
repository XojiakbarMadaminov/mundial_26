<script setup lang="ts">
defineProps<{
    title: string;
    message?: string;
}>();
</script>

<template>
    <div class="rounded-md border border-dashed p-8 text-center">
        <p class="font-medium">{{ title }}</p>
        <p v-if="message" class="mt-1 text-sm text-muted-foreground">
            {{ message }}
        </p>
    </div>
</template>
