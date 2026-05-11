import '../css/app.css';

import { createPinia } from 'pinia';
import { createApp } from 'vue';

import App from '@/spa/App.vue';
import { router } from '@/spa/router';
import { useAuthStore } from '@/spa/stores/auth';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

useAuthStore().boot();

app.mount('#spa');
