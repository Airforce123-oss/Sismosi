<script setup>
import { Link, Head, useForm, usePage, router } from '@inertiajs/vue3';
import {
  ref,
  watch,
  computed,
  onMounted,
  toRaw,
  nextTick,
  reactive,
  watchEffect,
} from 'vue';
import axios from 'axios';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { throttle } from 'lodash';
import { initFlowbite } from 'flowbite';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';
const selectedClassId = ref(null);
const isModalVisible = ref(false);
const showAbsensiModal = (teacherId, date) => {
  // Tambahkan logic untuk membuka modal sesuai dengan teacherId dan date
  selectedTeacherId.value = teacherId;
  selectedDate.value = date;
  isModalVisible.value = true;
};
const selectedTeacherId = ref(null);
const today = new Date();
const selectedDate = ref(today.toISOString().split('T')[0]);
const statuses = ref(['P', 'A', 'S', 'I']);
const customStatus = ref('');

const { props } = usePage();
/*
defineProps({
  attendanceRecords: {
    type: Array,
    default: () => [],
  },
});
 */

const cleanAttendanceRecords = (records) => {
  const map = {};

  records
    .filter((record) => record.tanggal_kehadiran) // hanya data absensi guru
    .forEach((record, index) => {
      console.log(`📌 [Record ${index + 1}]`, record);

      if (!record.teacher_id) {
        console.warn(`❌ Record tidak valid untuk absensi guru:`, record);
      }

      const teacherId = record.teacher_id;
      const dateObj = new Date(record.tanggal_kehadiran);
      const day = dateObj.getDate(); // Ambil tanggal sebagai angka (1–31)

      if (!map[teacherId]) {
        map[teacherId] = {
          teacher_id: teacherId,
          attendance: {},
        };
      }

      map[teacherId].attendance[day] = record.status || 'Belum Diisi';
    });

  return Object.values(map);
};

const attendanceRecords = computed(() => {
  const records = props.attendance || []; // Pastikan props.attendance ada

  const finalRecords = Array.isArray(records) ? records : records[''] || [];

  //console.log('Isi attendanceRecords:', finalRecords);

  return finalRecords;
});

//console.log('Props Attendance:', props.attendance);
//console.log('Tipe Data:', typeof props.attendance);

if (!Array.isArray(attendanceRecords.value)) {
  console.warn(
    'attendanceRecords is not an array. Initializing as an empty array.'
  );
  attendanceRecords.value = [];
}

// Fungsi untuk mengambil dan memvalidasi data dari localStorage
const fetchAttendanceRecords = () => {
  try {
    const data = localStorage.getItem('attendanceRecords');
    if (data) {
      let parsedData;
      try {
        parsedData = JSON.parse(data);
      } catch (error) {
        console.error('❌ Gagal parsing JSON dari localStorage:', error);
        return;
      }

      if (!Array.isArray(parsedData)) {
        console.warn('❌ Parsed data bukan array:', parsedData);
        return;
      }

      //console.log('📦 Parsed data dari localStorage:', parsedData);

      const validRecords = parsedData.filter((record, index) => {
        // Pastikan record adalah object
        if (typeof record !== 'object' || record === null) {
          console.warn(`❌ [Record ${index}] Bukan object:`, record);
          return false;
        }

        // Validasi: Harus punya teacher_id
        if (typeof record.teacher_id !== 'number' || record.teacher_id <= 0) {
          console.warn(
            `❌ [Record ${index}] teacher_id tidak valid atau tidak ada:`,
            record
          );
          return false;
        }

        // Validasi: attendance_date wajib ada & valid
        if (
          typeof record.attendance_date !== 'string' ||
          record.attendance_date.trim() === '' ||
          isNaN(Date.parse(record.attendance_date))
        ) {
          console.warn(
            `❌ [Record ${index}] attendance_date tidak valid:`,
            record
          );
          return false;
        }

        // Validasi: status harus salah satu dari daftar
        const validStatuses = ['P', 'A', 'S', 'I', 'Belum diabsen', 'Unknown'];
        if (
          typeof record.status !== 'string' ||
          !validStatuses.includes(record.status)
        ) {
          console.warn(`❌ [Record ${index}] status tidak valid:`, record);
          return false;
        }

        // Debug jika semua valid
        //console.log(`✅ [Record ${index}] Data absensi valid:`, record);
        return true;
      });

      attendanceRecords.value = validRecords;
    } else {
      console.warn('⚠️ Tidak ada data attendanceRecords di localStorage.');
    }
  } catch (error) {
    console.error('❌ Error saat akses localStorage:', error);
  }
};

// Debugging: Tampilkan hasil akhir inisialisasi
//console.log('Initialized attendanceRecords:', attendanceRecords.value);

function addFallbackStatus(record) {
  if (!record || typeof record !== 'object') {
    console.warn('Invalid record passed to addFallbackStatus:', record);
    return {
      teacher_id: null,
      attendance_date: null,
      status: 'Unknown',
    };
  }

  if (!record.status) {
    console.warn('Empty status:', record);
    record.status = 'Unknown'; // Atau set nilai default lain jika kosong.
  } else if (
    typeof record.status !== 'string' ||
    !['P', 'A', 'S', 'I'].includes(record.status)
  ) {
    console.warn('Invalid status:', record.status);
    record.status = 'Unknown'; // Jika status tidak valid, set ke default.
  }

  return record;
}

// Logic untuk menampilkan record valid ke dalam tabel

const filteredStatuses = computed(() => {
  if (!Array.isArray(statuses.value)) {
    console.warn('Statuses is not an array:', statuses.value);
    return [];
  }
  return statuses.value.filter((status) => status !== 'Belum diabsen');
});
//console.log('Filtered Statuses:', filteredStatuses.value);

function checkDataConsistency(newRecords, storedRecords) {
  if (!deepEqual(newRecords, storedRecords)) {
    console.error('Mismatch detected!');
    return false;
  }
  console.log('Data is consistent.');
  return true;
}

const processedRecords = computed(() => {
  if (!filteredStatuses || !filteredStatuses.value) {
    console.warn('filteredStatuses belum siap!');
    return [];
  }

  // Bersihkan attendanceRecords sebelum diproses
  const sanitizedRecords = cleanAttendanceRecords(attendanceRecords.value);

  //console.log('Sanitized Records:', sanitizedRecords);

  return sanitizedRecords.map((record) => {
    if (!record || typeof record !== 'object') {
      console.warn('Invalid record detected:', record);
      return {
        teacher_id: null,
        attendance_date: null,
        status: 'Unknown',
      };
    }

    // Tambahkan fallback status ke setiap record
    const recordWithFallback = addFallbackStatus(record);

    console.log('Checking record:', recordWithFallback);
    console.log('Record status:', recordWithFallback.status);
    console.log(
      'FilteredStatuses includes record status:',
      filteredStatuses.value.includes(recordWithFallback.status)
    );

    if (filteredStatuses.value.includes(recordWithFallback.status)) {
      console.log(
        'Record status found in filteredStatuses:',
        recordWithFallback.status
      );
      return {
        teacher_id: recordWithFallback.teacher_id,
        attendance_date: recordWithFallback.attendance_date,
        status: recordWithFallback.status,
      };
    } else {
      console.log(
        'Record status not found in filteredStatuses:',
        recordWithFallback.status
      );
      return {
        teacher_id: recordWithFallback.teacher_id,
        attendance_date: recordWithFallback.attendance_date,
        status: 'Unknown',
      };
    }
  });
});

//console.log('Processed Records:', processedRecords.value);

/*
const processedRecords = computed(() => {
    return attendanceRecords.value; // Menggunakan nilai valid yang sudah difilter
});
*/
function deepEqual(a, b) {
  return JSON.stringify(a) === JSON.stringify(b);
}

const { teachers } = usePage().props;
const selectedTeacherStatuses = ref({});
const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 5,
  total: 0,
});
const isLoading = ref(false);
if (!teachers) {
  console.error('Teacher data is missing in props.');
} else if (Array.isArray(teachers)) {
  const rawTeachers = teachers; // Jangan reactive
  if (rawTeachers.length > 0) {
    //console.log('Teacher found:', rawTeachers);
    rawTeachers.forEach((teacher) => {
      if (teacher && teacher.id !== undefined && !isNaN(teacher.id)) {
        //console.log(`Teacher ID: ${teacher.id}, Name: ${teacher.name}`);
      } else {
        console.warn('Invalid teacher.id:', teacher.id);
      }
    });
  } else {
    console.error('Teacher data is empty.');
  }
} else {
  console.error('Teacher data is not an array:', teachers);
}

// Computed property untuk memfilter atau memproses data absensi
const attendanceSummary = computed(() => {
  if (props.attendance && props.attendance.length > 0) {
    console.log('Attendance data is available.');
    return props.attendance.map((record) => ({
      teacherId: record.teacher_id,
      date: record.attendance_date,
      status: record.status,
    }));
  } else {
    //console.warn("Attendance data is not yet available.");
    return [];
  }
});

const emit = defineEmits(['update:attendance']);

const fetchTeachers = async (page = 1) => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/teachers', {
      params: { page, per_page: pagination.value.per_page },
    });
    // Misal response bentuknya seperti ini:
    // { data: [...], pagination: { current_page, last_page, total } }
    teachersList.value = response.data.data;

    pagination.value.current_page = response.data.current_page;
    pagination.value.last_page = response.data.last_page;
    pagination.value.total = response.data.total;
  } catch (error) {
    console.error('Gagal memuat data guru:', error);
  } finally {
    isLoading.value = false;
  }
};

const getAllTeachers = async () => {
  let currentPageUrl = 'http://127.0.0.1:8000/api/teachers?page=1';
  let allTeachers = [];

  while (currentPageUrl) {
    try {
      const response = await axios.get(currentPageUrl);

      //console.log('Response data:', response.data);

      allTeachers = [...allTeachers, ...response.data.data];

      currentPageUrl = response.data.next_page_url;
    } catch (error) {
      console.error('Error mengambil data guru:', error);
      break;
    }
  }

  teachers.value = allTeachers;

  allTeachers.forEach((teacher) => {
    selectedTeacherStatuses.value[teacher.id] ??= {
      status_kehadiran: 'Belum diabsen',
    };
  });
};

// Panggil saat dibutuhkan (atau gunakan onMounted)
getAllTeachers();

const updateAttendance = (attendance) => {
  console.log('Updating attendance records:', attendance);

  // Emit perubahan ke parent (opsional)
  emit('update:attendance', attendance);
};

const isAttendanceDataSent = ref(false);
const isAlertVisible = ref(false);

const handlePageChange = (page) => {
  // Menampilkan log halaman yang dipilih
  console.log('Tombol pagination ditekan, halaman sekarang:', page);

  // Mengupdate halaman saat ini
  if (page >= 1 && page <= pagination.value.last_page) {
    pagination.value.current_page = page;

    // Mengatur ulang status pengiriman data kehadiran
    if (isAttendanceDataSent.value) {
      isAttendanceDataSent.value = false;
    }

    // Memanggil fungsi untuk mengambil data siswa sesuai halaman
    fetchTeachers(page);

    // Menyembunyikan alert setelah perubahan halaman
    isAlertVisible.value = false;
  }
};

// Fungsi untuk mengambil data berdasarkan halaman
const fetchPageData = async (page) => {
  try {
    console.log(`Fetching data for page ${page}...`);
    // Simulasikan API atau sumber data lain
    const response = await mockFetchData(page, pagination.value.per_page);
    const { current_page, last_page, per_page, total } = response.pagination;

    // Perbarui data pagination
    pagination.value = {
      current_page,
      last_page,
      per_page,
      total,
    };

    console.log('Updated pagination:', pagination.value);
  } catch (error) {
    console.error('Error fetching page data:', error);
  }
};

const students = ref([]); // <- tempat nyimpan data siswa
const teachersList = ref(teachers);

const fetchStudents = async (page = 1) => {
  isLoading.value = true;
  try {
    const result = await mockFetchData(page, pagination.value.per_page);
    students.value = result.data;
    pagination.value = {
      ...pagination.value,
      current_page: result.pagination.current_page,
      last_page: result.pagination.last_page,
      total: result.pagination.total,
    };
  } catch (error) {
    console.error('Gagal memuat data siswa:', error);
  } finally {
    isLoading.value = false;
  }
};
const mockFetchData = async (page, perPage) => {
  return new Promise((resolve) => {
    setTimeout(() => {
      const totalItems = 50; // Total data
      const lastPage = Math.ceil(totalItems / perPage);
      const data = Array.from(
        { length: perPage },
        (_, i) => `Item ${(page - 1) * perPage + i + 1}`
      );

      resolve({
        data,
        pagination: {
          current_page: page,
          last_page: lastPage,
          per_page: perPage,
          total: totalItems,
        },
      });
    }, 500);
  });
};

watch(
  () => props.attendance,
  (newAttendance) => {
    // Coba ambil array pertamanya jika datanya berbentuk objek
    let recordsArray = [];

    if (Array.isArray(newAttendance)) {
      recordsArray = newAttendance;
    } else if (typeof newAttendance === 'object' && newAttendance !== null) {
      // Ambil array dari key pertama (misal: attendance = {1: [...]})
      const firstKey = Object.keys(newAttendance)[0];
      if (Array.isArray(newAttendance[firstKey])) {
        recordsArray = newAttendance[firstKey];
      } else {
        console.warn(
          'Attendance key does not contain an array:',
          newAttendance[firstKey]
        );
      }
    } else {
      console.error('Invalid attendance data received:', newAttendance);
    }

    const sanitizedRecords = cleanAttendanceRecords(recordsArray);
    attendanceRecords.value = sanitizedRecords;
  },
  { immediate: true }
);

watch(
  () => props.attendanceRecords,
  (newRecords) => {
    // Log mentah dari props.attendanceRecords
    //   console.log(  '📦 props.attendanceRecords:',   JSON.parse(JSON.stringify(newRecords))    );

    const rawRecords = toRaw(newRecords);

    if (rawRecords && typeof rawRecords === 'object') {
      const cleanedRecords = Object.keys(rawRecords).map((key) => {
        const record = rawRecords[key];
        if (record && typeof record === 'object') {
          return { ...record, status: record[''] || 'Belum Absen' };
        }
        return record;
      });

      attendanceRecords.value = cleanAttendanceRecords(cleanedRecords);
    } else {
      console.error('Invalid attendance data received:', rawRecords);
      attendanceRecords.value = [];
    }
  },
  { immediate: true, deep: true }
);

// Debug computed data
//console.log('Computed Attendance Summary:', attendanceSummary.value);
//console.log('Attendance records from props:', toRaw(attendanceRecords.value));

const loadAttendanceRecords = async () => {
  if (attendanceRecords.value && attendanceRecords.value.length > 0) {
    //console.log(
    //  "Attendance records are already loaded:",
    //attendanceRecords.value
    //);

    // Validasi dan inisialisasi props.attendanceRecords sebagai array reaktif
    props.attendanceRecords = props.attendanceRecords || reactive([]);

    // Pastikan semua status dalam props.attendance memiliki nilai yang valid
    props.attendance = props.attendance.map((record) => ({
      ...record,
      status: record.status || 'Unknown', // Beri default jika kosong
    }));

    console.log('Type of attendanceRecords:', typeof attendanceRecords.value);
    //console.log('Attendance Records:', attendanceRecords.value);
    console.log('Raw Attendance Records:', toRaw(attendanceRecords.value));

    attendanceRecords.value.forEach((record) => {
      console.log(
        'attendanceRecords.find():',
        attendanceRecords.find(
          (r) =>
            formattedDate(new Date(r.attendance_date)) ===
              formattedDate(new Date(currentYear, currentMonth, date)) &&
            r.teacher_id === record.teacher_id
        )
      );
      console.log('attendanceRecords:', attendanceRecords);

      try {
        const formatted = formattedDate(new Date(record.attendance_date));
        if (formatted) {
          console.log('Record Date:', formatted);
        } else {
          console.warn('Invalid Date Detected:', record.attendance_date);
        }
      } catch (error) {
        console.error('Error during formattedDate:', error);
      }

      console.log('Teacher ID:', record.teacher_id);
      console.log('Status:', record.status);
    });

    console.log('Final attendanceRecords:', props.attendanceRecords);
  } else {
    console.error('No attendance records found in props.');
    props.attendanceRecords = reactive([]);
  }
};

const normalizeDate = (date) => {
  const normalizedDate = new Date(date);
  const year = normalizedDate.getFullYear();
  const month = String(normalizedDate.getMonth() + 1).padStart(2, '0');
  const day = String(normalizedDate.getDate()).padStart(2, '0');

  return `${year}-${month}-${day}`; // Returns a string in 'yyyy-mm-dd' format
};

const handleTeacherStatusChange = async (id, date) => {
  console.log('Menangani status perubahan untuk guru:', id);
  const formattedDateValue = formattedDate(new Date(date));
  console.log('Formatted Date:', formattedDateValue);

  if (!props.attendanceRecords || props.attendanceRecords.length === 0) {
    console.warn('Data absensi belum tersedia. Menunggu data...');
    await loadAttendanceRecords();

    if (!props.attendanceRecords || props.attendanceRecords.length === 0) {
      console.error('attendanceRecords is empty or not loaded yet');
      alert('Data absensi tidak ditemukan. Silakan coba lagi.');
      return;
    }
  }

  // Cari record berdasarkan teacher_id
  const recordToUpdate = props.attendanceRecords.find(
    (record) =>
      Number(record.teacher_id) === Number(id) &&
      normalizeDate(record.attendance_date) === formattedDateValue
  );

  console.log('Record to update:', recordToUpdate);

  if (recordToUpdate) {
    // Pastikan attendance ada
    if (
      !recordToUpdate.attendance ||
      typeof recordToUpdate.attendance !== 'object'
    ) {
      recordToUpdate.attendance = {};
    }

    // Update attendance berdasarkan tanggal
    recordToUpdate.attendance[formattedDateValue] = customStatus.value;
    console.log('Updated attendance for existing record:', recordToUpdate);
  } else {
    // Jika belum ada record untuk teacher_id, buat baru
    const newRecord = {
      teacher_id: id, // ID guru
      class_id: selectedClassId.value || 1, // ID kelas (gunakan nilai default jika belum ada)
      attendance: {
        [formattedDateValue]: status || 'Belum diabsen', // Status absensi dengan tanggal
      },
      attendance_date: formattedDateValue, // Pastikan attendance_date ada di sini
      status: status || 'Belum diabsen', // Status default jika tidak ada
    };

    props.attendanceRecords.push(newRecord);
    console.log('Added new attendance record:', newRecord);
    saveAttendanceRecord(newRecord);
  }

  // Simpan ke localStorage
  localStorage.setItem(
    'attendanceRecords',
    JSON.stringify(toRaw(props.attendanceRecords))
  );

  console.log(
    'Attendance records saved to localStorage:',
    props.attendanceRecords
  );

  // Ambil data absensi untuk id dan tanggal
  const attendanceData = props.attendanceRecords.find(
    (item) =>
      Number(item.teacher_id) === Number(id) &&
      item.attendance &&
      item.attendance[formattedDateValue] !== undefined
  );

  console.log('Fetched attendance data:', attendanceData);

  if (attendanceData) {
    const currentStatus = attendanceData.attendance[formattedDateValue];
    console.log('Current status:', currentStatus);

    if (currentStatus && currentStatus !== 'Belum diabsen') {
      handleStatus(currentStatus);
    }

    selectedTeacherId.value = id;
    selectedDate.value = date;
    isModalVisible.value = true;
    customStatus.value = currentStatus || '';
    console.log('Modal updated with selected teacher and date.');
  } else {
    console.error(
      `Data absensi tidak ditemukan untuk guru ID: ${id} dan tanggal: ${formattedDateValue}`
    );
    alert('Data absensi tidak ditemukan. Silakan coba lagi.');
  }

  console.log(
    'Isi teacherAttendanceData setelah update:',
    teacherAttendanceData.value
  );

  await fetchAttendanceReport();
};

const selectStatus = (status) => {
  console.log(
    `Status selected: ${status}, for Teacher: ${selectedTeacherId.value}, Date: ${selectedDate.value}`
  );

  if (!Array.isArray(attendanceArray)) {
    console.error('Attendance data is not an array or is undefined');
    return;
  }

  if (!selectedTeacherId.value || !selectedDate.value) {
    console.error(
      'Invalid teacher ID or date:',
      selectedTeacherId.value,
      selectedDate.value
    );
    return;
  }

  const attendanceRecord = props.attendance.find(
    (item) =>
      item.teacher_id === selectedTeacherId.value &&
      item.attendance_date === selectedDate.value
  );

  if (attendanceRecord) {
    attendanceRecord.status = status;
    console.log('Attendance updated:', attendanceRecord);
  } else {
    props.attendance.push({
      teacher_id: selectedTeacherId.value,
      attendance_date: selectedDate.value,
      status,
    });
    console.log('New attendance record added:', {
      teacher_id: selectedTeacherId.value,
      attendance_date: selectedDate.value,
      status,
    });
  }

  // Panggil updateAttendance untuk menyimpan perubahan
  updateAttendance(props.attendance);

  // Tutup modal dan reset status
  isModalVisible.value = false;
  customStatus.value = '';
};

const closeModal = () => {
  isModalVisible.value = false;
  selectedTeacherId.value = null;
  selectedDate.value = null;
  customStatus.value = '';
};

const { classes, attendance } = usePage().props;

const selectedClass = classes[0];

const getButtonClass = (status) => {
  switch (status) {
    case 'P':
      return 'bg-info text-black status-btn info-btn'; // Kelas untuk hadir
    case 'A':
      return 'bg-danger text-black status-btn danger-btn'; // Kelas untuk alpa
    case 'S':
      return 'bg-warning text-black status-btn warning-btn'; // Kelas untuk sakit
    case 'I':
      return 'bg-primary text-black status-btn primary-btn'; // Kelas untuk izin
    default:
      return 'bg-light text-dark status-btn light-btn'; // Default class
  }
};

const getDateRange = (startDate, endDate) => {
  const start = new Date(startDate);
  const end = new Date(endDate);
  const dates = [];

  while (start <= end) {
    dates.push(start.toISOString().split('T')[0]); // Format YYYY-MM-DD
    start.setDate(start.getDate() + 1);
  }

  return dates;
};

// Properti yang digunakan dalam template

const sanitizedTeachers = computed(() => {
  if (!teachers || !Array.isArray(teachers)) {
    console.warn('Teachers data is not valid or unavailable.');
    return [];
  }

  // Tambahkan validasi jika diperlukan
  return teachers.filter(
    (teacher) =>
      teacher &&
      typeof teacher.id === 'number' &&
      typeof teacher.name === 'string'
  );
});

//const paginatedTeachers = ref(teachers || []);
const paginatedTeachers = computed(() => {
  if (!teachers || !Array.isArray(teachers)) {
    console.warn('Teachers data is not available or invalid.');
    return [];
  }
  const start = (currentPage.value - 1) * itemsPerPage;
  return teachers.slice(start, start + itemsPerPage);
});

const totalPages = ref(1);
const currentPage = ref(1);
const itemsPerPage = 5;
const rawTeachers = toRaw(paginatedTeachers.value);

// 📆 Ambil tanggal hari ini sekali saat komponen dimuat
const formattedToday = today.toISOString().split('T')[0]; // aman 'YYYY-MM-DD'
selectedDate.value = formattedToday;

// 🎯 Ambil bulan sekarang (1–12)
const currentMonth = ref(today.getMonth());
//console.log('🔍 currentMonth.value:', currentMonth.value);

// 🎯 Ambil tahun sekarang
const currentYear = ref(today.getFullYear());

// 🎯 Ambil tanggal sekarang (1–31)
const currentDate = ref(today.getDate());

// 📅 currentMonthYear secara dinamis (real-time setiap halaman dimuat)
const currentMonthYear = computed(() => {
  return `${currentYear.value}-${String(currentMonth.value).padStart(2, '0')}`;
});

const monthNames = [
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

const monthForDisplay = computed(() => {
  if (
    currentMonth.value !== null &&
    currentMonth.value >= 0 &&
    currentMonth.value <= 11
  ) {
    return monthNames[currentMonth.value];
  }
  return '-';
});

// totalDaysInMonth tetap statis
//const totalDaysInMonth = Array.from({ length: 31 }, (_, i) => i + 1);
const totalDaysInMonth = computed(() => {
  const year = currentYear.value;
  const month = currentMonth.value;
  const daysInMonth = new Date(year, month + 1, 0).getDate();
  return Array.from({ length: daysInMonth }, (_, i) => i + 1);
});

const teacherAttendanceData = ref([]);

watch(
  () => teacherAttendanceData.value,
  (newVal) => {
    console.log('🧾 teacherAttendanceData:', toRaw(newVal));
  },
  { deep: true }
);

watch(
  () => teacherAttendanceData.value,
  (newVal) => {
    console.log('🧾 teacherAttendanceData:', toRaw(newVal));
  },
  { deep: true }
);

// Debug akhir (setelah komponen ter-render)
console.log('📅 currentMonthYear:', currentMonthYear.value);
console.log('📅 currentDate:', currentDate.value);

// 🔍 Logging
console.log('📅 currentMonthYear:', currentMonthYear.value);
console.log('📅 currentDate:', currentDate.value);

const changePage = (direction) => {
  let newPage = currentPage.value;

  console.log('changePage called');
  console.log('Current Page in changePage:', currentPage.value); // Debug

  if (direction === 'prev' && currentPage.value > 1) {
    newPage -= 1;
  } else if (
    direction === 'next' &&
    currentPage.value < pagination.value.last_page
  ) {
    newPage += 1;
  }

  console.log('New Page in changePage:', newPage); // Debug

  // Ambil teacherId dan pastikan attendanceDate ada sebelum memanggil fetchAttendanceData
  const teacherId = 1;
  const classId = selectedClass ? selectedClass.id : 1; // Ambil ID dari selectedClass, atau nilai default 1 jika null

  // Ambil tanggal hari ini
  const attendanceDate = new Date().toISOString().split('T')[0];
  console.log('Attendance Date in fetchAttendanceData:', attendanceDate); // Debug

  // Pastikan attendanceDate sudah terisi sebelum memanggil fetchAttendanceData
  console.log('Before calling fetchAttendanceData');

  // Cek apakah nilai yang dikirimkan ke fetchAttendanceData valid
  console.log('Teacher ID before fetchAttendanceData:', teacherId);
  console.log('Attendance Date before fetchAttendanceData:', attendanceDate);

  fetchAttendanceData(teacherId, attendanceDate, newPage); // Fetch data untuk halaman baru
};

// Fungsi untuk mengambil data absensi guru
const attendanceMessage = ref('');
const localAttendanceRecords = ref([]);

watch(
  localAttendanceRecords,
  (newRecords) => {
    saveToLocalStorage();
    //console.log('Data saved to localStorage:', newRecords);
  },
  { deep: true }
);

watch(
  attendanceRecords,
  (newVal, oldVal) => {
    console.log('Attendance records changed:', {
      old: oldVal,
      new: newVal,
    });
    // Simpan perubahan ke localStorage
    localStorage.setItem('attendanceRecords', JSON.stringify(toRaw(newVal)));
  },
  { deep: true }
);

const handleAttendance = async (status) => {
  console.log(`Status selected: ${status}`);

  if (!['P', 'A', 'S', 'I'].includes(status)) {
    alert('Status tidak valid!');
    return;
  }

  // Pastikan selectedDate.value adalah objek Date, dan formatkan menjadi Y-m-d (ISO string)
  const dateObj = new Date(selectedDate.value);
  const formattedDate = dateObj.toLocaleDateString('en-CA');

  // 🔍 Tambahan log untuk debugging
  console.log('📍 User memilih tanggal:', selectedDate.value);
  console.log('🕓 Date sekarang (new Date()):', new Date());
  console.log('🧠 Diformat ke:', new Date(selectedDate.value).toISOString());

  console.log('🎯 selectedDate.value:', selectedDate.value);
  console.log('📅 Date object:', dateObj);
  console.log('✅ Formatted date (YYYY-MM-DD):', formattedDate);

  const newRecord = {
    teacher_id: selectedTeacherId.value,
    class_id: selectedClassId.value, // pastikan selectedClassId tersedia
    attendance_date: formattedDate, // Tanggal yang sudah diformat
    status: status,
  };

  console.log('🔄 Mengirim data ke backend:', newRecord);

  try {
    const response = await axios.post('/api/attendance', newRecord);
    console.log('✅ Tersimpan:', response.data);

    // Update lokal
    const index = attendanceRecords.value.findIndex(
      (r) =>
        r.teacher_id === newRecord.teacher_id &&
        r.attendance_date === newRecord.attendance_date
    );

    if (index !== -1) {
      attendanceRecords.value[index].status = status;
    } else {
      attendanceRecords.value.push(newRecord);
    }

    // 💾 Simpan ke localStorage setelah update
    localStorage.setItem(
      'attendanceRecords',
      JSON.stringify(attendanceRecords.value)
    );
    //console.log('📦 Data disimpan ke localStorage.');
  } catch (error) {
    console.error('❌ Gagal menyimpan:', error);
    alert('Gagal menyimpan data kehadiran!');
  }

  isModalVisible.value = false; // Tutup modal setelah submit
};

const displayAttendanceStatus = (date) => {
  // Validasi awal: Pastikan attendanceRecords adalah array
  if (!Array.isArray(attendanceRecords)) {
    console.warn('attendanceRecords is not an array.');
    return 'Belum diabsen';
  }

  // Log untuk membantu debugging
  console.log('Attendance records:', attendanceRecords);

  // Format tanggal yang diberikan untuk pencocokan
  const formattedDateValue = formattedDate(new Date(date));

  // Cari catatan absensi untuk tanggal tertentu
  const attendanceRecord = attendanceRecords.find(
    (record) =>
      formattedDate(new Date(record.attendance_date)) === formattedDateValue
  );

  if (!attendanceRecord) {
    console.log(`No attendance record found for date: ${formattedDateValue}`);
    return 'Belum diabsen';
  }

  // Kembalikan status kehadiran
  return attendanceRecord.status || 'Belum diabsen';
};

const getBackendMonth = (jsMonth) => String(jsMonth + 1).padStart(2, '0');

const getAttendanceByTeacherId = (id) => {
  //console.log('Looking for ID:', id);
  const result = teacherAttendanceData.value.find((t) => {
    //console.log('Comparing with:', t.teacher_id); // pastikan teacher_id ada di sini
    return t.teacher_id && Number(t.teacher_id) === Number(id);
  });

  console.log('Result found:', result);
  return result || null;
};

const fetchAttendanceReport = async () => {
  try {
    console.log('✅ Nilai sebelum pengecekan:', {
      currentMonth: currentMonth.value,
      currentYear: currentYear.value,
    });

    if (currentMonth.value === null || currentYear.value === null) {
      console.warn('⚠️ Bulan atau tahun belum dipilih.');
      return;
    }

    const backendMonth = getBackendMonth(currentMonth.value);
    console.log('🔍 Final params yang dikirim ke backend:', {
      month: backendMonth,
      year: currentYear.value,
    });

    const response = await axios.get('/api/teacher-attendance-report', {
      params: {
        month: backendMonth,
        year: currentYear.value,
      },
    });

    console.log('Struktur data teacher dari API:', response.data);

    if (Array.isArray(response.data)) {
      teacherAttendanceData.value = response.data.map((teacher) => ({
        teacher_id: teacher.teacher_id,
        name: teacher.name,
        attendance: teacher.attendance ?? {},
      }));
      //console.log('📥 Data laporan guru:', teacherAttendanceData.value);
    } else {
      teacherAttendanceData.value = [];
      console.warn('⚠️ Data laporan tidak dalam format array:', response.data);
    }

    teacherAttendanceData.value.forEach((teacher) => {
      const attendance = teacher.attendance;
      //console.log('👤 Attendance data:', teacher.name, attendance);
      Object.keys(attendance).forEach((date) => {
        const status = attendance[date] ?? 'Belum Diisi';
        //console.log(`👤 ${teacher.name} - ${date}: ${status}`);
      });
    });
  } catch (error) {
    console.error(
      '❌ Gagal mengambil laporan absensi:',
      error?.response?.data || error.message
    );
  }
};

const status = (teacher, day) => {
  const date = `${currentYear.value}-${String(currentMonth.value + 1).padStart(
    2,
    '0'
  )}-${String(day).padStart(2, '0')}`;

  const status = teacher.attendance?.[date];

  // Anggap "Belum Absen" itu kosong
  if (!status || status === 'Belum Absen') return null;

  return status;
};

onMounted(async () => {
  try {
    fetchTeachers(1);
    console.log('Component mounted, mulai proses load data...');

    fetchAttendanceRecords();
    fetchAttendanceReport();
    initFlowbite();

    // Load dan validasi localStorage
    let stored = loadFromLocalStorage();
    if (typeof stored === 'string') {
      try {
        stored = JSON.parse(stored);
      } catch (e) {
        console.error('Gagal parse localStorage string:', e);
        stored = [];
      }
    }

    if (!Array.isArray(stored)) {
      console.warn(
        'Data localStorage tidak valid, inisialisasi sebagai array kosong.'
      );
      stored = [];
    }

    localAttendanceRecords.value = stored;

    console.log('Sebelum dibersihkan:', toRaw(props.attendance));

    // Inisialisasi props.attendanceRecords
    props.attendanceRecords = [...localAttendanceRecords.value];

    // Buat record baru
    const today = formattedDate(new Date());
    const newRecord = {
      teacher_id: teachers,
      attendance_date: today,
      status: customStatus.value,
    };
    console.log('Record baru yang akan dicek:', newRecord);

    // Cek jika record sudah ada
    const alreadyExists = props.attendanceRecords.some(
      (record) =>
        record.teacher_id === newRecord.teacher_id &&
        record.attendance_date === newRecord.attendance_date
    );

    if (!alreadyExists) {
      props.attendanceRecords.push(newRecord);
      console.log('Record baru ditambahkan:', newRecord);
    } else {
      console.log('Record sudah ada, tidak ditambahkan ulang.');
    }

    // Simpan ke localStorage
    attendanceRecords.value = props.attendanceRecords;
    saveToLocalStorage();
    //console.log('Data disimpan ke localStorage:', attendanceRecords.value);

    // Validasi wali_kelas
    const { wali_kelas } = usePage().props;
    if (!Array.isArray(wali_kelas) || wali_kelas.length === 0) {
      attendanceMessage.value =
        'Data wali kelas tidak ditemukan atau tidak valid.';
      return;
    }
    console.log('Data wali_kelas tervalidasi:', wali_kelas);

    // 🟡 Tambahkan pemanggilan API untuk jadwal
    const kelas_id = props.kelas_id || 1; // atau sesuaikan sumber kelas_id
    const res = await axios.get(`/jadwal?kelas_id=${kelas_id}`);
    schedule.value = res.data;
    console.log('Jadwal berhasil diambil:', schedule.value);

    // Set status kehadiran
    attendanceStatus.value = displayAttendanceStatus(date);
    console.log('Status kehadiran guru:', attendanceStatus.value);

    fetchStudents(pagination.value.current_page);
  } catch (error) {
    console.error('Terjadi error saat onMounted:', error);
    attendanceMessage.value = 'Terjadi kesalahan saat memuat data.';
  }
});

console.log('Pre-validation currentMonth:', currentMonth);

// Fungsi untuk validasi tanggal
const isValidDate = (year, month, day) => {
  // Validasi tahun, bulan, dan tanggal
  return (
    typeof year === 'number' &&
    typeof month === 'number' &&
    typeof day === 'number' &&
    month >= 0 &&
    month <= 11 &&
    day > 0 &&
    day <= new Date(year, month + 1, 0).getDate()
  );
};

const formattedDate = (date) => {
  if (!date) return '';
  const year = date.getFullYear();
  const month = (date.getMonth() + 1).toString().padStart(2, '0');
  const day = date.getDate().toString().padStart(2, '0');
  return `${year}-${month}-${day}`;
};

// Debugging code where `teacher` might not be defined
//console.log('Checking if teacher is defined:', teachers);
//console.log('Debug formattedDate:', formattedDate);

// Fungsi untuk memuat data dari localStorage
const loadFromLocalStorage = () => {
  try {
    const data = localStorage.getItem('attendanceRecords');

    //console.log('Data from localStorage:', data ? JSON.parse(data) : data); // Tambahkan log disini

    if (!data) {
      console.info('No data found in localStorage.');
      return [];
    }

    let parsedRecords;
    try {
      parsedRecords = JSON.parse(data);

      // Validasi apakah parsedRecords adalah array of objects
      if (
        !Array.isArray(parsedRecords) ||
        !parsedRecords.every(
          (record) =>
            typeof record === 'object' &&
            record !== null &&
            typeof record.teacher_id === 'number' &&
            typeof record.attendance_date === 'string' &&
            !isNaN(Date.parse(record.attendance_date)) &&
            typeof record.status === 'string' &&
            ['P', 'A', 'S', 'I'].includes(record.status)
        )
      ) {
        console.warn(
          'Data from localStorage is not in the expected format. Clearing invalid data.'
        );
        localStorage.removeItem('attendanceRecords'); // Hapus data tidak valid
        return [];
      }

      return parsedRecords.map((record) => ({
        id: record.teacher_id,
        name: 'Teacher ' + record.teacher_id, // Tambahkan log disini
        status: record.status,
      }));
    } catch (error) {
      console.warn('Error parsing data from localStorage:', error);
      localStorage.removeItem('attendanceRecords'); // Hapus data tidak valid
      return [];
    }
  } catch (error) {
    console.error('Error loading data from localStorage:', error);
    return [];
  }
};

function isObject(obj) {
  return obj !== null && typeof obj === 'object';
}

function isArray(obj) {
  return obj !== null && Array.isArray(obj);
}

function isProxy(obj) {
  return (
    obj !== null &&
    obj.hasOwnProperty('__proxy') &&
    obj.hasOwnProperty('__proxy_type')
  );
}

function validateTeacherId(teacherId) {
  if (
    !teacherId ||
    !isObject(teacherId) ||
    !isArray(teacherId) ||
    teacherId.length === 0
  ) {
    return false;
  }
  for (let i = 0; i < teacherId.length; i++) {
    const element = teacherId[i];
    if (!isObject(element)) {
      return false; // not an object
    }
    for (const key in element) {
      if (isProxy(element[key])) {
        if (Object.keys(element).length > 1) {
          return true; // valid proxy array
        } else {
          return false; // invalid proxy array
        }
      }
    }
  }
  return true; // valid array object
}

// Fungsi untuk menyimpan data ke localStorage
const saveToLocalStorage = () => {
  try {
    // Event listener sebelum halaman unload (user meninggalkan halaman)
    window.addEventListener('beforeunload', () => {
      // Set status default jika kosong
      localAttendanceRecords.value = localAttendanceRecords.value.map(
        (record) => ({
          ...record,
          status: record.status || 'Unknown', // Berikan default jika kosong
        })
      );

      const dataToSave = localAttendanceRecords.value.map((record) => {
        return {
          ...record,
          status: record.status !== 'Unknown' ? record.status : 'Unknown', // Simpan status kecuali jika 'Unknown'
        };
      });

      console.log('Data yang akan disimpan ke localStorage:', dataToSave);
      localStorage.setItem(
        'attendanceRecords',
        JSON.stringify(toRaw(dataToSave))
      );
      // Simpan data ke localStorage

      console.log('Data absensi disimpan ke localStorage.');
    });

    // Simpan setiap 5 menit
    setInterval(() => {
      localAttendanceRecords.value = localAttendanceRecords.value.map(
        (record) => ({
          ...record,
          status: record.status || 'Unknown', // Set status default jika kosong
        })
      );

      const dataToSave = localAttendanceRecords.value.map((record) => {
        return {
          ...record,
          status: record.status !== 'Unknown' ? record.status : 'Unknown', // Pastikan status disimpan dengan benar
        };
      });

      console.log('Data yang akan disimpan ke localStorage:', dataToSave);
      localStorage.setItem(
        'attendanceRecords',
        JSON.stringify(toRaw(dataToSave))
      );
      // Simpan data ke localStorage

      console.log('Data absensi disimpan ke localStorage.');
    }, 300000); // Simpan setiap 5 menit
  } catch (error) {
    console.error('Terjadi kesalahan saat menyimpan ke localStorage:', error);
  }
};

const saveAttendanceRecord = async (newRecord) => {
  const rawRecord = toRaw(newRecord);

  if (!rawRecord.teacher_id) {
    console.warn('⛔ teacher_id kosong, tidak bisa menyimpan!');
    return;
  }

  rawRecord.class_id = rawRecord.class_id || 1;

  if (!rawRecord.attendance_date) {
    console.warn('❌ attendance_date tidak ada!', rawRecord);
    return;
  }

  const validStatuses = ['P', 'A', 'S', 'I'];
  if (!validStatuses.includes(rawRecord.status)) {
    // Jangan munculkan warning, cukup keluar diam-diam
    return;
  }

  console.log(
    '✅ Final data yang dikirim:',
    JSON.stringify(rawRecord, null, 2)
  );

  try {
    const response = await axios.post('/api/attendance', rawRecord);
    console.log('✅ Attendance record saved:', response.data);

    props.attendanceRecords.push(response.data.attendance);
  } catch (error) {
    console.error('❌ Error saving attendance record:', error);
    console.log(error.response?.data);
  }
};

const handleStatus = (status) => {
  if (status == null || status === undefined) {
    console.log('Status received is undefined or null');
    return;
  }
  console.log('Status received:', status);
  if (status === 'P') {
    // Lakukan sesuatu jika status hadir
  } else if (status === 'A') {
    // Lakukan sesuatu jika status alpa
  } else {
    console.log('Status is not recognized:', status);
  }
};

const fetchAttendanceData = (teacherId, attendanceDate, page = 1) => {
  if (!attendanceDate) {
    console.error('Attendance Date is required');
    return;
  }
  console.log('Checking teacherId:', teacherId);
  console.log('Checking teacher_id:', teacherId.id);

  const formattedAttendanceDate = new Date(attendanceDate)
    .toISOString()
    .split('T')[0];
  console.log('Checking attendance_date:', attendanceDate); // Tambahan untuk cek nilai attendance_date

  const url = `/api/attendance-teachers?teacher_id=${teacherId}&attendance_date=${formattedAttendanceDate}&page=${page}`;
  console.log('Fetching URL:', url); // Debug untuk memastikan URL yang dipanggil

  axios
    .get(url)
    .then((response) => {
      console.log('Raw API Response:', response.data);

      pagination.value = response.data.pagination || {
        current_page: 1,
        last_page: 1,
      };
      currentPage.value = pagination.value.current_page || 1;
    })
    .catch((error) => {
      console.error(
        'Error fetching attendance data:',
        error.response || error.message
      );
    });
};

// Fungsi untuk mendapatkan nama hari
const getDayName = (date) => {
  const days = [
    'Sunday',
    'Monday',
    'Tuesday',
    'Wednesday',
    'Thursday',
    'Friday',
    'Saturday',
  ];
  return days[new Date(date).getDay()];
};

// Fungsi untuk validasi tanggal (untuk memastikan validitas tanggal)
const getValidDate = (date) => {
  const validDate = new Date(currentYear.value, currentMonth.value, date);
  return isNaN(validDate.getTime()) ? null : validDate;
};

// Fungsi untuk memeriksa apakah hari Minggu
const isSunday = (day) => {
  return new Date(currentYear, currentMonth, day).getDay() === 0;
};

// Fungsi untuk mendapatkan status kehadiran guru
const getTeacherAttendanceStatus = (teacherId, date) => {
  // Cek apakah hari tersebut Sabtu (6) atau Minggu (0)
  const dayOfWeek = new Date(date).getDay();
  if (dayOfWeek === 0 || dayOfWeek === 6) {
    return 'Libur';
  }

  // Cek jika data kehadiran tersedia
  if (attendanceRecords.value && attendanceRecords.value.length > 0) {
    const record = attendanceRecords.value.find((r) => {
      const recordDate = formattedDate(new Date(r.attendance_date));
      return r.teacher_id === teacherId && recordDate === date;
    });

    if (record) {
      return record.status;
    } else {
      return 'Belum diabsen';
    }
  } else {
    return 'Belum diabsen';
  }
};

const isValidAttendanceDate = (date) => {
  return date && !isNaN(Date.parse(date)); // Pastikan nilai bisa dikonversi ke Date
};

const getAttendanceInfo = async (teacher, date) => {
  console.log('Teacher object received:', teacher);
  console.log('Type of teacher:', typeof teacher);

  if (!teacher) {
    console.warn('Teacher data is null or undefined.');
    return 'Belum diabsen';
  }

  const rawTeacher = toRaw(teacher);

  if (!rawTeacher || (Array.isArray(rawTeacher) && rawTeacher.length === 0)) {
    console.warn('Invalid teacher data.');
    return 'Belum diabsen';
  }

  const validTeachers = Array.isArray(rawTeacher)
    ? rawTeacher.filter((t) => t && t.id !== undefined && t.id !== null)
    : [];

  if (validTeachers.length === 0) {
    console.warn('No valid teachers found in data');
    return 'Belum diabsen';
  }

  // Pastikan teacherId dan date tidak null atau undefined
  const teacherId = validTeachers[0]?.id;
  if (!teacherId) {
    console.warn('Teacher ID is null or undefined.');
    return 'Belum diabsen';
  }

  if (!date) {
    console.warn('Date provided is null or undefined:', date);
    return 'Belum diabsen';
  }

  // Validasi attendanceRecords
  if (Array.isArray(attendanceRecords.value)) {
    const validRecords = toRaw(localAttendanceRecords.value).filter(
      (record) => record && record.teacher_id === teacherId
    ); // Filter berdasarkan teacher_id

    if (validRecords.length === 0) {
      console.warn('No valid attendance records found for this teacher.');
      return 'Belum diabsen';
    }

    // Tambahkan log sebelum perhitungan formattedDateInput
    console.log('Before formattedDateInput calculation');
    const formattedDateInput = formattedDate(new Date(date));
    console.log('Formatted date input:', formattedDateInput);

    const matchedRecord = validRecords.find((record) => {
      const recordDateFormatted = formattedDate(
        new Date(record.attendance_date)
      );
      return recordDateFormatted === formattedDateInput;
    });

    if (!matchedRecord) {
      console.warn(
        `No match found for teacher ID: ${teacherId}, date: ${formattedDateInput}`
      );
      return 'Belum diabsen';
    }

    // Tambahkan fallback untuk status kosong
    const status = matchedRecord.status || 'Belum diabsen';
    console.log('Status found:', status);

    return status; // Kembalikan status dari matchedRecord
  } else {
    console.warn('attendanceRecords.value is not an array.');
    return 'Belum diabsen';
  }
};

const attendanceInfo = getAttendanceInfo(teachers, new Date());
console.log(attendanceInfo);

const getAttendanceClass = (teacherId, date) => {
  const status = getTeacherAttendanceStatus(teacherId, date);
  //console.log("Attendance status for teacherId:", teacherId, "is:", status);
  switch (status) {
    case 'P':
      return 'bg-info text-black'; // Hadir
    case 'A':
      return 'bg-danger text-black'; // Absen
    case 'S':
      return 'bg-warning text-black'; // Sakit
    case 'I':
      return 'bg-primary text-black'; // Izin
    case 'Libur':
      return 'bg-blue-100 text-gray-700';
    default:
      return 'bg-light text-dark'; // Status tidak ditemukan atau belum diabsen
  }
};

//console.log('Teacher ID:', teachers);
//console.log('Attendance Records:', processedRecords.value);

//console.log('Attendance Records:', toRaw(attendanceRecords.value));
//console.log("usePage().propss:", usePage().props);
//const rawProps = toRaw(usePage().props);
//console.log("rawProps:", rawProps);

const propsData = usePage().props;

if (propsData) {
  if (propsData.then) {
    // Jika propsData adalah Promise
    propsData
      .then((data) => {
        if (data && data.errors) {
          console.log('Error in propsData:', data.errors);
        } else {
          const rawProps = toRaw(data); // Ambil data asli dari Proxy
          console.log('rawProps:', rawProps);

          if (
            rawProps.attendance &&
            Array.isArray(rawProps.attendance) &&
            rawProps.attendance.length > 0
          ) {
            const filteredRecord = rawProps.attendance.find(
              (record) =>
                formattedDate(new Date(record.attendance_date)) ===
                  formattedDate(new Date(currentYear, currentMonth, date)) &&
                record.teacher_id === teachers.id
            );

            if (filteredRecord) {
              console.log('Filtered Record:', filteredRecord);
            } else {
              console.log('Attendance record not found');
            }
          } else {
            console.log('Attendance records is empty or not an array');
          }
        }
      })
      .catch((error) => {
        console.log('Promise rejected:', error);
      });
  } else {
    // Jika bukan Promise
    const rawProps = toRaw(propsData); // Ambil data asli dari Proxy
    console.log('rawProps:', rawProps);

    if (
      rawProps.attendance &&
      Array.isArray(rawProps.attendance) &&
      rawProps.attendance.length > 0
    ) {
      const filteredRecord = rawProps.attendance.find(
        (record) =>
          formattedDate(new Date(record.attendance_date)) ===
            formattedDate(new Date(currentYear, currentMonth, date)) &&
          record.teacher_id === teachers.id
      );

      if (filteredRecord) {
        console.log('Filtered Record:', filteredRecord);
      } else {
        console.log('Attendance record not found');
      }
    } else {
      //console.log('Attendance records is empty or not an array');
    }
  }
} else {
  console.log('Props data is null or undefined');
}

//nextTick(() => {
//  const attendanceRecords = toRaw(usePage().props.attendance);
//console.log("Attendance records:", attendanceRecords);
//});

if (
  attendanceRecords &&
  Array.isArray(attendanceRecords) &&
  attendanceRecords.length > 0
) {
  const filteredRecord = attendanceRecords.find(
    (record) =>
      formattedDate(new Date(record.attendance_date)) ===
        formattedDate(new Date(currentYear, currentMonth, date)) &&
      record.teacher_id === teachers.id
  );

  if (filteredRecord) {
    console.log('Filtered Record:', filteredRecord);
  } else {
    console.log('No matching record found for today.');
  }
} else {
  console.log('attendanceRecords is not an array or is empty');
}

watch(
  () => attendanceRecords.value,
  (newValue, oldValue) => {
    // Pastikan untuk memeriksa perbedaan data yang lebih dalam
    const rawNewValue = toRaw(newValue); // Ambil data mentah dari reaktif
    const rawOldValue = toRaw(oldValue); // Ambil data mentah dari reaktif

    // Bandingkan apakah data berubah
    if (JSON.stringify(rawNewValue) !== JSON.stringify(rawOldValue)) {
      //console.log('AttendanceRecords updated:', rawOldValue, '->', rawNewValue);

      // Hanya menyimpan ke localStorage jika ada perubahan nyata
      localStorage.setItem(
        'attendanceRecords',
        JSON.stringify(rawNewValue) // Simpan data yang sudah dibersihkan
      );
    }
  },
  { immediate: true } // Jalankan segera setelah watch diaktifkan
);

watch([selectedTeacherId, selectedDate], () => {
  console.log('Selected Teacher ID:', selectedTeacherId.value);
  console.log('Selected Date:', selectedDate.value);
  console.log('Attendance Data:', props.attendance);
});
</script>

<style scoped>
.bg-dark {
  background-color: #343a40;
}

.text-white {
  color: #fff;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
}

.modal-content {
  background-color: white;
  padding: 20px;
  border-radius: 8px;
  width: 300px;
}

.status-options button {
  margin-right: 10px;
}

.close-btn {
  background-color: red;
  color: white;
  padding: 5px 10px;
  margin-top: 10px;
}

/* Media query untuk layar kecil */
@media (max-width: 576px) {
  .badge {
    font-size: 0.9rem; /* Ukuran font badge lebih kecil di layar kecil */
    padding: 0.5rem 1rem; /* Padding proporsional untuk layar kecil */
  }
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
      <form @submit.prevent="submitTeacherAttendance">
        <div class="container py-5">
          <div
            class="sm:flex sm:items-center bg-gradient-to-r from-blue-500 to-purple-500 text-white p-4 rounded-3xl shadow-lg w-full"
          >
            <div class="sm:flex-auto w-4/5 mx-auto">
              <!-- Bagian Judul + Bulan -->
              <div class="sm:flex-auto font-semibold text-center mb-4">
                <h1 class="text-3xl font-semibold text-white">
                  Tabel Absensi Guru
                </h1>
                <p class="text-sm mb-3 font-bold text-yellow-200">
                  Bulan {{ monthForDisplay }} Tahun {{ currentYear }}
                </p>
              </div>

              <!-- Info lainnya bisa ditambahkan di bawah sini -->
              <div>
                <div class="space-y-2">
                  <!-- Tempat konten lain -->
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- Tabel Absensi -->
        <div class="overflow-x-auto max-w-full">
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
              <tr v-for="teacher in teachersList" :key="teacher.id">
                <td class="font-medium">{{ teacher.name }}</td>
                <td
                  v-for="date in totalDaysInMonth"
                  :key="`attendance-${teacher.id}-${formattedDate(
                    new Date(currentYear, currentMonth, date)
                  )}`"
                  :class="
                    getAttendanceClass(
                      teacher.id,
                      formattedDate(new Date(currentYear, currentMonth, date))
                    ) + ' text-center cursor-pointer'
                  "
                  @click="
                    handleTeacherStatusChange(
                      teacher.id,
                      formattedDate(new Date(currentYear, currentMonth, date))
                    )
                  "
                >
                  <span class="block px-1 rounded">
                    {{
                      getTeacherAttendanceStatus(
                        teacher.id,
                        formattedDate(new Date(currentYear, currentMonth, date))
                      )
                    }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div v-if="pagination && pagination.current_page">
          Current Page: {{ pagination.current_page }}
        </div>

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
        <!--   
        <ul class="mt-4 list-disc list-inside text-gray-700 dark:text-gray-300">
          <li v-for="teacher in teachersList" :key="teacher.id">
            {{ teacher.name }} -
            {{
              teacher.mapel
                ? Array.isArray(teacher.mapel)
                  ? teacher.mapel.map((m) => m.name).join(', ')
                  : teacher.mapel.name ?? 'No Subject'
                : 'No Subject'
            }}
          </li>
        </ul>-->

        <!-- Keterangan Status Kehadiran -->
        <div class="row mt-3 me-3">
          <div class="col-12 mb-10">
            <p class="fw-bold fs-5">Status Kehadiran:</p>
            <div class="d-flex flex-wrap align-items-center">
              <div class="me-3 mb-2">
                <span class="badge bg-info text-black">Hadir (P)</span>
              </div>
              <div class="me-3 mb-2">
                <span class="badge bg-danger text-black">Absen (A)</span>
              </div>
              <div class="me-3 mb-2">
                <span class="badge bg-warning text-black">Sakit (S)</span>
              </div>
              <div class="me-3 mb-2">
                <span class="badge bg-primary text-black">Izin (I)</span>
              </div>
              <div class="me-3 mb-2">
                <span class="badge bg-light text-dark">Belum Diabsen</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal Tambah Absensi -->
        <div
          v-if="isModalVisible"
          class="fixed inset-0 bg-black bg-opacity-50 flex justify-center items-center z-50"
          @click.self="closeModal"
        >
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
                <circle
                  cx="12"
                  cy="12"
                  r="10"
                  class="stroke-current"
                  stroke-opacity="0.2"
                  stroke-width="1.5"
                />
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M9 9l6 6m0-6l-6 6"
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
              <h3 class="text-2xl font-bold text-gray-800">
                Pilih Status Kehadiran
              </h3>
              <p class="text-gray-500 text-sm">
                Silakan pilih salah satu status di bawah ini.
              </p>
            </div>

            <!-- @click="handleAttendance(status)"-->
            <!-- Pilihan Status -->
            <div class="space-y-4">
              <button
                v-for="status in statuses"
                :key="status"
                :class="getButtonClass(status)"
                class="w-full py-3 px-5 rounded-lg font-semibold text-black transition-all duration-300"
                @click="handleAttendance(status)"
              >
                {{ status }}
              </button>
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
      </form>
    </main>

    <!-- Sidebar -->
    <SidebarAdmin />
  </div>
</template>
