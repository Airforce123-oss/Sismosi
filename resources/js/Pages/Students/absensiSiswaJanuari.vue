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
  student: {
    type: Object,
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

const mapelNames = computed(() => {
  let list = [];

  if (Array.isArray(props.selectedMapel)) {
    list = props.selectedMapel.map((m) => m.mapel);
  } else if (props.selectedMapel && props.selectedMapel.mapel) {
    list = [props.selectedMapel.mapel];
  }

  if (list.length === 0) return '—';
  if (list.length === 1) return list[0];
  const last = list.pop();
  return `${list.join(', ')} dan ${last}`;
});

console.log('Props di Komponen Anak:', props);
console.log('Received selectedMapel:', props.selectedMapel);
console.log('Kelas List:', props.kelasList);
const kelasList = ref([]);
console.log('Received selectedKelas:', props.selectedKelas);

const emit = defineEmits();
emit('debug', props.selectedKelas);

console.log('📥 selectedKelas di Child (props):', props.selectedKelas);

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

  console.log(
    '🔍 After processing:',
    `"${selectedMapel}"`,
    `(${typeof selectedMapel})`
  );

  // Validasi harus string
  if (typeof selectedMapel !== 'string') {
    console.error('❌ Error: selectedMapel is not a string');
    return false;
  }

  // Jangan validasi jika kosong
  if (selectedMapel.trim() === '') {
    console.warn('⚠️ Mapel belum dipilih');
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
    console.log('API Response fetchKelasMapel:', response.data.data); // Log respons API

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
//const currentMonthYear = ref("");
const pageNumber = ref(1);
const newStatus = ref('');
//const newStatus = selectedStudentStatuses.value[studentId]?.status_kehadiran[0]; // Mengambil status dari array

const date = ref(new Date());
///const date = new Date();
const userInputDate = ref('');
// Mendapatkan tanggal saat ini (tanggal hari ini)
const currentDay = new Date().getDate();
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

const dayIndex = 0;
console.log('Nilai dayIndex:', dayIndex);

const getFormattedDate = (dayIndex) => {
  if (dayIndex === undefined || isNaN(dayIndex)) {
    console.error('dayIndex tidak valid:', dayIndex);
    return 'Tanggal tidak valid';
  }

  //const currentDate = new Date();
  const currentDate = ref(new Date());

  //console.log('currentDate sebelum setDate:', currentDate.value);
  if (dayIndex !== 0) {
    currentDate.value.setDate(currentDate.value.getDate() + dayIndex); // Gunakan .value
  }
  //console.log('currentDate setelah setDate:', currentDate.value);
  // Validasi jika tanggal valid
  if (isNaN(currentDate.value.getTime())) {
    // Gunakan .value
    console.error('Tanggal tidak valid untuk dayIndex:', dayIndex);
    return 'Tanggal tidak valid';
  }

  const day = currentDate.value.getDate().toString().padStart(2, '0'); // Format 2 digit
  const month = (currentDate.value.getMonth() + 1).toString().padStart(2, '0'); // Bulan dalam format 2 digit
  const year = currentDate.value.getFullYear();
  const dayName = getDayName(dayIndex); // Mendapatkan nama hari
  return `${dayName}, ${day}-${month}-${year}`; // Format: "Senin, 07-12-2024"
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

totalDaysInMonth.forEach((dayIndex) => {
  //console.log(getFormattedDate(dayIndex)); // Memanggil fungsi dengan dayIndex yang valid
});

console.log('totalDaysInMonth ' + totalDaysInMonth);

const isSunday = (day) => {
  const dateObj = new Date(currentYear, currentMonth, day);
  return dateObj.getDay() === 0; // 0 berarti Minggu
};

console.log(defaultTanggal.value);

//const tanggal_kehadiran = ref(
//  `${currentYear}-${(currentMonth + 1)
//    .toString()
//  .padStart(2, "0")}-${currentDay.toString().padStart(2, "0")}`
///);

const tanggal_kehadiran = ref('');

console.log('Tanggal Kehadiran:', tanggal_kehadiran.value);
const pageChanged = ref(false);
const isNavigating = ref(false);
const isSelectVisible = ref(false);
const nextMonthYear = ref('');
const nextNextMonthYear = ref('');
const isAddModalVisible = ref(false);

// Helper functions
const getCurrentMonthYear = () => {
  const today = new Date();
  return today.toLocaleDateString('id-ID', {
    month: 'long',
    year: 'numeric',
  });
};

// Modal handling functions
function toggleModalSave() {
  console.log('toggleModalSave dipanggil');
  isAddModalVisible.value = !isAddModalVisible.value;
}

// Logs for debugging
//console.log(studentId.value);
console.log('Data siswa:', toRaw(studentId.value));

// Example of page navigation state
watch(pageNumber, (newPageNumber) => {
  console.log('Page changed to:', newPageNumber);
});

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
console.log('Tanggal Kehadiran:', tanggal_kehadiran.value);

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

const fetchData = async () => {
  if (isFetchingData.value) return; // Cegah duplicate request
  isFetchingData.value = true;
  loading.value = true;

  try {
    console.log('Student ID sebelum fetchData:', studentId.value);

    if (!Array.isArray(studentId.value) || studentId.value.length === 0) {
      console.warn('❌ Student ID tidak valid:', studentId.value);
      return;
    }

    console.log('✅ Student IDs:', studentId.value);

    const response = await axios.get('/api/attendance1'); // Panggil API

    if (!response.data || !response.data.attendances) {
      console.warn('⚠️ Data absensi kosong atau tidak ditemukan.');
      return;
    }

    console.log('📩 Data absensi yang diterima:', response.data.attendances);

    const absensiArray = Object.entries(response.data.attendances).map(
      ([id, attendance]) => ({
        studentId: id,
        status: Object.entries(attendance).map(([date, status]) => ({
          date,
          status,
        })),
      })
    );

    absensiArray.forEach(({ studentId, status }) => {
      status.forEach((attendance) => {
        let rawDate = attendance.date || tanggal_kehadiran.value;

        if (rawDate && rawDate.__v_isRef) {
          rawDate = rawDate.value;
        }

        if (!isValidDate(rawDate)) {
          console.warn(`❌ Tanggal tidak valid untuk siswa ID: ${studentId}`);
          return;
        }

        const formattedDateString = formattedDate(new Date(rawDate));

        console.log(
          `🔄 Memperbarui status untuk siswa ID: ${studentId} pada ${formattedDateString}`
        );

        selectedStudentStatuses.value[studentId] =
          selectedStudentStatuses.value[studentId] || {};
        selectedStudentStatuses.value[studentId][formattedDateString] =
          attendance.status || 'Belum diabsen';
      });
    });

    attendances.value = absensiArray;

    paginatedStudents.value = attendances.value.filter((student) => {
      const idNumber = parseInt(student.studentId);
      if (!studentId.value.includes(idNumber)) return false;

      if (!student.studentId || !Array.isArray(student.status)) {
        console.warn('⚠️ Data siswa tidak valid:', student);
        return false;
      }

      return student.status.every((s) =>
        ['P', 'A', 'S', 'I'].includes(s.status)
      );
    });

    console.log('✅ Selected Student Statuses:', selectedStudentStatuses.value);
    console.log('✅ Paginated Students:', paginatedStudents.value);
  } catch (error) {
    console.error('❌ Error fetching data:', error);
  } finally {
    isFetchingData.value = false;
    loading.value = false;
  }
};

// Panggil fungsi fetchData untuk memulai pemanggilan data
fetchData();

const statusChanged = ref(false);

console.log(date);

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

    // Pastikan response.data.attendances adalah array
    if (Array.isArray(response.data.attendances)) {
      attendanceData.value = response.data.attendances;
      console.log('Absensi Data Valid:', attendanceData.value);

      // Memproses data absensi
      processAttendances();

      // Pastikan ada data yang valid untuk absensi
      if (attendanceData.value.length === 0) {
        console.error(
          'Data absensi kosong, pastikan semua siswa memiliki status absensi yang valid.'
        );
        alert(
          'Data absensi kosong, pastikan semua siswa memiliki status absensi yang valid.'
        );
        return;
      }
    } else {
      console.error(
        'Data absensi tidak valid: response.data.attendances bukan array.'
      );
      attendanceData.value = [];
    }

    // Pastikan newAttendance diisi dengan data yang benar
    newAttendance.value = Array.isArray(response.data.attendances)
      ? response.data.attendances
      : Object.values(response.data.attendances);

    console.log('New Attendance Array:', newAttendance.value);
  } catch (error) {
    console.error('Error fetching attendances:', error);
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
  return studentId.value.slice(0, 5);
});

const processAttendances = () => {
  if (Array.isArray(attendanceData.value)) {
    attendances.value = attendanceData.value
      .map((attendance) => {
        if (!attendance.student_id || !attendance.status_kehadiran) {
          console.warn(
            `Data tidak lengkap untuk siswa ID: ${attendance.student_id}`
          );
          return null; // Kembalikan null jika data tidak lengkap
        }
        return {
          student_id: attendance.student_id,
          status_kehadiran: attendance.status_kehadiran || 'Belum diabsen', // Gunakan status default
        };
      })
      .filter(Boolean); // Hapus nilai null atau data yang tidak lengkap
  } else {
    console.error('attendancesData bukan array:', attendanceData.value);
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
  fetchStudents(page);
  isAlertVisible.value = false;
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
  // Panggil formattedDate untuk memformat tanggal
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

const selectStatus = (status) => {
  if (['P', 'A', 'S', 'I'].includes(status)) {
    console.log(
      `Status kehadiran siswa ${selectedStudentId.value} pada ${selectedDate.value} diperbarui menjadi: ${status}`
    );

    // Update status pada selectedStudentStatuses
    const dateKey = formattedDate(selectedDate.value);
    const statusKey = `${selectedStudentId.value}-${dateKey}`;

    // Perbarui status di selectedStudentStatuses
    selectedStudentStatuses.value[selectedStudentId.value] =
      selectedStudentStatuses.value[selectedStudentId.value] || {};
    selectedStudentStatuses.value[selectedStudentId.value][dateKey] = {
      status_kehadiran: status,
    };

    // Update status pada localStorage
    let storedStatuses = JSON.parse(localStorage.getItem('attendances')) || {};

    // Perbarui status di storedStatuses
    storedStatuses[selectedStudentId.value] =
      storedStatuses[selectedStudentId.value] || {};
    storedStatuses[selectedStudentId.value][dateKey] = status;

    // Bandingkan jika data di localStorage berbeda dengan yang baru diubah
    if (
      JSON.stringify(storedStatuses) !==
      JSON.stringify(selectedStudentStatuses.value)
    ) {
      console.log('Data status di localStorage telah diperbarui.');
      localStorage.setItem('attendances', JSON.stringify(storedStatuses));
    } else {
      console.log('Tidak ada perubahan, data tidak disimpan.');
    }

    // Perbarui status di database atau server (misalnya melalui API)
    updateAttendanceStatus(selectedStudentId.value, selectedDate.value, status);

    closeModal(); // Menutup modal setelah memilih status
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
      const dateKey = formattedDate(tanggal_kehadiran.value);

      return {
        student_id: student.id,
        status_kehadiran: status,
        tanggal_kehadiran: dateKey,
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
const resetAttendanceForm = () => {
  // Reset tanggal kehadiran
  //tanggal_kehadiran.value = null; // Atur ini ke null atau sesuai dengan kebutuhan
  // Reset status absensi siswa
};

console.log('Attending data:', attendanceData);

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
console.log('CSRF Token:', axios.defaults.headers.common['X-CSRF-TOKEN']);

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
const fetchStudents = async (page = 1) => {
  isNavigating.value = true;
  loading.value = true;

  try {
    let token = sessionStorage.getItem('auth_token'); // Mengambil token dari sessionStorage

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

    const response = await axios.get(`/api/students?page=${page}&per_page=5`, {
      headers: {
        Authorization: `Bearer ${token}`,
      },
    });

    if (Array.isArray(response.data.data) && response.data.data.length > 0) {
      // Menyaring data siswa
      studentId.value = response.data.data.map((student) => ({
        id: student.id,
        name: student.name,
      }));

      // Inisialisasi status absensi untuk semua siswa yang terambil
      //selectedStudentStatuses.value = {}; // Reset status absensi
      response.data.data.forEach((student) => {
        if (student.id) {
          // Inisialisasi status absensi default jika belum ada
          if (!selectedStudentStatuses.value[student.id]) {
            selectedStudentStatuses.value[student.id] = {};
          }

          // Mengatur status default absensi berdasarkan tanggal yang dipilih
          if (
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

      // Update pagination
      pagination.value = {
        current_page: response.data.current_page,
        last_page: response.data.last_page,
        total: response.data.total,
        per_page: response.data.per_page,
      };

      // Panggil inisialisasi status absensi setelah data siswa terambil
      initializeStatuses();
    } else {
      console.error('Data siswa tidak ditemukan atau kosong');
      //selectedStudentStatuses.value = {}; // Kosongkan status jika data tidak valid
    }
  } catch (error) {
    console.error('Error saat mengirim data absensi:', error);
  } finally {
    isNavigating.value = false;
    loading.value = false;
  }
};

console.log('Data siswa sebelum pemeriksaan:', studentId.value);

// Inisialisasi selectedStudentStatuses dengan status default jika belum ada
const initializeStatuses = () => {
  if (Array.isArray(paginatedStudents.value)) {
    paginatedStudents.value.forEach((student) => {
      if (typeof student !== 'object' || student === null) {
        return;
      }

      // Validasi student.id
      if (student.id !== undefined && student.id !== null) {
        // Jika status untuk tanggal tertentu belum ada, set status default
        if (
          !selectedStudentStatuses.value[student.id][tanggal_kehadiran.value]
        ) {
          selectedStudentStatuses.value[student.id][tanggal_kehadiran.value] = {
            status_kehadiran: 'Belum diabsen', // Status default
            tanggal_kehadiran: tanggal_kehadiran.value,
          };
        }
      } else {
        console.warn(`ID siswa tidak valid: ${JSON.stringify(student)}`);
      }
    });
  } else {
    console.warn('Data siswa bukan array:', paginatedStudents.value);
  }
};

// Panggil fungsi inisialisasi saat data siswa pertama kali dimuat
if (!Object.keys(selectedStudentStatuses.value).length) {
  initializeStatuses();
}

const newData = ref([]); // Hanya mendeklarasikan newData sekali di luar

const updateAttendance = async (newData) => {
  try {
    const token = localStorage.getItem('auth_token');
    const csrfToken = document
      .querySelector('meta[name="csrf-token"]')
      .getAttribute('content');

    // Validasi newData sebelum dikirimkan ke API
    if (!newData || !newData.tanggal_kehadiran) {
      //console.error("Data tidak lengkap:", newData);
      //alert("Data tidak lengkap, silakan coba lagi.");
      return;
    }

    const response = await axios.put(
      `/api/attendances`,
      {
        tanggal_kehadiran: newData.tanggal_kehadiran, // Pastikan tanggal_kehadiran ada
      },
      {
        headers: {
          Authorization: `Bearer ${token}`,
          'X-CSRF-TOKEN': csrfToken,
        },
      }
    );

    console.log('Absensi berhasil diperbarui:', response.data);
    alert('Absensi berhasil diperbarui!');
    fetchAttendances(); // Ambil data absensi terbaru
  } catch (error) {
    console.error('Error memperbarui absensi:', error);
    alert('Gagal memperbarui absensi. Silakan coba lagi.');
  }
};

updateAttendance(newData);

// Ambil semua data siswa
const getAllStudents = async () => {
  let currentPageUrl = 'http://127.0.0.1:8000/api/students?page=1';
  let allStudents = [];

  while (currentPageUrl) {
    try {
      const response = await axios.get(currentPageUrl);
      allStudents = [...allStudents, ...response.data.data];
      currentPageUrl = response.data.next_page_url;
    } catch (error) {
      console.error('Error mengambil data siswa:', error);
      break;
    }
  }
  students.value = allStudents;

  // Inisialisasi status absensi dengan status default
  allStudents.forEach((student) => {
    selectedStudentStatuses.value[student.id] = selectedStudentStatuses.value[
      student.id
    ] || { status_kehadiran: 'Belum diabsen' };
  });
};

getAllStudents(); // Memanggil fungsi untuk mengambil semua siswa

//const date = new Date();

console.log('date:', date.value);
console.log('Calling updateAttendanceStatus with:');
console.log('date:', date.value);
console.log('newStatus:', newAttendance.value);
console.log('studentId:', studentId.value);
console.log('newStatus:', newAttendance.value);
console.log('newData:', newData.value);

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
      attendances.value = response.data.attendances;
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

const getAttendanceStatus = (studentId, date) => {
  //const rawStatuses = toRaw(selectedStudentStatuses.value); // Mengambil data yang tidak reaktif
  //const studentStatuses = rawStatuses[studentId];
  const studentStatuses = selectedStudentStatuses.value[studentId];

  // Cek jika ada status untuk studentId dan tanggal tertentu
  if (studentStatuses && studentStatuses[date]) {
    // Pastikan ada status absensi untuk tanggal tertentu
    const statusObj = studentStatuses[date];
    return statusObj.status_kehadiran; // Mengembalikan status
  }

  return 'Belum diabsen'; // Jika tidak ada status absensi untuk studentId
};

const getAttendanceStatusOnClick = (studentId, date) => {
  const student = selectedStudentStatuses.value[studentId];
  if (student && student[date]) {
    return student[date].status_kehadiran;
  }
  return 'Belum diabsen'; // Default jika tidak ada status
};

console.log('Nilai isAddModalVisible:', isAddModalVisible.value);

const getAttendanceClass = (studentId, tanggal_kehadiran) => {
  if (!tanggal_kehadiran) {
    return 'bg-light text-dark'; // Jika tidak ada tanggal yang dipilih, tampilkan default
  }

  // Panggil fungsi untuk mendapatkan status absensi berdasarkan studentId dan tanggal_kehadiran
  const status = getAttendanceStatus(studentId, tanggal_kehadiran);

  // Kelas berdasarkan status kehadiran
  switch (status) {
    case 'P':
      return 'bg-info text-white fw-bold'; // Hadir
    case 'A':
      return 'bg-danger text-white fw-bold'; // Absen
    case 'S':
      return 'bg-warning text-white fw-bold'; // Sakit
    case 'I':
      return 'bg-primary text-white fw-bold'; // Izin
    default:
      return 'bg-light text-dark'; // Status tidak ditemukan atau belum diabsen
  }
};

const getButtonClass = (status) => {
  switch (status) {
    case 'P':
      return 'bg-info text-black fw-bold status-btn info-btn'; // Hadir
    case 'A':
      return 'bg-danger text-black fw-bold status-btn danger-btn'; // Absen
    case 'S':
      return 'bg-warning text-black fw-bold status-btn warning-btn'; // Sakit
    case 'I':
      return 'bg-primary text-black fw-bold status-btn primary-btn'; // Izin
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
.spanan {
  text-align: center;
  color: #000;
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
                <td>{{ student.name }}</td>
                <td
                  v-for="(date, index) in totalDaysInMonth"
                  :key="
                    'attendance-' +
                    student.id +
                    '-' +
                    formattedDate(new Date(currentYear, currentMonth, date))
                  "
                  :class="
                    getAttendanceClass(
                      student.id,
                      formattedDate(new Date(currentYear, currentMonth, date))
                    ) + ' text-center'
                  "
                  @click="
                    handleStatusChange(
                      student.id,
                      formattedDate(new Date(currentYear, currentMonth, date))
                    )
                  "
                >
                  <span class="spanan">
                    {{
                      getAttendanceStatus(
                        student.id,
                        formattedDate(new Date(currentYear, currentMonth, date))
                      )
                    }}
                    <!-- {{ date }} -->
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- flex justify-center mt-4-->
        <div class="flex flex-col items-center mt-4">
          <!-- Help text -->
          <span class="text-sm text-gray-700 dark:text-gray-400">
            Page
            <span class="font-semibold text-gray-900 dark:text-white">{{
              pagination.current_page
            }}</span>
            of
            <span class="font-semibold text-gray-900 dark:text-white">{{
              pagination.last_page
            }}</span>
          </span>

          <div class="inline-flex mt-2 xs:mt-0">
            <!-- Tombol Previous -->
            <button
              @click="handlePageChange(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="flex items-center justify-center px-4 h-10 text-base font-medium text-white bg-gray-800 rounded-s hover:bg-gray-900 disabled:cursor-not-allowed disabled:opacity-50"
            >
              <svg
                class="w-3.5 h-3.5 me-2 rtl:rotate-180"
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 14 10"
              >
                <path
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M13 5H1m0 0 4 4M1 5l4-4"
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
                aria-hidden="true"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 14 10"
              >
                <path
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M1 5h12m0 0L9 1m4 4L9 9"
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
                >Buku Penghubung</span
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
                  href="#"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Tambah Buku Penghubung</a
                >
              </li>
              <li>
                <a
                  href="bukuPenghubung1"
                  class="flex items-center p-2 pl-11 w-full text-base font-medium text-gray-900 rounded-lg transition duration-75 group hover:bg-gray-100 dark:text-white dark:hover:bg-gray-700"
                  >Tampilkan Buku Penghubung</a
                >
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </aside>
  </div>
</template>
