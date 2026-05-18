<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { login } from '@/routes';
import { redirect as telegramRedirect } from '@/routes/telegram';

defineOptions({
    layout: {
        title: 'Register with Telegram',
        description: 'Use Telegram to create or access your account',
    },
});
</script>

<template>
    <Head title="Register" />

    <div class="flex flex-col gap-6">
        <p class="text-center text-sm text-muted-foreground">
            Accounts are created automatically after Telegram login. New users
            wait for admin approval before entering the competition.
        </p>

        <Button as-child class="w-full" data-test="register-user-button">
            <Link :href="telegramRedirect()">Continue with Telegram</Link>
        </Button>

        <div class="text-center text-sm text-muted-foreground">
            Already have an account?
            <TextLink
                :href="login()"
                class="underline underline-offset-4"
                :tabindex="1"
            >
                Log in
            </TextLink>
        </div>
    </div>
</template>
