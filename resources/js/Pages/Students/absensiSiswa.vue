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
} from 'vue';
import axios from 'axios';
import { initFlowbite } from 'flowbite';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { useForm, usePage, Head } from '@inertiajs/vue3';
import { Inertia } from '@inertiajs/inertia';
import '@assets/plugins/simple-calendar/jquery.simple-calendar.js';
import '@assets/plugins/simple-calendar/simple-calendar.css';
import { createRouter, createWebHistory, useRoute } from 'vue-router';
import AbsensiSiswa from './absensiSiswa.vue';
import AbsensiSiswaSatu from './absensiSiswaSatu.vue';

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

const teacherClass = ref(props.teacherClass || 'Tidak ada kelas terkait');
const mapelList = ref([]);

console.log('Props received:', JSON.stringify(props, null, 2));
const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

const taskForm = ref({
  mapel_id: '',
});

const selectedKelasComputed = computed(() => selectedKelas.value);

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
  // Cek apakah nama guru tersedia
  const teacherName = props.auth.user?.name;
  if (!teacherName) {
    console.warn('⚠️ Nama guru tidak tersedia, permintaan dibatalkan.');
    teacherClass.value = 'Tidak ada data guru';
    return;
  }

  try {
    console.log('📤 Mengambil data kelas untuk guru:', teacherName);

    const response = await axios.get('/api/class-by-teacher', {
      params: { name: teacherName },
    });

    // Validasi response dari API
    if (response.data && response.data.class) {
      teacherClass.value = response.data.class;
    } else {
      console.warn('⚠️ Data kelas tidak ditemukan dalam response.');
      teacherClass.value = 'Tidak ada kelas terkait';
    }
  } catch (error) {
    console.error(
      '❌ Terjadi kesalahan saat mengambil kelas berdasarkan guru:',
      error
    );

    // Cek apakah ada response dari server
    if (error.response) {
      console.error('📥 Response error:', error.response.data);
      teacherClass.value = `Error: ${error.response.status} - ${
        error.response.data.message || 'Terjadi kesalahan'
      }`;
    } else {
      teacherClass.value = 'Error: Tidak dapat terhubung ke server';
    }
  }
};

const fetchData = async () => {
  try {
    const response = await axios.get('/api/absensi-siswa');
    console.log('Full Response:', response); // Log the entire response object
    console.log('API Response fetchData:', response.data); // Log the data part of the response

    // Memastikan bahwa response.data.data ada dan berisi data mata pelajaran
    if (response.data.data && Array.isArray(response.data.data)) {
      mapelList.value = response.data.data; // Menyimpan data mata pelajaran ke dalam mapelList
      console.log('Mapel List:', mapelList.value); // Log untuk memeriksa data mata pelajaran
    } else {
      console.error(
        'Response does not contain valid mapel data:',
        response.data
      );
      mapelList.value = []; // Kosongkan mapelList jika data tidak valid
    }

    if (response.data) {
      console.log(
        'Struktur JSON fetchData:',
        JSON.stringify(response.data, null, 2)
      ); // Log struktur JSON dengan format yang lebih mudah dibaca
    }

    // Mengambil data kelas dari response
    if (response.data.classes && Array.isArray(response.data.classes.data)) {
      // Misalnya, kita ambil kelas pertama sebagai kelas yang dipilih
      selectedKelas.value = response.data.classes[0] || null; // Atur kelas yang dipilih
      console.log('Selected Kelas:', selectedKelas.value); // Log untuk memeriksa kelas yang dipilih
    } else {
      console.error(
        'Response does not contain valid classes data:',
        response.data
      );
      //selectedKelas.value = null; // Kosongkan selectedKelas jika data tidak valid
    }
    console.log('After setting, Selected Kelas:', selectedKelas.value);
  } catch (error) {
    console.error('Error fetching mata pelajaran:', error);
    mapelList.value = []; // Kosongkan mapelList jika terjadi error
    //selectedKelas.value = null; // Kosongkan selectedKelas jika terjadi error
  }
};

const fetchKelas = async () => {
  try {
    // Ambil data kelas dari API
    const response = await axios.get('/api/absensi-siswa');
    console.log('📥 API Response kelas:', response.data);

    // Pastikan data kelas valid
    if (response.data.classes?.data?.length > 0) {
      kelasList.value = response.data.classes.data;
      console.log('✅ Kelas List diperbarui:', kelasList.value);

      // Ambil kelas yang disimpan di localStorage jika ada
      const savedKelas = localStorage.getItem('selectedKelas');
      if (savedKelas) {
        const parsedKelas = JSON.parse(savedKelas);
        const foundKelas = kelasList.value.find((k) => k.id === parsedKelas.id);

        if (foundKelas) {
          selectedKelas.value = { ...foundKelas }; // Ambil kelas yang cocok dari API
          console.log(
            '📌 selectedKelas diambil dari localStorage:',
            selectedKelas.value
          );
        } else {
          console.warn(
            '⚠️ Kelas dari localStorage tidak ditemukan di API, set default kelas.'
          );
          selectedKelas.value = { ...kelasList.value[0] }; // Set kelas pertama sebagai default
        }
      } else {
        // Jika tidak ada kelas yang disimpan di localStorage, pilih kelas pertama
        selectedKelas.value = { ...kelasList.value[0] };
        console.log(
          '✅ selectedKelas diperbarui (kelas pertama):',
          selectedKelas.value
        );
      }

      // Simpan selectedKelas ke localStorage hanya jika ada perubahan
      const savedSelectedKelas = localStorage.getItem('selectedKelas');
      if (
        !savedSelectedKelas ||
        JSON.stringify(selectedKelas.value) !== savedSelectedKelas
      ) {
        localStorage.setItem(
          'selectedKelas',
          JSON.stringify(selectedKelas.value)
        );
      }
    } else {
      console.error('❌ Data kelas tidak valid:', response.data);
      kelasList.value = [];
      selectedKelas.value = null; // Set default null jika tidak ada kelas
    }
  } catch (error) {
    console.error('❌ Error fetching kelas:', error);
    kelasList.value = [];
    selectedKelas.value = null; // Set default null jika terjadi error
  }
};

const fetchMapel = async () => {
  try {
    const response = await axios.get('/api/absensi-siswa'); // Ganti dengan rute yang sesuai
    if (response.data.data && Array.isArray(response.data.data)) {
      // Pastikan setiap item dalam array memiliki properti 'mapel' yang valid
      const validMapelList = response.data.data.filter((mapel) => {
        return (
          mapel &&
          mapel.mapel &&
          typeof mapel.mapel === 'string' &&
          mapel.mapel.trim() !== ''
        );
      });

      if (validMapelList.length > 0) {
        mapelList.value = validMapelList; // Menyimpan data mata pelajaran yang valid
        // Misalnya, kita set selectedMapel ke mata pelajaran pertama jika ada
        selectedMapel.value = validMapelList[0].mapel;
      } else {
        mapelList.value = []; // Kosongkan jika data tidak valid
      }
    } else {
      mapelList.value = []; // Kosongkan jika data tidak valid
    }
  } catch (error) {
    console.error('Error fetching mapel:', error);
    mapelList.value = []; // Kosongkan jika terjadi error
  }
};

const saveSelectedMapel = async (mapel) => {
  try {
    console.log('Selected Kelas sebelum penyimpanan:', selectedKelas.value);
    console.log('Selected Student ID:', selectedStudentId.value);

    if (!selectedKelas.value || !selectedKelas.value.id) {
      console.warn(
        'Kelas yang dipilih tidak valid. Silakan pilih kelas terlebih dahulu.'
      );
      return;
    }

    if (!mapel || !mapel.mapel) {
      console.error(
        'Mapel yang dipilih tidak valid. Silakan pilih mapel terlebih dahulu.'
      );
      return;
    }

    const studentId = selectedStudentId.value;
    if (!studentId) {
      //console.error(
      //'ID siswa tidak valid. Silakan pilih siswa terlebih dahulu.'
      //);
      return;
    }

    const response = await axios.post('/api/save-selected-mapel', {
      mapel: mapel.mapel,
      kelas: selectedKelas.value.id,
      student_id: studentId,
    });

    if (response.status === 200) {
      console.log('Pilihan mapel berhasil disimpan:', response.data);
    } else {
      console.error('Gagal menyimpan pilihan mapel:', response.data);
    }
  } catch (error) {
    console.error('Error saving selected mapel:', error);
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

  Inertia.visit(route('absensiSiswaSatu'), {
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
  console.log('Selected Mapel before fetching:', props.selectedMapel);

  // Ambil selectedKelas dari localStorage sebelum fetching data
  const savedKelas = localStorage.getItem('selectedKelas');
  if (savedKelas) {
    selectedKelas.value = JSON.parse(savedKelas);
    console.log(
      '✅ selectedKelas loaded from localStorage:',
      selectedKelas.value
    );
  }

  // Panggil fungsi fetching data secara berurutan
  await fetchClassByTeacher();
  await fetchAttendanceData();
  initFlowbite();
  await fetchSessionData();
  await fetchData();
  await fetchKelas(); // Pastikan fetchKelas tidak menimpa selectedKelas dari localStorage
  await nextTick();
  await fetchMapel();

  // Pastikan selectedKelas tidak diganti jika sudah ada dari localStorage
  if (
    (!selectedKelas.value || Object.keys(selectedKelas.value).length === 0) &&
    kelasList.value.length > 0
  ) {
    selectedKelas.value = kelasList.value[0]; // Ambil kelas pertama hanya jika kosong
    console.log(
      '✅ selectedKelas diperbarui dari fetchKelas:',
      selectedKelas.value
    );
  }
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

//const selectedMapel = ref({ mapel: '' });
//const selectedMapel = ref({});
const selectedMapel = ref({ mapel: '' });
const student = ref({});
const kelasList = ref([]);
console.log('Sebelum mengirim props ke anak:', {
  selectedMapel: selectedMapel.value,
  student: student.value,
});

//const selectedKelas = ref({ id: 1, name: 'X-1' });
//const selectedKelas = ref('');
const selectedKelas = ref({});
const selectedStudentId = ref(null);
const selectedAttendanceStatus = ref(null);
console.log('Default Kelas:', props.defaultKelas);
console.log('🔍 selectedKelas setelah update:', selectedKelas.value);

const generateUrl = (year, month, mapel, kelas) => {
  console.log('🧐 Debug sebelum generate:', {
    year,
    month,
    mapel,
    kelas,
  });

  // Pastikan Ziggy tersedia
  if (typeof route !== 'function') {
    console.error('❌ Ziggy route helper is not available!');
    return '#';
  }

  // Pastikan kelasList memiliki default value
  if (!Array.isArray(kelasList.value) || kelasList.value.length === 0) {
    console.warn('⚠️ kelasList kosong! Menggunakan default kelas.');
    kelasList.value = [{ id: 1, name: 'Kelas Default' }];
  }

  // Jika kelas tidak diberikan, gunakan kelas pertama dari kelasList
  if (!kelas) {
    console.warn('⚠️ Kelas kosong! Menggunakan kelas pertama dari kelasList.');
    kelas =
      kelasList.value.length > 0
        ? kelasList.value[0]
        : { id: 1, name: 'Kelas Default' };
  }

  // Ambil ID kelas dan konversikan ke string
  const kelasId = String(kelas.id);

  // Bersihkan mapel agar aman digunakan di URL
  const cleanMapel = String(mapel || '')
    .trim()
    .replace(/\s+/g, '-') // Ganti spasi dengan "-"
    .replace(/[()]/g, '') // Hapus tanda kurung
    .toLowerCase();

  // Pastikan month dalam format yang benar:
  const cleanMonth = Array.isArray(month)
    ? month[0]
    : String(month || '')
        .trim()
        .toLowerCase();

  // Pastikan year adalah angka yang valid
  if (!year || isNaN(year)) {
    console.error('❌ Tahun tidak valid:', year);
    return '#';
  }

  // Gunakan Ziggy untuk membuat URL
  const url = route('absensiSiswaSatu', {
    kelas: kelasId,
    year,
    mapel: cleanMapel,
    month: cleanMonth,
  });

  console.log('✅ Generated URL (Ziggy):', url);
  return url;
};

const attendanceData = ref([]);
const errorMessage = ref('');

watch(selectedYear, (newValue) => {
  console.log('Selected year changed:', newValue);
});

watch(
  () => props.selectedMapel,
  (newVal) => {
    selectedMapel.value = newVal; // ⚠️ Bisa menimpa nilai sebelumnya
  }
);

watchEffect(() => {
  if (props.selectedMapel && props.selectedMapel !== '') {
    console.log('✅ Props valid, gunakan:', props.selectedMapel);
    selectedMapel.value = props.selectedMapel;
  } else {
    console.log('⚠️ Props kosong, tidak diubah.');
  }
});

watch(selectedKelas, (newValue) => {
  console.log('Selected Kelas changed:', newValue);
});

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

watch(selectedKelas, (newKelas, oldKelas) => {
  console.log(
    'Kelas yang dipilih berubah dari:',
    oldKelas,
    'menjadi:',
    newKelas
  );

  // Logika tambahan: Misalnya, jika kelas yang dipilih berubah, kita bisa mengupdate mapel yang tersedia
  if (newKelas) {
    // Misalnya, kita bisa memanggil fungsi untuk mengambil mapel berdasarkan kelas yang dipilih
    fetchMapel(newKelas.id); // Asumsikan newKelas memiliki id
  } else {
    // Jika tidak ada kelas yang dipilih, kita bisa mengosongkan mapel yang dipilih
    selectedMapel.value = null;
  }
});

// Watch untuk memantau perubahan pada selectedMapel

watch(selectedMapel, (newMapel, oldMapel) => {
  // Hanya trigger jika mapel baru tidak kosong dan berbeda dari mapel lama
  if (newMapel && newMapel.mapel && newMapel.mapel !== oldMapel.mapel) {
    console.log(
      'Mapel yang dipilih berubah dari:',
      oldMapel,
      'menjadi:',
      newMapel
    );
    saveSelectedMapel(newMapel); // Fungsi untuk menyimpan pilihan mapel
  }
});

watch(
  selectedKelas,
  (newVal) => {
    console.log('🔄 selectedKelas di Parent berubah:', newVal);
  },
  { deep: true }
);

watch(
  selectedKelas,
  (newValue) => {
    if (newValue && newValue.id) {
      localStorage.setItem('selectedKelas', JSON.stringify(newValue));
    }
  },
  { deep: true }
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

watch(selectedKelas, (newValue) => {
  console.log('Selected Kelas:', newValue);
});

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

const isManualChange = ref(false); // Jadikan reactive agar perubahan terdeteksi

watch(
  [selectedMonth, selectedKelas],
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

watch(selectedKelas, (newVal, oldVal) => {
  console.log('🔄 selectedKelas berubah:', oldVal, '➡️', newVal);
});
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
            <svg
              aria-hidden="true"
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
                  <td class="font-semibold">{{ teacherClass }}</td>
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

          <!-- Dropdown Mata Pelajaran -->
          <div class="flex flex-col space-y-2">
            <label for="mapel" class="font-semibold text-lg text-gray-700">
              Pilih Mata Pelajaran:
            </label>
            <select
              id="mapel"
              :value="selectedMapel"
              @change="onMapelChange"
              class="p-3 border rounded-lg shadow-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-gray-50 transition-colors duration-200"
            >
              <option
                v-for="mapel in mapelList"
                :key="mapel.id"
                :value="mapel.mapel"
              >
                {{ mapel.mapel }}
              </option>
            </select>
          </div>

          <!-- Dropdown Kelas -->
          <div class="flex flex-col space-y-2">
            <label for="kelas" class="font-semibold text-lg text-gray-700">
              Pilih Kelas:
            </label>
            <select
              id="kelas"
              v-model="selectedKelas"
              class="p-3 border rounded-lg shadow-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-gray-50 transition-colors duration-200"
            >
              <option v-for="kelas in kelasList" :key="kelas.id" :value="kelas">
                {{ kelas.name }}
              </option>
            </select>
          </div>
        </div>
      </div>
      <div :selectedMapel="selectedMapel"></div>
    
      <!-- Kartu Bulan -->
      <div class="flex justify-center mt-15">
        <div
          v-if="selectedYear && selectedMapel && selectedKelas && selectedMonth"
        >
          <a
            :href="
              generateUrl(
                selectedYear,
                selectedMonth,
                selectedMapel,
                toRaw(selectedKelas)
              )
            "
            class="flex items-center bg-blue-500 text-white p-4 rounded-md shadow-md hover:bg-blue-600 transition"
          >
            <span class="text-2xl mr-4">
              <i class="fas fa-calendar"></i>
            </span>
            <div>
              <span class="block text-lg font-bold">
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
    :selectedKelas="selectedKelas"

    <!--
        <AbsensiSiswaSatu
      :selectedKelas="selectedKelas"
      :selectedMapel="selectedMapel"
      :student="student"
      :kelasList="kelasList"
      v-if="selectedKelas && selectedKelas.id"
    />
    -->

    <!-- Sidebar -->
    <aside
      class="fixed top-0 left-0 z-40 w-60 h-screen pt-14 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-900"
      aria-label="Sidenav"
      id="drawer-navigation"
      style=""
    >
      <div class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800">
        <ul class="space-y-2">
          <li>
            <a
              href="dashboard"
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
            <a
              href="absensiSiswa"
              class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
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
              <span class="ml-3">Absensi Siswa</span>
            </a>
          </li>
          <li>
            <a
              href="membuat-enrollment"
              class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
            >
              <svg
                viewBox="0 0 512 512"
                width="24"
                height="24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <g id="E-learning_notification">
                  <path
                    d="M243.0771,299.7515V251.3271a12.5756,12.5756,0,0,0-12.5615-12.5615H212.44a5,5,0,0,0,0,10h18.0752a2.5646,2.5646,0,0,1,2.5615,2.5615v48.4244a2.5645,2.5645,0,0,1-2.5615,2.5615H102.127a2.5645,2.5645,0,0,1-2.5616-2.5615V251.3271a2.5646,2.5646,0,0,1,2.5616-2.5615h83.8183a5,5,0,1,0,0-10H102.127a12.5757,12.5757,0,0,0-12.5616,12.5615v48.4244A12.5757,12.5757,0,0,0,102.127,312.313H230.5156A12.5756,12.5756,0,0,0,243.0771,299.7515Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M305.1309,238.7656H270.8574a10.4457,10.4457,0,0,0-10.4336,10.4336v52.68a10.4458,10.4458,0,0,0,10.4336,10.4341h34.2735a10.4458,10.4458,0,0,0,10.4336-10.4341v-52.68A10.4457,10.4457,0,0,0,305.1309,238.7656Zm.4336,63.1133a.4343.4343,0,0,1-.4336.4341H270.8574a.4343.4343,0,0,1-.4336-.4341v-52.68a.4339.4339,0,0,1,.4336-.4336h34.2735a.4339.4339,0,0,1,.4336.4336Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M309.1992,360.7461H215.2568a5,5,0,1,0,0,10h93.9424a5,5,0,0,0,0-10Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M309.1992,335.2017H215.2568a5,5,0,1,0,0,10h93.9424a5,5,0,0,0,0-10Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M467.8184,122.0205a109.7113,109.7113,0,0,0-219.3941-2.4991H145.1484V119.15a12.48,12.48,0,0,0-12.4658-12.4658H102.0312A12.48,12.48,0,0,0,89.5654,119.15v1.07a54.0392,54.0392,0,0,0-45.3833,53.2611V459.8264l0,.0193a39.83,39.83,0,0,0,39.8467,39.8467H355.6045a5.0406,5.0406,0,0,0,5-5.0474V459.8457a5,5,0,0,0-10,0v29.0819a29.8445,29.8445,0,0,1,5.24-58.8871,5.01,5.01,0,0,0,4.3484-3.0642c.0051-.0115.0122-.0215.0171-.0329a5.0159,5.0159,0,0,0,.2688-.8653c.0059-.0254.0168-.0483.022-.0738A5.0241,5.0241,0,0,0,360.6045,425l-.0022-.0217V231.7017A109.84,109.84,0,0,0,467.8184,122.0205ZM358.1055,22.3076a99.7129,99.7129,0,1,1-99.7129,99.7129A99.8261,99.8261,0,0,1,358.1055,22.3076ZM99.5654,119.15a2.4687,2.4687,0,0,1,2.4658-2.4658h30.6514a2.4686,2.4686,0,0,1,2.4658,2.4658l-.0019,61.2065-6.9883-9.3046a12.7468,12.7468,0,0,0-10.0557-5.1226c-.0693-.0015-.1386-.002-.2089-.002a12.7429,12.7429,0,0,0-10.003,4.8038L99.5654,181.11Zm217.91,340.6958a39.6352,39.6352,0,0,0,11.6631,28.1777l.0039.0035q.8613.8621,1.7695,1.6655H84.0283A29.8521,29.8521,0,0,1,55.084,467.1733H238.0771a5,5,0,1,0,0-10H54.3074A29.8832,29.8832,0,0,1,84.0283,430H330.8867A39.77,39.77,0,0,0,317.4756,459.8457ZM84.0283,420a39.7524,39.7524,0,0,0-29.8476,13.4894l.0014-.0276v-259.98A44.0226,44.0226,0,0,1,89.5654,130.37v51.1742a9.7374,9.7374,0,0,0,6.583,9.2915,10.007,10.007,0,0,0,3.3233.5733,9.7314,9.7314,0,0,0,7.624-3.7036l8.5957-10.7188a2.827,2.827,0,0,1,2.2529-1.0591,2.7824,2.7824,0,0,1,2.2178,1.13l7.2647,9.6709a9.8479,9.8479,0,0,0,17.7216-5.915V129.5214H248.6541a109.8717,109.8717,0,0,0,101.9482,101.95V420Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M309.2578,171.2344h21.2686a25.572,25.572,0,0,0,50.65-5,5,5,0,0,0-5-5h-66.919a4.84,4.84,0,0,1-4.8349-4.835V145.6577a23.5977,23.5977,0,0,0,18.5556-21.7641c1.1221-19.8521,8.1309-43.5943,35.127-44.0254,26.9961.4311,34.0049,24.1733,35.1269,44.0254a23.6,23.6,0,0,0,18.5557,21.7641v10.7417a4.84,4.84,0,0,1-4.834,4.835h-9.5557a5,5,0,0,0,0,10h9.5557a14.8511,14.8511,0,0,0,14.834-14.835V141.1709a5,5,0,0,0-5-5h-.1192a13.5457,13.5457,0,0,1-13.4521-12.8418c-1.4985-26.5106-11.5112-43.8823-28.6445-50.4795V65.6013a16.4352,16.4352,0,0,0-16.41-16.42h-1.04a16.4439,16.4439,0,0,0-16.42,16.42v7.6323c-16.5585,6.8655-26.237,24.0745-27.708,50.096a13.5441,13.5441,0,0,1-13.4511,12.8413h-.12a5,5,0,0,0-5,5v15.2285A14.8518,14.8518,0,0,0,309.2578,171.2344Zm46.3467,10.5718a15.5882,15.5882,0,0,1-14.7337-10.5718h29.4673A15.588,15.588,0,0,1,355.6045,181.8062ZM350.7021,65.6013a6.4274,6.4274,0,0,1,6.42-6.42h1.04a6.4188,6.4188,0,0,1,6.41,6.42v4.7431a53.7425,53.7425,0,0,0-6.3946-.4762c-.0488-.001-.0957-.001-.1445,0a53.2611,53.2611,0,0,0-7.3311.597Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M300.7432,101.8721c.122.0088.2431.0127.3632.0127a5.0007,5.0007,0,0,0,4.9825-4.6416c1.1465-15.9453,11.3965-21.0577,11.9209-21.3086A5,5,0,0,0,313.874,66.83c-.6592.2959-16.164,7.5039-17.76,29.6963A5,5,0,0,0,300.7432,101.8721Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M397.9336,75.9316c.4394.2085,10.7773,5.295,11.9277,21.3116a5.0007,5.0007,0,0,0,4.9825,4.6416c.12,0,.2412-.0039.3632-.0127a5.0012,5.0012,0,0,0,4.6289-5.3457c-1.5947-22.1924-17.1-29.4-17.76-29.6963a5,5,0,1,0-4.1426,9.1015Z"
                    stroke="black"
                    stroke-width="4"
                  />
                </g>
              </svg>
              <span class="ml-3">Enrollment Tugas</span>
            </a>
          </li>
          <li>
            <a
              href="membuatTugasSiswa"
              class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
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
              <span class="ml-3">Tugas Siswa</span>
            </a>
          </li>

          <li>
            <a
              href="bukuPenghubung"
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
              <span class="ml-3">Identitas Siswa</span>
            </a>
          </li>

          <li>
            <a
              href="bukuPenghubung1"
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
        </ul>
      </div>
    </aside>
  </div>
</template>
