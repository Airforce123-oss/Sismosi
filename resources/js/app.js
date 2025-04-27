import './bootstrap';
import '../css/app.css';
import Alpine from 'alpinejs';

import { createApp, h } from 'vue';
import { useRoute } from 'vue-router';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createRouter, createWebHistory } from 'vue-router';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import router from './router';
import ApexCharts from 'vue3-apexcharts';
const csrfToken = document
  .querySelector('meta[name="csrf-token"]')
  .getAttribute('content');

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
console.log('resolvePageComponent:', resolvePageComponent);

axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;


// Inertia setup
createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue') // Lazy import tanpa { eager: true }
    ).then((module) => module.default), // Pastikan mengambil module.default
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) });

    app.use(plugin);
    app.use(router);
    app.use(ZiggyVue);
    app.use(ApexCharts); // Gunakan ApexCharts sebagai plugin Vue

    window.Alpine = Alpine;
    Alpine.start(); // Mulai Alpine.js

    app.mount(el);
    return app;
  },
  progress: {
    color: '#4B5563',
  },
});
