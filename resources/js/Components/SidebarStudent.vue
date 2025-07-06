<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
  student_id: {
    type: [String, Number],
    required: false,
    default: null,
  },
  student_name: {
    type: String,
    required: false,
    default: '',
  },
});
</script>

<template>
  <aside
    class="fixed top-0 left-0 z-40 w-60 h-screen pt-4 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-900"
    aria-label="Sidenav"
    id="drawer-navigation"
  >
    <div class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800">
      <ul class="space-y-2">
        <!-- Beranda -->
        <li>
          <Link
            :href="`/student-dashboard?student_id=${student_id}&student_name=${encodeURIComponent(
              student_name
            )}`"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <!-- Icon: Home -->
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-5 h-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M3 9.75L12 4l9 5.75M4.5 10.5v8.25A1.5 1.5 0 006 20.25h12a1.5 1.5 0 001.5-1.5V10.5"
              />
            </svg>
            <span class="ml-3">Beranda</span>
          </Link>
        </li>

        <!-- Melihat Tugas -->
        <li>
          <Link
            :href="`/melihatTugas?student_id=${student_id}&student_name=${encodeURIComponent(
              student_name
            )}`"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <!-- Icon: Clipboard List -->
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-5 h-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M9 12h6M9 16h6M9 8h.01M5 6h14a2 2 0 012 2v12a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2z"
              />
            </svg>
            <span class="ml-3">Melihat Tugas</span>
          </Link>
        </li>

        <!-- Melihat Absensi -->
        <li>
          <Link
            :href="`/melihatDataAbsensiSiswa?student_id=${student_id}&student_name=${encodeURIComponent(
              student_name
            )}`"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <!-- Icon: Calendar Check -->
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-5 h-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7V3m8 4V3M5 11h14M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2zm5-5l2 2 4-4"
              />
            </svg>
            <span class="ml-3">Melihat Absensi</span>
          </Link>
        </li>

        <!-- Melihat Jadwal -->
        <li>
          <Link
            :href="`/melihatJadwalPelajaran?student_id=${student_id}&student_name=${encodeURIComponent(
              student_name
            )}`"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <!-- Icon: Calendar Days -->
            <svg
              xmlns="http://www.w3.org/2000/svg"
              class="w-5 h-5"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 11h14M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"
              />
            </svg>
            <span class="ml-3">Melihat Jadwal Pelajaran</span>
          </Link>
        </li>
      </ul>
    </div>
  </aside>
</template>
