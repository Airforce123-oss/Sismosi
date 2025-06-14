<script setup>
import { ref, onMounted, nextTick, watch, computed } from 'vue';
import { initFlowbite } from 'flowbite';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SidebarParent from '@/Components/SidebarParent.vue';
import { Link, useForm, usePage, Head, router } from '@inertiajs/vue3';
import axios from 'axios';
const userName = ref('');
const { props } = usePage();
const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

const filters = ref({ ...props.filters });
const tahunList = props.tahunList;
const kelasList = props.kelasList;

const students = computed(() => props.students.data ?? []);
const selectedStudentId = ref(
  students.value.length > 0 ? students.value[0].id : null
);
const selectedStudent = computed(
  () => students.value.find((s) => s.id === selectedStudentId.value) || null
);

const pagination = computed(() => props.students);

const allMapel = props.allMapel ?? [];

const mapelWithNilai = computed(() => {
  if (!selectedStudent.value) return [];
  return allMapel.map((mapel, idx) => {
    // Cari enrollment untuk mapel ini
    const nilai =
      studentEnrollments.value.find((e) => e.mapel_id === mapel.id) || {};
    return {
      idx: idx + 1,
      mapel: mapel.mapel,
      kkm: 75,
      cognitive_1: nilai.cognitive_1 ?? '',
      skill_1: nilai.skill_1 ?? '',
      cognitive_average: nilai.cognitive_average ?? '',
      final_mark: nilai.final_mark ?? '',
    };
  });
});

let filterTimeout = null;

async function fetchFilteredStudents() {
  try {
    const response = await axios.get('/api/students/filter', {
      params: filters.value,
    });

    students.value = response.data.students;
  } catch (error) {
    console.error('Failed to fetch students:', error);
  }
}

function applyFilter() {
  clearTimeout(filterTimeout);
  filterTimeout = setTimeout(() => {
    fetchFilteredStudents();
  }, 400);
}

const studentEnrollments = computed(() => {
  if (!selectedStudent.value) return [];
  // Filter enrollments untuk siswa terpilih
  return props.enrollments
    ? props.enrollments.filter((e) => e.student_id === selectedStudent.value.id)
    : [];
});

const studentAttendances = computed(() => {
  if (!selectedStudent.value) return [];
  return props.attendances
    ? props.attendances.filter((a) => a.student_id === selectedStudent.value.id)
    : [];
});

// Hitung jumlah hari untuk tiap alasan
const absenCount = computed(
  () =>
    studentAttendances.value.filter((a) => a.status_kehadiran === 'A').length
);
const izinCount = computed(
  () =>
    studentAttendances.value.filter((a) => a.status_kehadiran === 'I').length
);
const sakitCount = computed(
  () =>
    studentAttendances.value.filter((a) => a.status_kehadiran === 'S').length
);
const tanpaKeteranganCount = computed(
  () =>
    studentAttendances.value.filter((a) => a.status_kehadiran === 'T').length
);

onMounted(() => {
  initFlowbite();
});

watch(students, (newVal) => {
  if (
    newVal.length > 0 &&
    !newVal.find((s) => s.id === selectedStudentId.value)
  ) {
    selectedStudentId.value = newVal[0].id;
  } else if (newVal.length === 0) {
    selectedStudentId.value = null;
  }
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
      <Head title="Melihat Nilai" />
      <form
        class="w-full max-w-4xl mx-auto grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-6 bg-gradient-to-r from-blue-100 via-pink-100 to-yellow-100 p-6 rounded-2xl shadow-lg border-2 border-dashed border-blue-300 transition-all"
        @submit.prevent="applyFilter"
      >
        <!-- Input Nama Siswa -->
        <div class="flex flex-col items-start w-full col-span-1 sm:col-span-2">
          <label
            class="mb-1 text-xs font-bold text-blue-700 flex items-center gap-1"
          >
            Nama Siswa:
          </label>
          <input
            v-model="filters.nama"
            @input="applyFilter"
            placeholder="Cari nama siswa..."
            class="w-full border-2 border-pink-300 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 rounded-xl px-4 py-2 text-base transition-all bg-white placeholder:text-pink-400"
            list="daftar-siswa"
            autocomplete="off"
          />
          <datalist id="daftar-siswa">
            <option
              v-for="student in students"
              :key="student.id"
              :value="student.name"
            >
              {{ student.name }}
            </option>
          </datalist>
        </div>

        <!-- Select Kelas -->
        <div class="flex flex-col items-start w-full col-span-1 sm:col-span-2">
          <label
            class="mb-1 text-xs font-bold text-blue-700 flex items-center gap-1"
          >
            Kelas:
          </label>
          <select
            v-model="filters.kelas"
            @change="applyFilter"
            class="w-full border-2 border-pink-300 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 rounded-xl px-4 py-2 text-base transition-all bg-white placeholder:text-pink-400"
          >
            <option value="">Pilih kelas</option>
            <option
              v-for="kelas in kelasList"
              :key="kelas.id"
              :value="kelas.id"
            >
              {{ kelas.name }}
            </option>
          </select>
        </div>

        <!-- Tombol Submit -->
        <div
          class="flex items-end justify-end col-span-1 sm:col-span-2 md:col-start-4"
        >
          <button
            type="submit"
            class="mt-2 px-6 py-2 bg-blue-600 text-white font-semibold rounded-xl hover:bg-blue-700 transition-all shadow-md"
          >
            Terapkan Filter
          </button>
        </div>
      </form>

      <div
        class="max-w-4xl mx-auto bg-white p-4 sm:p-8 rounded shadow text-sm border-2 border-dashed border-blue-300"
      >
        <!-- Header Sekolah -->
        <div class="flex flex-col items-center justify-center mb-4">
          <img
            src="/images/barunawati.jpeg"
            alt="Logo SMA Barunawati Surabaya"
            class="w-24 h-24 object-contain mb-2"
            style="max-width: 96px; max-height: 96px"
          />
          <div class="text-center font-bold uppercase text-base mb-1">
            Laporan Hasil Belajar Siswa
          </div>
          <div class="text-center font-semibold text-lg mb-1">
            SMA BARUNAWATI SURABAYA
          </div>
          <div class="text-center text-xs text-gray-600 leading-tight">
            Jl. Perak Bar. No.173, Perak Utara, Kec. Pabean Cantikan,<br />
            Surabaya, Jawa Timur 60165
          </div>
        </div>

        <!-- Identitas Siswa -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
          <div class="flex flex-col space-y-1">
            <div class="flex items-center">
              <span class="w-32 flex-shrink-0 text-gray-700">Nama Siswa</span>
              <span class="ml-2 flex-1"
                >: {{ selectedStudent?.name || '-' }}</span
              >
            </div>
            <div class="flex items-center">
              <span class="w-32 flex-shrink-0 text-gray-700">No. Induk</span>
              <span class="ml-2 flex-1"
                >: {{ selectedStudent?.no_induk?.no_induk || '-' }}</span
              >
            </div>
          </div>
          <div class="flex flex-col space-y-1 sm:items-end">
            <div class="flex items-center sm:justify-end w-full">
              <span
                class="w-32 flex-shrink-0 text-gray-700 text-left sm:text-right"
                >Tahun Pelajaran</span
              >
              <span class="ml-2 flex-1 text-left sm:text-right"
                >: {{ selectedStudent?.tahun_pelajaran || '-' }}</span
              >
            </div>
            <div class="flex items-center sm:justify-end w-full">
              <span
                class="w-32 flex-shrink-0 text-gray-700 text-left sm:text-right"
                >Kelas</span
              >
              <span class="ml-2 flex-1 text-left sm:text-right"
                >: {{ selectedStudent?.class?.name || '-' }}</span
              >
            </div>
          </div>
        </div>

        <!-- Tabel Nilai -->
        <div class="overflow-x-auto">
          <table class="min-w-full border border-black mb-4 text-center">
            <thead>
              <tr class="bg-gray-100">
                <th
                  rowspan="2"
                  class="border border-black px-2 py-1 align-middle"
                >
                  KOMPONEN
                </th>
                <th
                  rowspan="2"
                  class="border border-black px-2 py-1 align-middle"
                >
                  KKM
                </th>
                <th colspan="4" class="border border-black px-2 py-1">
                  NILAI HASIL BELAJAR
                </th>
              </tr>
              <tr class="bg-gray-100">
                <th class="border border-black px-2 py-1">KOGNITIF</th>
                <th class="border border-black px-2 py-1">PSIKOMOTOR</th>
                <th class="border border-black px-2 py-1">AFEKTIF</th>
                <th class="border border-black px-2 py-1">NILAI AKHIR</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="border border-black px-2 py-1 text-left" colspan="5">
                  <b>A. MATA PELAJARAN</b>
                </td>
              </tr>
              <tr v-for="row in mapelWithNilai" :key="row.idx">
                <td class="border border-black px-2 py-1 text-left">
                  {{ row.idx }}. {{ row.mapel }}
                </td>
                <td class="border border-black px-2 py-1">
                  {{ row.kkm || '-' }}
                </td>
                <td class="border border-black px-2 py-1">
                  {{ row.skill_1 || '-' }}
                </td>
                <td class="border border-black px-2 py-1">
                  {{ row.cognitive_average || '-' }}
                </td>
                <td class="border border-black px-2 py-1">
                  {{ row.final_mark || '-' }}
                </td>
                <td class="border border-black px-2 py-1">
                  {{ row.final_mark || '-' }}
                </td>
              </tr>
              <tr v-if="mapelWithNilai.length === 0">
                <td
                  class="border border-black px-2 py-1 text-center text-gray-400"
                  colspan="5"
                >
                  Tidak ada data mata pelajaran
                </td>
              </tr>
              <tr>
                <td class="border border-black px-2 py-1 text-left" colspan="5">
                  <b>B. MUATAN LOKAL</b>
                </td>
              </tr>
              <tr>
                <td class="border border-black px-2 py-1" colspan="5">
                  &nbsp; -
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Tabel Ketidakhadiran -->
        <div class="font-bold mb-1">KETIDAK HADIRAN</div>
        <div class="overflow-x-auto">
          <table class="min-w-full border border-black mb-4 text-center">
            <thead>
              <tr class="bg-gray-100">
                <th class="border border-black px-2 py-1">NO</th>
                <th class="border border-black px-2 py-1">
                  ALASAN KETIDAK HADIRAN
                </th>
                <th class="border border-black px-2 py-1">LAMA HARI</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class="border border-black px-2 py-1">1</td>
                <td class="border border-black px-2 py-1 text-left">Absen</td>
                <td class="border border-black px-2 py-1">{{ absenCount }}</td>
              </tr>
              <tr>
                <td class="border border-black px-2 py-1">2</td>
                <td class="border border-black px-2 py-1 text-left">Ijin</td>
                <td class="border border-black px-2 py-1">{{ izinCount }}</td>
              </tr>
              <tr>
                <td class="border border-black px-2 py-1">3</td>
                <td class="border border-black px-2 py-1 text-left">Sakit</td>
                <td class="border border-black px-2 py-1">{{ sakitCount }}</td>
              </tr>
              <tr>
                <td class="border border-black px-2 py-1">4</td>
                <td class="border border-black px-2 py-1 text-left">
                  Tanpa Keterangan
                </td>
                <td class="border border-black px-2 py-1">-</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Tanda Tangan -->
        <div class="flex flex-col sm:flex-row justify-between mt-8 gap-4">
          <div class="text-center flex-1">
            Orang Tua,<br /><br /><br />
            <span class="inline-block border-t border-black w-32 mt-4"></span
            ><br />
          </div>
          <div class="text-center flex-1">
            Surabaya, <span class="inline-block w-32"></span><br />
            Wali Kelas,<br /><br /><br />
            <span class="inline-block border-t border-black w-32 mt-4"></span
            ><br />
            <span class="inline-block w-32"></span><br />
            NIP. <span class="inline-block w-24"></span>
          </div>
          <div class="text-center flex-1">
            Mengetahui,<br />
            Kepala Sekolah,<br /><br /><br />
            <span class="inline-block border-t border-black w-32 mt-4"></span
            ><br />
            <span class="inline-block w-32"></span><br />
            NIP. <span class="inline-block w-24"></span>
          </div>
        </div>
      </div>
    </main>

    <!-- Sidebar -->
    <SidebarParent />
  </div>
</template>

<script>
export default {
  setup() {},
};
</script>
