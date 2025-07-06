<script setup>
import { Link } from '@inertiajs/vue3';
import {
  Home,
  GraduationCap,
  UserRoundCheck,
  LayoutGrid,
  BookOpenCheck,
  Briefcase,
  ChevronDown,
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
            href="/admin-dashboard"
            class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
          >
            <Home class="w-5 h-5" />
            <span class="ml-3">Beranda</span>
          </a>
        </li>

        <li>
          <button
            type="button"
            class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
            aria-controls="dropdown-pages"
            data-collapse-toggle="dropdown-pages"
            aria-expanded="true"
          >
            <GraduationCap class="w-5 h-5" />

            <span class="flex-1 ml-3 text-left whitespace-nowrap">Siswa</span>
            <svg
              class="w-6 h-6"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"
              ></path>
            </svg>
          </button>
          <ul id="dropdown-pages" class="hidden py-2 space-y-2">
            <li>
              <a
                href="/students"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                >Data Induk Siswa</a
              >
            </li>
          </ul>
        </li>

        <li>
          <button
            type="button"
            class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
            aria-controls="dropdown-pages-guru"
            data-collapse-toggle="dropdown-pages-guru"
          >
            <UserRoundCheck class="w-5 h-5" />

            <span class="flex-1 ml-3 text-left whitespace-nowrap">Guru</span>
            <svg
              class="w-6 h-6"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"
              ></path>
            </svg>
          </button>
          <ul id="dropdown-pages-guru" class="hidden py-2 space-y-2">
            <!-- Dropdown Data Induk Guru -->
            <li>
              <a
                href="/teachers"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                >Data Induk Guru</a
              >
            </li>
            <!-- Dropdown Absensi Guru -->
            <li>
              <a
                href="/absensiGuru"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                >Absensi Guru</a
              >
            </li>
            <!-- Dropdown Daftar Absensi Guru -->
            <li>
              <a
                href="/dataAbsensiGuru"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                >Data Absensi Guru</a
              >
            </li>
          </ul>
        </li>

        <li>
          <button
            type="button"
            class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
            aria-controls="dropdown-sales"
            data-collapse-toggle="dropdown-sales"
          >
            <LayoutGrid class="w-5 h-5" />
            <span class="flex-1 ml-3 text-left whitespace-nowrap">Kelas</span>
            <svg
              class="w-6 h-6"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"
              ></path>
            </svg>
          </button>
          <ul id="dropdown-sales" class="hidden py-2 space-y-2">
            <li>
              <a
                href="/kelas"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                >Data Kelas</a
              >
            </li>
          </ul>
        </li>

        <li>
          <button
            type="button"
            class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
            aria-controls="dropdown-authentication"
            data-collapse-toggle="dropdown-authentication1"
          >
            <BookOpenCheck class="w-5 h-5" />

            <span class="flex-1 ml-3 text-left whitespace-nowrap"
              >Mata Pelajaran</span
            >
            <svg
              class="w-6 h-6"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"
              ></path>
            </svg>
          </button>

          <ul id="dropdown-authentication1" class="hidden py-2 space-y-2">
            <li>
              <a
                href="/mataPelajaran"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                >Data Mata Pelajaran</a
              >
            </li>
            <li>
              <a
                href="/settingJadwalMataPelajaran"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                >Jadwal Mata Pelajaran</a
              >
            </li>
            <li>
              <a
                href="/laporanJadwalMataPelajaran"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                >Laporan Jadwal Mata Pelajaran</a
              >
            </li>
          </ul>
        </li>
        <li>
          <button
            type="button"
            class="flex items-center p-2 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
            aria-controls="dropdown-authentication"
            data-collapse-toggle="dropdown-authentication11"
          >
            <Briefcase class="w-5 h-5" />

            <span class="flex-1 ml-3 text-left whitespace-nowrap"
              >Master Jabatan</span
            >
            <svg
              class="w-6 h-6"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                clip-rule="evenodd"
              ></path>
            </svg>
          </button>

          <ul id="dropdown-authentication11" class="hidden py-2 space-y-2">
            <li>
              <a
                href="/indexMasterJabatan"
                class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                >Data Master Jabatan</a
              >
            </li>
          </ul>
        </li>
      </ul>
    </div>
  </aside>
</template>
