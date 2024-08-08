import "./bootstrap";
import "../css/app.css";
import Alpine from "alpinejs";

import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy";
import { Ziggy } from "./ziggy";
import "admin-lte/plugins/jquery/jquery.min.js";
import "admin-lte/plugins/bootstrap/js/bootstrap.bundle.min.js";
import "admin-lte/dist/js/adminlte.min.js";
import VueApexCharts from "vue-apexcharts";
import Dashboard from "./Pages/dashboard.vue";


const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});

const app = createApp({
    data() {
        return {
            series: [
                {
                    name: "series1",
                    data: [31, 40, 28, 51, 42, 109, 100],
                },
                {
                    name: "series2",
                    data: [11, 32, 45, 32, 34, 52, 41],
                },
            ],
            chartOptions: {
                chart: {
                    height: 350,
                    type: "area",
                },
                dataLabels: {
                    enabled: false,
                },
                stroke: {
                    curve: "smooth",
                },
                xaxis: {
                    type: "datetime",
                    categories: [
                        "2018-09-19T00:00:00.000Z",
                        "2018-09-19T01:30:00.000Z",
                        "2018-09-19T02:30:00.000Z",
                        "2018-09-19T03:30:00.000Z",
                        "2018-09-19T04:30:00.000Z",
                        "2018-09-19T05:30:00.000Z",
                        "2018-09-19T06:30:00.000Z",
                    ],
                },
                tooltip: {
                    x: {
                        format: "dd/MM/yy HH:mm",
                    },
                },
                components: {
                    Dashboard,
                },
            },
        };
    },
});

// Daftarkan komponen VueApexCharts secara global
app.component("apexchart", VueApexCharts);

//app.component("dashboard", Dashboard);


// Mount aplikasi Vue ke elemen dengan id 'app'
app.mount("#app");

app.use(ZiggyVue, Ziggy);

//console.log(route("admin.register"));

window.Alpine = Alpine;
Alpine.start();
