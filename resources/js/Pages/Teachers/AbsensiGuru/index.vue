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

const selectedClassId = ref(null);
const isModalVisible = ref(false);
const showAbsensiModal = (teacherId, date) => {
  // Tambahkan logic untuk membuka modal sesuai dengan teacherId dan date
  selectedTeacherId.value = teacherId;
  selectedDate.value = date;
  isModalVisible.value = true;
};
const selectedTeacherId = ref(null);
const selectedDate = ref(null);
const statuses = ref(['P', 'A', 'S', 'I']);
const customStatus = ref('');
import isEqual from 'fast-deep-equal/es6';
//const customStatus = "";

const isCustomStatus = computed(
  () => !statuses.value.includes(customStatus.value)
);

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
  // Pastikan records adalah array
  if (!Array.isArray(records)) {
    console.warn('Provided records is not an array:', records);
    return []; // Kembalikan array kosong jika tidak valid
  }

  return records
    .map((record) => {
      const rawRecord = toRaw(record); // Dapatkan nilai asli dari record yang terikat reactivity

      // Jika teacher_id adalah array, ambil nilai id dari elemen pertama, jika ada.
      let teacherId;
      if (Array.isArray(rawRecord.teacher_id)) {
        const rawTeacherArray = toRaw(rawRecord.teacher_id);
        if (
          rawTeacherArray.length > 0 &&
          typeof rawTeacherArray[0] === 'object' &&
          rawTeacherArray[0] !== null &&
          'id' in rawTeacherArray[0]
        ) {
          teacherId = rawTeacherArray[0].id;
        } else {
          teacherId = null;
        }
      } else {
        teacherId = rawRecord.teacher_id;
      }

      if (
        typeof rawRecord === 'object' &&
        rawRecord !== null &&
        typeof teacherId === 'number' && // Pastikan teacherId adalah number
        typeof rawRecord.attendance_date === 'string' &&
        typeof rawRecord.status === 'string' &&
        ['P', 'A', 'S', 'I'].includes(rawRecord.status)
      ) {
        // Lakukan pembersihan atau transformasi pada record
        return {
          teacher_id: teacherId, // Gunakan teacher_id yang telah divalidasi
          attendance_date: rawRecord.attendance_date,
          status: rawRecord.status,
        };
      } else {
        console.warn('Invalid record format:', rawRecord);
        return null; // Kembalikan null untuk record yang tidak valid
      }
    })
    .filter((record) => record !== null); // Hapus record yang null
};

const attendanceRecords = computed(() => {
  const records = props.attendance || []; // Pastikan props.attendance ada
  return Array.isArray(records) ? records : records[''] || []; // Ambil array dari objek jika ada
});

// Temukan data yang sesuai
/*
const foundRecord = props.attendance.find(
  (item) =>
    item.teacher_id === selectedTeacherId.value &&
    item.attendance_date === selectedDate.value
);
if (foundRecord) {
  attendanceRecords.value = attendanceRecords.value.map((record) => {
    if (
      record.teacher_id === foundRecord.teacher_id &&
      record.attendance_date === foundRecord.attendance_date
    ) {
      return foundRecord; // Update record yang sesuai
    }
    return record; // Biarkan record lainnya tidak berubah
  });
}
*/

console.log('Props Attendance:', props.attendance);
console.log('Tipe Data:', typeof props.attendance);

if (!Array.isArray(attendanceRecords.value)) {
  console.warn(
    'attendanceRecords is not an array. Initializing as an empty array.'
  );
  attendanceRecords.value = [];
}

// Fungsi untuk mengambil dan memvalidasi data dari localStorage
const fetchAttendanceRecords = () => {
  try {
    const data = localStorage.getItem('attendanceRecords'); // Ambil data dari localStorage
    if (data) {
      let parsedData;
      try {
        parsedData = JSON.parse(data); // Parsing data JSON
      } catch (error) {
        console.error('Error parsing JSON from localStorage:', error);
        return; // Keluar dari fungsi jika parsing gagal
      }

      // Memeriksa apakah parsedData adalah array
      if (!Array.isArray(parsedData)) {
        console.warn('Parsed data is not an array:', parsedData);
        return; // Keluar dari fungsi jika bukan array
      }

      console.log('Parsed data from localStorage:', parsedData);

      // Validasi setiap record
      const validRecords = parsedData.filter((record) => {
        // Pastikan record adalah objek dan tidak null
        if (typeof record !== 'object' || record === null) {
          console.warn('Invalid record format:', record);
          return false; // Skip record jika tidak valid
        }

        // Ambil id
        let id = record.id;

        // Jika id adalah objek, ambil nilai id
        if (typeof id === 'object' && id !== null) {
          id = id.id; // Ambil nilai id dari objek
        }

        // Validasi id
        if (typeof id !== 'number' || id <= 0) {
          console.warn('Invalid id:', id);
          return false; // Skip record jika id tidak valid
        }

        // Validasi attendance_date
        if (
          typeof record.attendance_date !== 'string' ||
          record.attendance_date.trim() === '' ||
          isNaN(Date.parse(record.attendance_date))
        ) {
          console.warn('Invalid attendance_date:', record.attendance_date);
          return false; // Skip record jika attendance_date tidak valid
        }

        // Validasi status
        const validStatuses = ['P', 'A', 'S', 'I', 'Belum diabsen', 'Unknown'];
        if (
          typeof record.status !== 'string' ||
          !validStatuses.includes(record.status)
        ) {
          console.warn('Invalid status:', record.status);
          return false; // Skip record jika status tidak valid
        }

        return true; // Record valid
      });

      // Debugging: Cek validRecords sebelum menyimpan
      console.log('Valid records before saving:', validRecords);

      // Menyimpan record yang valid ke attendanceRecords
      attendanceRecords.value = validRecords;
      console.log('Valid attendance records:', attendanceRecords.value);
    } else {
      console.warn('No data found in localStorage for attendanceRecords.');
    }
  } catch (error) {
    console.error('Error accessing localStorage:', error);
  }
};

// Debugging: Tampilkan hasil akhir inisialisasi
console.log('Initialized attendanceRecords:', attendanceRecords.value);
function validateRecord(record) {
  const requiredFields = ['teacher_id', 'attendance_date', 'status'];
  for (const field of requiredFields) {
    if (!(field in record)) {
      console.warn(`Missing field: ${field} in record`, record);
      return false;
    }
  }
  if (!['P', 'A', 'S', 'I'].includes(record.status)) {
    console.warn(`Invalid status in record`, record);
    return false;
  }
  return true;
}

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
console.log('Filtered Statuses:', filteredStatuses.value);

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

  console.log('Sanitized Records:', sanitizedRecords);

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

console.log('Processed Records:', processedRecords.value);

/*
const processedRecords = computed(() => {
    return attendanceRecords.value; // Menggunakan nilai valid yang sudah difilter
});
*/
function deepEqual(a, b) {
  return JSON.stringify(a) === JSON.stringify(b);
}

const { teachers } = usePage().props;
if (!teachers) {
  console.error('Teacher data is missing in props.');
} else if (Array.isArray(teachers)) {
  const rawTeachers = teachers; // Jangan reactive
  if (rawTeachers.length > 0) {
    console.log('Teacher found:', rawTeachers);
    rawTeachers.forEach((teacher) => {
      if (teacher && teacher.id !== undefined && !isNaN(teacher.id)) {
        console.log(`Teacher ID: ${teacher.id}, Name: ${teacher.name}`);
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

const updateAttendance = (attendance) => {
  console.log('Updating attendance records:', attendance);

  // Emit perubahan ke parent (opsional)
  emit('update:attendance', attendance);
};

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 5,
  total: 0,
});

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
    fetchStudents(page);

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
    if (Array.isArray(newAttendance)) {
      const sanitizedRecords = cleanAttendanceRecords(newAttendance);
      console.log('Sanitized Records:', sanitizedRecords);
      attendanceRecords.value = sanitizedRecords;
    } else {
      console.error('Invalid attendance data received:', newAttendance);
      attendanceRecords.value = [];
    }
  },
  { immediate: true }
);

watch(
  () => props.attendanceRecords,
  (newRecords) => {
    // Pastikan newRecords adalah array sebelum diproses
    if (Array.isArray(newRecords)) {
      console.log('New Attendance Records:', newRecords);
      attendanceRecords.value = cleanAttendanceRecords(newRecords);
    } else {
      console.error('Invalid attendance data received:', newRecords);
      attendanceRecords.value = [];
    }
  },
  { immediate: true }
);

// Debug computed data
console.log('Computed Attendance Summary:', attendanceSummary.value);
console.log('Attendance records from props:', toRaw(attendanceRecords.value));

const addOrUpdateAttendanceRecord = (teacherId, attendanceDate, status) => {
  if (!['P', 'A', 'S', 'I'].includes(status)) {
    console.error('Invalid status:', status);
    return;
  }

  const existingRecord = attendanceRecords.value.find(
    (record) =>
      record.teacher_id === teacherId &&
      record.attendance_date === attendanceDate
  );

  if (existingRecord) {
    existingRecord.status = status;
    console.log('Updated existing attendance record:', existingRecord);
  } else {
    const newRecord = {
      teacher_id: teacherId,
      attendance_date: attendanceDate,
      status: customStatus.value || 'Belum diabsen',
    };
    //attendanceRecords.value.push(newRecord);
    addAttendanceRecord.value.push(newRecord);
    console.log('New attendance record added:', toRaw(newRecord));
  }

  saveToLocalStorage();
};

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

const handleTeacherStatusChange = async (id, date) => {
  console.log('Menangani status perubahan untuk guru:', id);
  const formattedDateValue = formattedDate(new Date(date));
  console.log('Formatted Date:', formattedDateValue);

  // Debugging awal data absensi
  console.log('Initial attendance records:', props.attendanceRecords);

  if (!props.attendanceRecords || props.attendanceRecords.length === 0) {
    console.warn('Data absensi belum tersedia. Menunggu data...');
    await loadAttendanceRecords(); // Memuat data absensi jika belum ada
    console.log(
      'Props Attendance Records after loading:',
      props.attendanceRecords
    );

    // Cek lagi setelah memuat data
    if (!props.attendanceRecords || props.attendanceRecords.length === 0) {
      console.error('attendanceRecords is empty or not loaded yet');
      alert('Data absensi tidak ditemukan. Silakan coba lagi.');
      return; // Keluar dari fungsi jika tidak ada data
    }
  }

  // Mencari record yang sesuai untuk diperbarui
  const recordToUpdate = props.attendanceRecords.find(
    (record) =>
      typeof record.teacher_id === 'number' && // Pastikan id adalah number
      record.teacher_id === id &&
      formattedDate(new Date(record.attendance_date)) === formattedDateValue &&
      typeof record.status === 'string' &&
      ['Belum diabsen', 'P', 'A', 'S', 'I'].includes(record.status)
  );

  console.log('Record to update:', recordToUpdate);

  if (recordToUpdate) {
    // Jika record ditemukan, perbarui status
    recordToUpdate.status = customStatus.value; // Misalkan customStatus adalah status baru
    console.log('Updated existing attendance record:', recordToUpdate);
  } else {
    // Jika tidak ada record yang ditemukan, tambahkan catatan baru
    const newRecord = {
      id: id,
      attendance_date: formattedDateValue,
      status: customStatus.value || 'Belum diabsen', // Status default jika tidak ada
    };
    props.attendanceRecords.push(newRecord); // Menambahkan record baru
    console.log('Added new attendance record:', newRecord);
    saveAttendanceRecord(newRecord);
  }

  // Simpan perubahan ke localStorage
  localStorage.setItem(
    'attendanceRecords',
    JSON.stringify(toRaw(props.attendanceRecords))
  );

  console.log(
    'Attendance records saved to localStorage:',
    props.attendanceRecords
  );

  // Ambil data absensi berdasarkan id dan formattedDate
  const attendanceData = props.attendanceRecords.find(
    (item) =>
      item.id === id && // Ganti teacher_id dengan id
      item.attendance_date === formattedDateValue
  );

  console.log('Fetched attendance data:', attendanceData);

  if (attendanceData) {
    const currentStatus = attendanceData.status;
    console.log('Current status:', currentStatus);

    // Logika status tidak berubah pada "Belum diabsen"
    if (currentStatus && currentStatus !== 'Belum diabsen') {
      console.log('Handling status change for:', currentStatus);
      handleStatus(currentStatus);
    }

    // Selalu buka modal jika data absensi ditemukan
    selectedTeacherId.value = id;
    selectedDate.value = date;
    isModalVisible.value = true; // Menampilkan modal
    customStatus.value = currentStatus || ''; // Mengatur status kustom
    console.log('Modal updated with selected teacher and date.');
  } else {
    console.error(
      `Data absensi tidak ditemukan untuk guru ID: ${id} dan tanggal: ${formattedDateValue}`
    );
    alert('Data absensi tidak ditemukan. Silakan coba lagi.');
  }
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
      return 'bg-info text-black fw-bold status-btn info-btn'; // Kelas untuk hadir
    case 'A':
      return 'bg-danger text-black fw-bold status-btn danger-btn'; // Kelas untuk alpa
    case 'S':
      return 'bg-warning text-black fw-bold status-btn warning-btn'; // Kelas untuk sakit
    case 'I':
      return 'bg-primary text-black fw-bold status-btn primary-btn'; // Kelas untuk izin
    default:
      return 'bg-light text-dark status-btn light-btn'; // Default class
  }
};

// Ambil data dari props yang diterima dari Inertia
//const { teachers } = usePage().props; // Mengakses data teachers dari Inertia
console.log('Data Teachers dari usePage.props:', teachers);

const triggerTeacherStatusChange = async (teacherId) => {
  console.log('Triggering status change for Teacher ID:', teacherId);

  // Cek jika classes sudah ada, jika belum ambil dari API
  if (classes.value.length === 0) {
    await fetchClasses();
  }

  if (!classes.value.length) {
    alert('No classes available.');
    return;
  }

  // Tentukan rentang tanggal (misalnya 7 hari terakhir)
  const dates = getDateRange('2025-01-01', '2025-01-07');

  // Iterasi setiap kombinasi class_id dan tanggal
  classes.value.forEach((classId) => {
    dates.forEach((date) => {
      console.log(
        'Processing Teacher ID:',
        teacherId,
        'Date:',
        date,
        'Class ID:',
        classId
      );

      // Validasi class_id
      if (!Number.isInteger(classId) || classId <= 0) {
        console.error('Invalid class_id:', classId);
        return; // Skip class_id yang tidak valid
      }

      // Panggil fungsi untuk memproses status
      handleTeacherStatusChange(teacherId, date, classId);
    });
  });
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
const totalDaysInMonth = Array.from({ length: 31 }, (_, i) => i + 1); // 31 hari dalam sebulan
const date = new Date();
// Ekstrak nilai tanggal (1-31) dari objek Date
const currentYear = new Date().getFullYear();
const currentMonth = new Date().getMonth() + 1;
console.log('currentMonth:', currentMonth);
new Date(currentYear, currentMonth - 1, date);
console.log('Pre-correction currentMonth:', currentMonth);

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
console.log('Teachers Data:', toRaw(teachers)); // Untuk mengakses data mentahnya
const rawTeachers = toRaw(paginatedTeachers.value);
console.log(rawTeachers.value);

console.log(currentPage.value); // Periksa nilai currentPage
console.log(itemsPerPage); // Periksa nilai itemsPerPag

// Properti modal
const isAddModalVisible = ref(false);

// Properti yang diperlukan untuk bulan dan tahun
const currentMonthYear = ref(`${currentYear}-${currentMonth + 1}`);

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
    console.log('Data saved to localStorage:', newRecords);
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

const addAttendanceRecord = (newRecord) => {
  if (
    !localAttendanceRecords.value.some(
      (record) =>
        record.teacher_id === newRecord.teacher_id &&
        record.attendance_date === newRecord.attendance_date
    )
  ) {
    localAttendanceRecords.value.push(newRecord);
    console.log('New attendance record added:', newRecord);
    saveToLocalStorage(); // Simpan data baru ke localStorage
  } else {
    console.warn('Attendance record already exists:', newRecord);
  }
};

const handleAttendance = (status) => {
  console.log(`Status selected: ${status}`);

  if (!['P', 'A', 'S', 'I'].includes(status)) {
    alert('Status tidak valid!');
    return;
  }

  const newRecord = {
    teacher_id: selectedTeacherId.value,
    class_id: selectedClassId.value, // Pastikan ini ada
    attendance_date: selectedDate.value,
    status: status,
  };

  console.log('🔄 Mengirim data ke backend:', newRecord);
  saveAttendanceRecord(newRecord); // <-- Kirim langsung ke backend

  isModalVisible.value = false; // Tutup modal
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

onMounted(() => {
  try {
    console.log('Component mounted, data loading process started.');

    fetchAttendanceRecords();

    attendanceRecords.value = loadFromLocalStorage();

    // Load data awal dari localStorage
    loadFromLocalStorage();
    const rawData = toRaw(localAttendanceRecords.value);

    console.log('Data loaded from localStorage:', rawData);

    // Validasi rawData dan inisialisasi localAttendanceRecords
    if (Array.isArray(rawData)) {
      localAttendanceRecords.value = rawData;
    } else if (typeof rawData === 'string') {
      try {
        const parsedData = JSON.parse(rawData);
        if (Array.isArray(parsedData)) {
          localAttendanceRecords.value = parsedData;
        } else {
          console.warn(
            'Parsed data is not an array, initializing as empty array.'
          );
          localAttendanceRecords.value = [];
        }
      } catch (e) {
        console.error('Failed to parse localAttendanceRecords string:', e);
        localAttendanceRecords.value = [];
      }
    } else {
      console.warn(
        'localAttendanceRecords is neither array nor string, initializing as empty array.'
      );
      localAttendanceRecords.value = [];
    }

    console.log(
      'Local Attendance Records after validation:',
      localAttendanceRecords.value
    );

    // Update props.attendanceRecords setelah data berhasil divalidasi
    props.attendanceRecords = Array.isArray(localAttendanceRecords.value)
      ? [...localAttendanceRecords.value]
      : [];
    console.log(
      'Initialized props.attendanceRecords:',
      props.attendanceRecords
    );

    const formattedDateValue = formattedDate(new Date());

    // Deklarasi newRecord sebelum digunakan
    let newRecord = {
      teacher_id: teachers, // Ambil ID guru dari variabel dinamis
      attendance_date: formattedDateValue, // Gunakan tanggal yang diformat
      status: customStatus.value, // Ambil status dari variabel atau input
    };

    console.log('New record initialized:', newRecord);

    // Validasi attendanceRecords sebelum menggunakan .filter()
    if (Array.isArray(attendanceRecords.value)) {
      console.log('Valid attendanceRecords array');
      const uniqueRecords = attendanceRecords.value.filter(
        (record) =>
          !props.attendanceRecords.some(
            (item) =>
              item.teacher_id === record.teacher_id &&
              item.attendance_date === record.attendance_date
          )
      );
      props.attendanceRecords = [...props.attendanceRecords, ...uniqueRecords];
    } else {
      console.warn('Invalid attendanceRecords array');
      console.warn(
        'attendanceRecords is not an array. Initializing as an empty array.'
      );
      attendanceRecords.value = [newRecord]; // Inisialisasi dengan newRecord
    }

    console.log(
      'Final props.attendanceRecords after addition:',
      props.attendanceRecords
    );

    // Tambahkan ke attendanceRecords
    props.attendanceRecords.push(newRecord);
    console.log('Added new attendance record:', newRecord);

    // Simpan data ke localStorage setelah diperbarui
    saveToLocalStorage();

    // Validasi wali_kelas
    const { wali_kelas } = usePage().props;
    if (!Array.isArray(wali_kelas) || wali_kelas.length === 0) {
      attendanceMessage.value =
        'Data wali kelas tidak ditemukan atau tidak valid.';
      return;
    }
    console.log('Validated wali_kelas data:', wali_kelas);

    // Debugging dan log status
    console.log('Formatted Date Function Test:', formattedDate(new Date()));

    if (Array.isArray(attendanceRecords)) {
      attendanceStatus.value = displayAttendanceStatus(date);
    } else {
      console.warn('attendanceRecords is not an array.');
      attendanceStatus.value = 'Belum diabsen';
    }
  } catch (error) {
    console.error('An error occurred during onMounted:', error);
    attendanceMessage.value = 'Terjadi kesalahan saat memuat data.';
  }
});

console.log('Pre-validation currentMonth:', currentMonth);
console.log('Pre-validation date:', date);

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

// Pastikan `date` adalah angka
let extractedDate = date instanceof Date ? date.getDate() : date;

// Validasi input dengan menggunakan `isValidDate`
const formattedDate = (attendance_date) => {
  // Jika parameter adalah objek Date, ubah menjadi string dengan format YYYY-MM-DD
  if (attendance_date instanceof Date) {
    attendance_date = attendance_date.toISOString().split('T')[0];
  }

  // Validasi apakah attendance_date adalah string dalam format YYYY-MM-DD
  const dateRegex = /^\d{4}-\d{2}-\d{2}$/;
  if (!dateRegex.test(attendance_date)) {
    console.error('Invalid date format:', attendance_date);
    return null;
  }

  // Konversi menjadi objek Date untuk validasi lebih lanjut
  const date = new Date(attendance_date);
  if (isNaN(date.getTime())) {
    console.error('Invalid date object:', attendance_date);
    return null;
  }

  // Kembalikan tanggal dalam format YYYY-MM-DD
  return date.toISOString().split('T')[0];
};

// Debugging code where `teacher` might not be defined
console.log('Checking if teacher is defined:', teachers);

if (!isValidDate(currentYear, currentMonth, extractedDate)) {
  console.error('Invalid date input:', {
    currentYear,
    currentMonth,
    extractedDate,
  });
} else {
  // Format tanggal menjadi "YYYY-MM-DD"
  const dateToFormat = new Date(currentYear, currentMonth - 1, extractedDate);
  const formattedDateString = formattedDate(dateToFormat);

  // Pastikan `attendanceRecords` adalah array
  if (!Array.isArray(attendanceRecords.value)) {
    console.error('attendanceRecords is not an array:', attendanceRecords);
  } else {
    // Mencari data absensi
    const recordForCurrentDate = attendanceRecords.value.find((record) => {
      if (!record || typeof record !== 'object') {
        console.warn('Invalid record format detected:', record);
        return false;
      }

      return (
        formattedDate(new Date(record.attendance_date)) ===
          formattedDateString &&
        Number(toRaw(record.teacher_id)) ===
          Number(toRaw(selectedTeacherId.value))
      );
    });

    // Pengecekan data absensi
    if (recordForCurrentDate) {
      console.log('Attendance status:', recordForCurrentDate.status);
    } else {
      console.log('No attendance found for this date and teacher.');
    }
  }
}

console.log(
  'Debug formattedDate:',
  currentYear,
  currentMonth,
  date,
  new Date(currentYear, currentMonth, date)
);

console.log('Debug formattedDate:', formattedDate);

// Fungsi untuk memuat data dari localStorage
const loadFromLocalStorage = () => {
  try {
    const data = localStorage.getItem('attendanceRecords');

    console.log('Data from localStorage:', data ? JSON.parse(data) : data); // Tambahkan log disini

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

  rawRecord.teacher_id = rawRecord.teacher_id || 1;
  rawRecord.class_id = rawRecord.class_id || 1;

  if (!rawRecord.attendance_date) {
    console.warn('❌ attendance_date tidak ada!', rawRecord);
    return;
  }

  const validStatuses = ['P', 'A', 'S', 'I'];
  if (!validStatuses.includes(rawRecord.status)) {
    console.warn('⛔ Status tidak valid atau belum dipilih:', rawRecord.status);
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
  const validDate = new Date(currentYear, currentMonth, date);
  return validDate.getTime() === validDate.getTime() ? validDate : null;
};

// Fungsi untuk memeriksa apakah hari Minggu
const isSunday = (day) => {
  return new Date(currentYear, currentMonth, day).getDay() === 0;
};

// Fungsi untuk mendapatkan status kehadiran guru
const getTeacherAttendanceStatus = (teacherId, date) => {
  //console.log("Fetching attendance status for:", teacherId, "on date:", date);

  if (attendanceRecords.value && attendanceRecords.value.length > 0) {
    //console.log("Attendance records available:", attendanceRecords.value);

    const record = attendanceRecords.value.find((r) => {
      const recordDate = formattedDate(new Date(r.attendance_date));
      //console.log("Checking record:", r);
      //console.log("Record Date:", recordDate);
      //console.log("Comparing with:", date);

      return r.teacher_id === teacherId && recordDate === date;
    });

    if (record) {
      console.log('Matching record found:', record);
      return record.status;
    } else {
      //console.warn("No matching record found for teacherId:", teacherId, "on date:", date);
      return 'Belum diabsen';
    }
  } else {
    //console.warn("Attendance records are empty or not loaded.");
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
      return 'bg-info text-black fw-bold'; // Hadir
    case 'A':
      return 'bg-danger text-black fw-bold'; // Absen
    case 'S':
      return 'bg-warning text-black fw-bold'; // Sakit
    case 'I':
      return 'bg-primary text-black fw-bold'; // Izin
    default:
      return 'bg-light text-dark'; // Status tidak ditemukan atau belum diabsen
  }
};

console.log('Teacher ID:', teachers);
console.log('Attendance Records:', processedRecords.value);

console.log('Attendance Records:', toRaw(attendanceRecords.value));
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
      console.log('Attendance records is empty or not an array');
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
      console.log('AttendanceRecords updated:', rawOldValue, '->', rawNewValue);

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
          <div class="text-3xl d-flex justify-content-between mb-3">
            <div class="sm:flex sm:items-center">
              <div class="sm:flex-auto font-semibold">
                <h1 class="text-3xl font-semibold text-gray-900">
                  Tabel Absensi Guru
                </h1>
                <p class="text-sm mb-3 fw-bold text-danger">
                  Bulan {{ currentMonthYear }}
                </p>
              </div>
            </div>
          </div>

          <!-- Button untuk Tambah Absensi -->
          <!--
                                   <button
                        type="button"
                        class="btn btn-primary mb-4"
                        @click="isModalVisible = true"
                    >
                        Tambah Absensi
                    </button>
                     -->

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
                <tr v-for="teacher in paginatedTeachers" :key="teacher.id">
                  <td>{{ teacher.name }}</td>
                  <td
                    v-for="(date, index) in totalDaysInMonth"
                    :key="`attendance-${teacher.id}-${formattedDate(
                      new Date(currentYear, currentMonth, date)
                    )}`"
                    :class="
                      getAttendanceClass(
                        teacher.id,
                        formattedDate(new Date(currentYear, currentMonth, date))
                      ) + ' text-center'
                    "
                    @click="
                      handleTeacherStatusChange(
                        teacher.id,
                        formattedDate(new Date(currentYear, currentMonth, date))
                      )
                    "
                  >
                    <span
                      :class="
                        getAttendanceClass(
                          teacher.id,
                          formattedDate(
                            new Date(currentYear, currentMonth, date)
                          )
                        ) + ' block text-center'
                      "
                    >
                      {{
                        getTeacherAttendanceStatus(
                          teacher.id,
                          formattedDate(
                            new Date(currentYear, currentMonth, date)
                          )
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

          <!-- Keterangan Status Kehadiran -->
          <div class="row mt-3 me-3">
            <div class="col-12">
              <p class="fw-bold fs-5">Status Kehadiran:</p>
              <div class="d-flex flex-wrap align-items-center">
                <div class="me-3 mb-2">
                  <span class="badge bg-info text-black fw-bold"
                    >Hadir (P)</span
                  >
                </div>
                <div class="me-3 mb-2">
                  <span class="badge bg-danger text-black fw-bold"
                    >Absen (A)</span
                  >
                </div>
                <div class="me-3 mb-2">
                  <span class="badge bg-warning text-black fw-bold"
                    >Sakit (S)</span
                  >
                </div>
                <div class="me-3 mb-2">
                  <span class="badge bg-primary text-black fw-bold"
                    >Izin (I)</span
                  >
                </div>
                <div class="me-3 mb-2">
                  <span class="badge bg-light text-dark fw-bold"
                    >Belum Diabsen</span
                  >
                </div>
              </div>
            </div>
            <!--<pre>{{ attendanceRecords }}</pre>-->
          </div>
          <div class="p-6 bg-gray-100 min-h-screen">
            <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg p-6">
              <h2 class="text-xl font-semibold text-gray-700 mb-4">
                Data Kehadiran
              </h2>

              <!-- Debugging: Menampilkan isi attendanceRecords -->
              <pre class="text-sm bg-gray-200 p-3 rounded-md overflow-x-auto">{{
                attendanceRecords
              }}</pre>

              <div v-if="attendanceRecords.length > 0">
                <table
                  class="w-full mt-4 border border-gray-300 rounded-lg overflow-hidden"
                >
                  <thead class="bg-blue-500 text-white">
                    <tr>
                      <th class="py-2 px-4 text-left">ID</th>
                      <th class="py-2 px-4 text-left">Teacher ID</th>
                      <th class="py-2 px-4 text-left">Tanggal</th>
                      <th class="py-2 px-4 text-left">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr
                      v-for="record in attendanceRecords"
                      :key="record.id"
                      class="border-b hover:bg-gray-100"
                    >
                      <td class="py-2 px-4">{{ record.id }}</td>
                      <td class="py-2 px-4">{{ record.teacher_id }}</td>
                      <td class="py-2 px-4">{{ record.attendance_date }}</td>
                      <td class="py-2 px-4">
                        <span
                          :class="{
                            'bg-green-500 text-white px-2 py-1 rounded':
                              record.status === 'P',
                            'bg-red-500 text-white px-2 py-1 rounded':
                              record.status === 'A',
                            'bg-yellow-500 text-white px-2 py-1 rounded':
                              record.status === 'S',
                            'bg-gray-500 text-white px-2 py-1 rounded':
                              record.status === 'I',
                          }"
                        >
                          {{ record.status }}
                        </span>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <p v-else class="text-center text-gray-600 mt-4">
                Tidak ada data kehadiran.
              </p>
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
        </div>
      </form>
    </main>

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
                  >Membuat Kelas</a
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
                  >Tambah Mata Pelajaran</a
                >
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </aside>
  </div>
</template>
