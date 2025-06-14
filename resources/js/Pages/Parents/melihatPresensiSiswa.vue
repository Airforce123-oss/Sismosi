<script setup>
import { ref, onMounted, nextTick, watch, computed } from 'vue';
import { initFlowbite } from 'flowbite';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SidebarParent from '@/Components/SidebarParent.vue';
import { Link, useForm, usePage, Head, router } from '@inertiajs/vue3';
import Pagination8 from '@/Components/Pagination8.vue';
const { props } = usePage();
const page = usePage();
const expandedTanggalId = ref(null);
const students = computed(() => page.props.students || null);

const processedStudents = computed(() => {
  if (!students.value || !students.value.data) return [];

  return students.value.data.map((student) => {
    console.log('Nama:', student.name, 'Attendances:', student.attendances);
    return {
      ...student,
      attendancesExceptFirst: (student.attendances || []).slice(1),
    };
  });
});

const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

onMounted(() => {
  console.log('page.props.students:', page.props.students);
  initFlowbite();
});

const goToPage = (page) => {
  router.get(
    route('melihat-presensi'),
    {
      class_id: props.classId,
      year: props.year,
      month: props.month,
      mapel: props.mapel,
      page: page,
    },
    {
      preserveScroll: true,
      preserveState: true,
    }
  );
};

console.log(props.students);

watch(
  () => props.students,
  (newVal) => {
    console.log('props.students changed:', newVal);
  },
  { immediate: true }
);

watch(
  () => props.students,
  (val) => {
    console.log('props.students changed:', val);
  },
  { immediate: true, deep: true }
);
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
      <Head title="Melihat Presensi Siswa" />
      <form
        method="GET"
        action="/melihat-presensi"
        class="w-full max-w-6xl mx-auto bg-blue-50 rounded-xl shadow-md px-4 sm:px-6 lg:px-8 py-4 sm:py-5 mb-6"
      >
        <div class="grid gap-4 grid-cols-[repeat(auto-fit,minmax(200px,1fr))]">
          <!-- Judul Filter -->
          <div
            class="col-span-full flex items-center gap-2 text-blue-700 font-semibold text-lg"
          >
            <svg
              class="w-5 h-5 text-blue-500"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            Filter Data Presensi
          </div>

          <!-- Field: Kelas -->
          <div class="flex flex-col">
            <label for="class_id" class="mb-1 text-sm font-medium text-blue-700"
              >Kelas</label
            >
            <select
              id="class_id"
              name="class_id"
              required
              class="w-full border border-blue-300 rounded-md px-3 py-2 text-sm text-blue-700 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white shadow-sm transition"
            >
              <option value="">Pilih Kelas</option>
              <option
                v-for="kelas in props.kelasList"
                :key="kelas.id"
                :value="kelas.id"
                :selected="kelas.id == props.classId"
              >
                {{ kelas.name }}
              </option>
            </select>
          </div>

          <!-- Field: Tahun -->
          <div class="flex flex-col">
            <label for="year" class="mb-1 text-sm font-medium text-blue-700"
              >Tahun</label
            >
            <select
              id="year"
              name="year"
              required
              class="w-full border border-blue-300 rounded-md px-3 py-2 text-sm text-blue-700 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white shadow-sm transition"
            >
              <option value="">Pilih Tahun</option>
              <option
                v-for="tahun in props.tahunList"
                :key="tahun"
                :value="tahun"
                :selected="tahun == props.year"
              >
                {{ tahun }}
              </option>
            </select>
          </div>

          <!-- Field: Bulan -->
          <div class="flex flex-col">
            <label for="month" class="mb-1 text-sm font-medium text-blue-700"
              >Bulan</label
            >
            <select
              id="month"
              name="month"
              required
              class="w-full border border-blue-300 rounded-md px-3 py-2 text-sm text-blue-700 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white shadow-sm transition"
            >
              <option value="">Pilih Bulan</option>
              <option
                v-for="(namaBulan, idx) in [
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
                ]"
                :key="idx"
                :value="idx + 1"
                :selected="idx + 1 == props.month"
              >
                {{ namaBulan }}
              </option>
            </select>
          </div>

          <!-- Field: Mapel -->
          <div class="flex flex-col">
            <label for="mapel" class="mb-1 text-sm font-medium text-blue-700"
              >Mata Pelajaran</label
            >
            <select
              id="mapel"
              name="mapel"
              required
              class="w-full border border-blue-300 rounded-md px-3 py-2 text-sm text-blue-700 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 bg-white shadow-sm transition"
            >
              <option value="">Pilih Mapel</option>
              <option
                v-for="mapel in props.mapelList"
                :key="mapel.id"
                :value="mapel.mapel"
                :selected="mapel.mapel == props.mapel"
              >
                {{ mapel.mapel }}
              </option>
            </select>
          </div>

          <!-- Tombol Submit -->
          <div class="flex items-end">
            <button
              type="submit"
              class="w-full h-10 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition font-semibold shadow"
            >
              Tampilkan
            </button>
          </div>
        </div>
      </form>

      <div class="my-6 flex flex-col items-center">
        <p class="font-bold text-lg mb-2 text-gray-700">Status Kehadiran:</p>
        <div class="flex flex-wrap gap-3 justify-center">
          <span
            class="inline-flex items-center px-3 py-1 rounded-full bg-blue-100 text-blue-800 font-semibold shadow-sm text-sm"
          >
            <svg
              class="w-4 h-4 mr-1 text-blue-500"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <circle
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                fill="none"
              />
              <path
                d="M9 12l2 2 4-4"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            Hadir (P)
          </span>
          <span
            class="inline-flex items-center px-3 py-1 rounded-full bg-red-100 text-red-800 font-semibold shadow-sm text-sm"
          >
            <svg
              class="w-4 h-4 mr-1 text-red-500"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <circle
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                fill="none"
              />
              <path
                d="M15 9l-6 6M9 9l6 6"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            Absen (A)
          </span>
          <span
            class="inline-flex items-center px-3 py-1 rounded-full bg-yellow-100 text-yellow-800 font-semibold shadow-sm text-sm"
          >
            <svg
              class="w-4 h-4 mr-1 text-yellow-500"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <circle
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                fill="none"
              />
              <path
                d="M12 8v4l2 2"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            Sakit (S)
          </span>
          <span
            class="inline-flex items-center px-3 py-1 rounded-full bg-purple-100 text-purple-800 font-semibold shadow-sm text-sm"
          >
            <svg
              class="w-4 h-4 mr-1 text-purple-500"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <circle
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                fill="none"
              />
              <path
                d="M12 8v4h4"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            Izin (I)
          </span>
          <span
            class="inline-flex items-center px-3 py-1 rounded-full bg-gray-200 text-gray-700 font-semibold shadow-sm text-sm"
          >
            <svg
              class="w-4 h-4 mr-1 text-gray-500"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <circle
                cx="12"
                cy="12"
                r="10"
                stroke="currentColor"
                fill="none"
              />
              <path
                d="M8 12h8"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            Belum Diabsen
          </span>
        </div>
      </div>

      <!-- Tabel Absensi -->
      <div
        v-if="processedStudents.length"
        class="max-w-8xl mx-auto mt-10 bg-white rounded-xl shadow p-6 space-y-6"
      >
        <h2 class="text-2xl font-bold text-blue-700 text-center tracking-wide">
          Daftar Presensi Siswa
        </h2>

        <hr class="border-t border-blue-200" />

        <div class="rounded-lg shadow-md overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-200 rounded-lg text-sm">
              <thead
                class="bg-blue-100 text-blue-900 uppercase text-xs sticky top-0 z-30"
              >
                <tr>
                  <th
                    class="p-3 border-b border-gray-200 text-center sticky left-0 bg-blue-100 z-30"
                  >
                    No
                  </th>
                  <th
                    class="p-3 border-b border-gray-200 text-center sticky left-12 bg-blue-100 z-30"
                  >
                    Nama Siswa
                  </th>
                  <th class="p-3 border-b border-gray-200 text-center">
                    Mata Pelajaran
                  </th>
                  <th class="p-3 border-b border-gray-200 text-center">
                    Tanggal
                  </th>
                  <th class="p-3 border-b border-gray-200 text-center">
                    Status
                  </th>
                </tr>
              </thead>
              <tbody>
                <template
                  v-for="(student, sIdx) in processedStudents"
                  :key="student.id"
                >
                  <!-- Jika ada absensi -->
                  <template
                    v-if="student.attendances && student.attendances.length > 0"
                  >
                    <tr
                      v-for="(attendance, aIdx) in expandedTanggalId ===
                      student.id
                        ? student.attendances
                        : student.attendances.slice(0, 5)"
                      :key="attendance.tanggal + '-' + student.id"
                      class="even:bg-blue-50 odd:bg-white hover:bg-blue-200/40 transition-colors duration-150"
                    >
                      <!-- No dan Nama Siswa hanya di baris pertama -->
                      <template v-if="aIdx === 0">
                        <td
                          class="p-3 border-b border-gray-100 font-semibold align-top sticky left-0 bg-white z-20"
                          :rowspan="
                            expandedTanggalId === student.id
                              ? student.attendances.length
                              : Math.min(5, student.attendances.length)
                          "
                        >
                          {{ sIdx + 1 }}
                        </td>
                        <td
                          class="p-3 border-b border-gray-100 font-medium align-top sticky left-12 bg-white z-20"
                          :rowspan="
                            expandedTanggalId === student.id
                              ? student.attendances.length
                              : Math.min(5, student.attendances.length)
                          "
                        >
                          <div class="flex flex-col items-center">
                            <span
                              class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-500 text-white font-bold text-base shadow mb-1"
                            >
                              {{
                                student.name
                                  .split(' ')
                                  .map((n) => n[0])
                                  .join('')
                                  .substring(0, 2)
                                  .toUpperCase()
                              }}
                            </span>
                            <div class="text-xs text-gray-400">
                              Total: {{ student.attendances.length }} absensi
                            </div>
                          </div>
                          <div class="mt-1 text-base text-gray-800 text-center">
                            {{ student.name }}
                          </div>
                        </td>
                        <td
                          class="p-3 border-b border-gray-100 align-top text-center"
                          :rowspan="
                            expandedTanggalId === student.id
                              ? student.attendances.length
                              : Math.min(5, student.attendances.length)
                          "
                        >
                          <span
                            class="inline-block px-2 py-0.5 rounded bg-blue-200 text-blue-800 text-xs font-semibold"
                          >
                            {{ attendance.mapel }}
                          </span>
                        </td>
                      </template>

                      <!-- Tanggal -->
                      <td class="p-3 border-b border-gray-100">
                        <span
                          class="inline-flex items-center gap-1 px-2 py-1 rounded bg-blue-50 text-blue-700 font-sans text-sm font-semibold shadow-sm"
                          :title="
                            new Date(attendance.tanggal).toLocaleDateString(
                              'id-ID',
                              {
                                weekday: 'long',
                                day: '2-digit',
                                month: 'long',
                                year: 'numeric',
                              }
                            )
                          "
                        >
                          <svg
                            class="w-4 h-4 text-blue-400"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            viewBox="0 0 24 24"
                          >
                            <rect
                              x="3"
                              y="4"
                              width="18"
                              height="18"
                              rx="2"
                              stroke="currentColor"
                              fill="none"
                            />
                            <path
                              d="M16 2v4M8 2v4M3 10h18"
                              stroke="currentColor"
                            />
                          </svg>
                          {{
                            new Date(attendance.tanggal).toLocaleDateString(
                              'id-ID',
                              {
                                weekday: 'short',
                                day: '2-digit',
                                month: 'short',
                                year: 'numeric',
                              }
                            )
                          }}
                        </span>
                      </td>

                      <!-- Status -->
                      <td class="p-3 border-b border-gray-100">
                        <span
                          :title="attendance.status"
                          :class="{
                            'bg-blue-100 text-blue-800': [
                              'Hadir',
                              'P',
                            ].includes(attendance.status),
                            'bg-red-100 text-red-800': [
                              'Absen',
                              'A',
                              'Alpa',
                            ].includes(attendance.status),
                            'bg-yellow-100 text-yellow-800': [
                              'Sakit',
                              'S',
                            ].includes(attendance.status),
                            'bg-purple-100 text-purple-800': [
                              'Izin',
                              'I',
                            ].includes(attendance.status),
                            'bg-gray-200 text-gray-700': ![
                              'Hadir',
                              'P',
                              'Absen',
                              'A',
                              'Alpa',
                              'Sakit',
                              'S',
                              'Izin',
                              'I',
                            ].includes(attendance.status),
                          }"
                          class="px-3 py-1 rounded-full font-semibold text-xs cursor-help"
                        >
                          {{ attendance.status }}
                        </span>
                      </td>
                    </tr>

                    <!-- Tombol Expand/Sembunyikan -->
                    <tr v-if="student.attendances.length > 10">
                      <td colspan="5" class="text-center py-2">
                        <button
                          class="text-xs text-blue-600 hover:underline focus:outline-none"
                          @click="
                            expandedTanggalId =
                              expandedTanggalId === student.id
                                ? null
                                : student.id
                          "
                        >
                          {{
                            expandedTanggalId === student.id
                              ? 'Sembunyikan'
                              : 'Lihat semua'
                          }}
                        </button>
                      </td>
                    </tr>
                  </template>

                  <!-- Jika tidak ada absensi -->
                  <tr
                    v-else
                    class="even:bg-blue-50 odd:bg-white hover:bg-blue-200/40 transition-colors duration-150"
                  >
                    <td
                      class="p-3 border-b border-gray-100 font-semibold sticky left-0 bg-white z-20"
                    >
                      {{ sIdx + 1 }}
                    </td>
                    <td
                      class="p-3 border-b border-gray-100 font-medium sticky left-12 bg-white z-20"
                    >
                      <span
                        class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-400 text-white font-bold text-base shadow mb-1"
                      >
                        {{
                          student.name
                            .split(' ')
                            .map((n) => n[0])
                            .join('')
                            .substring(0, 2)
                            .toUpperCase()
                        }}
                      </span>
                      <div class="text-center mt-1">{{ student.name }}</div>
                    </td>
                    <td class="p-3 border-b border-gray-100 text-center">
                      <span
                        class="inline-block px-2 py-0.5 rounded bg-blue-200 text-blue-800 text-xs font-semibold"
                      >
                        {{ student.subject || '—' }}
                      </span>
                    </td>
                    <td
                      colspan="2"
                      class="p-3 border-b border-gray-100 text-gray-400 italic text-center"
                    >
                      Tidak ada data absensi
                    </td>
                  </tr>
                </template>
              </tbody>
            </table>
            <div v-if="students && students.data && students.data.length > 0">
              <Pagination8 :data="students" :updatedPageNumber="goToPage" />
            </div>
            <div v-else class="flex justify-center items-center py-6">
              <p class="text-gray-500 text-center animate-pulse">
                Loading data...
              </p>
            </div>
          </div>
        </div>
      </div>

      <div v-else>
        <span class="flex justify-center text-lg font-bold mt-15">
          <img
            src="/images/kecewa_jadwal1.png"
            class="mr-3 h-60 max-h-full w-auto"
            alt="Tidak ada jadwal"
          />
        </span>
        <p class="text-gray-500 text-center mt-18">
          Silakan pilih filter dan klik "Tampilkan" untuk melihat data presensi
          siswa.
        </p>
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
