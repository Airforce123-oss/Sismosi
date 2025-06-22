<script setup>
import { initFlowbite } from 'flowbite';
import Pagination from '../../Components/Pagination.vue';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';
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
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mt-8 mb-12 text-center">
          <h2 class="text-2xl font-bold text-gray-800 tracking-wide">
            Laporan Jadwal Mata Pelajaran
          </h2>
        </div>

        <div
          v-for="(slot, index) in schedule"
          :key="index"
          class="w-full max-w-7xl mx-auto mt-16 mb-10 p-4 sm:p-6 bg-white rounded-xl shadow-lg"
        >
          <h3 class="font-bold text-lg mb-4 text-blue-700 text-center">
            Jam ke-{{ slot.jam_ke }}
            <span class="text-gray-500">({{ slot.jam }})</span>
          </h3>

          <div class="overflow-x-auto">
            <table
              class="table-fixed w-full min-w-[1000px] text-sm border border-gray-200 rounded-lg overflow-hidden shadow-sm"
            >
              <thead
                class="bg-gradient-to-r from-blue-100 to-blue-300 text-blue-900 uppercase text-xs tracking-wider"
              >
                <tr>
                  <th class="w-24 p-3 border-b border-gray-200 text-left">
                    Hari
                  </th>
                  <th class="w-48 p-3 border-b border-gray-200 text-left">
                    Mata Pelajaran
                  </th>
                  <th class="w-32 p-3 border-b border-gray-200 text-left">
                    Kelas
                  </th>
                  <th class="w-40 p-3 border-b border-gray-200 text-left">
                    Guru
                  </th>
                  <th class="w-40 p-3 border-b border-gray-200 text-left">
                    Wali Kelas
                  </th>
                  <th class="w-24 p-3 border-b border-gray-200 text-left">
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
                    class="w-24 p-3 border-b border-gray-100 capitalize font-semibold text-blue-800"
                  >
                    {{ day }}
                  </td>
                  <td class="w-48 p-3 border-b border-gray-100">
                    <span
                      class="inline-block px-2 py-1 rounded bg-blue-200 text-black font-medium max-w-[12rem] line-clamp-2"
                    >
                      {{
                        getMapelName(
                          slot.jadwal[day]?.mapel_id,
                          slot.jadwal[day]?.mapel || '-'
                        )
                      }}
                    </span>
                  </td>
                  <td class="w-32 p-3 border-b border-gray-100">
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
                  <td class="w-40 p-3 border-b border-gray-100">
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
                  <td class="w-40 p-3 border-b border-gray-100">
                    <span
                      class="inline-block px-2 py-1 rounded bg-purple-100 text-black font-medium whitespace-nowrap"
                    >
                      {{ slot.jadwal[day]?.wali_kelas || '-' }}
                    </span>
                  </td>
                  <td class="w-24 p-3 border-b border-gray-100">
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
            <p class="text-gray-400 italic text-center mt-4">
              Belum ada data jadwal untuk jam ke-{{ slot.jam_ke }}
            </p>
          </div>
        </div>
      </div>
    </main>

    <!-- end1-->

    <!-- Sidebar -->
    <SidebarAdmin />
  </div>
</template>
