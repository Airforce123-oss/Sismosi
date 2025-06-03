<script setup>
import { initFlowbite } from 'flowbite';
import Pagination from '../../Components/Pagination.vue';
import { Link, Head, useForm, usePage, router } from '@inertiajs/vue3';
import { onMounted, ref, watch, computed } from 'vue';
import Swal from 'sweetalert2';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
const props = defineProps({
  auth: { type: Object },
  schedule: {
    type: Array,
    default: () => [],
  },
  master_mapel: {
    type: Object,
    required: true,
  },
  classes_for_student: {
    type: Object,
    required: true,
  },
  wali_kelas: {
    type: Object,
    default: () => ({ data: [] }),
  },
  teachers: Array,
  kelas_id: {
    type: Number,
    required: true,
  },
});

const teachers = ref([]);

const fetchTeachers = () => {
  try {
    // Menggunakan data yang diterima langsung dari props
    const teachersData = props.teachers; // Asumsi bahwa data guru diteruskan sebagai props

    // Memastikan data guru valid
    if (teachersData && Array.isArray(teachersData)) {
      // Proses data guru (seperti penambahan nama atau atribut lain)
      teachersData.forEach((teacher) => {
        //console.log('Nama Guru:', teacher.name); // Menampilkan nama guru
      });

      // Perbarui data teachers tanpa menghapus data yang ada
      teachersData.forEach((newTeacher) => {
        const index = teachers.value.findIndex(
          (teacher) => teacher.id === newTeacher.id
        );

        if (index === -1) {
          teachers.value.push(newTeacher); // Jika data baru, tambahkan
        } else {
          teachers.value[index] = newTeacher; // Jika ada data lama, update
        }
      });
    } else {
      console.error('Invalid or empty data for teachers:', teachersData);
    }
  } catch (error) {
    console.error('Error fetching teachers:', error);
  }
};

console.log('Classes for Student:', props.classes_for_student);
console.log('Teachers:', props.teachers);
console.log('Classes Data:', props.classes_for_student.data);

const form = useForm({
  name: props.auth?.user?.name || '',
  email: props.auth?.user?.email || '',
  role_type: props.auth?.user?.role_type || '',
});

const currentPage = ref(1); // Gunakan ini sebagai pengganti pageNumber
const searchTerm = ref('');

const waliKelas = ref(props.wali_kelas || { data: [] });
console.log('Wali Kelas:', waliKelas.value);

const kelasUrl = computed(() => {
  const url = new URL(route('matapelajaran.index'));
  console.log('URL manual:', url);
  url.searchParams.set('page', currentPage.value); // Gunakan currentPage
  if (searchTerm.value) {
    url.searchParams.set('search', searchTerm.value);
  }
  return url;
});

watch(
  () => kelasUrl.value,
  (updatedKelasUrl) => {
    console.log('Navigating to URL:', updatedKelasUrl.toString());
    router.visit(updatedKelasUrl.toString(), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
  }
);

// Ambil nama mapel dari master_mapel
const getMapelName = (id, fallbackName = '-') => {
  if (!id || !props.master_mapel || !Array.isArray(props.master_mapel.data)) {
    return fallbackName;
  }
  const mapel = props.master_mapel.data.find(
    (m) => String(m.id) === String(id)
  );
  return mapel && mapel.nama ? mapel.nama : fallbackName;
};

// Ambil nama kelas dari classes_for_student
const getKelasName = (id) => {
  if (
    !id ||
    !props.classes_for_student ||
    !Array.isArray(props.classes_for_student.data)
  ) {
    console.log(
      'getKelasName: id tidak valid atau classes_for_student tidak tersedia',
      id
    );
    return '-';
  }
  console.log(
    'getKelasName: mencari id',
    id,
    'di',
    props.classes_for_student.data
  );

  const kelas = props.classes_for_student.data.find(
    (k) => String(k.id) === String(id)
  );
  console.log('getKelasName:', { id, kelas });
  // Ganti .nama menjadi .name
  return kelas && kelas.name ? kelas.name : '-';
};

// Ambil nama guru dari teachers
const getGuruName = (id) => {
  if (!id || !props.teachers || !Array.isArray(props.teachers)) return '-';
  const guru = props.teachers.find((g) => g.id === id);
  return guru ? guru.name : '-';
};

const selectedKelas = ref(props.kelas_id || '');
console.log('Selected Kelas:', selectedKelas.value);

// HARI
const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

const schedule = ref([]);

const loadSchedule = async (id = null) => {
  try {
    let response;

    if (id === null || id === undefined) {
      // Ambil semua jadwal dari semua kelas
      response = await axios.get('/api/api-schedule', {
        params: { no_paginate: true },
      });
      console.log('📦 Semua jadwal dari semua kelas:', response.data);
    } else {
      const kelasId = parseInt(id, 10);
      console.log('Selected Kelas (before parsing):', id);

      if (isNaN(kelasId) || kelasId <= 0) {
        console.error(`❗ Kelas ID tidak valid: "${id}"`);
        schedule.value = [];
        return;
      }

      console.log('Parsed ID:', kelasId);

      response = await axios.get(route('jadwal.get'), {
        params: { kelas_id: kelasId },
      });
      console.log('📦 Jadwal untuk kelas ID:', kelasId, response.data);
    }

    const rawData = Array.isArray(response.data?.data)
      ? response.data.data
      : Array.isArray(response.data)
      ? response.data
      : [];

    // Gabungkan data berdasarkan jam_ke
    const groupedByJam = {};

    rawData.forEach((entry) => {
      const { jam_ke, jam, hari } = entry;

      if (!groupedByJam[jam_ke]) {
        groupedByJam[jam_ke] = {
          jam_ke: jam_ke,
          jam:
            jam ||
            `${String(jam_ke).padStart(2, '0')}:00 - ${String(jam_ke).padStart(
              2,
              '0'
            )}:45`,
          jadwal: {}, // ini akan diisi per hari
        };
      }

      // Masukkan data jadwal ke dalam hari yang sesuai
      groupedByJam[jam_ke].jadwal[hari] = {
        mapel: entry.mapel_nama || entry.mapel?.nama || '-',
        mapel_id: entry.mapel_id,
        kelas: entry.kelas_nama || entry.kelas?.nama || '-',
        kelas_id: entry.kelas_id,
        guru: entry.guru_nama || entry.guru?.nama || '-',
        guru_id: entry.guru_id,
        wali_kelas: entry.wali_kelas || '-',
        tahun: entry.tahun || '-',
      };
    });

    // Pastikan setiap jam_ke memiliki key jadwal untuk semua hari (meskipun null)
    for (const jamSlot of Object.values(groupedByJam)) {
      days.forEach((day) => {
        if (!jamSlot.jadwal[day]) {
          jamSlot.jadwal[day] = null;
        }
      });
    }

    // Ubah hasil ke bentuk array dan urutkan berdasarkan jam_ke
    const transformed = Object.values(groupedByJam).sort(
      (a, b) => a.jam_ke - b.jam_ke
    );

    schedule.value = transformed;
    console.log('✅ Schedule transformed & loaded:', schedule.value);
  } catch (error) {
    console.error('❌ Gagal mengambil jadwal:', error.response?.data || error);
    schedule.value = [];
  }
};

const getTeacherNameById = (id) => {
  if (!props.teachers || props.teachers.length === 0) return '-';
  const teacher = props.teachers.find((t) => t.id === id);
  return teacher ? teacher.name : '-';
};

const formatGuru = (guru) => {
  if (!guru) return '-';

  if (Array.isArray(guru)) {
    return guru.map((g) => g.name).join(', ');
  }

  if (typeof guru === 'number') {
    return getTeacherNameById(guru);
  }

  if (typeof guru === 'string') {
    return guru || '-';
  }

  return guru.name ?? '-';
};

const flatSchedule = computed(() => {
  console.log('Mengeksekusi flatSchedule, data schedule:', schedule.value);

  const scheduleArray = Object.values(schedule.value).flat();
  let counter = 1; // mulai dari 1

  return scheduleArray.flatMap((slot) => {
    return days
      .filter((day) => slot.jadwal[day])
      .map((day) => {
        const data = slot.jadwal[day];

        const entry = {
          id: counter++, // generate nomor urut, lalu tambah counter
          jam_ke: slot.jam_ke,
          jam: slot.jam,
          day,
          mapel: data?.mapel ?? '-',
          mapel_id: data?.mapel_id ?? null,
          kelas: data?.kelas ?? '-',
          guru: data?.guru || null,
          guru_id: data?.guru_id ?? null,
          tahun: data?.tahun ?? '-',
          wali_kelas: data?.wali_kelas ?? 'Tidak ada wali',
        };

        return entry;
      });
  });
});

const mergedSchedule = computed(() => {
  if (!schedule.value) return [];

  // Gabungkan semua jadwal dari tiap kelas
  return Object.values(schedule.value).flat();
});

console.log('DEBUG — days:', days);
const entries = ref([]);
console.log('isi', entries.value);

// MODAL STATE
const showModal = ref(false);
const selectedMapelModal = ref('');
console.log(
  'Selected Mapel:',
  selectedMapelModal.value,
  typeof selectedMapelModal.value
);

const editingSlot = ref({ jamKe: null, hari: '' });

const isTimeSlotMatching = (timeSlot, targetTime) => {
  const parseTime = (time) => {
    const [hours, minutes] = time.split(':');
    return parseInt(hours) * 60 + parseInt(minutes); // Mengubah waktu menjadi menit
  };

  // Mengubah jam yang ada menjadi format menit
  const [start, end] = timeSlot.split(' - ').map(parseTime);
  const [targetStart, targetEnd] = targetTime.split(' - ').map(parseTime);

  // Memeriksa apakah target time berada dalam rentang waktu yang ada
  return targetStart >= start && targetEnd <= end;
};

// Fungsi untuk mencari apakah ada slot yang sesuai
const openEditModal = (jamKe, hari) => {
  const jamYangDicari = editingSlot.jamKe; // Misalnya "07:00 - 07:30"

  // Cari slot yang memiliki jam yang sesuai
  const currentSlot = schedule.value.find((slot) => {
    return isTimeSlotMatching(slot.jam, jamYangDicari);
  });

  if (currentSlot) {
    console.log('Slot ditemukan:', currentSlot);
    // Lakukan aksi yang diinginkan dengan currentSlot
  } else {
    console.log('❌ Tidak ditemukan jam untuk jam_ke:', jamYangDicari);
  }
};

onMounted(() => {
  fetchTeachers();
  loadSchedule(null); // null berarti load semua jadwal
  initFlowbite();
});

watch(selectedKelas, (newVal) => {
  console.log('Selected Kelas changed to:', newVal);
  const id = parseInt(newVal, 10);
  if (isNaN(id)) {
    console.warn('❗ kelas_id tidak valid. Jadwal tidak dimuat.');
    schedule.value = [];
    return;
  }

  loadSchedule(id); // Pastikan loadSchedule menerima ID numerik
});
watch(schedule, (newVal) => {
  if (Array.isArray(newVal) && newVal.length > 0) {
    console.log('🎯 Jadwal masuk lewat watch:', newVal);
    // lanjutkan logika lain di sini
  } else {
    console.log('⚠️ Schedule belum siap atau kosong:', newVal);
  }
});
</script>

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
    <!-- start1 -->

    <main class="md:ml-64 pt-20 min-h-screen bg-gray-100">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <div class="p-6 space-y-6">
          <!--LAPORAN JADWAL-->
          <div class="-mt-12">
            <h2
              class="text-2xl text-center font-bold text-gray-800 tracking-wide"
            >
              Laporan Jadwal Mata Pelajaran
            </h2>
          </div>
        </div>
      </div>
      <div v-for="(item, index) in schedule.value" :key="index">
        <p>Jam Ke: {{ item.jam_ke }}</p>
        <p>Jam: {{ item.jam }}</p>
        <div v-for="(hari, day) in item.jadwal" :key="day">
          <p>
            {{ day.charAt(0).toUpperCase() + day.slice(1) }}:
            <span v-if="hari">{{ hari.mapel }} ({{ hari.kelas }})</span>
            <span v-else>-</span>
          </p>
        </div>
      </div>

      <!-- Perbaikan tampilan jadwal per jam_ke -->
      <div
        v-for="(slot, index) in schedule"
        :key="index"
        class="w-full max-w-6xl mx-auto mb-8 p-4 sm:p-6 bg-white rounded-xl shadow-lg min-h-[200px]"
      >
        <h3 class="font-bold text-lg mb-4 text-blue-700 text-center">
          Jam ke-{{ slot.jam_ke }}
          <span class="text-gray-500">({{ slot.jam }})</span>
        </h3>

        <div class="overflow-x-auto">
          <table
            class="min-w-[900px] w-full text-sm border border-gray-200 rounded-lg overflow-hidden shadow-sm"
          >
            <thead
              class="bg-gradient-to-r from-blue-100 to-blue-300 text-blue-900 uppercase text-xs tracking-wider"
            >
              <tr>
                <th
                  class="p-3 border-b border-gray-200 text-left whitespace-nowrap"
                >
                  Hari
                </th>
                <th
                  class="p-3 border-b border-gray-200 text-left whitespace-nowrap"
                >
                  Mata Pelajaran
                </th>
                <th
                  class="p-3 border-b border-gray-200 text-left whitespace-nowrap"
                >
                  Kelas
                </th>
                <th
                  class="p-3 border-b border-gray-200 text-left whitespace-nowrap"
                >
                  Guru
                </th>
                <th
                  class="p-3 border-b border-gray-200 text-left whitespace-nowrap"
                >
                  Wali Kelas
                </th>
                <th
                  class="p-3 border-b border-gray-200 text-left whitespace-nowrap"
                >
                  Tahun
                </th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="day in days"
                :key="day"
                class="even:bg-blue-50 odd:bg-white hover:bg-blue-100 transition-colors"
              >
                <td
                  class="p-3 border-b border-gray-100 capitalize font-semibold text-blue-800 whitespace-nowrap"
                >
                  {{ day }}
                </td>
                <td class="p-3 border-b border-gray-100">
                  <span
                    class="inline-block px-2 py-1 rounded bg-blue-200 text-black font-medium whitespace-nowrap"
                  >
                    {{
                      getMapelName(
                        slot.jadwal[day]?.mapel_id,
                        slot.jadwal[day]?.mapel || '-'
                      )
                    }}
                  </span>
                </td>
                <td class="p-3 border-b border-gray-100">
                  <span
                    class="inline-block px-2 py-1 rounded bg-green-100 text-black font-medium whitespace-nowrap"
                  >
                    {{
                      slot.jadwal[day]?.kelas_id
                        ? getKelasName(slot.jadwal[day].kelas_id)
                        : '-'
                    }}
                  </span>
                </td>
                <td class="p-3 border-b border-gray-100">
                  <span
                    class="inline-block px-2 py-1 rounded bg-yellow-100 text-black font-medium whitespace-nowrap"
                  >
                    {{
                      slot.jadwal[day]?.guru_id
                        ? getGuruName(slot.jadwal[day].guru_id)
                        : '-'
                    }}
                  </span>
                </td>
                <td class="p-3 border-b border-gray-100">
                  <span
                    class="inline-block px-2 py-1 rounded bg-purple-100 text-black font-medium whitespace-nowrap"
                  >
                    {{ slot.jadwal[day]?.wali_kelas || '-' }}
                  </span>
                </td>
                <td class="p-3 border-b border-gray-100">
                  <span
                    class="inline-block px-2 py-1 rounded bg-gray-100 text-black font-medium whitespace-nowrap"
                  >
                    {{ slot.jadwal[day]?.tahun || '-' }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="Object.values(slot.jadwal).every((j) => !j)">
          <span class="text-gray-400 italic block text-center mt-4"
            >Belum ada data jadwal untuk jam ke-{{ slot.jam_ke }}</span
          >
        </div>
      </div>
    </main>

    <!-- end1-->

    <!-- Sidebar -->
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
              href="admin-dashboard"
              class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
            >
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
                />
              </svg>
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
              <svg
                viewBox="0 0 256 256"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
              >
                <rect fill="none" height="256" width="256" />
                <path
                  d="M226.5,56.4l-96-32a8.5,8.5,0,0,0-5,0l-95.9,32h-.2l-1,.5h-.1l-1,.6c0,.1-.1.1-.2.2l-.8.7h0l-.7.8c0,.1-.1.1-.1.2l-.6.9c0,.1,0,.1-.1.2l-.4.9h0l-.3,1.1v.3A3.7,3.7,0,0,0,24,64v80a8,8,0,0,0,16,0V75.1L73.6,86.3A63.2,63.2,0,0,0,64,120a64,64,0,0,0,30,54.2,96.1,96.1,0,0,0-46.5,37.4,8.1,8.1,0,0,0,2.4,11.1,7.9,7.9,0,0,0,11-2.3,80,80,0,0,1,134.2,0,8,8,0,0,0,6.7,3.6,7.5,7.5,0,0,0,4.3-1.3,8.1,8.1,0,0,0,2.4-11.1A96.1,96.1,0,0,0,162,174.2,64,64,0,0,0,192,120a63.2,63.2,0,0,0-9.6-33.7l44.1-14.7a8,8,0,0,0,0-15.2ZM128,168a48,48,0,0,1-48-48,48.6,48.6,0,0,1,9.3-28.5l36.2,12.1a8,8,0,0,0,5,0l36.2-12.1A48.6,48.6,0,0,1,176,120,48,48,0,0,1,128,168Z"
                />
              </svg>

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
                  href="students"
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
              <svg
                viewBox="0 0 640 512"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
              >
                <path
                  d="M208 352c-2.39 0-4.78.35-7.06 1.09C187.98 357.3 174.35 360 160 360c-14.35 0-27.98-2.7-40.95-6.91-2.28-.74-4.66-1.09-7.05-1.09C49.94 352-.33 402.48 0 464.62.14 490.88 21.73 512 48 512h224c26.27 0 47.86-21.12 48-47.38.33-62.14-49.94-112.62-112-112.62zm-48-32c53.02 0 96-42.98 96-96s-42.98-96-96-96-96 42.98-96 96 42.98 96 96 96zM592 0H208c-26.47 0-48 22.25-48 49.59V96c23.42 0 45.1 6.78 64 17.8V64h352v288h-64v-64H384v64h-76.24c19.1 16.69 33.12 38.73 39.69 64H592c26.47 0 48-22.25 48-49.59V49.59C640 22.25 618.47 0 592 0z"
                />
              </svg>

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
                  href="teachers"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Data Induk Guru</a
                >
              </li>
              <!-- Dropdown Absensi Guru -->
              <li>
                <a
                  href="absensiGuru"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Absensi Guru</a
                >
              </li>
              <!-- Dropdown Daftar Absensi Guru -->
              <li>
                <a
                  href="dataAbsensiGuru"
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
              <svg
                id="Icons_Teacher"
                overflow="hidden"
                version="1.1"
                viewBox="0 0 96 96"
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                class="w-5 h-5"
              >
                <path
                  d=" M 87.8 19 L 23.8 19 C 21.6 19 19.8 20.8 19.8 23 L 19.8 37.5 C 20.9 37.2 22.2 37 23.4 37 C 24.2 37 25 37.1 25.8 37.2 L 25.8 25 L 85.8 25 L 85.8 63 L 51.9 63 L 46.2 69 L 87.8 69 C 90 69 91.8 67.2 91.8 65 L 91.8 23 C 91.8 20.8 90 19 87.8 19"
                />
                <path
                  d=" M 23.5 58 C 28.2 58 32 54.2 32 49.5 C 32 44.8 28.2 41 23.5 41 C 18.8 41 15 44.8 15 49.5 C 14.9 54.2 18.8 58 23.5 58"
                />
                <path
                  d=" M 56.2 48.1 C 54.9 46.1 52.3 45.6 50.3 46.8 C 49.9 47 49.7 47.4 49.5 47.6 L 34.9 62.8 C 33.5 62.1 32 61.5 30.5 61 C 28.2 60.6 25.8 60.1 23.5 60.1 C 21.2 60.1 18.8 60.5 16.5 61.2 C 13.1 62.1 10.1 63.8 7.6 65.9 C 7 66.5 6.5 67.4 6.3 68.2 L 4.2 77 L 34.1 77 L 34.1 76.9 L 42.6 67 L 55.7 53.2 C 56.9 52 57.3 49.7 56.2 48.1"
                />
              </svg>
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
                  href="kelas"
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
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="w-5 h-5"
              >
                <path
                  fill-rule="evenodd"
                  d="M9.664 1.319a.75.75 0 0 1 .672 0 41.059 41.059 0 0 1 8.198 5.424.75.75 0 0 1-.254 1.285 31.372 31.372 0 0 0-7.86 3.83.75.75 0 0 1-.84 0 31.508 31.508 0 0 0-2.08-1.287V9.394c0-.244.116-.463.302-.592a35.504 35.504 0 0 1 3.305-2.033.75.75 0 0 0-.714-1.319 37 37 0 0 0-3.446 2.12A2.216 2.216 0 0 0 6 9.393v.38a31.293 31.293 0 0 0-4.28-1.746.75.75 0 0 1-.254-1.285 41.059 41.059 0 0 1 8.198-5.424ZM6 11.459a29.848 29.848 0 0 0-2.455-1.158 41.029 41.029 0 0 0-.39 3.114.75.75 0 0 0 .419.74c.528.256 1.046.53 1.554.82-.21.324-.455.63-.739.914a.75.75 0 1 0 1.06 1.06c.37-.369.69-.77.96-1.193a26.61 26.61 0 0 1 3.095 2.348.75.75 0 0 0 .992 0 26.547 26.547 0 0 1 5.93-3.95.75.75 0 0 0 .42-.739 41.053 41.053 0 0 0-.39-3.114 29.925 29.925 0 0 0-5.199 2.801 2.25 2.25 0 0 1-2.514 0c-.41-.275-.826-.541-1.25-.797a6.985 6.985 0 0 1-1.084 3.45 26.503 26.503 0 0 0-1.281-.78A5.487 5.487 0 0 0 6 12v-.54Z"
                  clip-rule="evenodd"
                />
              </svg>

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
                  href="mataPelajaran"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Data Mata Pelajaran</a
                >
              </li>
              <li>
                <a
                  href="settingJadwalMataPelajaran"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Jadwal Mata Pelajaran</a
                >
              </li>
              <li>
                <a
                  href="laporanJadwalMataPelajaran"
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
              <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
                class="w-5 h-5"
              >
                <path
                  fill-rule="evenodd"
                  d="M9.664 1.319a.75.75 0 0 1 .672 0 41.059 41.059 0 0 1 8.198 5.424.75.75 0 0 1-.254 1.285 31.372 31.372 0 0 0-7.86 3.83.75.75 0 0 1-.84 0 31.508 31.508 0 0 0-2.08-1.287V9.394c0-.244.116-.463.302-.592a35.504 35.504 0 0 1 3.305-2.033.75.75 0 0 0-.714-1.319 37 37 0 0 0-3.446 2.12A2.216 2.216 0 0 0 6 9.393v.38a31.293 31.293 0 0 0-4.28-1.746.75.75 0 0 1-.254-1.285 41.059 41.059 0 0 1 8.198-5.424ZM6 11.459a29.848 29.848 0 0 0-2.455-1.158 41.029 41.029 0 0 0-.39 3.114.75.75 0 0 0 .419.74c.528.256 1.046.53 1.554.82-.21.324-.455.63-.739.914a.75.75 0 1 0 1.06 1.06c.37-.369.69-.77.96-1.193a26.61 26.61 0 0 1 3.095 2.348.75.75 0 0 0 .992 0 26.547 26.547 0 0 1 5.93-3.95.75.75 0 0 0 .42-.739 41.053 41.053 0 0 0-.39-3.114 29.925 29.925 0 0 0-5.199 2.801 2.25 2.25 0 0 1-2.514 0c-.41-.275-.826-.541-1.25-.797a6.985 6.985 0 0 1-1.084 3.45 26.503 26.503 0 0 0-1.281-.78A5.487 5.487 0 0 0 6 12v-.54Z"
                  clip-rule="evenodd"
                />
              </svg>

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
                  href="indexMasterJabatan"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Data Master Jabatan</a
                >
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </aside>
  </div>
</template>
