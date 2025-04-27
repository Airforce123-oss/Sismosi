import { createRouter, createWebHistory } from 'vue-router';
import StudentsDashboard from './Pages/studentsDashboard.vue';

const routes = [
  {
    path: '/studentsDashboard', // Pastikan path yang benar di sini
    name: 'studentsDashboard',  // Opsional: memberi nama untuk navigasi yang lebih mudah
    component: StudentsDashboard,
  },
  // Tambahkan route lainnya jika perlu
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;
