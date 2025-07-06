import './bootstrap';
import '../css/app.css';
import Alpine from 'alpinejs';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import ApexCharts from 'vue3-apexcharts';
import router from './router';

const csrfToken = document
  .querySelector('meta[name="csrf-token"]')
  .getAttribute('content');

axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,

  // ✅ GUNAKAN FUNGSI ASYNC UNTUK RESOLVE
  resolve: async (name) => {
    const pages = import.meta.glob('./Pages/**/*.vue');
    const page = await pages[`./Pages/${name}.vue`]();
    return page.default;
  },

  setup({ el, App, props, plugin }) {
    const vueApp = createApp({ render: () => h(App, props) });

    vueApp.use(plugin);
    vueApp.use(router);
    vueApp.use(ZiggyVue);
    vueApp.use(ApexCharts);

    window.Alpine = Alpine;
    Alpine.start();

    vueApp.mount(el);
    return vueApp;
  },

  progress: {
    color: '#4B5563',
  },
});
