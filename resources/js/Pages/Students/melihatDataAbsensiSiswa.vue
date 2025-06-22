<script setup>
import { onMounted, ref, defineProps, computed } from 'vue';
import axios from 'axios';
import { initFlowbite } from 'flowbite';
import { useForm, usePage, Head } from '@inertiajs/vue3';
import SidebarStudent from '@/Components/SidebarStudent.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import VueApexCharts from 'vue3-apexcharts';
import ApexCharts from 'apexcharts';
import $ from 'jquery';

// Plugin untuk kalender
import '@assets/plugins/simple-calendar/jquery.simple-calendar.js';
import '@assets/plugins/simple-calendar/simple-calendar.css';

// Inisialisasi data
//const props = defineProps();
const { props } = usePage();
const currentDate = ref('');
const data = ref([]); // Inisialisasi data sebagai array kosong
const loading = ref(true); // Status loading
const userName = ref('');

console.log('🧾 props.subjects:', props.subjects);

// Pastikan props.auth dan props.auth.user terdefinisi sebelum mengaksesnya
const form = useForm({
  name: props.auth?.user?.name || '', // Gunakan optional chaining
  email: props.auth?.user?.email || '',
  role_type: props.auth?.user?.role_type || '',
});

const attendanceData = computed(() => props.attendanceData || {});
console.log('isi attendance Data: ', attendanceData.value);
const month = computed(() => props.month);
const year = computed(() => props.year);

function formatDate(dateStr) {
  const date = new Date(dateStr);
  return date.toLocaleDateString('id-ID', {
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric',
  });
}

// Ambil langsung dari props
const page = usePage();

// Pastikan akses properti yang benar dari `page.props.value`
const student = computed(() => page.props.student ?? null);

// Ambil student_id dan student_name dari query string URL
const query = new URLSearchParams(window.location.search);
const studentId = ref(query.get('student_id'));
const studentName = ref(query.get('student_name'));

// Jika kamu ingin computed juga bisa:
const student_id = computed(() => studentId.value ?? student.value?.id ?? '');

const student_name = computed(
  () => studentName.value ?? student.value?.name ?? ''
);

// Debug
console.log('✅ student_id dari query:', student_id.value);
console.log('✅ student_name dari query:', student_name.value);

const formattedMonth = computed(() => {
  const date = new Date(2000, Number(props.month) - 1);
  return date.toLocaleDateString('id-ID', { month: 'long' });
});

const fetchSessionData = async () => {
  try {
    const response = await axios.get('/api/session-name');
    userName.value = response.data.name;
  } catch (error) {
    console.error('There was an error fetching the session data:', error);
  }
};

// Menggunakan onMounted untuk mengambil data saat komponen dimuat
onMounted(() => {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  currentDate.value = `${year}-${month}-${day}`;
  fetchSessionData();
  initFlowbite();
});

// Contoh penggunaan data
const itemCount = computed(() => {
  return data.value ? data.value.length : 0;
});

const subjectsArray = computed(() => {
  if (Array.isArray(props.subjects)) {
    return props.subjects;
  } else if (props.subjects) {
    return [props.subjects]; // bungkus satu objek ke dalam array
  }
  return [];
});

console.log('📚 Mata pelajaran (props.subjects):', props.subjects);

const totalAbsensi = computed(() => {
  const totals = {
    H: 0,
    A: 0,
    S: 0,
    I: 0,
    P: 0,
    total: 0, // ⬅️ total keseluruhan langsung dihitung di sini
  };

  for (const status of Object.values(attendanceData.value)) {
    if (totals.hasOwnProperty(status)) {
      totals[status]++;
      totals.total++; // ⬅️ naikkan total untuk setiap status valid
    }
  }

  return totals;
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
          <!--
                                        <button
                        type="button"
                        data-drawer-toggle="drawer-navigation"
                        aria-controls="drawer-navigation"
                        class="p-2 mr-1 text-gray-500 rounded-lg md:hidden hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
                    >
                        <span class="sr-only">Toggle search</span>
                        <svg
                            class="w-6 h-6"
                            fill="currentColor"
                            viewBox="0 0 20 20"
                            xmlns="http://www.w3.org/2000/svg"
                        >
                            <path
                                clip-rule="evenodd"
                                fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                            ></path>
                        </svg>
                    </button>
                    -->
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

    <main class="p-7 md:ml-64 h-screen pt-20">
      <Head title="Melihat Data Absensi Siswa" />
      <div>
        <div class="p-6 bg-white shadow-lg rounded-lg max-w-4xl mx-auto">
          <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">
            Laporan Absensi Bulan {{ formattedMonth }}/{{ year }}
          </h2>
          <div class="overflow-x-auto">
            <table
              class="min-w-full table-auto border-separate border-spacing-0 bg-gray-50 rounded-lg"
            >
              <caption
                class="text-left px-6 py-3 text-gray-700 text-sm font-medium"
              >
                Data Absensi Siswa Bulan
                {{
                  month
                }}
                {{
                  year
                }}
              </caption>

              <thead>
                <!-- Informasi Siswa -->
                <tr class="bg-white mb-10">
                  <th
                    colspan="3"
                    class="px-6 py-2 text-left text-sm text-gray-700"
                  >
                    Nama Siswa:
                    <span class="font-semibold">{{ studentName }}</span>
                  </th>
                </tr>

                <!-- Header kolom utama -->
                <tr class="bg-gray-200">
                  <th
                    class="px-6 py-3 text-left text-sm font-medium text-gray-600"
                  >
                    Tanggal
                  </th>
                  <th
                    class="px-6 py-3 text-left text-sm font-medium text-gray-600"
                  >
                    Mapel
                  </th>
                  <th
                    class="px-6 py-3 text-left text-sm font-medium text-gray-600"
                  >
                    Status
                  </th>
                </tr>
              </thead>

              <tbody>
                <tr
                  v-for="(status, tanggal, index) in attendanceData"
                  :key="tanggal"
                  :class="[
                    index % 2 === 0 ? 'bg-white' : 'bg-gray-50',
                    'border-b',
                  ]"
                >
                  <td class="px-6 py-4 text-sm text-gray-700">
                    {{ formatDate(tanggal) }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">
                    <ul>
                      <li
                        v-for="(subject, index) in subjectsArray"
                        :key="index"
                        class="text-sm text-gray-700"
                      >
                        {{ subject || 'Tidak tersedia' }}
                      </li>
                    </ul>

                    <!-- Menampilkan mapel -->
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-700">
                    <span
                      v-if="status === 'H'"
                      class="text-green-600 font-semibold"
                      >Hadir</span
                    >
                    <span
                      v-if="status === 'A'"
                      class="text-red-600 font-semibold"
                      >Absen</span
                    >
                    <span
                      v-if="status === 'S'"
                      class="text-yellow-600 font-semibold"
                      >Sakit</span
                    >
                    <span
                      v-if="status === 'I'"
                      class="text-blue-600 font-semibold"
                      >Izin</span
                    >
                  </td>
                </tr>

                <tr v-if="Object.keys(attendanceData).length === 0">
                  <td colspan="3" class="text-center py-4 text-gray-500">
                    Tidak ada data absensi.
                  </td>
                </tr>
              </tbody>
              <!--          <pre>{{ props.subject.mapel }}</pre>-->
            </table>

            <!-- Keterangan status -->
            <div
              class="px-6 py-3 text-sm text-gray-700 flex flex-wrap items-center gap-4 bg-gray-100 rounded-lg mt-4"
            >
              <div class="flex items-center gap-2">
                <span
                  class="inline-block w-3 h-3 bg-green-500 rounded-full"
                ></span>
                Hadir
              </div>
              <div class="flex items-center gap-2">
                <span
                  class="inline-block w-3 h-3 bg-red-500 rounded-full"
                ></span>
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
      </div>
    </main>

    <!-- Sidebar -->
    <SidebarStudent :student_id="student_id" :student_name="student_name" />
  </div>
</template>
