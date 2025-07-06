<script setup>
import { Link } from '@inertiajs/vue3';
import {
  Home,
  NotebookPen,
  ClipboardList,
  Users,
  BookOpenCheck,
  FileText,
  Calendar,
  Settings,
  User,
} from 'lucide-vue-next';
</script>

<template>
  <aside
    class="fixed top-0 left-0 z-40 w-60 h-screen pt-4 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-900"
    aria-label="Sidenav"
    id="drawer-navigation"
    style=""
  >
    <div class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800">
      <ul class="space-y-2">
        <li>
          <a
            href="/teacher-dashboard"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <Home class="w-5 h-5" />
            <span class="ml-3">Beranda</span>
          </a>
        </li>
        <li>
          <a
            href="/absensiSiswa"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <ClipboardList class="w-5 h-5" />
            <span class="ml-3">Absensi Siswa</span>
          </a>
        </li>
        <li>
          <a
            href="/membuat-enrollment"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <BookOpenCheck class="w-5 h-5" />
            <span class="ml-3">Enrollment Tugas</span>
          </a>
        </li>
        <li>
          <a
            href="/membuatTugasSiswa"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <NotebookPen class="w-5 h-5" />
            <span class="ml-3">Tugas Siswa</span>
          </a>
        </li>

        <li>
          <a
            href="/bukuPenghubung"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <User class="w-5 h-5" />
            <span class="ml-3">Identitas Siswa</span>
          </a>
        </li>

        <li>
          <a
            href="/bukuPenghubung1"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <svg
              viewBox="0 0 576 512"
              class="w-6 h-6"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M144.3 32.04C106.9 31.29 63.7 41.44 18.6 61.29c-11.42 5.026-18.6 16.67-18.6 29.15l0 357.6c0 11.55 11.99 19.55 22.45 14.65c126.3-59.14 219.8 11 223.8 14.01C249.1 478.9 252.5 480 256 480c12.4 0 16-11.38 16-15.98V80.04c0-5.203-2.531-10.08-6.781-13.08C263.3 65.58 216.7 33.35 144.3 32.04zM557.4 61.29c-45.11-19.79-88.48-29.61-125.7-29.26c-72.44 1.312-118.1 33.55-120.9 34.92C306.5 69.96 304 74.83 304 80.04v383.1C304 468.4 307.5 480 320 480c3.484 0 6.938-1.125 9.781-3.328c3.925-3.018 97.44-73.16 223.8-14c10.46 4.896 22.45-3.105 22.45-14.65l.0001-357.6C575.1 77.97 568.8 66.31 557.4 61.29z"
              />
            </svg>

            <span class="ml-3">Buku Penghubung</span>
          </a>
        </li>

        <li>
          <Link
            :href="route('jadwal.byTeacher')"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <Calendar class="w-5 h-5" />
            <span class="ml-3">Jadwal Mata Pelajaran</span>
          </Link>
        </li>

        <li>
          <Link
            :href="route('settingLaporanNilaiSiswa')"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <FileText class="w-5 h-5" />
            <span class="ml-3">Setting Laporan Nilai Siswa</span>
          </Link>
        </li>
      </ul>
    </div>
  </aside>
</template>
