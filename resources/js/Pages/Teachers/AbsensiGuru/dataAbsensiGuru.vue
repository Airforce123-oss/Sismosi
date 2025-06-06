<script setup>
import { ref, onMounted, computed, watch, onUpdated } from 'vue';
import { initFlowbite } from 'flowbite';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '../../../Components/Pagination4.vue';
import { Link, useForm, usePage, Head, router } from '@inertiajs/vue3';
const { props } = usePage();
const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

const months = [
  'Januari',
  'Februari',
  'Maret',
  'April',
  'Mei',
  'Juni',
  'Juli',
  'Agustus',
  'September',
  'Oktober',
  'November',
  'Desember',
];
const currentDate = new Date();
const selectedMonth = ref(currentDate.getMonth());
const selectedYear = ref(currentDate.getFullYear());

const years = Array.from({ length: 6 }, (_, i) => 2020 + i);

const monthForDisplay = computed(() => months[selectedMonth.value]);
const currentYear = computed(() => selectedYear.value);

const totalDaysInMonth = computed(() => {
  const days = new Date(
    currentYear.value,
    selectedMonth.value + 1,
    0
  ).getDate();
  return Array.from({ length: days }, (_, i) => i + 1);
});

const teachers = ref(props.teachers);
const allTeachers = ref(props.teachers?.data || []);
const paginatedTeachers = ref(props.teachers?.data || []);
const paginationInfo = ref(props.teachers?.meta || {});
const paginationLinks = ref(props.teachers?.links || []);
const filterTeacherName = ref('');
const filterNIP = ref('');

onUpdated(() => {
  teachers.value = props.teachers;
  paginatedTeachers.value = props.teachers?.data || [];
  paginationInfo.value = props.teachers?.meta || {};
  paginationLinks.value = props.teachers?.links || [];
  console.log('🌀 onUpdated terpicu, data di-refresh dari props.teachers');
});

const today = new Date();
const selectedDate = ref(today.toISOString().split('T')[0]);
const dateObj = new Date(selectedDate.value);
function applyFilter() {
  paginatedTeachers.value = allTeachers.value.filter((teacher) => {
    const nameMatch = teacher.name
      .toLowerCase()
      .includes(filterTeacherName.value.toLowerCase());
    const nipMatch = teacher.nip
      ? teacher.nip.toLowerCase().includes(filterNIP.value.toLowerCase())
      : true;
    return nameMatch && nipMatch;
  });
}
const formattedDate = (dateObj) => {
  //console.log('Checking dateObj:', dateObj);
  const date = new Date(dateObj);
  if (isNaN(date.getTime())) {
    console.error('Invalid date:', dateObj);
    return 'invalid-date';
  }
  return date.toLocaleDateString('en-CA'); // "YYYY-MM-DD"
};

const getKey = (teacherId, date) => {
  //console.log('Tanggal loop:', date);
  const dateObj = new Date(currentYear.value, selectedMonth.value, date);
  return `status-${teacherId}-${formattedDate(dateObj)}`;
};

const getStatus = (teacher, date) => {
  const year = Number(currentYear.value);
  const month = Number(selectedMonth.value); // 0-based index
  const day = Number(date);

  const dateObj = new Date(year, month, day);
  const dayOfWeek = dateObj.getDay(); // 0 = Sunday, 6 = Saturday

  if (dayOfWeek === 0 || dayOfWeek === 6) {
    return 'Libur';
  }

  const dateKey = formattedDate(dateObj); // e.g. "2025-04-01"
  return teacher.attendance?.[dateKey] || 'Belum Diabsen';
};

const fetchData = (page = 1) => {
  isLoading.value = true;
  router.get(
    '/dataAbsensiGuru',
    {
      bulan: selectedMonth.value + 1,
      tahun: selectedYear.value,
      filter_nama: filterTeacherName.value,
      filter_nip: filterNIP.value,
      page: page,
    },
    {
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        isLoading.value = false;
      },
    }
  );
};

const isLoading = ref(false);
const updatedPageNumber = (link) => {
  if (!link || !link.url) return;

  const url = new URL(link.url, window.location.origin);
  const page = url.searchParams.get('page') || '1';

  // Cegah permintaan ulang jika halaman sama dengan yang sekarang
  if (page === String(paginationInfo.value.current_page)) return;

  // Panggil fetchData dengan halaman yang diinginkan
  fetchData(Number(page));
};

onMounted(async () => {
  if (paginatedTeachers.value.length > 0) {
    console.log(
      'Attendance Guru pertama:',
      paginatedTeachers.value[3].attendance
    );
  }
  fetchData();
  initFlowbite();
});

watch(
  [() => paginationInfo.value.current_page, selectedMonth, selectedYear],
  ([currentPage, month, year], [oldPage, oldMonth, oldYear]) => {
    // Ketika halaman, bulan, atau tahun berubah, ambil data baru
    fetchData();
  }
);

watch(
  () => props.teachers,
  (newVal, oldVal) => {
    console.log('🔥 props.teachers berubah:', newVal, oldVal);
    allTeachers.value = newVal?.data || [];
    paginationInfo.value = newVal?.meta || {};
    paginationLinks.value = newVal?.links || [];
    applyFilter();
  },
  { immediate: true, deep: true }
);

watch([filterTeacherName, filterNIP], () => {
  applyFilter();
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
                  {{ $page.props.auth.user.name }}
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
    <main class="p-7 md:ml-64 h-screen pt-20">
      <Head title="Data Absensi Guru" />
      <div class="p-6 bg-white rounded-2xl shadow-md border border-gray-200">
        <!-- Heading -->
        <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">
          📊 Laporan Absensi Guru - {{ monthForDisplay }} {{ currentYear }}
        </h2>

        <!-- Filter -->
        <div
          class="w-full bg-white p-4 rounded-xl shadow-sm border border-gray-200 mb-6"
        >
          <div
            class="flex flex-col md:flex-row md:items-end md:justify-between gap-4"
          >
            <!-- Filter Nama dan NIP Guru -->
            <div
              class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 gap-4 w-full"
            >
              <!-- Nama Guru -->
              <div class="flex flex-col">
                <label
                  for="nama-guru"
                  class="text-sm font-medium text-gray-700 mb-1"
                >
                  Nama Guru
                </label>
                <input
                  id="nama-guru"
                  type="text"
                  v-model="filterTeacherName"
                  placeholder="Masukkan nama guru"
                  class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>

              <!-- Nomor Induk Pegawai (NIP) -->
              <div class="flex flex-col">
                <label for="nip" class="text-sm font-medium text-gray-700 mb-1">
                  Nomor Induk Pegawai (NIP)
                </label>
                <input
                  id="nip"
                  type="text"
                  v-model="filterNIP"
                  placeholder="Masukkan NIP"
                  class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-auto rounded-lg border border-gray-200">
          <table class="min-w-full text-sm text-center border-collapse">
            <thead class="bg-blue-100 text-gray-700 sticky top-0">
              <tr>
                <th
                  class="px-4 py-3 border border-gray-300 text-left bg-blue-200 font-semibold"
                >
                  Nama Guru
                </th>
                <th class="px-3 py-2 border border-gray-300 text-left">NIP</th>
                <th
                  v-for="(date, index) in totalDaysInMonth"
                  :key="'day-header-' + index"
                  class="px-3 py-2 border border-gray-300 bg-blue-50"
                >
                  {{ date }}
                </th>
              </tr>
            </thead>

            <!-- Spinner tampil jika isLoading true -->
            <tbody v-if="isLoading">
              <tr>
                <td :colspan="2 + totalDaysInMonth.length" class="py-8">
                  <div class="flex justify-center">
                    <svg
                      class="animate-spin h-6 w-6 text-indigo-600"
                      xmlns="http://www.w3.org/2000/svg"
                      fill="none"
                      viewBox="0 0 24 24"
                    >
                      <circle
                        class="opacity-25"
                        cx="12"
                        cy="12"
                        r="10"
                        stroke="currentColor"
                        stroke-width="4"
                      ></circle>
                      <path
                        class="opacity-75"
                        fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                      ></path>
                    </svg>
                  </div>
                </td>
              </tr>
            </tbody>

            <!-- Data tabel tampil saat isLoading false -->
            <tbody v-else>
              <tr
                v-for="(teacher, index) in paginatedTeachers"
                :key="`${teacher.teacher_id}-${index}`"
                class="hover:bg-gray-50 transition-colors duration-150"
              >
                <td
                  class="px-4 py-2 border border-gray-300 text-left font-medium whitespace-nowrap"
                >
                  {{ teacher.name }}
                </td>
                <td
                  class="px-3 py-2 border border-gray-300 text-left text-sm text-gray-700"
                >
                  {{ teacher.nip || '-' }}
                </td>
                <td
                  v-for="(date, index) in totalDaysInMonth"
                  :key="getKey(teacher.teacher_id, date)"
                  class="px-2 py-1 border border-gray-300"
                >
                  <span
                    :class="{
                      'bg-green-200 text-green-800 px-2 py-1 rounded-full text-xs font-semibold':
                        getStatus(teacher, date) === 'P',
                      'bg-red-200 text-red-800 px-2 py-1 rounded-full text-xs font-semibold':
                        getStatus(teacher, date) === 'A',
                      'bg-yellow-200 text-yellow-800 px-2 py-1 rounded-full text-xs font-semibold':
                        getStatus(teacher, date) === 'S',
                      'bg-purple-200 text-purple-800 px-2 py-1 rounded-full text-xs font-semibold':
                        getStatus(teacher, date) === 'I',
                      'bg-gray-300 text-gray-700 italic px-2 py-1 rounded-full text-xs':
                        getStatus(teacher, date) === 'Libur',
                      'text-gray-500 italic text-xs':
                        getStatus(teacher, date) === 'Belum Diabsen',
                    }"
                  >
                    {{ getStatus(teacher, date) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <Pagination :data="teachers" :updatxedPageNumber="updatedPageNumber" />
        <div class="row mt-3 me-3">
          <div class="col-12">
            <p class="fw-bold">Status Kehadiran:</p>
            <div class="d-flex">
              <div class="me-3">
                <span class="badge bg-green-200 text-green-800 fw-bold"
                  >Hadir (P)</span
                >
              </div>
              <div class="me-3">
                <span class="badge bg-red-200 text-red-800 fw-bold"
                  >Absen (A)</span
                >
              </div>
              <div class="me-3">
                <span class="badge bg-yellow-200 text-yellow-800 fw-bold"
                  >Sakit (S)</span
                >
              </div>
              <div class="me-3">
                <span class="badge bbg-purple-200 text-purple-800 fw-bold"
                  >Izin (I)</span
                >
              </div>
              <div class="me-3">
                <span class="badge bg-light text-dark fw-bold"
                  >Belum Diabsen</span
                >
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Sidebar -->
    <SidebarAdmin />
  </div>
</template>

<script>
export default {
  setup() {},
};
</script>
