<script setup>
import { onMounted, ref, computed, watchEffect, watch } from 'vue';
import axios from 'axios';
import { initFlowbite } from 'flowbite';
import { Head, useForm, usePage, router } from '@inertiajs/vue3';
import SidebarStudent from '@/Components/SidebarStudent.vue';
import Pagination from '@/Components/Pagination13.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import VueApexCharts from 'vue3-apexcharts';
import ApexCharts from 'apexcharts';
import $ from 'jquery';

// Plugin kalender
import '@assets/plugins/simple-calendar/jquery.simple-calendar.js';
import '@assets/plugins/simple-calendar/simple-calendar.css';

// Ambil data dari props inertia
const { props } = usePage();
const page = usePage();

// Data dasar
const currentDate = ref('');
const loading = ref(true);
const userName = ref('');
const data = ref([]);

// User login info
const form = useForm({
  name: props.auth?.user?.name || '',
  email: props.auth?.user?.email || '',
  role_type: props.auth?.user?.role_type || '',
});

// Ambil data siswa dari query atau props
const query = new URLSearchParams(window.location.search);
const studentId = ref(query.get('student_id'));
const studentName = ref(query.get('student_name'));
const student = computed(() => page.props.student ?? null);

const student_id = computed(() => studentId.value ?? student.value?.id ?? '');
const student_name = computed(
  () => studentName.value ?? student.value?.name ?? ''
);

// Sinkronkan selectedMonth dan selectedYear dari query URL (bila tersedia)
const selectedMonth = ref(new Date().getMonth() + 1);
const selectedYear = ref(new Date().getFullYear());

const monthFromQuery = parseInt(query.get('month'));
const yearFromQuery = parseInt(query.get('year'));

if (!isNaN(monthFromQuery)) {
  selectedMonth.value = monthFromQuery;
}
if (!isNaN(yearFromQuery)) {
  selectedYear.value = yearFromQuery;
}

// Absensi penuh
const attendanceData = computed(() => props.attendanceData || {});
console.log('isi attendance Data: ', attendanceData.value);

// Mata pelajaran
const subjectsArray = computed(() => {
  if (Array.isArray(props.subjects)) {
    return props.subjects;
  } else if (props.subjects) {
    return [props.subjects];
  }
  return [];
});

const monthOptions = Array.from({ length: 12 }, (_, i) => i + 1);
const yearOptions = computed(() => {
  const data = attendanceData.value?.data || [];
  const years = data
    .map((item) => {
      const d = new Date(item.tanggal_kehadiran);
      return isNaN(d) ? null : d.getFullYear();
    })
    .filter((y) => y !== null);

  return [...new Set(years)].sort((a, b) => b - a);
});

const paginatedAttendance = computed(() => props.attendanceData);

// Format tanggal
const formatDate = (dateStr) => {
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
};

// Ambil nama user dari session (opsional)
const fetchSessionData = async () => {
  try {
    const response = await axios.get('/api/session-name');
    userName.value = response.data.name;
  } catch (error) {
    console.error('Gagal mengambil data session:', error);
  }
};

onMounted(() => {
  const now = new Date();
  currentDate.value = now.toISOString().split('T')[0];

  const urlParams = new URLSearchParams(window.location.search);
  const month = parseInt(urlParams.get('month'));
  const year = parseInt(urlParams.get('year'));

  if (!isNaN(month)) selectedMonth.value = month;
  if (!isNaN(year)) selectedYear.value = year;

  fetchSessionData();
  initFlowbite();
});

const updatedPageNumber = (page) => {
  router.get(
    route('melihatDataAbsensiSiswa'),
    {
      student_id: student_id.value,
      student_name: student_name.value,
      month: selectedMonth.value,
      year: selectedYear.value,
      page,
    },
    {
      preserveState: true,
      preserveScroll: true,
    }
  );
};

watchEffect(() => {
  console.log('📦 paginatedAttendance:', paginatedAttendance.value);
});

watch([selectedMonth, selectedYear], () => {
  router.get(
    route('melihatDataAbsensiSiswa'),
    {
      student_id: student_id.value,
      student_name: student_name.value,
      month: selectedMonth.value,
      year: selectedYear.value,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
});

watchEffect(() => {
  const urlParams = new URLSearchParams(window.location.search);
  const month = parseInt(urlParams.get('month'));
  const year = parseInt(urlParams.get('year'));

  console.log('🔁 Sync from URL:', { month, year });

  if (!isNaN(month)) selectedMonth.value = month;
  if (!isNaN(year)) selectedYear.value = year;
});
</script>

<style scoped>
@import url('https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
.bg-primary1 {
  background-color: #0e70cc;
}

.bg-success {
  background-color: #28a745;
}

.bg-warning {
  background-color: #ffc107;
}

.bg-cyan {
  background-color: #10b0cc;
}

.attendance-record {
  margin-bottom: 10px;
  padding: 10px;
  border: 1px solid #ddd;
  border-radius: 5px;
}
</style>

<template>
  <div class="antialiased bg-gray-50 dark:bg-gray-900">
    <nav
      class="bg-white border-b border-gray-200 px-4 py-2.5 dark:bg-gray-800 dark:border-gray-700 fixed left-0 right-0 top-0 z-50"
    >
      <div class="flex flex-wrap justify-between items-center">
        <div class="flex justify-start items-center">
          <button
            data-drawer-target="drawer-navigation"
            data-drawer-toggle="drawer-navigation"
            aria-controls="drawer-navigation"
            class="p-2 mr-2 text-gray-600 rounded-lg cursor-pointer md:hidden hover:text-gray-900 hover:bg-gray-100 focus:bg-gray-100 dark:focus:bg-gray-700 focus:ring-2 focus:ring-gray-100 dark:focus:ring-gray-700 dark:text-gray-400 dark:hover:bg-gray-700 dark:hover:text-white"
          >
            <svg
              class="w-6 h-6"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                clip-rule="evenodd"
              ></path>
            </svg>
            <svg
              class="hidden w-6 h-6"
              fill="currentColor"
              viewBox="0 0 20 20"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                fill-rule="evenodd"
                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                clip-rule="evenodd"
              ></path>
            </svg>
            <span class="sr-only">Toggle sidebar</span>
          </button>
          <a href="" class="flex items-center justify-between mr-4">
            <img src="/images/barunawati.jpeg" class="mr-3 h-8" alt="" />
            <span
              class="self-center text-base md:text-lg lg:text-xl xl:text-2xl font-semibold whitespace-nowrap dark:text-white"
              >SMA BARUNAWATI SURABAYA</span
            >
          </a>
        </div>
        <div class="flex items-center lg:order-2">
          <!-- Apps -->
          <button
            type="button"
            class="p-2 text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
          ></button>

          <button
            type="button"
            class="flex mx-3 text-sm rounded-full md:mr-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
            id="user-menu-button"
            aria-expanded="false"
            data-dropdown-toggle="dropdown"
          >
            <span class="sr-only">Open user menu</span>
            <svg
              baseProfile="tiny"
              height="24px"
              id="Layer_1"
              version="1.2"
              viewBox="0 0 24 24"
              width="24px"
              xml:space="preserve"
              xmlns="http://www.w3.org/2000/svg"
              xmlns:xlink="http://www.w3.org/1999/xlink"
            >
              <path
                d="M12,3c0,0-6.186,5.34-9.643,8.232C2.154,11.416,2,11.684,2,12c0,0.553,0.447,1,1,1h2v7c0,0.553,0.447,1,1,1h3  c0.553,0,1-0.448,1-1v-4h4v4c0,0.552,0.447,1,1,1h3c0.553,0,1-0.447,1-1v-7h2c0.553,0,1-0.447,1-1c0-0.316-0.154-0.584-0.383-0.768  C18.184,8.34,12,3,12,3z"
                fill="black"
              />
            </svg>
          </button>
          <!-- Dropdown menu -->
          <div
            class="hidden w-full sm:w-1/2 lg:w-1/4 text-base list-none bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 rounded-xl"
            id="dropdown"
          >
            <div class="py-3 px-3">
              <div
                class="'block w-full ps-3 pe-4 py-2 border-l-4 border-indigo-400 text-start text-base text-indigo-700 focus:outline-none focus:text-indigo-800 focus:bg-indigo-100 focus:border-indigo-700 transition duration-150 ease-in-out text-[12px]'"
              >
                <span
                  class="block text-sm font-semibold text-gray-900 dark:text-white"
                  >{{ $page.props.auth.user.email }}
                </span>
                <span
                  class="block text-sm text-gray-900 truncate dark:text-white"
                >
                  {{ studentName }}
                </span>
                <span
                  class="block text-sm text-gray-900 truncate dark:text-white"
                  >{{ form.role_type }}</span
                >
              </div>
            </div>
            <div class="mt-3 space-y-1">
              <ResponsiveNavLink :href="route('profile.edit')">
                Profil Saya
              </ResponsiveNavLink>
              <ResponsiveNavLink
                :href="route('logout')"
                method="post"
                as="button"
              >
                Log Out
              </ResponsiveNavLink>
            </div>
          </div>
        </div>
      </div>
    </nav>
    <!-- Main -->

    <main class="min-h-screen pt-20 px-4 sm:px-6 md:px-8 bg-gray-100 md:ml-64">
      <Head title="Melihat Data Absensi Siswa" />

      <div class="w-full overflow-x-auto bg-white rounded-xl shadow-lg mb-8">
        <div class="p-6">
          <!-- Judul -->
          <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
            Laporan Absensi Bulan
            {{
              new Date(2000, selectedMonth - 1).toLocaleString('id-ID', {
                month: 'long',
              })
            }}
            / {{ selectedYear }}
          </h2>

          <div
            class="mb-10 flex flex-col sm:flex-row flex-wrap items-center justify-center gap-6 bg-gradient-to-br from-indigo-50 via-white to-indigo-100 rounded-2xl shadow-lg p-6 border border-indigo-200"
          >
            <!-- Nama Siswa -->
            <div class="w-full">
              <p class="text-sm text-gray-700">
                Nama Siswa:
                <span class="font-semibold">{{ studentName }}</span>
              </p>
            </div>

            <!-- Kartu Dropdown Bulan -->
            <div class="w-full sm:w-auto">
              <label class="block text-sm font-semibold text-indigo-700 mb-2">
                <div class="flex items-center gap-2">
                  <i class="fas fa-calendar-alt text-indigo-500"></i>
                  Pilih Bulan
                </div>
              </label>
              <select
                v-model.number="selectedMonth"
                class="w-full sm:w-56 bg-white border border-indigo-300 text-indigo-700 text-sm rounded-xl px-4 py-2 shadow-md focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-300 ease-in-out hover:shadow-lg"
              >
                <option v-for="m in monthOptions" :key="m" :value="m">
                  {{
                    new Date(2000, m - 1).toLocaleString('id-ID', {
                      month: 'long',
                    })
                  }}
                </option>
              </select>
            </div>

            <!-- Kartu Dropdown Tahun -->
            <div class="w-full sm:w-auto">
              <label class="block text-sm font-semibold text-indigo-700 mb-2">
                <div class="flex items-center gap-2">
                  <i class="fas fa-clock text-indigo-500"></i>
                  Pilih Tahun
                </div>
              </label>
              <select
                v-model.number="selectedYear"
                class="w-full sm:w-44 bg-white border border-indigo-300 text-indigo-700 text-sm rounded-xl px-4 py-2 shadow-md focus:ring-2 focus:ring-indigo-400 focus:border-indigo-500 transition duration-300 ease-in-out hover:shadow-lg"
              >
                <option v-for="y in yearOptions" :key="y" :value="y">
                  {{ y }}
                </option>
              </select>
            </div>
          </div>

          <!-- Tabel Absensi -->
          <div class="overflow-x-auto">
            <table
              class="min-w-full table-auto bg-gray-50 rounded-lg overflow-hidden"
            >
              <thead>
                <tr class="bg-white"></tr>
                <tr class="bg-gray-200 text-sm text-gray-600">
                  <th class="px-6 py-3 text-left font-medium">Tanggal</th>
                  <th class="px-6 py-3 text-left font-medium">Mapel</th>
                  <th class="px-6 py-3 text-left font-medium">Status</th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="(row, index) in paginatedAttendance.data"
                  :key="row.id || index"
                  :class="index % 2 === 0 ? 'bg-white' : 'bg-gray-50'"
                  class="border-b"
                >
                  <td class="px-6 py-4 text-sm text-gray-700">
                    {{ formatDate(row.tanggal_kehadiran) }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">
                    {{ row.mapel || 'Tidak tersedia' }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">
                    <span
                      v-if="row.status_kehadiran === 'P'"
                      class="text-green-600 font-semibold"
                      >Hadir</span
                    >
                    <span
                      v-else-if="row.status_kehadiran === 'A'"
                      class="text-red-600 font-semibold"
                      >Absen</span
                    >
                    <span
                      v-else-if="row.status_kehadiran === 'S'"
                      class="text-yellow-600 font-semibold"
                      >Sakit</span
                    >
                    <span
                      v-else-if="row.status_kehadiran === 'I'"
                      class="text-blue-600 font-semibold"
                      >Izin</span
                    >
                    <span v-else class="text-gray-500">-</span>
                  </td>
                </tr>
              </tbody>
            </table>
            <Pagination
              :data="paginatedAttendance"
              :updatedPageNumber="updatedPageNumber"
            />
          </div>

          <!-- Keterangan status -->
          <div
            class="mt-6 px-6 py-4 bg-gray-100 rounded-lg flex flex-wrap gap-4 text-sm text-gray-700"
          >
            <div class="flex items-center gap-2">
              <span
                class="inline-block w-3 h-3 bg-green-500 rounded-full"
              ></span>
              Hadir
            </div>
            <div class="flex items-center gap-2">
              <span class="inline-block w-3 h-3 bg-red-500 rounded-full"></span>
              Absen
            </div>
            <div class="flex items-center gap-2">
              <span
                class="inline-block w-3 h-3 bg-yellow-400 rounded-full"
              ></span>
              Sakit
            </div>
            <div class="flex items-center gap-2">
              <span
                class="inline-block w-3 h-3 bg-blue-500 rounded-full"
              ></span>
              Izin
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Sidebar -->
    <SidebarStudent
      :student_id="props.student_id"
      :student_name="props.student_name"
    />
  </div>
</template>
