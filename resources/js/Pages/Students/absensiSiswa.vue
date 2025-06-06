<script setup>
import {
  onMounted,
  ref,
  computed,
  watch,
  createApp,
  watchEffect,
  nextTick,
  toRaw,
  isRef,
} from 'vue';
import axios from 'axios';
import { initFlowbite } from 'flowbite';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SidebarTeacher from '@/Components/SidebarTeacher.vue';
import { useForm, usePage, Head } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import '@assets/plugins/simple-calendar/jquery.simple-calendar.js';
import '@assets/plugins/simple-calendar/simple-calendar.css';
import { createRouter, createWebHistory, useRoute } from 'vue-router';
import AbsensiSiswa from './absensiSiswa.vue';
import AbsensiSiswaJanuari from './absensiSiswaJanuari.vue';

const routes = [
  {
    path: '/absensi/:kelas/:year/:mapel/:month',
    name: 'absensi', // Harus sesuai dengan nama route di Laravel
    component: AbsensiSiswa, // Komponen tampilan absensi
  },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

console.log('Daftar rute yang terdaftar dalam router:', router.getRoutes());

/*
const app = createApp({
  template: `
   <div>
         <h1>Selamat Datang di Aplikasi Vue!</h1>
       </div>
  `,
});
app.use(router);
app.mount('#app');
*/

const userName = ref('');
const { props } = usePage();

//const teacherClass = ref(props.teacherClass || 'Tidak ada kelas terkait');
const teacherClass = ref(
  typeof props.teacherClass === 'object' && props.teacherClass !== null
    ? props.teacherClass
    : { id: null, name: 'Belum ada kelas' }
);

//console.log('🧪 Inisialisasi teacherClass:', teacherClass.value);

const mapelList = ref([]);
const auth = props.auth;
//console.log('Props received:', JSON.stringify(props, null, 2));
const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

const taskForm = ref({
  mapel_id: '',
});

const fetchSessionData = async () => {
  try {
    const response = await axios.get('/api/session-name');
    userName.value = response.data.name;
  } catch (error) {
    console.error('Terjadi kesalahan saat mengambil data session:', error);
  }
};

// Fungsi untuk mengambil kelas berdasarkan guru
const fetchClassByTeacher = async () => {
  const interval = setInterval(async () => {
    const teacherId = props.auth.user?.id;

    if (!teacherId) {
      console.warn('⏳ Menunggu ID guru tersedia...');
      return;
    }

    clearInterval(interval); // ID sudah tersedia, hentikan polling
    console.log('📤 Mengambil data kelas untuk guru ID:', teacherId);

    try {
      const response = await axios.get('/api/class-by-teacher', {
        params: { id: teacherId },
      });

      console.log('✅ Response API:', response.data);

      if (response.data && response.data.class) {
        teacherClass.value = response.data.class;
        console.log(
          '🎯 teacherClass setelah di-set dari API:',
          teacherClass.value
        );
      } else {
        console.warn('⚠️ Data kelas tidak ditemukan dalam response.');
        teacherClass.value = 'Tidak ada kelas terkait';
      }
    } catch (error) {
      console.error('❌ Terjadi kesalahan:', error);
      if (error.response) {
        teacherClass.value = `Error: ${error.response.status} - ${
          error.response.data.message || 'Terjadi kesalahan'
        }`;
      } else {
        teacherClass.value = 'Error: Tidak dapat terhubung ke server';
      }
    }
  }, 300); // cek tiap 300ms sampai ID tersedia
};

const selectedTeacherId = ref(props.teacherId || null);

const fetchMapelByTeacher = async () => {
  try {
    const teacherId = props.auth.user.id;
    const response = await axios.get('/api/get-mapel-by-teacher-id', {
      params: { teacher_id: teacherId },
    });

    if (response.data && Array.isArray(response.data.mapel)) {
      selectedMapel.value = response.data.mapel;
      console.log('✅ Mapel yang diambil:', selectedMapel.value);
    } else {
      console.warn('Mapel tidak ditemukan dalam response.');
    }
  } catch (error) {
    console.error('❌ Error fetching mapel:', error);
  }
};

const fetchData = async () => {
  try {
    console.log('🧪 props:', props);

    // ✅ Cek dan set selectedKelas dari teacherClass
    if (teacherClass) {
      const value = isRef(teacherClass) ? teacherClass.value : teacherClass;

      if (typeof value === 'object' && value?.id && value?.name) {
        teacherClass.value = {
          id: value.id,
          name: value.name,
        };
        console.log(
          '✅ selectedKelas di-set dari teacherClass (object):',
          teacherClass.value
        );
      } else if (typeof value === 'string') {
        teacherClass.value = {
          id: null,
          name: value,
        };
        console.log(
          '✅ selectedKelas di-set dari teacherClass (string):',
          teacherClass.value
        );
      } else {
        console.warn('⚠️ teacherClass tidak valid:', value);
        teacherClass.value = { id: null, name: 'No Class Available' };
      }
    } else {
      console.warn('⚠️ teacherClass tidak tersedia');
      teacherClass.value = { id: null, name: 'No Class Available' };
    }

    // ✅ Ambil data mapel (tanpa ambil kelas dari API)
    const response = await axios.get('/api/absensi-siswa');
    const data = response.data;

    console.log('✅ Full API Response:', data);

    if (Array.isArray(data.data)) {
      mapelList.value = data.data;
      console.log('📚 Mapel List:', mapelList.value);
    } else {
      console.warn('⚠️ Data mapel tidak valid:', data);
      mapelList.value = [];
    }

    console.log(
      '🎯 Final Selected Kelas setelah proses API:',
      teacherClass.value
    );
  } catch (error) {
    console.error('❌ Gagal fetch data absensi:', error);
    mapelList.value = [];
    teacherClass.value = { id: null, name: 'No Class Available' };
  }
};

const fetchKelas = async () => {
  try {
    const response = await axios.get('/api/absensi-siswa');
    const kelasData = response.data.classes?.data || [];

    console.log('📥 API Response kelas:', kelasData);

    // Saring kelas dengan ID valid
    const validKelasList = kelasData.filter((k) => k && k.id !== null);
    kelasList.value = validKelasList;

    if (validKelasList.length === 0) {
      console.warn('⚠️ Tidak ada kelas valid ditemukan!');
      teacherClass.value = { id: null, name: 'No Class Available' };
      return;
    }

    // Ambil dari localStorage jika ada
    const savedKelas = localStorage.getItem('selectedKelas');
    if (savedKelas) {
      try {
        const parsedKelas = JSON.parse(savedKelas);
        const found = validKelasList.find((k) => k.id === parsedKelas.id);

        if (found && found.id) {
          console.log('✅ Ditemukan kelas dari localStorage:', found);
          teacherClass.value = found;
        } else {
          console.warn('⚠️ ID dari savedKelas tidak cocok. Gunakan default.');
          teacherClass.value = validKelasList[0];
        }
      } catch (e) {
        console.error('❌ Gagal parse savedKelas:', e);
        teacherClass.value = validKelasList[0];
      }
    } else {
      teacherClass.value = validKelasList[0];
    }

    // Simpan kembali ke localStorage saat selectedKelas berubah
    watch(
      () => teacherClass.value,
      (newKelas) => {
        if (newKelas && newKelas.id !== null) {
          try {
            // Gunakan `toRaw()` untuk menghilangkan reaktivitas Vue
            localStorage.setItem(
              'selectedKelas',
              JSON.stringify(toRaw(newKelas)) // mengonversi menjadi objek biasa
            );
            console.log('💾 selectedKelas disimpan ke localStorage:', newKelas);
          } catch (error) {
            console.error('❌ Gagal simpan ke localStorage:', error);
          }
        }
      },
      { deep: true }
    );
  } catch (error) {
    console.error('❌ Error saat fetch kelas:', error);
    kelasList.value = [];
    teacherClass.value = { id: null, name: 'No Class Available' };
  }
};

const saveSelectedMapel = async (mapel) => {
  try {
    console.log('Selected Kelas sebelum penyimpanan:', teacherClass.value);
    console.log('Selected Student ID:', selectedStudentId.value);

    // Validasi kelas yang dipilih
    if (!selectedKelas.value || !selectedKelas.value.id) {
      console.warn(
        'Kelas yang dipilih tidak valid. Silakan pilih kelas terlebih dahulu.'
      );
      return;
    }

    // Validasi mapel yang dipilih
    if (!mapel || !mapel.mapel) {
      console.error(
        'Mapel yang dipilih tidak valid. Silakan pilih mapel terlebih dahulu.'
      );
      return;
    }

    // Validasi ID siswa
    const studentId = selectedStudentId.value;
    if (!studentId) {
      console.error(
        'ID siswa tidak valid. Silakan pilih siswa terlebih dahulu.'
      );
      return;
    }

    // Mengirim data ke backend
    const response = await axios.post('/api/save-selected-mapel', {
      mapel: mapel.mapel,
      kelas: selectedKelas.value.id,
      student_id: studentId,
    });

    // Mengecek respons dari server
    if (response.status === 200) {
      console.log('Pilihan mapel berhasil disimpan:', response.data);
    } else {
      console.error('Gagal menyimpan pilihan mapel:', response.data);
    }
  } catch (error) {
    console.error('Error saving selected mapel:', error);
    if (error.response) {
      console.error('Server response:', error.response);
    }
  }
};

const isSubmitting = ref(false); // Cegah request ganda

const onMapelChange = async (event) => {
  const newValue = event.target.value;

  if (isSubmitting.value) {
    console.warn('⚠️ Request sedang diproses, dibatalkan.');
    return;
  }

  if (!newValue) {
    console.error('❌ selectedMapel kosong, request dibatalkan.');
    return;
  }

  console.log('🔥 onMapelChange triggered!');
  console.log('👉 event.target.value:', newValue);

  if (selectedMapel.value === newValue) {
    console.log('⚠️ Tidak ada perubahan, request dibatalkan.');
    return;
  }

  isSubmitting.value = true; // Mencegah spam request
  selectedMapel.value = newValue; // Set langsung sebelum request

  // Tunggu hingga Vue memperbarui selectedMapel
  await nextTick();

  console.log('📤 Setelah nextTick, selectedMapel:', selectedMapel.value);

  Inertia.visit(route('absensiSiswaJanuari'), {
    method: 'get',
    data: { selectedMapel: newValue },
    preserveState: false,
    onSuccess: () => {
      console.log('✅ Inertia visit success!');
      isSubmitting.value = false;
    },
    onError: (error) => {
      console.error('❌ Inertia error:', error);
      isSubmitting.value = false;
    },
  });
};

// Fungsi yang dipanggil ketika komponen dimuat
onMounted(async () => {
  console.log('✅ selectedKelas sebelum fetch:', teacherClass.value);

  // Jika props.teacherClass tersedia, set ke selectedKelas
  if (props.teacherClass?.id && props.teacherClass?.name) {
    teacherClass.value = {
      id: props.teacherClass.id,
      name: props.teacherClass.name,
    };
    console.log(
      '✅ [onMounted] Mengatur dari props.teacherClass:',
      selectedKelas.value
    );
  }

  console.log('🧑‍🏫 Fetching kelas berdasarkan teacherClass...');
  await fetchClassByTeacher(); // tidak akan overwrite jika selectedKelas sudah valid

  console.log(
    '✅ selectedKelas setelah fetchClassByTeacher:',
    teacherClass.value
  );

  fetchMapelByTeacher();
  await fetchAttendanceData();
  initFlowbite();
  await fetchSessionData();
  await fetchData();
  await fetchKelas();
  await nextTick();
});

const years = ['2025', '2026', '2027', '2028']; // Hanya tahun tunggal
const currentMonth = [
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

// Pemilihan Tahun dan Bulan untuk filter
const selectedYear = ref(years[0]);
console.log('✅ currentMonth sebelum ref:', currentMonth);
//const selectedMonth = ref(currentMonth);
const selectedMonth = ref('Januari');
console.log('✅ selectedMonth setelah ref:', selectedMonth.value);

const selectedMapel = ref([]);
const student = ref({});
const teacherId = props.auth.user.id;
const kelasList = ref([]);
console.log('Sebelum mengirim props ke anak:', {
  selectedMapel: selectedMapel.value,
  student: student.value,
});

//const selectedKelas = ref({ id: 1, name: 'X-1' });
//const selectedKelas = ref('');
//const selectedKelas = ref(null);
const selectedStudentId = ref(null);
const selectedAttendanceStatus = ref(null);
console.log('Default Kelas:', props.defaultKelas);
console.log('🔍 selectedKelas setelah update:', teacherClass.value);

const generateUrl = (year, month, mapel, selectedKelas) => {
  console.log('🧐 [generateUrl] Input awal:', {
    year,
    month,
    mapel,
    selectedKelas,
  });

  console.log('📦 selectedKelas type:', typeof selectedKelas);
  console.log('📦 selectedKelas is reactive ref:', !!teacherClass?.value);
  console.log(
    '📦 selectedKelas.id:',
    teacherClass?.id ?? teacherClass?.value?.id
  );

  // Cek fungsi route dari Ziggy
  if (typeof route !== 'function') {
    console.error('❌ [generateUrl] Ziggy route helper tidak tersedia!');
    return '#';
  }

  // Cek dan ambil selectedKelas jika belum ada
  if (!selectedKelas) {
    const userId = props.auth?.user?.id;
    console.log(
      '🔍 [generateUrl] selectedKelas tidak ada, cek userId:',
      userId
    );

    if (userId) {
      const match = kelasList.value.find((k) => k.wali_kelas_id === userId);
      if (match) {
        console.log(
          '✅ [generateUrl] Ditemukan kelas berdasarkan user_id:',
          match
        );
        selectedKelas = match;
      } else {
        console.warn(
          '⚠️ [generateUrl] Tidak ada kelas cocok untuk user_id:',
          userId
        );
      }
    } else {
      console.error('❌ [generateUrl] user_id tidak ditemukan di props.auth!');
      return '#';
    }
  }

  // Ambil id kelas dengan fallback jika reactive
  const kelasId = selectedKelas?.id ?? selectedKelas?.value?.id ?? null;

  console.log('🧾 [generateUrl] selectedKelas saat ini:', selectedKelas);
  console.log('🧾 [generateUrl] kelasId terdeteksi:', kelasId);

  if (!kelasId) {
    console.error(
      '❌ [generateUrl] Tidak bisa generate URL karena kelasId tidak valid!',
      { selectedKelas }
    );
    return '#';
  }

  // Persiapan mapel
  let mapelList = [];
  if (Array.isArray(mapel)) {
    mapelList = mapel.map((m) => (typeof m === 'object' ? m.mapel : m));
  } else if (Array.isArray(mapel?.value)) {
    mapelList = mapel.value.map((m) => (typeof m === 'object' ? m.mapel : m));
  } else if (typeof mapel === 'string') {
    mapelList = [mapel];
  }

  console.log(
    '📚 [generateUrl] Daftar mapel yang akan dibersihkan:',
    mapelList
  );

  const cleanMapel = mapelList
    .map((m) =>
      String(m).trim().replace(/\s+/g, '-').replace(/[()]/g, '').toLowerCase()
    )
    .join(',');

  // Persiapan bulan
  const cleanMonth = Array.isArray(month)
    ? month[0]
    : String(month || '')
        .trim()
        .toLowerCase();

  // Tahun validasi
  const validYear = year && !isNaN(year) ? year : new Date().getFullYear();

  console.log('🧾 [generateUrl] Final Param:', {
    kelas: kelasId,
    year: validYear,
    mapel: cleanMapel,
    month: cleanMonth || selectedMonth.value || '',
  });

  // Generate URL
  const url = route('absensiSiswaJanuari', {
    kelas: kelasId,
    year: validYear,
    mapel: cleanMapel || '',
    month: cleanMonth || selectedMonth.value || '',
  });

  console.log('✅ [generateUrl] URL yang dihasilkan:', url);
  return url;
};

const attendanceData = ref([]);
const errorMessage = ref('');

/*
watch(selectedKelas, (newVal) => {
  if (newVal && typeof newVal === 'object' && newVal.id) {
    //localStorage.setItem('selectedKelas', JSON.stringify(newVal));
    //console.log('✅ Disimpan ke localStorage:', newVal);
  } else {
    console.warn(
      '❌ Tidak menyimpan selectedKelas karena tidak valid:',
      newVal
    );
  }
});
*/

watch(
  () => props.selectedMapel,
  (newVal) => {
    if (newVal && newVal !== '') {
      console.log('🟢 [watch] Setting selectedMapel to:', newVal);
      selectedMapel.value = newVal;
    } else {
      console.log('🟡 [watch] newVal kosong, tidak diubah.');
    }
  }
);

watch(
  () => taskForm.mapel_id,
  (newValue, oldValue) => {
    console.log('Mapel ID berubah:', oldValue, '->', newValue);
  }
);

watch(
  () => taskForm.student_id,
  (newValue, oldValue) => {
    console.log('Student ID berubah:', oldValue, '->', newValue);
  }
);

const fetchAttendanceData = async (month, year) => {
  try {
    const response = await axios.get('/api/attendance', {
      params: {
        month: month,
        year: year,
      },
    });
    attendanceData.value = response.data; // Menyimpan data absensi
  } catch (error) {
    console.error('Terjadi kesalahan saat mengambil data absensi:', error);
    errorMessage.value = 'Gagal mengambil data session.';
  }
};

/*
watch(mapelList, (newValue) => {
  console.log('mapelList berubah:', newValue);
  if (Array.isArray(newValue)) {
    console.log('mapelList adalah array');
    newValue.forEach((mapel, index) => {
      //console.log(`Mapel ${index + 1}:`, mapel);
    });
  } else {
    console.error('mapelList bukan array');
  }
});
*/

const isManualChange = ref(false); // Jadikan reactive agar perubahan terdeteksi

watch(
  [selectedMonth, teacherClass],
  ([newMonth, newKelas], [oldMonth, oldKelas]) => {
    console.log(`🔥 selectedMonth berubah dari ${oldMonth} ke ${newMonth}`);

    // Pastikan oldMonth memiliki nilai sebelum melakukan pengecekan
    if (!oldMonth) return;

    // **Cegah reset bulan ke Februari tanpa input user**
    if (
      !isManualChange.value &&
      newMonth === 'Februari' &&
      oldMonth !== 'Februari'
    ) {
      console.warn(
        '🚨 selectedMonth kembali ke Februari tanpa input user! Kembalikan ke nilai sebelumnya.'
      );
      isManualChange.value = true; // Set flag agar tidak looping
      selectedMonth.value = oldMonth;
      setTimeout(() => (isManualChange.value = false), 100); // Reset flag
    }

    // **Cegah perubahan bulan karena perubahan kelas**
    if (!isManualChange.value && oldKelas && newMonth === 'Februari') {
      console.warn(
        '⚠️ selectedMonth di-reset setelah kelas berubah, mengembalikan ke nilai sebelumnya.'
      );
      isManualChange.value = true;
      selectedMonth.value = oldMonth;
      setTimeout(() => (isManualChange.value = false), 100);
    }
  }
);
</script>

<style scoped>
.bg-dark {
  background-color: #343a40;
}

.text-white {
  color: #fff;
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
              aria-hidden="true"
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
              aria-hidden="true"
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
              class="self-center text-2xl font-semibold whitespace-nowrap dark:text-white"
              >SMA BARUNAWATI SURABAYA</span
            >
          </a>
        </div>
        <div class="flex items-center lg:order-2">
          <button
            type="button"
            data-drawer-toggle="drawer-navigation"
            aria-controls="drawer-navigation"
            class="p-2 mr-1 text-gray-500 rounded-lg md:hidden hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
          >
            <span class="sr-only">Toggle search</span>
          </button>
          <!-- Apps -->
          <button
            type="button"
            class="p-2 text-gray-500 rounded-lg hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
          >
            <span class="sr-only">View notifications</span>
          </button>

          <button
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
            class="hidden z-50 my-4 w-56 text-base list-none bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 rounded-xl"
            id="dropdown"
          >
            <div class="py-3 px-4">
              <span
                class="block text-sm font-semibold text-gray-900 dark:text-white"
                >{{ $page.props.auth.user.email }}</span
              >
              <span
                class="block text-sm text-gray-900 truncate dark:text-white"
                >{{ $page.props.auth.user.name }}</span
              >
              <span
                class="block text-sm text-gray-900 truncate dark:text-white"
                >{{ $page.props.auth.user.role_type }}</span
              >
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

    <main class="p-4 sm:p-6 lg:p-8 md:ml-64 h-screen">
      <Head title="Absensi Siswa" />
      <!-- section -->
      <h2
        class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 mt-20 mb-6 text-center"
      >
        Absensi Siswa
      </h2>

      <div v-if="errorMessage" class="alert alert-danger text-red-600 p-4 mb-4">
        <strong>Error: </strong>{{ errorMessage }}
      </div>

      <!-- Card Header -->
      <div class="content bg-yellow-100 p-4 py-6 mb-10">
        <div class="grid grid-cols-1 gap-2">
          <div>
            <table class="table-auto w-full">
              <tbody>
                <tr>
                  <td class="font-bold">Kelas</td>
                  <td class="px-2">:</td>
                  <td class="font-semibold">{{ teacherClass.name }}</td>
                </tr>
                <tr>
                  <td class="font-bold">Wali Kelas</td>
                  <td class="px-2">:</td>
                  <td class="font-semibold">
                    {{ $page.props.auth.user.name }}
                  </td>
                </tr>
                <tr>
                  <td class="font-bold">Tahun Pelajaran</td>
                  <td class="px-2">:</td>
                  <td class="font-semibold">{{ selectedYear }}</td>
                  <!-- Menampilkan selectedYear di sini -->
                </tr>
                <tr>
                  <td class="font-bold">Mata Pelajaran</td>
                  <td class="px-2">:</td>
                  <td class="font-semibold">
                    {{
                      selectedMapel.length === 0
                        ? '—'
                        : selectedMapel.length === 1
                        ? selectedMapel[0].mapel
                        : selectedMapel.length === 2
                        ? `${selectedMapel[0].mapel} dan ${selectedMapel[1].mapel}`
                        : selectedMapel
                            .slice(0, -1)
                            .map((m) => m.mapel)
                            .join(', ') +
                          ', dan ' +
                          selectedMapel[selectedMapel.length - 1].mapel
                    }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <!-- Dropdown Filter untuk Tahun dan Bulan -->
      <div
        class="content bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-lg shadow-lg mb-6"
      >
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Dropdown Tahun -->
          <div class="flex flex-col space-y-2">
            <label for="year" class="font-semibold text-lg text-gray-700">
              Pilih Tahun Ajaran:
            </label>
            <select
              id="year"
              v-model="selectedYear"
              class="p-3 border rounded-lg shadow-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-gray-50 transition-colors duration-200"
            >
              <option v-for="year in years" :key="year" :value="year">
                {{ year }}
              </option>
            </select>
          </div>
          <!-- Dropdown Bulan -->
          <div class="flex flex-col space-y-2">
            <label for="month" class="font-semibold text-lg text-gray-700">
              Pilih Bulan Ajaran:
            </label>
            <select
              id="month"
              v-model="selectedMonth"
              class="p-3 border rounded-lg shadow-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-gray-50 transition-colors duration-200"
            >
              <option
                v-for="(month, index) in currentMonth"
                :key="index"
                :value="month"
              >
                {{ month }}
              </option>
            </select>
          </div>
        </div>
      </div>
      <div :selectedMapel="selectedMapel"></div>

      <!-- Kartu Bulan -->
      <div class="flex justify-center mt-15">
        <div
          v-if="selectedYear && selectedMapel && teacherClass && selectedMonth"
        >
          <a
            :href="
              generateUrl(
                selectedYear,
                selectedMonth,
                selectedMapel,
                toRaw(teacherClass)
              )
            "
            class="flex items-center bg-blue-500 text-white p-4 rounded-md shadow-md hover:bg-blue-600 transition"
          >
            <span class="text-2xl mr-4">
              <i class="fas fa-calendar"></i>
            </span>
            <div>
              <span
                v-if="teacherClass && teacherClass.id"
                class="block text-lg font-bold"
              >
                Absensi Bulan {{ selectedMonth }} {{ selectedYear }}
              </span>
            </div>
          </a>
        </div>
        <div v-else class="text-gray-500">
          <span class="flex justify-center text-lg font-bold mt-20">
            <img
              src="/images/attendance.png"
              class="mr-3 h-40 max-h-full w-auto"
              alt=""
          /></span>
          <span class="block text-lg font-bold text-center">
            Silakan pilih tahun ajaran, bulan ajaran, mata pelajaran, dan kelas
            untuk melihat absensi.
          </span>
        </div>
      </div>
    </main>

    <!-- Sidebar -->
    <SidebarTeacher />
  </div>
</template>
