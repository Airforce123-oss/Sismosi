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

const routes = [
  {
    path: '/absensi/:kelas/:year/:mapel/:month',
    name: 'absensi', // Harus sesuai dengan nama route di Laravel
    //component: AbsensiSiswa, // Komponen tampilan absensi
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
/*
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
 */

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

  Inertia.visit(route('studentsabsensiGuru'), {
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
  //await fetchClassByTeacher();
  await fetchAttendanceData();
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
  initFlowbite();
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

const generateUrl = (year, month, kelas) => {
  console.log('🧐 Debug sebelum generate:', {
    year,
    month,
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
  const url = route('studentsabsensiGuru', {
    kelas: kelasId,
    year,
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
        Absensi Guru
      </h2>

      <div v-if="errorMessage" class="alert alert-danger text-red-600 p-4 mb-4">
        <strong>Error: </strong>{{ errorMessage }}
      </div>
      <!-- Dropdown Filter untuk Tahun dan Bulan -->
      <div
        class="content bg-gradient-to-r from-blue-50 to-indigo-50 p-4 sm:p-6 rounded-lg shadow-lg mb-6"
      >
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Tahun Ajaran -->
          <div class="flex flex-col space-y-2">
            <label
              for="year"
              class="font-semibold text-base sm:text-lg text-gray-700"
            >
              Pilih Tahun Ajaran:
            </label>
            <select
              id="year"
              v-model="selectedYear"
              class="p-2 sm:p-3 border rounded-lg shadow-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-gray-50 transition-colors duration-200"
            >
              <option v-for="year in years" :key="year" :value="year">
                {{ year }}
              </option>
            </select>
          </div>

          <!-- Bulan Ajaran -->
          <div class="flex flex-col space-y-2">
            <label
              for="month"
              class="font-semibold text-base sm:text-lg text-gray-700"
            >
              Pilih Bulan Ajaran:
            </label>
            <select
              id="month"
              v-model="selectedMonth"
              class="p-2 sm:p-3 border rounded-lg shadow-sm bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 hover:bg-gray-50 transition-colors duration-200"
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
        <div v-if="selectedYear && selectedKelas && selectedMonth">
          <a
            :href="
              generateUrl(selectedYear, selectedMonth, toRaw(selectedKelas))
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
            />
          </span>
          <span class="block text-lg font-bold text-center">
            Silakan pilih tahun ajaran, bulan ajaran, dan kelas untuk melihat
            absensi.
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
            </ul>
          </li>
          <li>
            <a
              href="#"
              class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
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
              <span class="ml-3">Master Jabatan</span>
            </a>
          </li>
        </ul>
      </div>
    </aside>
  </div>
</template>
