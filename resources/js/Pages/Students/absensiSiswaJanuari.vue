'
'<script setup>
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
// State management
const newAttendance = ref([]);
const loading = ref(false);
const studentId = ref([1]);
const students = ref([]);
//const props = defineProps(['student']);
const props = defineProps({
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
});

const studentsList = ref([]);
const currentPage = ref(1);
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

//const student = props.student;
const selectedStudentStatuses = ref({});
///const date = new Date();
const userInputDate = ref('');
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth();
const currentMonthYear = computed(() => {
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
  const monthName = months[currentMonth]; // Mendapatkan nama bulan berdasarkan currentMonth
  return `${monthName} ${currentYear}`; // Format "Bulan YYYY", misalnya "Desember 2024"
});

// Mendapatkan tanggal pertama bulan ini
const defaultDay = new Date(currentYear, currentMonth, 1).getDate();
if (defaultDay === 0) {
  console.error('Default Day tidak valid:', defaultDay);
}

// Mendapatkan tanggal minimum dan maksimum dalam bulan ini
const minDate = `${currentYear}-${(currentMonth + 1)
  .toString()
  .padStart(2, '0')}-01`;
const maxDate = `${currentYear}-${(currentMonth + 1)
  .toString()
  .padStart(2, '0')}-${new Date(currentYear, currentMonth + 1, 0).getDate()}`;

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

const defaultTanggal = computed(() => {
  const today = new Date();
  const day = today.getDate().toString().padStart(2, '0');
  const month = (today.getMonth() + 1).toString().padStart(2, '0');
  const year = today.getFullYear();
  return `${year}-${month}-${day}`; // Pastikan format YYYY-MM-DD
});

const totalDaysInMonth = Array.from(
  { length: new Date(currentYear, currentMonth + 1, 0).getDate() },
  (_, i) => i + 1
);

//console.log('totalDaysInMonth ' + totalDaysInMonth);

const isSunday = (day) => {
  const dateObj = new Date(currentYear, currentMonth, day);
  return dateObj.getDay() === 0; // 0 berarti Minggu
};

const tanggal_kehadiran = ref('');
const isNavigating = ref(false);
const isSelectVisible = ref(false);
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

const formattedDate = (date) => {
  const dateObj = new Date(date);

  if (isNaN(dateObj.getTime())) {
    console.warn('Tanggal tidak valid:', date);
    return ''; // Tanggal tidak valid, kembalikan string kosong atau default lainnya
  }

  const day = dateObj.getDate().toString().padStart(2, '0');
  const month = (dateObj.getMonth() + 1).toString().padStart(2, '0');
  const year = dateObj.getFullYear();

  return `${year}-${month}-${day}`;
};
const isFetchingData = ref(false);

function normalizeDate(rawDate) {
  if (!rawDate) return '';
  return rawDate.__v_isRef ? rawDate.value : rawDate;
}

const fetchData = async () => {
  if (isFetchingData.value) return;

  isFetchingData.value = true;
  loading.value = true;

  try {
    console.log('Student ID sebelum fetchData:', studentId.value);

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
      return;
    }

    console.log('📩 Data absensi dari API:', attendancesArray);

    const updatedStatuses = {};

    attendancesArray.forEach((item) => {
      const id = item.siswa_id ?? item.student_id; // fallback untuk backward compatibility

      if (id === null || id === undefined) {
        console.warn('⚠️ item absensi tanpa student_id/siswa_id:', item);
        return;
      }

      const studentIdNum = Number(id);
      if (isNaN(studentIdNum)) {
        console.warn('⚠️ student_id bukan angka valid:', id);
        return;
      }

      if (!updatedStatuses[studentIdNum]) {
        updatedStatuses[studentIdNum] = {};
      }

      const formatted = formattedDate(new Date(item.tanggal_kehadiran));
      updatedStatuses[studentIdNum][formatted] =
        item.status_kehadiran?.trim() || item.status?.trim() || 'Belum diabsen';
    });

    selectedStudentStatuses.value = updatedStatuses;

    // ✅ Gabungkan dengan data siswa
    const allowedStatuses = ['P', 'A', 'S', 'I'];

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
      .filter(Boolean); // filter student yg tidak ditemukan

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
const saveStatusToLocalStorage = () => {
  // Pastikan data sudah berupa objek biasa tanpa ref
  const storedStatuses = toRaw(selectedStudentStatuses.value);

  // Iterasi untuk memastikan tanggal_kehadiran adalah string, bukan ref
  Object.keys(storedStatuses).forEach((studentId) => {
    const studentStatus = storedStatuses[studentId];

    Object.keys(studentStatus).forEach((date) => {
      let status = studentStatus[date]?.status_kehadiran || 'P'; // Fallback ke "P" jika tidak ada status

      console.log(`Status untuk siswa ID ${studentId} pada ${date}:`, status);
      if (!['P', 'A', 'S', 'I', 'Belum diabsen'].includes(status)) {
        console.warn(`Data tidak valid untuk siswa ID: ${studentId}`, {
          status,
        });
        return; // Lewati siswa ini jika status tidak valid
      }

      // Lanjutkan dengan logika untuk memperbarui status atau lainnya
    });
  });

  // Periksa apakah data telah berubah dibandingkan dengan data yang ada di localStorage
  const currentStoredData = localStorage.getItem('selectedStudentStatuses');
  if (JSON.stringify(storedStatuses) !== currentStoredData) {
    console.log('Data sebelum disimpan:', storedStatuses);
    // Simpan data yang telah diperbarui ke localStorage
    localStorage.setItem(
      'selectedStudentStatuses',
      JSON.stringify(storedStatuses)
    );
    console.log('Data setelah disimpan:', storedStatuses);
  }
};

const attendanceData = Object.entries(selectedStudentStatuses.value).map(
  ([studentId, status]) => ({
    student_id: studentId,
    status_kehadiran: status.status_kehadiran,
  })
);
console.log('Data absensi yang siap dikirim:', attendanceData);

function updateStatusesFromServer(attendanceData) {
  attendanceData.forEach((attendance) => {
    const studentId = attendance.student_id;
    const newStatus = attendance.status_kehadiran;

    // Validasi data absensi
    if (!studentId || !newStatus) {
      console.warn(`Data absensi tidak lengkap untuk siswa ID: ${studentId}`);
      return; // Skip jika data tidak valid
    }

    // Periksa apakah status siswa sudah diubah oleh pengguna
    const currentStatus =
      selectedStudentStatuses.value[studentId]?.status_kehadiran;

    // Jika status sudah diubah oleh pengguna, jangan diperbarui oleh server
    if (currentStatus && currentStatus !== newStatus) {
      console.log(
        `Status siswa ID ${studentId} tidak diperbarui karena sudah diubah oleh pengguna`
      );
      return;
    }

    // Jika status belum diubah oleh pengguna atau siswa belum ada di selectedStudentStatuses
    if (selectedStudentStatuses.value[studentId]) {
      // Pastikan status selalu disimpan dalam bentuk objek dengan properti 'status_kehadiran'
      if (typeof selectedStudentStatuses.value[studentId] === 'string') {
        selectedStudentStatuses.value[studentId] = {
          status_kehadiran: selectedStudentStatuses.value[studentId],
        };
      }

      // Jika status siswa berbeda dengan status baru dari server, perbarui status
      if (
        selectedStudentStatuses.value[studentId].status_kehadiran !== newStatus
      ) {
        selectedStudentStatuses.value[studentId].status_kehadiran = newStatus;
        console.log(`Status siswa ID ${studentId} diperbarui ke: ${newStatus}`);
      }
    } else {
      // Jika siswa belum ada, tambahkan status siswa baru
      selectedStudentStatuses.value[studentId] = {
        student_id: studentId,
        status_kehadiran: newStatus,
      };
      console.log(`Status siswa ID ${studentId} ditambahkan: ${newStatus}`);
    }
  });

  console.log(
    'Selected Student Statuses setelah update:',
    selectedStudentStatuses.value
  );
}

function normalizeAttendanceData(attendances) {
  const normalizedAttendances = {};

  Object.keys(attendances).forEach((studentId) => {
    let attendance = attendances[studentId];

    // Jika attendance memiliki key "P", itu berarti format data yang salah
    if (attendance.hasOwnProperty('P')) {
      // Menormalisasi format absensi untuk siswa dengan ID 1 atau format serupa
      attendance = {
        status_kehadiran: attendance.P.status_kehadiran,
        tanggal_kehadiran: attendance.P.tanggal_kehadiran,
      };
    }

    // Pastikan bahwa setiap siswa memiliki "status_kehadiran" dan "tanggal_kehadiran"
    if (!attendance.hasOwnProperty('tanggal_kehadiran')) {
      attendance.tanggal_kehadiran =
        userInputDate || new Date().toISOString().split('T')[0]; // Gunakan tanggal input atau tanggal hari ini
    }

    // Menambahkan status "Belum diabsen" jika status tidak ada
    if (!attendance.hasOwnProperty('status_kehadiran')) {
      attendance.status_kehadiran = 'Belum diabsen'; // Status default
    }

    // Ganti status "P" dengan "Belum diabsen"
    if (attendance.status_kehadiran === 'P') {
      attendance.status_kehadiran = 'Belum diabsen';
    }

    // Jika status belum ada, pastikan formatnya benar
    if (!normalizedAttendances[studentId]) {
      normalizedAttendances[studentId] = {};
    }

    // Menyimpan attendance untuk setiap tanggal yang ada
    normalizedAttendances[studentId][attendance.tanggal_kehadiran] = {
      status_kehadiran: attendance.status_kehadiran,
      tanggal_kehadiran: attendance.tanggal_kehadiran,
    };
  });

  return normalizedAttendances;
}

async function processAttendanceUpdates() {
  // Cek apakah selectedStudentStatuses kosong
  if (
    !selectedStudentStatuses.value ||
    Object.keys(selectedStudentStatuses.value).length === 0
  ) {
    console.error('selectedStudentStatuses masih kosong!');
    return;
  }

  // Mendapatkan total hari dalam bulan saat ini
  const currentYear = new Date().getFullYear();
  const currentMonth = new Date().getMonth();
  const totalDaysInMonth = Array.from(
    { length: new Date(currentYear, currentMonth + 1, 0).getDate() },
    (_, i) => i + 1
  );

  // Iterasi setiap siswa di selectedStudentStatuses
  for (const studentId in selectedStudentStatuses.value) {
    const status = selectedStudentStatuses.value[studentId]?.status_kehadiran;

    // Validasi status kehadiran
    if (!status || !['P', 'A', 'S', 'I'].includes(status)) {
      console.warn(`Data tidak valid untuk siswa ID: ${studentId}`, {
        status,
      });
      continue; // Lewati siswa ini jika status tidak valid
    }

    // Ambil data absensi dari localStorage
    const storedAttendances =
      JSON.parse(localStorage.getItem('attendances')) || {};
    console.log('Stored Attendancesssss:', storedAttendances);

    // Normalisasi data absensi
    const normalizedAttendances = normalizeAttendanceData(storedAttendances);

    // Simpan kembali data yang sudah dinormalisasi ke localStorage
    localStorage.setItem('attendances', JSON.stringify(normalizedAttendances));

    // Pastikan bahwa storedAttendances adalah objek yang valid
    if (typeof storedAttendances !== 'object' || storedAttendances === null) {
      console.error('Data absensi tidak dalam format objek yang valid!');
      return;
    }

    const normalizedStudentId = Number(studentId);

    // Temukan data absensi terkait siswa
    const attendance = normalizedAttendances[normalizedStudentId];

    console.log(
      'Absensi ditemukan untuk siswa ID:',
      normalizedStudentId,
      attendance
    );

    if (!attendance) {
      console.warn(
        `Absensi tidak ditemukan untuk siswa ID: ${normalizedStudentId}`
      );
      continue;
    }

    // Mengakses nilai tanggal_kehadiran yang merupakan Ref
    const date = attendance.tanggal_kehadiran?.value; // Akses .value jika tanggal_kehadiran adalah Ref
    console.log(`Tanggal yang diterima: ${date}`);

    // Periksa apakah tanggal valid sebelum memanggil updateAttendanceStatus
    if (!date || !isValidDate(date)) {
      console.warn(
        `Tanggal kehadiran tidak valid untuk siswa ID: ${studentId}`
      );
      continue; // Lewati siswa ini jika tanggal tidak valid
    }

    // Cek apakah tanggal ada dalam totalDaysInMonth
    const dayIndex = new Date(date).getDate(); // Ambil hari dari tanggal
    if (!totalDaysInMonth.includes(dayIndex)) {
      console.warn(
        `Tanggal kehadiran tidak valid untuk siswa ID: ${studentId}`
      );
      continue; // Jika tanggal tidak ada dalam bulan ini, lewati
    }

    // Proses pembaruan absensi
    console.log('Memperbarui absensi:', { studentId, status, date });

    // Update status absensi berdasarkan ID siswa dan tanggal yang valid
    updateAttendanceStatus(studentId, date, status); // Pastikan fungsi ini menerima ID, tanggal, dan status
  }

  console.log('Pembaruan absensi selesai!');
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
  last_page: 1,
  per_page: 5,
  total: 0,
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

//const date = totalDaysInMonth.value[0];

// Inisialisasi array totalDaysInMonth
const initializeDaysInMonth = () => {
  const daysInMonth = new Date(currentYear, currentMonth + 1, 0).getDate();
  totalDaysInMonth.value = Array.from(
    { length: daysInMonth },
    (_, index) => new Date(currentYear, currentMonth, index + 1)
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
// Menyusun data absensi hanya untuk siswa yang ada pada halaman ini

//const attendancesData = Object.entries(selectedStudentStatuses.value).filter(
([studentId, status]) => {
  // Kirim data jika status tidak 'P', 'A', 'S', atau 'I'
  //return (
  ////status.status_kehadiran &&
  //!["P", "A", "S", "I"].includes(status.status_kehadiran)
  //);
};
//);
/*
    .map(([studentId, status]) => ({
        student_id: parseInt(studentId, 10),
        status_kehadiran: status.status_kehadiran || "P",
        tanggal_kehadiran: tanggal_kehadiran.value,
    }))
    .filter(Boolean);
   */

const paginatedStudents = computed(() => {
  // Pastikan studentsList.value adalah array yang valid
  if (!Array.isArray(studentsList.value)) {
    console.error('studentsList.value bukan array:', studentsList.value);
    return [];
  }

  // Menampilkan data studentsList sebelum melakukan pagination
  //console.log('Data studentsList sebelum pagination:', studentsList.value);

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

const showAddModal = () => {
  isAddModalVisible.value = true;
};
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
  const formattedDateValue = formattedDate(date);
  console.log('isi formattedDateValue:', formattedDateValue);

  const currentStatus =
    selectedStudentStatuses.value[studentId]?.[formattedDate(date)]
      ?.status_kehadiran;

  // Jika status sudah dipilih, langsung perbarui tanpa prompt
  if (currentStatus) {
    selectedStudentId.value = studentId;
    selectedDate.value = date;
    isModalVisible.value = true;
    customStatus.value = currentStatus; // Set status yang sudah ada ke input modal
  } else {
    // Jika belum ada status, maka buka modal untuk memilih status baru
    selectedStudentId.value = studentId;
    selectedDate.value = date;
    isModalVisible.value = true;
  }
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

    // Update status di reactive state
    if (!selectedStudentStatuses.value[selectedStudentId.value]) {
      selectedStudentStatuses.value[selectedStudentId.value] = {};
    }
    selectedStudentStatuses.value[selectedStudentId.value][dateKey] = {
      status_kehadiran: status,
    };

    // Update localStorage
    let storedStatuses = JSON.parse(localStorage.getItem('attendances')) || {};
    if (!storedStatuses[selectedStudentId.value]) {
      storedStatuses[selectedStudentId.value] = {};
    }
    storedStatuses[selectedStudentId.value][dateKey] = status;

    localStorage.setItem('attendances', JSON.stringify(storedStatuses));
    console.log('Data status di localStorage telah diperbarui.');

    // Kirim update ke backend satu per satu
    await updateAttendance({
      siswa_id: selectedStudentId.value,
      tanggal_kehadiran: dateKey,
      status: status,
      mapel: props.selectedMapel.length > 0 ? props.selectedMapel[0].mapel : '',
    });

    // Kirim batch semua data sekaligus
    await saveAllAttendancesBatch();

    // Tutup modal setelah update berhasil
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
  // Gunakan formattedDate untuk memformat tanggal

  if (!formattedDate) {
    console.error(`Tanggal tidak valid untuk Student ID: ${studentId}`);
    return; // Jika tanggal tidak valid, keluar dari fungsi
  }

  console.log(
    `Memperbarui status kehadiran untuk siswa ID: ${studentId} pada tanggal: ${formattedDate}`
  );

  // Ambil status dari UI atau modal
  const newStatus = status; // Menggunakan status yang diteruskan ke fungsi

  if (newStatus && ['P', 'A', 'S', 'I'].includes(newStatus)) {
    let student = selectedStudentStatuses.value[studentId];

    if (!student) {
      student = {};
    }

    student[formattedDate] = {
      status_kehadiran: newStatus,
      tanggal_kehadiran: formattedDate,
    };

    selectedStudentStatuses.value[studentId] = student;

    // Menyimpan data ke localStorage
    localStorage.setItem(
      'selectedStudentStatuses',
      JSON.stringify(selectedStudentStatuses.value)
    );

    console.log(
      `Status kehadiran siswa ${studentId} pada ${formattedDate} diperbarui menjadi: ${newStatus}`
    );
  } else {
    // Jika status tidak valid, beri status default "P"
    console.log(
      `Status tidak valid untuk Student ID: ${studentId}. Memberikan status default "P".`
    );

    selectedStudentStatuses.value[studentId] = {
      [formattedDate]: {
        status_kehadiran: 'P', // Status default
        tanggal_kehadiran: formattedDate,
      },
    };
  }

  console.log(
    'Updated Attendance:',
    JSON.stringify(selectedStudentStatuses.value, null, 2)
  );

  // Simpan status yang diperbarui ke localStorage
  saveStatusToLocalStorage();
};

// Fungsi untuk menampilkan modal dan menangani form submit
const submitAttendance = async () => {
  try {
    if (!tanggal_kehadiran.value) {
      return;
    }

    const studentsPerPage = 5;
    const currentPage = pagination.current_page || 1; // Menggunakan nilai halaman aktif
    const startIndex = (currentPage - 1) * studentsPerPage;
    const endIndex = startIndex + studentsPerPage;

    const currentPageStudents = paginatedStudents.value.slice(
      startIndex,
      endIndex
    );

    // Validasi data absensi
    const isValidData = currentPageStudents.every((student) => {
      let studentStatus = selectedStudentStatuses.value[student.id] || {
        status_kehadiran: 'P',
      };

      // Memastikan tanggal diformat dengan benar
      const dateKey = formattedDate(tanggal_kehadiran.value);

      // Menyimpan status kehadiran berdasarkan tanggal yang sudah diformat
      studentStatus[dateKey] = studentStatus.status_kehadiran;

      const statusIsValid = ['P', 'A', 'S', 'I'].includes(
        studentStatus.status_kehadiran
      );
      const dateIsValid = dateKey !== ''; // Pastikan tanggal valid

      return statusIsValid && dateIsValid;
    });

    if (!isValidData) {
      alert('Terdapat siswa dengan status absensi yang tidak valid.');
      return;
    }

    const attendancesData = currentPageStudents.map((student) => {
      const status =
        selectedStudentStatuses.value[student.id]?.status_kehadiran || 'P';

      // Format tanggal sebelum digunakan
      // const dateKey = formattedDate(tanggal_kehadiran.value); // Tidak perlu ini di item attendance

      return {
        student_id: student.id,
        status_kehadiran: status,
        // tanggal_kehadiran: dateKey, // Hapus ini agar sesuai backend
      };
    });

    if (attendancesData.length === 0) {
      alert('Tidak ada data absensi untuk siswa.');
      return;
    }

    // Simpan data absensi ke localStorage
    localStorage.setItem('attendances', JSON.stringify(attendancesData));

    // Kirim data absensi ke server
    const response = await axios.post('/api/attendance3', {
      tanggal_kehadiran: tanggal_kehadiran.value,
      attendances: attendancesData,
    });

    console.log('Response dari server:', response.data);
    alert('Data absensi berhasil disimpan.');

    // Mengatur status setelah sukses
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

const getValidDate = (day) => {
  const dateObj = new Date(currentYear, currentMonth, day);
  if (isNaN(dateObj.getTime())) {
    // Tangani kasus tanggal tidak valid
    return '';
  }
  return dateObj.toISOString().split('T')[0];
};

const toggleSelectVisibility = (date) => {
  isSelectVisible.value = true; // Menampilkan dropdown
  selectedDate.value = date; // Menyimpan tanggal yang dipilih
};

const isWeekend = (date) => {
  //const day = new Date(date).getDay();
  //return day === 0 || day === 6; // 0 = Minggu, 6 = Sabtu
  const dayOfWeek = new Date(date).getDay();
  return dayOfWeek === 0 || dayOfWeek === 6;
};

// Ambil CSRF token dari meta tag di halaman Blade
const csrfToken = document.head.querySelector(
  'meta[name="csrf-token"]'
)?.content;

// Set CSRF token di header Axios untuk setiap request
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

// memverifikasi apakah token disetel dengan benar sebelum mengirimkan permintaan
//console.log('CSRF Token:', axios.defaults.headers.common['X-CSRF-TOKEN']);

const mapStudentStatuses = () => {
  const savedStatuses = localStorage.getItem('studentStatuses');

  if (savedStatuses) {
    try {
      const parsedStatuses = JSON.parse(savedStatuses);
      if (parsedStatuses && typeof parsedStatuses === 'object') {
        selectedStudentStatuses.value = parsedStatuses;
      } else {
        console.warn('Data status absensi yang disimpan tidak valid.');
        selectedStudentStatuses.value = {}; // Inisialisasi jika data tidak valid
      }
    } catch (error) {
      console.error('Error parsing saved statuses:', error);
      selectedStudentStatuses.value = {}; // Fallback jika parsing gagal
    }
  } else {
    selectedStudentStatuses.value = {}; // Inisialisasi jika tidak ada data
  }
};

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
            selectedStudentStatuses.value[student.id][tanggal_kehadiran.value] =
              {
                status_kehadiran: 'Belum diabsen', // Status default
                tanggal_kehadiran: tanggal_kehadiran.value,
              };
          }
        }
      });

      console.log(
        'Selected Student Statuses setelah inisialisasi:',
        selectedStudentStatuses.value
      );

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

    // Pastikan struktur nested object aman sebelum digunakan
    if (!selectedStudentStatuses.value) {
      selectedStudentStatuses.value = {};
    }

    if (!selectedStudentStatuses.value[student.id]) {
      selectedStudentStatuses.value[student.id] = {};
    }

    if (!selectedStudentStatuses.value[student.id][tanggal_kehadiran.value]) {
      selectedStudentStatuses.value[student.id][tanggal_kehadiran.value] =
        'Belum diabsen';
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

    // Simpan ke localStorage cache (opsional)
    const cacheKey = 'local_attendance_cache';
    const existingCache = JSON.parse(localStorage.getItem(cacheKey)) || {};

    if (!existingCache[siswa_id]) {
      existingCache[siswa_id] = {};
    }
    existingCache[siswa_id][tanggalFormatted] = status;

    localStorage.setItem(cacheKey, JSON.stringify(existingCache));
    console.log('🗂️ Disimpan ke LocalStorage:', existingCache);

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

    for (const tanggal of Object.keys(statusMap)) {
      // Validasi: hanya push jika tanggal valid format YYYY-MM-DD
      if (!/^\d{4}-\d{2}-\d{2}$/.test(tanggal)) {
        console.warn('⛔ Dilewati karena key tanggal tidak valid:', tanggal);
        continue;
      }

      const statusRaw = statusMap[tanggal];
      // Konversi ke string jika status adalah object (misalnya { status_kehadiran: 'P' })
      const status =
        typeof statusRaw === 'object' && statusRaw !== null
          ? statusRaw.status_kehadiran
          : statusRaw;

      console.log('✅ Push payload:', {
        siswa_id: student.id,
        tanggal_kehadiran: tanggal,
        status: status,
        mapel:
          props.selectedMapel.length > 0 ? props.selectedMapel[0].mapel : '',
      });

      payload.push({
        siswa_id: student.id,
        tanggal_kehadiran: tanggal,
        status: status,
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

//updateAttendance(data);

const getAttendanceStatus = (studentId, date) => {
  if (
    typeof studentId !== 'number' ||
    typeof date !== 'string' ||
    !date.trim()
  ) {
    console.warn('ID atau tanggal absensi tidak valid:', studentId, date);
    return 'Belum diabsen';
  }

  const allStatuses = selectedStudentStatuses.value;

  if (
    !allStatuses ||
    typeof allStatuses !== 'object' ||
    !(studentId in allStatuses) ||
    typeof allStatuses[studentId] !== 'object' ||
    allStatuses[studentId] === null
  ) {
    return 'Belum diabsen';
  }

  return allStatuses[studentId][date] || 'Belum diabsen';
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

//const date = new Date();

//console.log('date:', date.value);
//console.log('Calling updateAttendanceStatus with:');
//console.log('date:', date.value);
//console.log('newStatus:', newAttendance.value);
//console.log('studentId:', studentId.value);
//console.log('newStatus:', newAttendance.value);
//console.log('newData:', newData.value);

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

const getAttendanceClass = (studentId, tanggal_kehadiran) => {
  if (!tanggal_kehadiran) {
    return 'bg-light text-dark';
  }

  // Cek apakah tanggal merupakan akhir pekan
  const dateObj = new Date(tanggal_kehadiran);
  const day = dateObj.getDay(); // 0 = Minggu, 6 = Sabtu
  const isWeekend = day === 0 || day === 6;

  if (isWeekend) {
    return 'bg-red-500 text-white italic cursor-not-allowed'; // Style khusus untuk "Libur"
  }

  const status = getAttendanceStatus(studentId, tanggal_kehadiran);

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

// Fungsi untuk memuat dan menggabungkan status dari localStorage
function loadStatusFromLocalStorage() {
  const savedStatuses = localStorage.getItem('selectedStudentStatuses');

  if (savedStatuses) {
    const parsedStatuses = JSON.parse(savedStatuses);

    // Gabungkan data yang dimuat dengan data yang ada di selectedStudentStatuses
    Object.keys(parsedStatuses).forEach((studentId) => {
      // Jika studentId sudah ada, gabungkan statusnya, jika tidak, set status baru
      if (!selectedStudentStatuses.value[studentId]) {
        selectedStudentStatuses.value[studentId] = parsedStatuses[studentId];
      } else {
        // Gabungkan status baru dengan yang sudah ada di selectedStudentStatuses
        Object.assign(
          selectedStudentStatuses.value[studentId],
          parsedStatuses[studentId]
        );
      }
    });

    console.log('Status absensi dimuat dan digabungkan dari localStorage.');
    console.log(selectedStudentStatuses.value);
  } else {
    console.log('Tidak ada data status yang disimpan di localStorage.');
  }
}

onMounted(async () => {
  initFlowbite();
  try {
    console.log('🟢 Props saat mounted:', props);
    console.log('🟢 selectedMapel saat mounted:', props.selectedMapel);
    loading.value = true; // Menandakan bahwa data sedang diambil

    fetchMapelByMonth();
    fetchKelas();
    fetchKelasMapel();
    // Ambil data siswa terlebih dahulu
    await fetchStudents(); // Menunggu hasil dari fetchStudents

    await getAttendanceData();

    mapStudentStatuses();

    // Memulihkan data dari localStorage
    loadStatusFromLocalStorage(); // Memuat dan menggabungkan status dari localStorage

    // Mengonversi Proxy menjadi objek biasa
    const studentsData = JSON.parse(JSON.stringify(paginatedStudents.value));
    console.log('Data siswa tanpa Proxy:', studentsData);

    // Pastikan selectedStudentStatuses adalah objek yang valid dan kosong
    console.log('Paginated Students || onMounted:', paginatedStudents.value);
    paginatedStudents.value.forEach((student) => {
      if (student && student.id !== undefined && student.id !== null) {
        if (!selectedStudentStatuses.value[student.id]) {
          selectedStudentStatuses.value[student.id] = {
            status_kehadiran: 'Belum diabsen', // Status default
          };
        }
      } else {
        console.warn(`ID siswa tidak valid: ${student.id}`);
      }
    });
    // Cek data absensi dan update status absensi untuk setiap siswa
    console.log('Data Absensi: ', attendances.value);
    if (attendances.value && attendances.value.length > 0) {
      attendances.value.forEach((attendance) => {
        const studentId = attendance.student_id;
        const status = attendance.status_kehadiran || 'Belum diabsen'; // Default status
        const attendanceDate = attendance.tanggal_kehadiran; // Pastikan tanggal_kehadiran ada

        // Cek apakah studentId sudah ada di selectedStudentStatuses
        if (!selectedStudentStatuses.value[studentId]) {
          selectedStudentStatuses.value[studentId] = {};
        }

        // Set status kehadiran untuk tanggal yang relevan
        selectedStudentStatuses.value[studentId][attendanceDate] = status;
      });
    } else {
      console.warn('Data absensi kosong, status default diterapkan.');
    }

    console.log(
      'Selected Student Statuses setelah update:',
      selectedStudentStatuses.value
    );
  } catch (error) {
    console.error('Error saat memuat data awal:', error);
  } finally {
    loading.value = false; // Menandakan bahwa loading selesai
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
  () => props.students,
  (newVal) => {
    studentsList.value = newVal || [];
    pagination.value.total = studentsList.value.length;
    pagination.value.last_page = Math.ceil(
      pagination.value.total / pagination.value.per_page
    );
  },
  { immediate: true }
);

watch(
  () => paginatedStudents.value,
  (newStudents) => {
    const today = new Date().toISOString().slice(0, 10); // 'YYYY-MM-DD'

    newStudents.forEach((student) => {
      if (!selectedStudentStatuses.value[student.id]) {
        selectedStudentStatuses.value[student.id] = {
          [today]: 'Belum diabsen',
        };
        console.log(
          `🆕 Inisialisasi status untuk siswa ID ${student.id} dengan tanggal ${today}`
        );
      } else {
        console.log(
          `✅ Status siswa ID ${student.id} sudah ada:`,
          toRaw(selectedStudentStatuses.value[student.id])
        );
      }
    });

    console.log(
      '📦 selectedStudentStatuses setelah inisialisasi:',
      selectedStudentStatuses.value
    );
  },
  { immediate: true }
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
                    <p class="text-sm">{{ currentMonthYear }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="g-responsive overflow-x-auto max-w-full">
          <table class="table table-bordered table-sm">
            <thead style="background-color: aliceblue">
              <tr class="custom-tr">
                <th>Tanggal</th>
                <th
                  v-for="(date, index) in totalDaysInMonth"
                  :key="'date-' + index"
                  class="text-center w-42"
                >
                  {{ date }}
                </th>
              </tr>
              <tr class="custom-tr">
                <th>Hari</th>
                <th
                  v-for="(day, index) in totalDaysInMonth"
                  :key="'day-name-' + index"
                  :class="{
                    'bg-danger text-white': isSunday(day),
                  }"
                >
                  {{ getDayName(getValidDate(day)) }}
                </th>
              </tr>
            </thead>

            <tbody>
              <tr v-for="student in paginatedStudents" :key="student.id">
                <td class="font-medium">{{ student.name }}</td>
                <td
                  v-for="(date, index) in totalDaysInMonth"
                  :key="
                    (() => {
                      const validDate = getValidDate(date);
                      const formatted = formattedDate(validDate);
                      return `attendance-${student.id}-${formatted}`;
                    })()
                  "
                  :class="
                    (() => {
                      const validDate = getValidDate(date);
                      const formatted = formattedDate(validDate);
                      return [
                        getAttendanceClass(student.id, formatted),
                        'text-center',
                        isWeekend(validDate)
                          ? 'bg-red-500 text-white italic cursor-not-allowed'
                          : 'cursor-pointer',
                      ];
                    })()
                  "
                  @click="
                    (() => {
                      const validDate = getValidDate(date);
                      const formatted = formattedDate(validDate);
                      if (!isWeekend(validDate)) {
                        handleStatusChange(student.id, formatted);
                      }
                    })()
                  "
                >
                  <span class="text-center text-black">
                    {{
                      (() => {
                        const validDate = getValidDate(date);
                        const formatted = formattedDate(validDate);
                        return isWeekend(validDate)
                          ? 'Libur'
                          : getAttendanceStatus(student.id, formatted);
                      })()
                    }}
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
