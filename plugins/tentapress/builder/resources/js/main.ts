import { createApp } from 'vue';
import { createPinia } from 'pinia';
import App from './App.vue';
import type { BuilderConfig } from './types';

const root = document.getElementById('tp-builder-root');
if (!root) {
    throw new Error('Builder root element not found.');
}

const rawConfig = root.getAttribute('data-config');
if (!rawConfig) {
    throw new Error('Builder config missing.');
}

const config = JSON.parse(rawConfig) as BuilderConfig;

const app = createApp(App, { config });
app.use(createPinia());
app.mount(root);
