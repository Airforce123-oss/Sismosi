<script setup>
import {
  onMounted,
  ref,
  watch,
  watchEffect,
  computed,
  reactive,
  toRaw,
  defineProps,
  isProxy,
} from 'vue';
import axios from 'axios';
import Swal from 'sweetalert2';
import SidebarTeacher from '@/Components/SidebarTeacher.vue';
import { Link, Head, useForm, usePage, router } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { initFlowbite } from 'flowbite';
import { useRoute } from 'vue-router';
// State management
const newAttendance = ref([]);
const loading = ref(false);
const studentId = ref([1]);
const students = ref([]);
//const props = defineProps(['student']);
const props = defineProps({
  month: {
    type: [String, Number],
    required: true,
  },
  year: {
    type: [String, Number],
    required: true,
  },
  selectedMapel: {
    type: Array,
    default: () => [],
  },
  students: {
    type: Array,
    required: true,
  },
  kelasList: {
    type: Array,
    default: () => [],
  },
  selectedKelas: {
    type: Object,
    default: () => ({}),
  },
  semester: {
    type: String,
    default: '',
  },
});

console.log('✅ Props:', props);
console.log('📅 Bulan:', props.month);
console.log('📅 Tahun:', props.year);

const studentsPerPage = 5; // atau bisa dari pagination.per_page

const paginatedStudentsFromProps = computed(() => {
  if (!Array.isArray(props.students)) {
    console.warn('props.students bukan array:', props.students);
    return [];
  }

  // Pastikan pagination valid, fallback ke halaman 1 kalau tidak valid
  const currentPage = pagination.value?.current_page ?? 1;
  const perPage = pagination.value?.per_page ?? studentsPerPage;

  const start = (currentPage - 1) * perPage;
  let end = start + perPage;

  if (end > props.students.length) end = props.students.length;

  return props.students.slice(start, end);
});

const semester = props.semester;
console.log('📘 Semester:', semester);

const studentsList = ref(props.students || []);
//console.log('currentPage:', currentPage.value);
//console.log('🧑‍🎓 Students yang diterima:', props.students);
//console.log('isi props: ', props);

const kelasList = ref([]);

const emit = defineEmits();
emit('debug', props.selectedKelas);

//console.log('📥 selectedKelas di Child (props):', props.selectedKelas);

// Fungsi untuk mengambil data mata pelajaran
const fetchMapelByMonth = async () => {
  console.log('Received selectedMapel before validation:', props.selectedMapel);
  try {
    // Memeriksa validitas selectedMapel
    if (!isValidSelectedMapel(props.selectedMapel)) {
      return; // Jika tidak valid, keluar dari fungsi
    }

    console.log('Selected Mapel:', props.selectedMapel); // Log untuk memeriksa nilai selectedMapel

    const response = await axios.get(
      `/api/absensi-siswa?month=${currentMonth + 1}&year=${currentYear}`
    );
    console.log('API Response fetchMapelByMonth:', response.data.data); // Log respons API

    // Memastikan bahwa response.data.data ada dan berisi data mata pelajaran
    if (response.data.data && Array.isArray(response.data.data)) {
      // Filter data berdasarkan selectedMapel
      attendanceData.value = response.data.data.filter((mapel) => {
        return mapel.mapel === props.selectedMapel.mapel; // Hanya ambil mapel yang dipilih
      });
      //console.log('Filtered Mapel List:', attendanceData.value); // Log untuk memeriksa data yang difilter
    } else {
      console.error(
        'Response does not contain valid mapel data:',
        response.data
      );
      attendanceData.value = []; // Kosongkan jika data tidak valid
    }
  } catch (error) {
    console.error('Error fetching mapel:', error);
    attendanceData.value = []; // Kosongkan jika terjadi error
  }
};

const isValidSelectedMapel = (selectedMapel) => {
  console.log(
    '🔍 Before validation:',
    selectedMapel,
    `(${typeof selectedMapel})`
  );

  if (!selectedMapel) {
    console.warn('⚠️ selectedMapel is null, undefined, atau tidak valid');
    return false;
  }

  // Jika selectedMapel adalah object, ambil nama mapel
  if (typeof selectedMapel === 'object' && selectedMapel !== null) {
    selectedMapel = selectedMapel.mapel ?? ''; // Pastikan mengambil key yang benar
  }

  // Validasi harus string
  if (typeof selectedMapel !== 'string') {
    console.error('❌ Error: selectedMapel is not a string');
    return false;
  }

  // Cek apakah selectedMapel termasuk dalam daftar yang valid
  return true;
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
//const selectedKelas = ref(null);
//const selectedKelas = ref({});
const selectedKelas = ref(null);

const fetchKelasMapel = async () => {
  try {
    const response = await axios.get(
      `/api/absensi-siswa?month=${currentMonth + 1}&year=${currentYear}`
    );
    //console.log('API Response fetchKelasMapel:', response.data.data); // Log respons API

    // Memastikan bahwa response.data.data ada dan berisi data mata pelajaran
    if (response.data.data && Array.isArray(response.data.data)) {
      // Filter data berdasarkan selectedMapel
      attendanceData.value = response.data.data.filter((mapel) => {
        return mapel.mapel === props.selectedMapel.mapel; // Hanya ambil mapel yang dipilih
      });
      console.log('Filtered Mapel List:', attendanceData.value); // Log untuk memeriksa data yang difilter
    } else {
      console.error(
        'Response does not contain valid mapel data:',
        response.data
      );
      attendanceData.value = []; // Kosongkan jika data tidak valid
    }
  } catch (error) {
    console.error('Error fetching mapel:', error);
    attendanceData.value = []; // Kosongkan jika terjadi error
  }
};

const selectedStudentStatuses = ref({});
//const currentYear = new Date().getFullYear();
//const currentMonth = new Date().getMonth();

// Mendapatkan tanggal pertama bulan ini
const defaultDay = computed(() => {
  const day = new Date(currentYear.value, currentMonth.value, 1).getDate();
  if (day === 0) {
    console.error('Default Day tidak valid:', day);
  }
  return day;
});

const monthMap = {
  januari: 0,
  februari: 1,
  maret: 2,
  april: 3,
  mei: 4,
  juni: 5,
  juli: 6,
  agustus: 7,
  september: 8,
  oktober: 9,
  november: 10,
  desember: 11,
};

const vueRoute = useRoute();

const year = parseInt(vueRoute.params.year);
const monthName = vueRoute.params.month?.toLowerCase();
const month = monthMap[monthName];

// Mendapatkan tanggal minimum dan maksimum dalam bulan ini
const paddedMonth = String(month + 1).padStart(2, '0');
const minDate = `${year}-${paddedMonth}-01`;
const maxDate = `${year}-${paddedMonth}-${new Date(
  year,
  month + 1,
  0
).getDate()}`;

// Format tanggal dengan dua digit untuk hari dan bulan

const getDayName = (date) => {
  if (isNaN(new Date(date))) {
    console.log('Tanggal tidak valid:', date);
    return;
  }
  const dateObj = new Date(date);
  //console.log("Date Object:", dateObj);
  const daysOfWeek = [
    'Minggu',
    'Senin',
    'Selasa',
    'Rabu',
    'Kamis',
    'Jumat',
    'Sabtu',
  ];
  const dayName = daysOfWeek[dateObj.getDay()];
  return dayName;
};

const monthIndex = computed(() => {
  if (typeof props.month === 'string') {
    return monthMap[props.month.toLowerCase()];
  } else {
    return Number(props.month) - 1; // misal props.month = 3 (Maret)
  }
});

const totalDaysInMonth = computed(() => {
  const year = Number(props.year);
  const month = monthIndex.value;

  if (isNaN(year) || isNaN(month)) {
    console.warn('❌ Tahun atau bulan tidak valid:', { year, month });
    return []; // Harus array, bukan undefined/null
  }

  const days = new Date(year, month + 1, 0).getDate();

  return Array.from({ length: days }, (_, i) => {
    return new Date(year, month, i + 1); // hasil: array of Date
  });
});

const isSunday = (dateObj) => {
  if (!(dateObj instanceof Date) || isNaN(dateObj.getTime())) {
    console.warn('❌ Bukan objek Date yang valid:', dateObj);
    return false;
  }
  return dateObj.getDay() === 0; // 0 = Minggu
};

const tanggal_kehadiran = ref('');
const isNavigating = ref(false);
const isAddModalVisible = ref(false);

// Logs for debugging
//console.log(studentId.value);
console.log('Data siswa:', toRaw(studentId.value));

watch(
  () => props,
  (newProps) => {
    console.log('Props telah diperbarui:', newProps);
  }
);

const data = {
  tanggal_kehadiran: tanggal_kehadiran.value,
  attendances: Object.values(selectedStudentStatuses.value), // Contoh pengiriman status absensi
};

console.log('Data yang dikirim ke API ||const data: ', data);

// Data form yang akan dikirim ke backend
const form = ref({
  tanggal_kehadiran: '',
  attendances: [], // Array untuk menyimpan status kehadiran tiap siswa
});

const attendances = ref([]);
const isFetchingData = ref(false);

const tahun = parseInt(props.tahun); // misal: 2025

// 🔁 Fungsi pemformat tanggal menjadi YYYY-MM-DD
const formattedDate = (date) => {
  const obj = new Date(date);

  if (isNaN(obj.getTime())) {
    console.warn('❌ Tanggal tidak valid:', date);
    return '';
  }

  const day = obj.getDate().toString().padStart(2, '0');
  const month = (obj.getMonth() + 1).toString().padStart(2, '0');
  const year = obj.getFullYear();

  const result = `${year}-${month}-${day}`;
  return result;
};

const paginatedStudents = computed(() => {
  // Pastikan studentsList.value adalah array yang valid
  if (!Array.isArray(studentsList.value)) {
    console.error('studentsList.value bukan array:', studentsList.value);
    return [];
  }

  // Hitung start dan end berdasarkan pagination
  const start = (pagination.value.current_page - 1) * pagination.value.per_page;
  let end = start + pagination.value.per_page;

  // Menyesuaikan nilai end jika lebih besar dari jumlah total data
  if (end > studentsList.value.length) {
    end = studentsList.value.length;
  }

  // Menampilkan informasi pagination
  console.log(
    'Start:',
    start,
    'End:',
    end,
    'Halaman:',
    pagination.value.current_page,
    'Per Halaman:',
    pagination.value.per_page
  );

  // Ambil data yang sesuai dengan pagination
  const paginatedData = studentsList.value.slice(start, end);

  // Menampilkan data setelah pagination
  //console.log('Data yang ditampilkan pada halaman ini:', paginatedData);

  return paginatedData;
});

const fetchData = async () => {
  if (isFetchingData.value) return;

  isFetchingData.value = true;
  loading.value = true;

  try {
    //console.log('Student ID sebelum fetchData:', studentId.value);

    if (!Array.isArray(studentId.value) || studentId.value.length === 0) {
      console.warn('❌ Student ID tidak valid:', studentId.value);
      return;
    }

    const response = await axios.get('/api/attendances', {
      params: { student_ids: studentId.value },
    });

    const attendancesArray = response.data;

    if (!Array.isArray(attendancesArray) || attendancesArray.length === 0) {
      console.warn('⚠️ Data absensi kosong atau tidak ditemukan.');
      selectedStudentStatuses.value = {};
      return;
    }

    //console.log('📩 Data absensi dari API:', attendancesArray);

    // Ambil tahun dan bulan dari props
    const tahun = Number(props.tahun); // contoh: 2025
    const bulanMap = {
      januari: 0,
      februari: 1,
      maret: 2,
      april: 3,
      mei: 4,
      juni: 5,
      juli: 6,
      agustus: 7,
      september: 8,
      oktober: 9,
      november: 10,
      desember: 11,
    };
    const bulanString =
      typeof props.month === 'string' ? props.month.toLowerCase() : '';
    const monthIndex = bulanMap[bulanString];

    if (monthIndex === undefined) {
      console.warn('⚠️ Bulan dari props tidak dikenali:', props.month);
      return;
    }

    const bulan = (monthIndex + 1).toString().padStart(2, '0'); // contoh: '03'

    const updatedStatuses = {};

    attendancesArray.forEach((item) => {
      const id = item.siswa_id ?? item.student_id;

      if (id === null || id === undefined) {
        console.warn('⚠️ item absensi tanpa student_id/siswa_id:', item);
        return;
      }

      const studentIdNum = Number(id);
      if (isNaN(studentIdNum)) {
        console.warn('⚠️ student_id bukan angka valid:', id);
        return;
      }

      const tglObj = new Date(item.tanggal_kehadiran);
      const itemYear = tglObj.getFullYear();
      const itemMonth = (tglObj.getMonth() + 1).toString().padStart(2, '0');

      // ✅ Filter hanya untuk bulan dan tahun yang sedang dibuka
      if (itemYear.toString() === tahun.toString() && itemMonth === bulan) {
        const formatted = formattedDate(item.tanggal_kehadiran);

        if (!updatedStatuses[studentIdNum]) {
          updatedStatuses[studentIdNum] = {};
        }

        updatedStatuses[studentIdNum][formatted] =
          item.status_kehadiran?.trim() ||
          item.status?.trim() ||
          'Belum diabsen';
      }
    });

    selectedStudentStatuses.value = updatedStatuses;

    paginatedStudents.value = Object.entries(updatedStatuses)
      .map(([studentId, statuses]) => {
        const studentData = students.value.find(
          (s) => Number(s.id) === Number(studentId)
        );

        if (!studentData) return null;

        return {
          id: studentData.id,
          name: studentData.name,
          status: Object.entries(statuses).map(([date, status]) => ({
            date,
            status,
          })),
        };
      })
      .filter(Boolean);

    console.log('✅ Selected Student Statuses:', selectedStudentStatuses.value);
    console.log('✅ Paginated Students:', paginatedStudents.value);
  } catch (error) {
    console.error('❌ Gagal mengambil data absensi:', error);
    alert('Gagal mengambil data absensi. Silakan coba lagi.');
  } finally {
    isFetchingData.value = false;
    loading.value = false;
  }
};

// Panggil fungsi fetchData untuk memulai pemanggilan data
fetchData();

// Fungsi untuk menyimpan status ke localStorage
const saveStatusToLocalStorage = (currentYear, currentMonth) => {
  const storedStatuses = toRaw(selectedStudentStatuses.value);

  // Validasi data absensi
  Object.keys(storedStatuses).forEach((studentId) => {
    const studentStatus = storedStatuses[studentId];
    Object.keys(studentStatus).forEach((date) => {
      const status = studentStatus[date]?.status_kehadiran || 'P';

      if (!/^\d{4}-\d{2}-\d{2}$/.test(date)) {
        console.warn(
          `⛔ Tanggal tidak valid: ${date} untuk siswa ID: ${studentId}`
        );
        delete studentStatus[date];
        return;
      }

      if (!['P', 'A', 'S', 'I', 'Belum diabsen'].includes(status)) {
        console.warn(
          `⛔ Status tidak valid untuk siswa ID: ${studentId}`,
          status
        );
        delete studentStatus[date];
      }
    });
  });

  // Gunakan format key khusus bulan, misalnya: statuses-2025-06
  const monthKey = `${currentYear}-${String(currentMonth + 1).padStart(
    2,
    '0'
  )}`;
  const storageKey = `statuses-${monthKey}`;
  const jsonToSave = JSON.stringify(storedStatuses);
  const jsonFromStorage = localStorage.getItem(storageKey);

  if (jsonToSave !== jsonFromStorage) {
    localStorage.setItem(storageKey, jsonToSave);
    console.log(
      `✅ Data absensi disimpan ke localStorage dengan key: ${storageKey}`
    );
  } else {
    console.log('ℹ️ Tidak ada perubahan pada data absensi bulan ini.');
  }
};

// ✅ Fungsi untuk menghapus status dari localStorage
const removeStatusFromLocalStorage = (year, month) => {
  const monthKey = `${year}-${String(month).padStart(2, '0')}`;
  const storageKey = `statuses-${monthKey}`;

  if (localStorage.getItem(storageKey)) {
    localStorage.removeItem(storageKey);
    console.log(`🗑️ Data localStorage dihapus dengan key: ${storageKey}`);
  } else {
    console.log(
      `⚠️ Tidak ditemukan data localStorage dengan key: ${storageKey}`
    );
  }
};

const attendanceData = Object.entries(selectedStudentStatuses.value).map(
  ([studentId, status]) => ({
    student_id: studentId,
    status_kehadiran: status.status_kehadiran,
  })
);
//console.log('Data absensi yang siap dikirim:', attendanceData);

const updateStatusesFromServer = (attendanceData) => {
  attendanceData.forEach((attendance) => {
    const studentId = attendance.student_id;
    const tanggal = attendance.tanggal_kehadiran;
    const newStatus = attendance.status_kehadiran;

    if (!studentId || !tanggal || !newStatus) {
      console.warn(`Data absensi tidak lengkap untuk siswa ID: ${studentId}`);
      return;
    }

    if (!selectedStudentStatuses.value[studentId]) {
      selectedStudentStatuses.value[studentId] = {};
    }

    if (selectedStudentStatuses.value[studentId][tanggal] !== newStatus) {
      selectedStudentStatuses.value[studentId][tanggal] = newStatus;
      console.log(
        `Status siswa ID ${studentId} pada tanggal ${tanggal} diperbarui ke: ${newStatus}`
      );
    }
  });

  console.log(
    'Selected Student Statuses setelah update:',
    selectedStudentStatuses.value
  );
};

async function processAttendanceUpdates() {
  if (
    !selectedStudentStatuses.value ||
    Object.keys(selectedStudentStatuses.value).length === 0
  ) {
    console.error('❌ selectedStudentStatuses masih kosong!');
    return;
  }

  if (isNaN(year) || month == null) {
    console.error('❌ Parameter tahun/bulan tidak valid!', { year, monthName });
    return;
  }

  const totalDaysInMonth = Array.from(
    { length: new Date(year, month + 1, 0).getDate() },
    (_, i) => i + 1
  );

  console.log('📅 Processing attendance for:', {
    year,
    month: month + 1,
    totalDays: totalDaysInMonth.length,
  });

  for (const studentId in selectedStudentStatuses.value) {
    const statusMap = selectedStudentStatuses.value[studentId];
    if (!statusMap || typeof statusMap !== 'object') continue;

    for (const dateStr in statusMap) {
      const status = statusMap[dateStr];

      if (!['P', 'A', 'S', 'I'].includes(status)) {
        console.warn(
          `⚠️ Status tidak valid untuk siswa ID ${studentId}:`,
          status
        );
        continue;
      }

      const date = new Date(dateStr);
      const dayIndex = date.getDate();

      if (!totalDaysInMonth.includes(dayIndex)) {
        console.warn(
          `⚠️ Tanggal ${dateStr} tidak valid untuk siswa ID ${studentId}`
        );
        continue;
      }

      console.log('✅ Memperbarui absensi:', {
        studentId,
        tanggal: dateStr,
        status,
      });

      updateAttendanceStatus(studentId, dateStr, status);
    }
  }

  console.log('🎉 Pembaruan absensi selesai!');
}

// Fungsi untuk memvalidasi tanggal
const isValidDate = (date) => {
  if (!date || typeof date !== 'string') {
    return false;
  }

  const parsedDate = new Date(date);
  return !isNaN(parsedDate.getTime());
};

const fetchAttendances = async () => {
  try {
    const response = await axios.get('/api/attendances');
    console.log('Response Data:', response.data);

    // Cek apakah response.data adalah objek dan memiliki properti 'attendances' yang array
    if (
      typeof response.data !== 'object' ||
      !Array.isArray(response.data.attendances)
    ) {
      console.error(
        'Data absensi tidak valid: response.data.attendances bukan array.'
      );
      attendanceData.value = [];
      newAttendance.value = [];
      return;
    }

    // Simpan data absensi yang ada di dalam 'attendances'
    attendanceData.value = response.data.attendances;
    newAttendance.value = response.data.attendances;

    console.log('Absensi Data Valid:', attendanceData.value);

    // Jika ingin juga simpan tanggal kehadiran
    const tanggalKehadiran = response.data.tanggal_kehadiran;
    console.log('Tanggal Kehadiran:', tanggalKehadiran);

    // Periksa apakah data kosong
    if (attendanceData.value.length === 0) {
      console.warn('Data absensi kosong.');
      alert(
        'Data absensi kosong, pastikan semua siswa memiliki status absensi yang valid.'
      );
    }

    // Jika ada proses lanjutan
    processAttendances();
  } catch (error) {
    console.error('Error fetching attendances:', error);
    attendanceData.value = [];
    newAttendance.value = [];
  }
};

const combineStudentAndAttendance = () => {
  const studentAttendanceMap = studentId.value.map((student) => {
    const attendance = newAttendance.value.find(
      (att) => att.student_id === student.id
    );
    return {
      ...student,
      attendance_status: attendance ? attendance.status_kehadiran : 'P', // Default "P" if no attendance found
    };
  });
  console.log('Combined Student and Attendance:', studentAttendanceMap);
  return studentAttendanceMap;
};

const refreshAttendanceData = async () => {
  // Ambil data absensi berdasarkan tanggal yang dipilih
  await fetchAttendances({ tanggal_kehadiran: selectedDate.value });

  // Log data terbaru (opsional)
  console.log('Data attendances setelah refresh:', attendances.value);
};

const refreshAttendanceDataAndCombine = async () => {
  await refreshAttendanceData(); // Ambil data absensi terbaru
  const combinedData = combineStudentAndAttendance(); // Gabungkan data siswa dan absensi
  studentAttendanceMap.value = combinedData; // Simpan data gabungan ke variabel yang akan digunakan di template
};

const pagination = ref({
  current_page: 1,
  per_page: 5,
  total: studentsList.value.length,
  last_page: Math.ceil(studentsList.value.length / 5),
});

const loadNextPage = async (nextPageUrl) => {
  try {
    const response = await axios.get(nextPageUrl);

    if (response.data && response.data.data && response.data.data.length > 0) {
      // Mengambil ID dan nama siswa
      const nextPageData = response.data.data.map((student) => ({
        id: student.id,
        name: student.name,
      }));

      // Menggabungkan ID siswa dari halaman berikutnya dengan ID siswa yang sudah ada
      studentId.value = [
        ...new Set([
          ...studentId.value.filter((student) => typeof student === 'number'), // Menyaring hanya ID yang valid
          ...nextPageData.filter((student) => typeof student === 'number'), // Menyaring data halaman berikutnya
        ]),
      ];

      console.log('Siswa setelah memuat halaman berikutnya:', studentId.value);
      // Jika ada halaman berikutnya, lanjutkan memuat
      if (response.data.next_page_url) {
        console.log(
          'Melanjutkan ke halaman berikutnya:',
          response.data.next_page_url
        );
        await loadNextPage(response.data.next_page_url);
      }
    }
  } catch (error) {
    console.error('Error loading next page:', error);
  }
};

const initializeDaysInMonth = () => {
  const route = useRoute();
  const year = parseInt(route.params.year);
  const monthName = route.params.month?.toLowerCase();
  const month = monthMap[monthName];

  if (isNaN(year) || month === undefined) {
    console.error('❌ Tahun atau bulan dari URL tidak valid:', {
      year,
      monthName,
    });
    return;
  }

  const daysInMonth = new Date(year, month + 1, 0).getDate();

  totalDaysInMonth.value = Array.from(
    { length: daysInMonth },
    (_, index) => new Date(year, month, index + 1)
  );
};
initializeDaysInMonth();

const createDate = (dateString) => {
  const parsedDate = new Date(dateString);
  if (isNaN(parsedDate.getTime())) {
    // Check if the parsed date is valid
    console.error('Tanggal tidak valid:', dateString);
    return null; // Or a default value
  }
  return parsedDate;
};

const processAttendances = () => {
  const statusMap = {};

  if (Array.isArray(attendanceData.value)) {
    attendanceData.value.forEach((attendance) => {
      const studentId = attendance.siswa_id; // Gunakan 'siswa_id' dari API
      const tanggal = attendance.tanggal_kehadiran;
      const status = attendance.status;

      if (!studentId || !tanggal || !status) {
        console.warn(`Data tidak lengkap untuk siswa ID: ${studentId}`);
        return;
      }

      if (!statusMap[studentId]) {
        statusMap[studentId] = {};
      }

      statusMap[studentId][tanggal] = {
        status_kehadiran: status, // Gunakan key 'status_kehadiran' agar cocok dengan getAttendanceStatus()
      };
    });

    selectedStudentStatuses.value = statusMap;
    console.log('selectedStudentStatuses:', selectedStudentStatuses.value);
  } else {
    console.error('attendanceData.value bukan array:', attendanceData.value);
  }
};

const resetInvalidStatuses = () => {
  Object.entries(selectedStudentStatuses.value).forEach(
    ([studentId, status]) => {
      // Cek apakah status adalah Proxy, dan jika iya, ambil objek mentahnya
      if (isProxy(status)) {
        status = toRaw(status);
      }

      // Jika status adalah objek, pastikan kita memeriksa properti status_kehadiran
      if (
        typeof status === 'object' &&
        status !== null &&
        status.hasOwnProperty('status_kehadiran')
      ) {
        status = status.status_kehadiran;
      }

      // Periksa status, jika status kosong atau "Belum diabsen", atau tidak valid, baru hapus
      if (
        !status || // Jika status kosong
        status === 'Belum diabsen' || // Jika status "Belum diabsen"
        !['P', 'A', 'S', 'I'].includes(status) // Jika status bukan P, A, S, I
      ) {
        console.warn(`Menghapus data siswa yang tidak valid:`, {
          studentId,
          status,
        });
        delete selectedStudentStatuses.value[studentId];
      }
    }
  );
};

// Status untuk menentukan apakah data sudah terkirim
const isAttendanceDataSent = ref(false);

const isAlertVisible = ref(false);
const alertMessage = ref('');

// Logika ketika halaman berubah
const handlePageChange = (page) => {
  console.log('Tombol pagination ditekan, halaman sekarang:', page);
  pagination.current_page = page;

  if (isAttendanceDataSent.value) {
    isAttendanceDataSent.value = false;
  }

  fetchStudents(page); // Pastikan Anda memanggil API atau mengambil data untuk halaman tersebut
  isAlertVisible.value = false;

  // Setelah memuat data, cek jika halaman terakhir tercapai
  if (pagination.current_page >= pagination.last_page) {
    console.log('Halaman terakhir, tombol Next dinonaktifkan');
  }
};

// Variabel untuk mengontrol modal
const isModalOpen = ref(false);

const isUpdatingAttendance = ref(false);

//const hideAddModal = () => {
//  isAddModalVisible.value = false;
//};

// Fungsi untuk menutup modal (setelah data terkirim)
const handleModalClose = () => {
  //isModalOpen.value = false; // Tutup modal
  isAddModalVisible.value = false;

  // Pastikan pengiriman data hanya terjadi setelah modal ditutup atau tombol submit ditekan
  if (!isAttendanceDataSent.value && !isUpdatingAttendance.value) {
    submitAttendance(); // Kirim data absensi jika belum terkirim
  }
};

const selectedStudentId = ref(null);
const selectedDate = ref(null);
const statuses = ref(['P', 'A', 'S', 'I']);
const isModalVisible = ref(false);
const isCustomStatus = ref(false);
const customStatus = ref('');

const handleStatusChange = (studentId, date) => {
  console.log('Menangani status perubahan untuk siswa:', studentId);
  const dateKey = formattedDate(date);
  console.log('isi formattedDateValue:', dateKey);

  // Akses langsung string status, bukan objek
  const currentStatus =
    selectedStudentStatuses.value[studentId]?.[dateKey] || '';

  selectedStudentId.value = studentId;
  selectedDate.value = date;
  isModalVisible.value = true;
  customStatus.value = currentStatus; // Kalau kosong, tetap ''
};

const selectStatus = async (status) => {
  if (!['P', 'A', 'S', 'I'].includes(status)) {
    console.warn(`Status "${status}" tidak valid.`);
    return;
  }

  if (!selectedStudentId.value || !selectedDate.value) {
    console.warn('ID siswa atau tanggal belum dipilih.');
    return;
  }

  try {
    const dateKey = formattedDate(selectedDate.value);

    console.log(
      `Status kehadiran siswa ${selectedStudentId.value} pada ${dateKey} diperbarui menjadi: ${status}`
    );

    if (!selectedStudentStatuses.value[selectedStudentId.value]) {
      selectedStudentStatuses.value[selectedStudentId.value] = {};
    }

    selectedStudentStatuses.value[selectedStudentId.value][dateKey] = status;

    await updateAttendance({
      siswa_id: selectedStudentId.value,
      tanggal_kehadiran: dateKey,
      status: status,
      mapel: props.selectedMapel.length > 0 ? props.selectedMapel[0].mapel : '',
    });

    await saveAllAttendancesBatch();
    closeModal();
  } catch (error) {
    console.error('Gagal memperbarui status kehadiran:', error);
    alert('Terjadi kesalahan saat menyimpan data absensi. Silakan coba lagi.');
  }
};

const closeModal = () => {
  isModalVisible.value = false;
};

//const formattedDate = formattedDate(date);

const updateAttendanceStatus = (studentId, date, status) => {
  const formatted = formattedDate(date); // ← gunakan fungsi yang benar

  if (!formatted) {
    console.error(`Tanggal tidak valid untuk Student ID: ${studentId}`);
    return; // Jika tanggal tidak valid, keluar dari fungsi
  }

  console.log(
    `Memperbarui status kehadiran untuk siswa ID: ${studentId} pada tanggal: ${formatted}`
  );

  const newStatus = status;

  if (newStatus && ['P', 'A', 'S', 'I'].includes(newStatus)) {
    let student = selectedStudentStatuses.value[studentId] || {};

    student[formatted] = {
      status_kehadiran: newStatus,
      tanggal_kehadiran: formatted,
    };

    selectedStudentStatuses.value[studentId] = student;

    // Menyimpan data ke localStorage
    localStorage.setItem(
      'selectedStudentStatuses',
      JSON.stringify(selectedStudentStatuses.value)
    );

    console.log(
      `Status kehadiran siswa ${studentId} pada ${formatted} diperbarui menjadi: ${newStatus}`
    );
  } else {
    console.log(
      `Status tidak valid untuk Student ID: ${studentId}. Memberikan status default "P".`
    );

    selectedStudentStatuses.value[studentId] = {
      [formatted]: {
        status_kehadiran: 'P',
        tanggal_kehadiran: formatted,
      },
    };
  }

  console.log(
    'Updated Attendance:',
    JSON.stringify(selectedStudentStatuses.value, null, 2)
  );

  saveStatusToLocalStorage();
};

// Fungsi untuk menampilkan modal dan menangani form submit
const submitAttendance = async () => {
  try {
    if (!tanggal_kehadiran.value) {
      return;
    }

    const studentsPerPage = 5;
    const currentPage = pagination.current_page || 1;
    const startIndex = (currentPage - 1) * studentsPerPage;
    const endIndex = startIndex + studentsPerPage;

    const currentPageStudents = paginatedStudents.value.slice(
      startIndex,
      endIndex
    );

    const isValidData = currentPageStudents.every((student) => {
      const studentStatus = selectedStudentStatuses.value[student.id] || {
        status_kehadiran: 'P',
      };

      const dateKey = formattedDate(tanggal_kehadiran.value);
      const statusIsValid = ['P', 'A', 'S', 'I'].includes(
        studentStatus.status_kehadiran
      );
      const dateIsValid = dateKey !== '';

      return statusIsValid && dateIsValid;
    });

    if (!isValidData) {
      alert('Terdapat siswa dengan status absensi yang tidak valid.');
      return;
    }

    const attendancesData = currentPageStudents.map((student) => {
      const status =
        selectedStudentStatuses.value[student.id]?.status_kehadiran || 'P';

      return {
        student_id: student.id,
        status_kehadiran: status,
      };
    });

    if (attendancesData.length === 0) {
      alert('Tidak ada data absensi untuk siswa.');
      return;
    }

    const response = await axios.post('/api/attendance3', {
      tanggal_kehadiran: tanggal_kehadiran.value,
      attendances: attendancesData,
    });

    console.log('Response dari server:', response.data);
    alert('Data absensi berhasil disimpan.');

    isAttendanceDataSent.value = true;
    resetAttendanceForm();
    updateStatusesFromServer(response.data.attendances);
    processAttendanceUpdates();
  } catch (error) {
    console.error('Terjadi kesalahan saat mengirim data absensi:', error);
    alert('Terjadi kesalahan saat mengirim data absensi. Mohon coba lagi.');
  }

  isModalOpen.value = true;
};

// Fungsi untuk mereset form input absensi setelah data berhasil disimpan
const resetAttendanceForm = () => {};

if (selectedStudentStatuses.value[studentId]) {
  selectedStudentStatuses.value[studentId].status_kehadiran = status;
}

const getValidDate = (date) => {
  if (date instanceof Date && !isNaN(date.getTime())) return date;

  const year = Number(props.year);
  const month = monthIndex.value;

  const dateObj = new Date(year, month, Number(date));
  if (isNaN(dateObj.getTime())) {
    console.warn('❌ Tanggal tidak valid:', { day: date, year, month });
    return '';
  }

  return dateObj;
};

const isWeekend = (date) => {
  const dayOfWeek = new Date(date).getDay();
  return dayOfWeek === 0 || dayOfWeek === 6;
};

// Ambil CSRF token dari meta tag di halaman Blade
const csrfToken = document.head.querySelector(
  'meta[name="csrf-token"]'
)?.content;

// Set CSRF token di header Axios untuk setiap request
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

const fetchStudents = async (page = 1, perPage = 5) => {
  isNavigating.value = true;
  loading.value = true;

  try {
    let token = sessionStorage.getItem('auth_token'); // Mengambil token dari sessionStorage

    // Pastikan token valid atau perbarui token
    if (!token) {
      const response = await axios.post('/api/auth/refresh-token');
      console.log('Response dari /api/auth/refresh-token:', response);

      if (response.data && response.data.data) {
        token = response.data.data;
        sessionStorage.setItem('auth_token', token);
      } else {
        console.error(
          JSON.stringify(
            {
              message: 'Data token tidak ditemukan',
              responseData: response.data || null,
              status: response.status || 'Unknown status',
              timestamp: new Date().toISOString(),
            },
            null,
            2
          ) // Indentasi 2 spasi untuk keterbacaan
        );
        throw new Error('Gagal mendapatkan token baru');
      }
    }

    // Ambil data siswa dengan pagination berdasarkan siswa_id unik
    const response = await axios.get(
      `/api/students?page=${page}&per_page=${perPage}`,
      {
        headers: {
          Authorization: `Bearer ${token}`,
        },
      }
    );

    if (Array.isArray(response.data.data) && response.data.data.length > 0) {
      // Menyaring data siswa
      studentId.value = response.data.data.map((student) => ({
        id: student.id,
        name: student.name,
      }));

      // Inisialisasi status absensi untuk semua siswa yang terambil
      if (!selectedStudentStatuses.value) {
        selectedStudentStatuses.value = {}; // Inisialisasi hanya sekali
      }

      response.data.data.forEach((student) => {
        if (student.id && !selectedStudentStatuses.value[student.id]) {
          selectedStudentStatuses.value[student.id] = {}; // Inisialisasi status untuk siswa jika belum ada

          // Mengatur status default absensi berdasarkan tanggal yang dipilih
          if (
            tanggal_kehadiran.value &&
            !selectedStudentStatuses.value[student.id][tanggal_kehadiran.value]
          ) {
            const dateKey = formattedDate(tanggal_kehadiran.value);
            selectedStudentStatuses.value[student.id][dateKey] =
              'Belum diabsen';
          }
        }
      });

      console.log(
        '📋 Semua ID siswa dari fetchStudents:',
        Object.keys(selectedStudentStatuses.value)
      );
      console.log('✅ Status ID 2:', toRaw(selectedStudentStatuses.value[2]));

      // Update pagination berdasarkan jumlah siswa unik
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        total: response.data.total, // Jumlah total siswa unik
        per_page: response.data.per_page,
      };
      console.log('➡️ last_pageeee:', pagination.value.last_page);
      console.log('API response last_page:', response.data.last_page);

      // Panggil inisialisasi status absensi setelah data siswa terambil
      if (Object.keys(selectedStudentStatuses.value).length > 0) {
        initializeStatuses(); // Panggil hanya jika ada status yang sudah diinisialisasi
      }
    } else {
      console.error('Data siswa tidak ditemukan atau kosong');
      // Kosongkan status jika data tidak valid
      selectedStudentStatuses.value = {};
    }
  } catch (error) {
    console.error('Error saat mengirim data absensi:', error);
    // Tangani error lebih rinci
    if (error.response) {
      console.error('Response error:', error.response.data);
    } else if (error.request) {
      console.error('Request error:', error.request);
    } else {
      console.error('Error:', error.message);
    }
  } finally {
    isNavigating.value = false;
    loading.value = false;
  }
};

console.log('Data siswa sebelum pemeriksaan:', studentId.value);

// Inisialisasi selectedStudentStatuses dengan status default jika belum ada
const initializeStatuses = () => {
  if (!Array.isArray(paginatedStudents.value)) return;

  paginatedStudents.value.forEach((student) => {
    if (!student?.id || !tanggal_kehadiran.value) return;

    if (!selectedStudentStatuses.value[student.id]) {
      selectedStudentStatuses.value[student.id] = {};
    }

    const dateKey = formattedDate(tanggal_kehadiran.value);
    if (dateKey && !selectedStudentStatuses.value[student.id][dateKey]) {
      selectedStudentStatuses.value[student.id][dateKey] = 'Belum diabsen';
    }
  });
};

// Panggil fungsi inisialisasi saat data siswa pertama kali dimuat
if (!Object.keys(selectedStudentStatuses.value).length) {
  initializeStatuses();
}

const updateAttendance = async (newData) => {
  try {
    const token = localStorage.getItem('auth_token');
    const csrfToken = document
      .querySelector('meta[name="csrf-token"]')
      ?.getAttribute('content');

    // Mendukung newData berupa ref atau objek biasa
    const data = newData?.value || newData || {};
    const { siswa_id, tanggal_kehadiran, status } = data;

    // Validasi input sebelum dikirim ke server
    if (!siswa_id || !tanggal_kehadiran || !status) {
      console.warn('❌ Data absensi tidak lengkap:', data);
      return;
    }

    // Format tanggal ke YYYY-MM-DD
    const tanggalFormatted = new Date(tanggal_kehadiran)
      .toISOString()
      .split('T')[0];

    console.log('📤 Mengirim data ke backend:', {
      siswa_id,
      tanggal_kehadiran: tanggalFormatted,
      status,
    });

    // Kirim PUT request ke API Laravel
    const response = await axios.put(
      '/api/attendances/update',
      {
        siswa_id,
        tanggal_kehadiran: tanggalFormatted,
        status,
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'X-CSRF-TOKEN': csrfToken,
          'Content-Type': 'application/json',
        },
      }
    );

    console.log('✅ Response dari server:', response.data);

    // Hapus localStorage cache karena tidak diperlukan lagi
    localStorage.removeItem('local_attendance_cache');
    console.log('🗑️ local_attendance_cache berhasil dihapus dari localStorage');

    // Refresh data jika tersedia method fetchData
    fetchData?.();
  } catch (error) {
    console.error('❌ Gagal update absensi:', error);

    if (error.response) {
      console.error('🔁 Response error:', error.response.data);
    }

    alert('Gagal memperbarui absensi. Silakan coba lagi.');
  }
};

const saveAllAttendancesBatch = async () => {
  const token = localStorage.getItem('auth_token');
  const csrfToken = document
    .querySelector('meta[name="csrf-token"]')
    ?.getAttribute('content');

  const payload = [];

  for (const student of paginatedStudents.value) {
    const statusMap = selectedStudentStatuses.value[student.id];

    // 🔍 Filter hanya key yang berformat tanggal valid (YYYY-MM-DD)
    const tanggalKeys = Object.keys(statusMap).filter((k) =>
      /^\d{4}-\d{2}-\d{2}$/.test(k)
    );

    for (const tanggal of tanggalKeys) {
      const statusRaw = statusMap[tanggal];

      // 🛠️ Ambil nilai status akhir
      const status =
        typeof statusRaw === 'object' && statusRaw !== null
          ? statusRaw.status_kehadiran
          : statusRaw;

      console.log('✅ Push payload:', {
        siswa_id: student.id,
        tanggal_kehadiran: tanggal,
        status,
        mapel:
          props.selectedMapel.length > 0 ? props.selectedMapel[0].mapel : '',
      });

      payload.push({
        siswa_id: student.id,
        tanggal_kehadiran: tanggal,
        status,
        mapel:
          props.selectedMapel.length > 0 ? props.selectedMapel[0].mapel : '',
      });
    }
  }

  try {
    const response = await axios.post(
      '/api/attendances/batch-update',
      { data: payload },
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'X-CSRF-TOKEN': csrfToken,
        },
      }
    );

    console.log('✅ Batch response:', response.data);
    Swal.fire({
      icon: 'success',
      title: 'Berhasil',
      text: 'Data absensi berhasil disimpan.',
      timer: 2000,
      showConfirmButton: false,
    });
  } catch (error) {
    console.error('❌ Gagal batch update:', error);
    Swal.fire({
      icon: 'error',
      title: 'Gagal',
      text: 'Gagal menyimpan data absensi.',
    });
  }
};

const getAttendanceStatus = (student, date) => {
  if (!student || typeof student !== 'object') {
    console.warn('📛 Student tidak valid:', student);
    return 'Belum diabsen';
  }

  if (!Array.isArray(student.attendances)) {
    console.warn('📛 Attendance tidak valid:', student);
    return 'Belum diabsen';
  }

  if (typeof date !== 'string' || !date.trim()) {
    console.warn('📛 Tanggal tidak valid:', date);
    return 'Belum diabsen';
  }

  const found = student.attendances.find((a) => a.tanggal === date);
  return found?.status || 'Belum diabsen';
};

const status = getAttendanceStatus(students.id, selectedDate.value);

updateAttendance({
  siswa_id: students.id,
  tanggal_kehadiran: selectedDate.value,
  status,
});

// Ambil semua data siswa
const getAllStudents = async () => {
  let currentPageUrl = 'http://127.0.0.1:8000/api/students?page=1'; // URL untuk mengambil data siswa
  let allStudents = []; // Menyimpan semua data siswa

  // Loop untuk mengambil data siswa per halaman hingga tidak ada halaman berikutnya
  while (currentPageUrl) {
    try {
      const response = await axios.get(currentPageUrl);

      // Debugging: Menampilkan response yang diterima
      //console.log('Response data:', response.data);

      // Menambahkan data siswa ke dalam array allStudents
      allStudents = [...allStudents, ...response.data.data];

      // Menyimpan URL halaman berikutnya untuk diambil data selanjutnya
      currentPageUrl = response.data.next_page_url;
    } catch (error) {
      console.error('Error mengambil data siswa:', error);

      // Memastikan log error lebih rinci
      if (error.response) {
        console.error('Response error:', error.response);
      } else if (error.request) {
        console.error('Request error:', error.request);
      } else {
        console.error('Error message:', error.message);
      }

      break; // Jika error, keluar dari loop
    }
  }

  // Menyimpan data siswa ke dalam variable reactive atau state
  students.value = allStudents;

  // Inisialisasi status absensi dengan status default jika status belum ada
  allStudents.forEach((student) => {
    // Menggunakan status default jika belum ada status kehadiran untuk siswa tersebut
    selectedStudentStatuses.value[student.id] = selectedStudentStatuses.value[
      student.id
    ] || { status_kehadiran: 'Belum diabsen' };
  });
};

getAllStudents(); // Memanggil fungsi untuk mengambil semua siswa

const updateSelectedStatuses = (newAttendance) => {
  // Membersihkan selectedStudentStatuses.value
  selectedStudentStatuses.value = Object.entries(selectedStudentStatuses.value)
    .filter(([key, value]) => value.student_id && value.status_kehadiran) // Hanya ambil yang valid
    .map(([key, value]) => {
      value.status_kehadiran = value.status_kehadiran || 'Belum diabsen'; // Default status
      return value;
    });

  console.log('Updating statuses with newAttendance:', newAttendance);

  if (newAttendance && Array.isArray(newAttendance)) {
    newAttendance.forEach((attendance) => {
      const studentId = attendance.student_id;

      // Validasi: pastikan student_id ada dan valid
      if (!studentId) {
        console.error('ID siswa tidak valid atau tidak ditemukan:', attendance);
        return; // Tidak lanjutkan pemrosesan untuk item ini
      }

      // Ambil status siswa saat ini dari selectedStudentStatuses
      const currentStatus = selectedStudentStatuses.value[studentId];

      // Jika status siswa sudah ada
      if (currentStatus) {
        // Update status jika belum ada atau status default
        if (currentStatus.status_kehadiran === 'Belum diabsen') {
          // Update status kehadiran dengan status yang diterima atau default "P"
          currentStatus.status_kehadiran = attendance.status_kehadiran || 'P';
        }
      } else {
        // Jika status siswa belum ada, tambahkan status baru
        selectedStudentStatuses.value[studentId] = {
          student_id: studentId,
          status_kehadiran: attendance.status_kehadiran || 'Belum diabsen',
        };
      }
    });
  } else {
    console.error('newAttendance kosong atau bukan array:', newAttendance);
  }

  // Debug hasil akhir
  console.log(
    'Updated selectedStudentStatuses:',
    selectedStudentStatuses.value
  );
};

const bulanSemester = computed(() => {
  return semester.value === 'Ganjil'
    ? [7, 8, 9, 10, 11, 12] // Juli–Desember
    : [1, 2, 3, 4, 5, 6]; // Januari–Juni
});

const filteredStudents = computed(() => {
  return props.students.map((student) => {
    const filteredAttendances = student.attendances.filter((attendance) => {
      const bulan = new Date(attendance.tanggal).getMonth() + 1;
      return bulanSemester.value.includes(bulan);
    });

    return {
      ...student,
      attendances: filteredAttendances,
    };
  });
});

const perPage = ref(5);
const currentPage = ref(1);

const filteredPaginatedStudents = computed(() => {
  const start = (currentPage.value - 1) * perPage.value;
  const end = start + perPage.value;

  const sliced = filteredStudents.value.slice(start, end);

  console.log('📄 Current Page:', currentPage.value);
  console.log('🔢 Per Page:', perPage.value);
  console.log('🔍 Start:', start, 'End:', end);
  console.log('👥 Students on this page:', sliced.length);
  console.log('📋 Data:', sliced);

  return sliced;
});

const getAttendanceData = async () => {
  try {
    const response = await axios.get('/api/attendance');
    console.log('Data absensi setelah diambil:', response.data);

    // Periksa apakah data absensi kosong
    if (
      Array.isArray(response.data.attendances) &&
      response.data.attendances.length > 0
    ) {
      attendances.value = response.data;
      updateSelectedStatuses(response.data.attendances);
    } else {
      console.warn('Data absensi kosong.');
      // Berikan penanganan atau feedback untuk kasus kosong
      attendances.value = []; // Kosongkan array absensi
    }
  } catch (error) {
    console.error('Gagal mengambil data absensi:', error);
  }
};

console.log('Nilai isAddModalVisible:', isAddModalVisible.value);

const getAttendanceClass = (student, tanggal_kehadiran) => {
  if (!tanggal_kehadiran) {
    return 'bg-light text-dark';
  }

  const dateObj = new Date(tanggal_kehadiran);
  const day = dateObj.getDay();
  const isWeekend = day === 0 || day === 6;

  if (isWeekend) {
    return 'bg-red-500 text-white italic cursor-not-allowed';
  }

  const status = getAttendanceStatus(student, tanggal_kehadiran);

  switch (status) {
    case 'P':
      return 'bg-info text-white';
    case 'A':
      return 'bg-danger text-white';
    case 'S':
      return 'bg-warning text-white';
    case 'I':
      return 'bg-primary text-white';
    default:
      return 'bg-light text-dark';
  }
};

const getButtonClass = (status) => {
  switch (status) {
    case 'P':
      return 'bg-info text-black status-btn info-btn'; // Hadir
    case 'A':
      return 'bg-danger text-black status-btn danger-btn'; // Absen
    case 'S':
      return 'bg-warning text-black status-btn warning-btn'; // Sakit
    case 'I':
      return 'bg-primary text-black status-btn primary-btn'; // Izin
    default:
      return 'bg-light text-dark status-btn light-btn'; // Status tidak ditemukan atau belum diabsen
  }
};

axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

const injectStatusesFromAttendances = (students) => {
  const map = {};

  students.forEach((student) => {
    const statusMap = {};

    //console.log(`🔍 Memproses student: ${student.name} (ID: ${student.id})`);

    student.attendances?.forEach((att) => {
      const rawDate = att.tanggal;
      const date = new Date(rawDate).toISOString().split('T')[0];
      const status = att.status;

      //console.log(`📅 Tanggal: ${rawDate} → ${date}, Status: ${status}`);

      if (status) {
        statusMap[date] = status;
      }
    });

    //console.log(`✅ Status map untuk ${student.name}:`, statusMap);
    map[student.id] = statusMap;
  });

  //console.log('📦 Final selectedStudentStatuses:', map);
  selectedStudentStatuses.value = map;
};

function getStatusTooltip(student, date) {
  const formatted = formattedDate(date);
  const match = student.attendances?.find((a) => {
    const attendanceDate =
      typeof a.tanggal === 'string' ? a.tanggal : formattedDate(a.tanggal);
    return attendanceDate === formatted;
  });
  const status = isWeekend(date) ? 'Libur' : match?.status || 'Belum diabsen';

  return `${student.name} - ${formatted} → ${status}`;
}

function getStatusText(student, date) {
  const formatted = formattedDate(date);
  const match = student.attendances?.find((a) => {
    const attendanceDate =
      typeof a.tanggal === 'string' ? a.tanggal : formattedDate(a.tanggal);
    return attendanceDate === formatted;
  });
  return isWeekend(date) ? 'Libur' : match?.status || 'Belum diabsen';
}

onMounted(async () => {
  initFlowbite();
  loading.value = true;

  try {
    console.log('🟢 Props saat mounted:', props);
    console.log('🟢 selectedMapel saat mounted:', props.selectedMapel);

    // Ambil data kelas dan mapel terkait
    fetchMapelByMonth();
    fetchKelas();
    fetchKelasMapel();

    // Ambil data siswa dari backend
    await fetchStudents();

    // Jika belum ada data status kehadiran, fallback dari props.students
    if (
      !selectedStudentStatuses.value ||
      Object.keys(selectedStudentStatuses.value).length === 0
    ) {
      console.log(
        '🔍 selectedStudentStatuses masih kosong. Akan diisi dari props.students sebagai fallback...'
      );

      const fallbackStatuses = {};

      props.students?.forEach((student) => {
        const id = Number(student.id);
        if (!fallbackStatuses[id]) fallbackStatuses[id] = {};

        student.attendances?.forEach((att) => {
          const date = att.tanggal.split('T')[0]; // Format YYYY-MM-DD
          if (att.status && typeof att.status === 'string') {
            fallbackStatuses[id][date] = att.status;
          }
        });
      });

      selectedStudentStatuses.value = fallbackStatuses;

      console.log(
        '✅ selectedStudentStatuses diinisialisasi dari props.students:',
        toRaw(selectedStudentStatuses.value)
      );
    }

    // Ambil data tambahan dari backend jika ada
    await fetchData();

    // Suntikkan data status ke setiap student dari selectedStudentStatuses
    paginatedStudents.value.forEach((student) => {
      const id = student.id;
      const statuses = selectedStudentStatuses.value[id];

      if (statuses && typeof statuses === 'object') {
        const validStatuses = {};

        Object.entries(statuses).forEach(([date, status]) => {
          if (
            status &&
            typeof status === 'object' &&
            typeof status.status_kehadiran === 'string'
          ) {
            validStatuses[date] = status.status_kehadiran;
          } else if (typeof status === 'string') {
            validStatuses[date] = status;
          }
        });

        student.statuses = validStatuses;

        console.log(
          `📌 ${student.name} (ID: ${id}) - Injected statuses:`,
          validStatuses
        );
      } else {
        student.statuses = {};
        console.warn(
          `⚠️ Tidak ada status valid untuk siswa ${student.name} (ID: ${id})`
        );
      }
    });

    // Pastikan semua siswa di paginatedStudents memiliki entry di selectedStudentStatuses
    paginatedStudents.value.forEach((student) => {
      const id = student?.id;
      if (id != null && !selectedStudentStatuses.value[id]) {
        selectedStudentStatuses.value[id] = {};
        //console.log(`➕ Inisialisasi kosong untuk siswa ID ${id}`);
      }
    });

    // ⬇️ Tambahkan ini di bagian akhir onMounted untuk menyuntikkan langsung ke student.statuses dari attendances
    injectStatusesFromAttendances(props.students);

    console.log(
      '✅ Selected Student Statuses final:',
      toRaw(selectedStudentStatuses.value)
    );
  } catch (error) {
    console.error('❌ Error saat memuat data awal:', error);
  } finally {
    loading.value = false;
  }
});

// Tambahkan watchEffect untuk memantau perubahan props.selectedMapel
watchEffect(() => {
  console.log('🔄 selectedMapel berubah:', props.selectedMapel);
  console.log('selectedMapel.value:', props.selectedMapel);
  console.log('Tipe selectedMapel.value:', typeof props.selectedMapel);

  if (Array.isArray(props.selectedMapel)) {
    console.log('selectedMapel.value adalah Array');
  } else {
    console.log('selectedMapel.value bukan Array');
  }
});

watch(
  () => props.selectedMapel,
  (newVal) => {
    console.log(
      '🔄 selectedMapel di child berubah:',
      newVal,
      '| Type:',
      typeof newVal
    );
  }
);

watch(
  () => props.selectedKelas,
  (newValue) => {
    console.log('📥 selectedKelas baru:', newValue);
  }
);

watch(
  () => props.students,
  (newValue) => {
    console.log('🧑‍🎓 Students yang diterima:', newValue);
    studentsList.value = newValue || [];
  },
  { immediate: true }
);

watch(currentPage, (newPage) => {
  fetchStudents(newPage);
});

watch(
  () => studentsList.value.length,
  (newLength) => {
    pagination.value.total = newLength;
    pagination.value.last_page = Math.ceil(
      newLength / pagination.value.per_page
    );
    if (pagination.value.current_page > pagination.value.last_page) {
      pagination.value.current_page = pagination.value.last_page || 1;
    }
  }
);

watch(
  () => paginatedStudents.value,
  (newStudents) => {
    const today = new Date().toISOString().slice(0, 10);

    newStudents.forEach((student) => {
      // Cek apakah student benar-benar harus diinisialisasi
      // Misal, hanya jika student.attendances ada dan length > 0
      // atau jika kamu punya flag khusus di student, sesuaikan di sini
      const hasAttendance =
        student.attendances && student.attendances.length > 0;

      if (!selectedStudentStatuses.value[student.id] && hasAttendance) {
        selectedStudentStatuses.value[student.id] = {
          [today]: 'Belum diabsen',
        };
        console.log(
          `🆕 Inisialisasi status untuk siswa ID ${student.id} dengan tanggal ${today}`
        );
      } else {
        //   console.log(`✅ Status siswa ID ${student.id} sudah ada atau tidak perlu diinisialisasi:`,toRaw(selectedStudentStatuses.value[student.id]));
      }
    });

    console.log(
      '📦 selectedStudentStatuses setelah inisialisasi:',
      selectedStudentStatuses.value
    );
  },
  { immediate: true }
);

watchEffect(() => {
  console.log('Students atau totalDaysInMonth berubah');
  console.log('Jumlah siswa:', paginatedStudentsFromProps.value.length);
  console.log('Total hari dalam bulan:', totalDaysInMonth.length);

  paginatedStudentsFromProps.value.forEach((student) => {
    totalDaysInMonth.value.forEach((date) => {
      const formatted = formattedDate(getValidDate(date));
      const status = getAttendanceStatus(student.id, formatted);
      // Bisa lakukan sesuatu dengan status ini jika perlu
    });
  });
});

const filteredStatuses = computed(() => {
  const validDates = totalDaysInMonth.value.map(
    (d) => formattedDate(d) // Langsung saja, karena d sudah Date
  );
  //console.log('📅 Total valid dates:', validDates);

  const validStudents = filteredPaginatedStudents.value.map((student) => {
    const injectedStatuses = {};

    student.attendances?.forEach((att) => {
      const date = formattedDate(att.tanggal);
      if (validDates.includes(date)) {
        injectedStatuses[date] = att.status;
      }
    });

    return {
      ...student,
      statuses: injectedStatuses,
    };
  });

  return validStudents;
});

watch(
  () => pagination.value.current_page,
  (newPage, oldPage) => {
    fetchStudents(newPage);
  }
);
</script>

<style scoped>
.g-bordered {
  border: 1px solid #606060;
}
.table-bordered td {
  height: 20px;
  width: 20px;
  font-size: 12px;
}
.close {
  color: #aaa;
  font-size: 28px;
  font-weight: bold;
  position: absolute;
  top: 0;
  right: 20px;
}
.close:hover,
.close:focus {
  color: black;
  text-decoration: none;
  cursor: pointer;
}
.large-td {
  font-size: 28px;
  font-style: normal;
  padding: 15px;
  /* Sesuaikan dengan lebar yang diinginkan */
}
.large-p {
  font-size: 12px;
  font-style: normal;
  text-align: center;
  padding: 15px;
}

.custom-thead {
  background-color: #4caf50; /* Ganti dengan warna yang Anda inginkan */
  color: yellow; /* Warna teks */
}

.custom-thead th {
  padding: 10px; /* Menambahkan padding agar teks tidak terlalu rapat */
  text-align: center; /* Menjaga agar teks berada di tengah */
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 999; /* Pastikan modal di atas konten lain */
  transition: opacity 0.3s ease, visibility 0.3s ease;
}

.modal-content {
  background-color: #fff;
  padding: 20px 30px; /* Atur padding untuk ruang di dalam modal */
  border-radius: 10px; /* Membuat sudut melengkung */
  max-width: 500px; /* Tentukan lebar modal agar lebih ramping */
  width: 100%;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Bayangan lembut untuk modal */
  transition: transform 0.3s ease-in-out; /* Efek transformasi saat modal muncul */
}

.modal-content h3 {
  font-size: 1.5em;
  margin-bottom: 15px; /* Ruang di bawah judul */
}

.status-options button {
  background-color: #c4c9ce;
  color: #fff;
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  margin: 5px;
  cursor: pointer;
  transition: background-color 0.3s;
}

.status-options button {
  margin: 5px;
  padding: 10px;
}

.status-btn {
  transition: background-color 0.3s ease;
}

/* Hover effect untuk setiap status */
.status-btn.info-btn:hover {
  background-color: #0056b3; /* Hover untuk "Hadir" */
}

.status-btn.danger-btn:hover {
  background-color: #dc3545; /* Hover untuk "Absen" */
}

.status-btn.warning-btn:hover {
  background-color: #e0a800; /* Hover untuk "Sakit" */
}

.status-btn.primary-btn:hover {
  background-color: #004085; /* Hover untuk "Izin" */
}

.status-btn.light-btn:hover {
  background-color: #007bff; /* bg-primary */
  color: white; /* text-white */
  font-weight: bold;
}

.custom-status-input {
  margin-top: 15px;
}

.custom-status-input input {
  width: 100%;
  padding: 10px;
  border: 1px solid #ccc;
  border-radius: 5px;
  margin-top: 10px;
  font-size: 1em;
}

.close-btn {
  background-color: #f44336; /* Tombol Tutup dengan warna merah */
  color: #fff;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  margin-top: 20px;
  transition: background-color 0.3s;
}
</style>

<style>
.modal.fade.show {
  display: block;
  background-color: rgba(15, 13, 14, 0.889);
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

    <main class="p-7 md:ml-64 h-screen pt-20">
      <Head title="Tabel Absensi Siswa Januari" />
      <form @submit.prevent="submitAttendance">
        <div class="container py-5">
          <div
            class="sm:flex sm:items-center bg-gradient-to-r from-blue-500 to-purple-500 text-white p-4 rounded-3xl shadow-lg w-full"
          >
            <div class="sm:flex-auto w-4/5 mx-auto">
              <h1 class="text-3xl text-white text-center font-bold mb-4">
                Tabel Absensi Siswa
              </h1>
              <div>
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <p class="text-sm">
                      <span class="font-semibold">Mata Pelajaran:</span>
                    </p>
                    <div class="text-sm">
                      <p v-if="selectedMapel.length">
                        {{
                          selectedMapel.length === 0
                            ? '—'
                            : selectedMapel.length === 1
                            ? selectedMapel[0].mapel
                            : selectedMapel.length === 2
                            ? `${selectedMapel[0].mapel} dan ${selectedMapel[1].mapel}`
                            : selectedMapel
                                .slice(0, -1)
                                .map((m) => m.mapel) // Mengakses 'mapel' dari objek
                                .join(', ') +
                              ', dan ' +
                              selectedMapel[selectedMapel.length - 1].mapel
                        }}
                      </p>
                      <p v-else>Tidak ada mata pelajaran yang dipilih.</p>
                    </div>
                  </div>
                  <div class="flex justify-between">
                    <p class="text-sm">
                      <span class="font-semibold">Kelas:</span>
                    </p>
                    <div class="text-sm">
                      <p v-if="props.selectedKelas && props.selectedKelas.name">
                        {{ props.selectedKelas.name }}
                      </p>

                      <p v-if="selectedKelas && selectedKelas.name">
                        {{ selectedKelas.name }}
                      </p>
                      <p v-else>Tidak ada kelas dipilih.</p>
                    </div>
                  </div>
                  <div class="flex justify-between">
                    <p class="text-sm">
                      <span class="font-semibold">Bulan/Tahun:</span>
                    </p>
                    <p class="text-sm">{{ props.month }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="g-responsive overflow-x-auto max-w-full">
          <table class="table table-bordered table-sm w-full">
            <thead style="background-color: aliceblue">
              <!-- Baris Tanggal -->
              <tr class="custom-tr">
                <th class="text-center">Tanggal</th>
                <th
                  v-for="(date, index) in totalDaysInMonth"
                  :key="'date-' + index"
                  class="text-center w-42"
                >
                  {{ date.getDate() }}
                </th>
              </tr>

              <!-- Baris Hari -->
              <tr class="custom-tr">
                <th class="text-center">Hari</th>
                <th
                  v-for="(date, index) in totalDaysInMonth"
                  :key="'day-' + index"
                  class="text-center"
                  :class="{ 'bg-danger text-white': isSunday(date) }"
                >
                  {{ getDayName(date) }}
                </th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="student in paginatedStudents" :key="student.id">
                <td class="font-medium">{{ student.name }}</td>

                <td
                  v-for="date in totalDaysInMonth"
                  :key="`attendance-${student.id}-${formattedDate(date)}`"
                  :class="[
                    getAttendanceClass(student, formattedDate(date)),
                    'text-center',
                    isWeekend(date)
                      ? 'bg-red-500 text-white italic cursor-not-allowed'
                      : 'cursor-pointer',
                  ]"
                  :title="getStatusTooltip(student, date)"
                  @click="
                    () => {
                      const formatted = formattedDate(date);
                      if (!isWeekend(date)) {
                        handleStatusChange(student.id, formatted);
                      }
                    }
                  "
                >
                  <span class="text-black">
                    {{ getStatusText(student, date) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- flex justify-center mt-4-->
        <div
          v-if="pagination.last_page > 1"
          class="flex flex-col items-center mt-4"
        >
          <!-- Info Halaman -->
          <span class="text-sm text-gray-700 dark:text-gray-400">
            Page
            <span class="font-semibold text-gray-900 dark:text-white">
              {{ pagination.current_page }}
            </span>
            of
            <span class="font-semibold text-gray-900 dark:text-white">
              {{ pagination.last_page }}
            </span>
          </span>

          <!-- Tombol Navigasi -->
          <div class="inline-flex mt-2">
            <!-- Tombol Previous -->
            <button
              @click="handlePageChange(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="flex items-center justify-center px-4 h-10 text-base font-medium text-white bg-gray-800 rounded-s hover:bg-gray-900 disabled:cursor-not-allowed disabled:opacity-50"
            >
              <svg
                class="w-3.5 h-3.5 me-2 rtl:rotate-180"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 14 10"
                aria-hidden="true"
              >
                <path
                  d="M13 5H1m0 0 4 4M1 5l4-4"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
              Previous
            </button>

            <!-- Tombol Next -->
            <button
              @click="handlePageChange(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="flex items-center justify-center px-4 h-10 text-base font-medium text-white bg-blue-500 border-0 border-s border-gray-700 rounded-e hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"
            >
              Next
              <svg
                class="w-3.5 h-3.5 ms-2 rtl:rotate-180"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 14 10"
                aria-hidden="true"
              >
                <path
                  d="M1 5h12m0 0L9 1m4 4L9 9"
                  stroke="currentColor"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </button>
          </div>
        </div>
        <div class="row mt-3 me-3">
          <div class="col-12">
            <p class="fw-bold">Status Kehadiran:</p>
            <div class="d-flex">
              <div class="me-3">
                <span class="badge bg-info text-black fw-bold">Hadir (P)</span>
              </div>
              <div class="me-3">
                <span class="badge bg-danger text-black fw-bold"
                  >Absen (A)</span
                >
              </div>
              <div class="me-3">
                <span class="badge bg-warning text-black fw-bold"
                  >Sakit (S)</span
                >
              </div>
              <div class="me-3">
                <span class="badge bg-primary text-black fw-bold"
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

        <!-- Modal Tambah Absen -->
        <div
          v-if="
            isAddModalVisible &&
            Array.isArray(paginatedStudents) &&
            paginatedStudents.length > 0
          "
          class="modal fade show"
          tabindex="-1"
        >
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title text-3xl">Tambah Absensi</h5>
                <button
                  type="button"
                  class="btn-close"
                  @click="handleModalClose"
                  aria-label="Close"
                ></button>
              </div>
              <div class="modal-body">
                <form @submit.prevent="submitAttendance">
                  <div>
                    <label for="tanggal-kehadiran">Pilih Tanggal</label>
                    <input
                      id="tanggal-kehadiran"
                      type="date"
                      class="form-control"
                      v-model="tanggal_kehadiran"
                      :min="minDate"
                      :max="maxDate"
                      required
                    />
                  </div>

                  <table class="table table-bordered">
                    <tr v-for="student in paginatedStudents" :key="student.id">
                      <td>{{ student.name }}</td>
                      <td>
                        <select
                          v-model="
                            selectedStudentStatuses[student.id].status_kehadiran
                          "
                          class="form-select"
                          :class="{
                            'is-invalid':
                              !selectedStudentStatuses[student.id]
                                .status_kehadiran || !tanggal_kehadiran,
                          }"
                          required
                        >
                          <option value="" disabled>Pilih Status</option>
                          <option value="P">HADIR</option>
                          <option value="A">ALPHA</option>
                          <option value="S">SAKIT</option>
                          <option value="I">IZIN</option>
                        </select>
                      </td>
                    </tr>
                    <pre>{{ student.name }}</pre>
                  </table>
                  <!-- @click="isAlertVisible"-->
                  <button type="submit" class="btn btn-primary w-100">
                    Simpan Absensi
                  </button>

                  <!-- Alert notifikasi -->
                  <div v-if="isAlertVisible" class="alert alert-success">
                    {{ alertMessage }}
                    <!-- Menampilkan pesan alert -->
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal update absen -->
        <div>
          <!-- Daftar Siswa dan Tanggal -->
          <div v-for="student in students" :key="student.id"></div>

          <!-- Modal untuk memilih status kehadiran -->
          <div
            v-if="isModalVisible"
            class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50"
            @click.self="closeModal"
          >
            <!-- Modal Content -->
            <div
              class="bg-white p-6 rounded-xl shadow-2xl max-w-md w-full relative overflow-hidden transform transition-all duration-300 scale-95 hover:scale-100"
            >
              <!-- Close Icon -->
              <button
                class="absolute top-4 right-4 text-gray-500 hover:text-red-500 transition"
                @click="closeModal"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                  stroke-width="2"
                  stroke="currentColor"
                  class="w-6 h-6"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    d="M6 18L18 6M6 6l12 12"
                  />
                </svg>
              </button>

              <!-- Modal Header -->
              <div class="text-center mb-6">
                <div
                  class="w-14 h-14 mx-auto flex items-center justify-center bg-blue-100 rounded-full mb-4"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke-width="2"
                    stroke="currentColor"
                    class="w-8 h-8 text-blue-600"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      d="M9 12h6m-3-3v6m9-6a9 9 0 11-18 0 9 9 0 0118 0z"
                    />
                  </svg>
                </div>
                <h3 class="text-2xl ft-bold text-gray-800">
                  Pilih Status Kehadiran
                </h3>
                <p class="text-gray-500 text-sm">
                  Silakan pilih salah satu status di bawah ini.
                </p>
              </div>

              <!-- Pilihan Status -->
              <div class="space-y-4">
                <button
                  v-for="status in statuses"
                  :key="status"
                  :class="getButtonClass(status)"
                  class="w-full py-3 px-5 rounded-lg font-semibold text-black transition-all duration-300"
                  @click="selectStatus(status)"
                >
                  {{ status }}
                </button>
              </div>

              <!-- Input Custom Status -->
              <div v-if="isCustomStatus" class="custom-status-input mt-4">
                <input
                  v-model="customStatus"
                  type="text"
                  placeholder="Masukkan status (P, A, S, I)"
                  @keyup.enter="selectStatus(customStatus)"
                  class="w-full p-3 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-300"
                />
              </div>

              <!-- Modal Footer -->
              <div class="mt-6 text-center">
                <button
                  class="w-full py-3 bg-gray-200 rounded-lg text-gray-700 font-medium hover:bg-gray-300 transition-colors"
                  @click="closeModal"
                >
                  Tutup
                </button>
              </div>
            </div>
          </div>
        </div>
      </form>
    </main>

    <!-- Sidebar -->
    <SidebarTeacher />
  </div>
</template>
