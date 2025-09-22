import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';
import { initializeTheme } from './composables/useAppearance';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

// Setup axios defaults for Laravel Sanctum
import axios from 'axios';

// Detect environment and set appropriate base URL
const isProduction = import.meta.env.PROD;
const isDevelopment = import.meta.env.DEV;

// Configure axios for CSRF and cookies
axios.defaults.withCredentials = true;

// Set base URL based on environment
if (isProduction) {
    // In production, use same domain (no need for explicit baseURL)
    // This will use the same domain as the current page
    axios.defaults.baseURL = '';
} else if (isDevelopment) {
    // In development, use Laravel dev server
    axios.defaults.baseURL = 'http://localhost:8000';
}

// Set up axios interceptor to handle CSRF
axios.interceptors.request.use(async (config) => {
    // For non-GET requests, ensure we have CSRF cookie
    if (config.method !== 'get') {
        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (token) {
            config.headers['X-CSRF-TOKEN'] = token;
        }
    }
    return config;
});

// Make axios available globally for debugging (only in dev)
if (isDevelopment) {
    (window as any).axios = axios;
}

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: (name) =>
        resolvePageComponent(
            `./pages/${name}.vue`,
            import.meta.glob<DefineComponent>('./pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin);

        // Global configuration for Inertia
        app.config.globalProperties.$http = axios;

        app.mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});

// This will set light / dark mode on page load...
initializeTheme();
