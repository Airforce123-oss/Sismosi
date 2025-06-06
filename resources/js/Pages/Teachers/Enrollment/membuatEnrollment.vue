<script setup>
import { ref, computed, onMounted, toRaw, nextTick, watch } from 'vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SidebarTeacher from '@/Components/SidebarTeacher.vue';
import { initFlowbite } from 'flowbite';
import axios from 'axios';
import { Link, useForm, usePage, Head } from '@inertiajs/vue3';

defineProps({
  students: Array,
  courses: Array,
});

const { students, courses, teachers, auth } = usePage().props;
console.log('Courses:', toRaw(courses));
console.log('Students:', toRaw(students));
console.log('Teachers:', toRaw(teachers));
console.log('Data pertama di array courses:', courses[0]);
console.log('ID Mata Pelajaran:', courses[0].id);
console.log('Nama Mata Pelajaran:', courses[0].mapel);

const selectedEnrollmentId = ref(null);
// Fungsi untuk memilih enrollment dan mengatur ID
const selectEnrollment = (id) => {
  selectedEnrollmentId.value = id;
  console.log('Selected Enrollment ID:', selectedEnrollmentId.value);
};

const newEnrollment = ref({
  studentId: null,
  courseId: null,
  teacher_id: null,
  enrollment_date: '',
  status: 'active',
});

console.log('Isi newEnrollment.value:', newEnrollment.value);

// Panggil getEnrollment setelah ID dipilih
const getEnrollment = async () => {
  if (!selectedEnrollmentId.value) {
    //console.error("Enrollment ID is null or not set");
    return; // Pastikan ID ada sebelum melanjutkan
  }

  try {
    const response = await axios.get(
      `/enrollments/${selectedEnrollmentId.value}`
    );
    console.log('Response data getenrollment:', response.data);
    newEnrollment.value.status = response.data.status;
    newEnrollment.value.studentId = response.data.student_id;
    newEnrollment.value.courseId = response.data.course_id;
    newEnrollment.value.teacher_id = response.data.teacher_id;
    newEnrollment.value.enrollment_date = response.data.enrollment_date;
  } catch (error) {
    console.error('Error fetching enrollment:', error);
  }
};

const emit = defineEmits(['close', 'markAdded']);

const newMark = ref({
  studentId: '',
  mapelId: '',
  status: 'active',
  enrollmentDate: null,
  noKd: '',
  cognitive1: 0,
  cognitive2: 0,
  cognitivePAS: 0,
  cognitiveAverage: 0,
  skill1: 0,
  skill2: 0,
  skillPAS: 0,
  skillAverage: 0,
  finalMark: 0,
});

const isMarkModalVisible = ref(false);
const isEnrollmentModalVisible = ref(false);

const markEnrollment = async (enrollmentId, studentId, mapelId) => {
  selectedEnrollmentId.value = enrollmentId;

  // Debug awal input
  console.log('Incoming studentId:', studentId);
  console.log('Incoming mapelId:', mapelId);

  // Set awal berdasarkan parameter langsung (jika tersedia)
  newMark.value.studentId = studentId || null;
  newMark.value.mapelId = mapelId || null;
  isMarkModalVisible.value = true;

  try {
    console.log(`Fetching enrollment data for ID: ${enrollmentId}`);
    const response = await axios.get(`/api/enrollments/${enrollmentId}`);
    const enrollmentData = response.data;
    console.log('Enrollment API response:', enrollmentData);

    if (enrollmentData && enrollmentData.teacher_id) {
      // Update newMark berdasarkan API
      newMark.value.studentId = enrollmentData.student_id;
      newMark.value.mapelId = enrollmentData.mapel_id;
      newMark.value.teacherId = enrollmentData.teacher_id;
      newMark.value.enrollmentDate = enrollmentData.enrollment_date;
      newMark.value.status = enrollmentData.status || 'active';

      // Update nilai kognitif dan keterampilan
      newMark.value.cognitive1 = enrollmentData.nilai1_tek || '';
      newMark.value.cognitive2 = enrollmentData.nilai2_tek || '';
      newMark.value.cognitivePAS = enrollmentData.nilai1_nil || '';
      newMark.value.cognitiveAverage = enrollmentData.nilai2_tek || '';

      newMark.value.skill1 = enrollmentData.nilai1_tek || '';
      newMark.value.skill2 = enrollmentData.nilai2_tek || '';
      newMark.value.skillPAS = enrollmentData.nilai1_nil || '';
      newMark.value.skillAverage = enrollmentData.nilai2_tek || '';

      newMark.value.finalMark = enrollmentData.nilai1_tek || '';

      // Log data final
      console.log('Updated newMark:', toRaw(newMark.value));

      // Optional: mencari student dan course untuk ditampilkan (jika tersedia)
      if (!students || students.length === 0) {
        console.warn('No students data found.');
      }
      if (!courses || courses.length === 0) {
        console.warn('No courses data found.');
      }

      const selectedStudent = students.find(
        (student) => student.id === enrollmentData.student_id
      );
      const selectedCourse = courses.find(
        (course) => course.id === enrollmentData.mapel_id
      );

      console.log('Selected Student:', selectedStudent);
      console.log('Selected Course:', selectedCourse);
    } else {
      console.error(
        'Teacher ID atau data lainnya tidak ditemukan dalam response.'
      );
      alert('Data teacher_id tidak tersedia.');
    }
  } catch (error) {
    console.error('Error fetching enrollment details:', error);
  }
};

const closeAddMarkModal = () => {
  isMarkModalVisible.value = false;

  // Reset berdasarkan enrollmentData yang sudah ada
  newMark.value = {
    studentId: '',
    courseId: '',
    status: 'active',
    cognitive1: null,
    cognitive2: null,
    cognitivePAS: null,
    cognitiveAverage: null,
    skill1: null,
    skill2: null,
    skillPAS: null,
    skillAverage: null,
    finalMark: null,
  };

  emit('close');
};

const closeModal = () => {
  isModalVisible.value = false;
  selectedEnrollmentId.value = null; // Reset ID enrollment setelah modal ditutup
};
const hideAddModal = () => {
  console.log('Modal ditutup');
  isEnrollmentModalVisible.value = false;
};
const hideMarkModal = () => {
  console.log('Modal ditutup');
  isMarkModalVisible.value = false;
};

const searchQuery = ref('');
const enrollments = ref([]);
const paginatedEnrollments = ref([]);
const isLoading = ref(true);
const currentPage = ref(1); // Halaman aktif
const totalPages = ref(1);
const perPage = ref(5); // Jumlah per halaman

console.log('Current Page:', currentPage.value);
console.log('Enrollments Data:', enrollments.value);
console.log('Paginated Enrollments:', paginatedEnrollments.value);
console.log('Total Pages:', totalPages.value);
console.log('Per Page:', perPage.value);

const updatePaginatedEnrollments = () => {
  console.log('Current perPage Value:', perPage.value);

  const startIndex = (currentPage.value - 1) * perPage.value;
  const endIndex = startIndex + perPage.value;
  console.log('Start Index:', startIndex, 'End Index:', endIndex);

  const totalData = enrollments.value.length;
  if (startIndex < totalData) {
    paginatedEnrollments.value = enrollments.value.slice(
      startIndex,
      Math.min(endIndex, totalData)
    );
  } else {
    paginatedEnrollments.value = [];
  }

  console.log('Paginated Enrollments:', paginatedEnrollments.value);

  // Simpan enrollments ke localStorage
  saveEnrollmentsToLocalStorage();
};

const fetchDataForPage = async (page) => {
  try {
    const response = await axios.get('/api/enrollments', {
      params: { page, perPage: perPage.value },
    });

    console.log('Data dari API:', response.data);

    const data = response.data.data;

    if (response.data.data.length === 0) {
      alert('No data available for this page.');
      return;
    }

    enrollments.value = response.data.data;
    paginatedEnrollments.value = response.data.data;

    // Perbarui info pagination
    currentPage.value = response.data.pagination.current_page;
    totalPages.value = response.data.pagination.total_pages;
  } catch (error) {
    console.error('Error fetching data:', error);
  }
};

const loadEnrollmentsFromLocalStorage = () => {
  const savedData = localStorage.getItem('enrollments');
  if (savedData) {
    enrollments.value = JSON.parse(savedData);
    console.log('Enrollments loaded from localStorage:', enrollments.value);
  } else {
    console.log('No enrollments found in localStorage.');
  }
};

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    fetchDataForPage(page);
  }
};

fetchDataForPage(currentPage.value);

const saveEnrollmentsToLocalStorage = () => {
  console.log('Enrollments saved to localStorage:', paginatedEnrollments.value);
  localStorage.setItem(
    'enrollments',
    JSON.stringify(paginatedEnrollments.value)
  );
};

watch(currentPage, (newPage) => {
  fetchDataForPage(newPage);
});

updatePaginatedEnrollments();

const formatDate = (date) => {
  if (!date) return 'N/A';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(date).toLocaleDateString('en-US', options);
};

const fetchData = async () => {
  await Promise.all([fetchStudents(), fetchCourses(), fetchEnrollments()]);
  getEnrollment();
};

const changePage = (page) => {
  currentPage.value = page;
  console.log('Current Page:', currentPage.value);

  // Memanggil fetchDataForPage untuk mendapatkan data berdasarkan halaman yang dipilih
  fetchDataForPage(currentPage.value);
};

const fetchStudents = async () => {
  try {
    const response = await axios.get('/api/students');
    students.value = response.data;
  } catch (error) {
    console.error('Error fetching students:', error);
  }
};

const totalCourses = ref(0);
const fetchCourses = async () => {
  try {
    const response = await axios.get('/api/courses');
    courses.value = response.data;
  } catch (error) {
    console.error('Error fetching courses:', error);
  }
};

const fetchEnrollments = async () => {
  const token = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute('content');
  if (!token) {
    console.error('Token tidak ditemukan!');
    return;
  }

  isLoading.value = true; // Menandakan bahwa data sedang di-load

  try {
    const response = await axios.get('/api/enrollments', {
      headers: {
        Authorization: `Bearer ${token}`,
        'X-CSRF-TOKEN': token,
      },
    });

    if (response.status === 200) {
      const enrollmentsData = response.data.data || [];

      // Perbarui paginatedEnrollments dengan data terbaru
      paginatedEnrollments.value = enrollmentsData;

      // Simpan data ke localStorage setelah mendapatkan data dari API
      localStorage.setItem('enrollments', JSON.stringify(enrollmentsData));
      console.log('Enrollments saved to localStorage:', enrollmentsData);
    } else {
      console.error('Failed to fetch enrollments:', response.status);
    }
  } catch (error) {
    console.error('Error fetching enrollments:', error);
  } finally {
    isLoading.value = false; // Menandakan bahwa loading telah selesai
  }
};

const totalEnrollments = computed(() => enrollments.value.length);

const activeEnrollments = computed(
  () => enrollments.value.filter((e) => e.status === 'active').length
);

const inactiveEnrollments = computed(
  () => enrollments.value.filter((e) => e.status === 'inactive').length
);

const isModalVisible = ref(false);
const showAddModal = () => {
  isEnrollmentModalVisible.value = true;
};

const showMarkModal = () => {
  isMarkModalVisible.value = true;
};

const addEnrollmentToDatabase = async (enrollmentData) => {
  try {
    const page = usePage();
    const authUser = page.props.auth.user;

    // Set teacher_id dari user yang login
    enrollmentData.teacher_id = authUser.id;
    console.log('Data dikirim:', enrollmentData);

    const response = await axios.post('/api/enrollments', enrollmentData);
    console.log('Response dari server:', response.data);

    const created = response.data.data;

    if (created && created.student_id && created.mapel_id) {
      // Tambahkan data yang baru ke enrollments
      enrollments.value.push(created);

      // Pastikan juga menambahkan data ke paginatedEnrollments jika perlu
      paginatedEnrollments.value.push(created);

      // Menunggu perubahan render pada UI
      await nextTick();
      console.log('Data enrollments setelah update:', enrollments.value);

      // Pastikan untuk memberikan feedback ke pengguna atau pembaruan lainnya
    } else {
      console.warn('Data penting tidak lengkap:', created);
      alert('Data penting (student_id atau mapel_id) tidak ada.');
    }

    return response;
  } catch (error) {
    console.error('Error adding enrollment:', error);
    if (error.response) {
      console.error('API Response Error:', error.response.data);
      alert(
        'Gagal tambah enrollment: ' + JSON.stringify(error.response.data.errors)
      );
    } else {
      alert('Gagal menambahkan enrollment.');
    }
    throw error;
  }
};

const addEnrollment = async () => {
  console.log('newMark:', newMark);
  console.log('newMark.studentId:', newMark.studentId);
  console.log('newMark.mapelId:', newMark.mapelId);

  console.log('studentId:', newMark.studentId);
  console.log('mapelId:', newMark.mapelId);

  // Mengakses teacher_id dari props.auth.user.id
  const teacherId = auth.user.id; // Pastikan props sudah diteruskan ke komponen ini

  console.log('Sebelum perubahan:', students);
  console.log('Isi newEnrollment.value:', newEnrollment.value); // Tambahkan di sini

  // Pastikan teacher_id sudah diatur sebelum melanjutkan
  if (!newEnrollment.value.teacher_id) {
    newEnrollment.value.teacher_id = teacherId; // Set teacher_id dari props.auth.user.id
  }

  // Periksa apakah courseId dan studentId terisi dengan benar
  if (
    newEnrollment.value.studentId &&
    newEnrollment.value.courseId &&
    newEnrollment.value.teacher_id
  ) {
    if (Array.isArray(students)) {
      // Pastikan courseId sudah terisi dengan benar
      students.push({ ...newEnrollment.value });
    } else {
      console.error('students bukan array');
      return;
    }
  } else {
    console.error('Data enrollment tidak lengkap');
    return;
  }

  console.log('Setelah perubahan:', students);

  try {
    const response = await addEnrollmentToDatabase({
      student_id: newEnrollment.value.studentId,
      mapel_id: newEnrollment.value.courseId,
      teacher_id: newEnrollment.value.teacher_id,
      enrollment_date: newEnrollment.value.enrollment_date,
      status: newEnrollment.value.status,
    });

    // Pastikan data telah terupdate di enrollments.value
    enrollments.value.push(response.data);
    console.log('Data setelah render ulang:', enrollments.value);

    // Reset form setelah berhasil
    newEnrollment.value = {
      studentId: null,
      courseId: null,
      enrollmentDate: '',
      status: 'active',
      teacher_id: teacherId, // Menjaga agar teacher_id tetap terisi setelah reset
    };

    // Menutup modal setelah data berhasil disimpan
    hideAddModal();
  } catch (error) {
    console.error('Error adding enrollment:', error);
  }
};

console.log('Enrollments:', enrollments.value);
console.log('New Enrollment Status:', newEnrollment.value.status);

const payload = {
  student_id: newEnrollment.value.studentId || null,
  enrollment_id: newEnrollment.value.enrollmentId || null,
  enrollment_date: newEnrollment.value.enrollment_date || null,
  cognitive_1: newEnrollment.value.cognitive_1 || null,
  cognitive_2: newEnrollment.value.cognitive_2 || null,
  cognitive_pas: newEnrollment.value.cognitive_pas || null,
  cognitive_average: newEnrollment.value.cognitive_average || null,
  skill_1: newEnrollment.value.skill_1 || null,
  skill_2: newEnrollment.value.skill_2 || null,
  skill_pas: newEnrollment.value.skill_pas || null,
  skill_average: newEnrollment.value.skill_average || null,
  final_mark: newEnrollment.value.final_mark || null,
  mapel_id: newEnrollment.value.courseId || null,
  teacher_id: newEnrollment.value.teacher_id || null,
  status: newEnrollment.value.status || 'active',
  no_kd: newEnrollment.value.no_kd || null,
};

// Debug payload sebelum dikirim
console.log('Payload yang akan dikirim:', payload);
console.log('payload buat teacher_id', typeof payload.teacher_id);

// Debug URL API yang digunakan
const url = `/api/enrollments/${payload.enrollment_id}`;
console.log('URL API:', url);

// Cek apakah enrollment_id ada dan payload tidak kosong
if (!payload.enrollment_id || Object.keys(payload).length === 0) {
  //console.error('Data enrollment tidak valid atau kosong.');
  //alert('Data enrollment tidak valid atau kosong.');
} else {
  axios
    .put(url, payload)
    .then((response) => {
      console.log('Respons berhasil:', response.data);

      // Cek apakah mapel_id valid
      if (payload.mapel_id === 0) {
        alert('Mapel ID tidak valid!');
        return;
      }

      // Tambahan logika untuk penanganan lebih lanjut setelah update berhasil
      if (response.data && response.data.data) {
        // Misalnya, bisa update tampilan atau data lain setelah update sukses
        alert('Enrollment berhasil diperbarui!');
      }
    })
    .catch((error) => {
      if (error.response) {
        // Respons dari server
        console.error('Respons error dari server:', error.response.data);
        console.error('Status code:', error.response.status);
        console.error('Headers:', error.response.headers);

        if (error.response.status === 404) {
          alert('Enrollment tidak ditemukan!');
        } else {
          //alert('Gagal memperbarui data enrollment.');
        }
      } else if (error.request) {
        // Jika request dikirim tapi tidak ada respons
        console.error('Request dikirim tapi tidak ada respons:', error.request);
        alert('Tidak ada respons dari server.');
      } else {
        // Jika terjadi kesalahan lain dalam setup request
        console.error('Kesalahan lain:', error.message);
        alert('Terjadi kesalahan saat mengirim request.');
      }
    });
}

const saveMark = async () => {
  try {
    // Ambil nilai dari form modal
    const enrollmentId = selectedEnrollmentId.value; // Pastikan ini adalah ID yang sesuai
    const numericEnrollmentId = Number(enrollmentId);

    if (!numericEnrollmentId || isNaN(numericEnrollmentId)) {
      MarkController;
      throw new Error('Invalid enrollment ID: Expected a numeric value');
    }

    // Ambil nilai penilaian yang dimasukkan di modal dan set default jika null atau undefined
    const cognitive1 = newMark.value.cognitive1 ?? 0;
    const cognitive2 = newMark.value.cognitive2 ?? 0;
    const cognitivePAS = newMark.value.cognitivePAS ?? 0;
    const cognitiveAverage = newMark.value.cognitiveAverage ?? 0;

    const skill1 = newMark.value.skill1 ?? 0;
    const skill2 = newMark.value.skill2 ?? 0;
    const skillPAS = newMark.value.skillPAS ?? 0;
    const skillAverage = newMark.value.skillAverage ?? 0;

    const finalMark = newMark.value.finalMark ?? 0;

    // Validasi nilai kognitif dan keterampilan
    if (
      isNaN(cognitive1) ||
      isNaN(cognitive2) ||
      isNaN(cognitivePAS) ||
      isNaN(cognitiveAverage)
    ) {
      throw new Error('Invalid cognitive values');
    }

    if (
      isNaN(skill1) ||
      isNaN(skill2) ||
      isNaN(skillPAS) ||
      isNaN(skillAverage)
    ) {
      throw new Error('Invalid skill values');
    }

    if (isNaN(finalMark)) {
      throw new Error('Invalid final mark');
    }

    // Kirim data ke API
    const response = await axios.post('/api/marks', {
      enrollment_id: numericEnrollmentId, // ID Enrollment
      student_id: newMark.studentId ?? 0, // ID Siswa
      mapel_id: newMark.value.mapelId ?? 0, // ID Mata Pelajaran
      enrollment_date: newMark.value.enrollmentDate ?? '', // Tanggal Enrollment
      status: newMark.value.status ?? 'active', // Status (active/inactive)
      description: newMark.value.remark ?? '', // Deskripsi / Catatan
      no_kd: newMark.value.noKd ?? '', // Nomor KD
      cognitive_1: cognitive1, // Nilai Kognitif 1
      cognitive_2: cognitive2, // Nilai Kognitif 2
      cognitive_pas: cognitivePAS, // Nilai Kognitif PAS
      cognitive_average: cognitiveAverage, // Rata-rata Kognitif
      skill_1: skill1, // Nilai Skill 1
      skill_2: skill2, // Nilai Skill 2
      skill_pas: skillPAS, // Nilai Skill PAS
      skill_average: skillAverage, // Rata-rata Skill
      final_mark: finalMark, // Nilai Akhir
      mark: finalMark, // Mark
    });

    // Log API response untuk debugging
    console.log('API Response:', response.data);

    if (response.status === 200) {
      // Reset form setelah berhasil
      newMark.value = {
        studentId: null,
        mapelId: null,
        cognitive1: 0,
        cognitive2: 0,
        cognitivePAS: 0,
        cognitiveAverage: 0,
        skill1: 0,
        skill2: 0,
        skillPAS: 0,
        skillAverage: 0,
        finalMark: 0,
        status: 'active',
        remark: '',
      };
    }

    // Update enrollments setelah berhasil
    const updatedEnrollment = response.data;
    const enrollmentIndex = enrollments.value.findIndex(
      (enrollment) => enrollment.id === updatedEnrollment.id
    );

    if (enrollmentIndex !== -1) {
      enrollments.value[enrollmentIndex] = updatedEnrollment;
    }

    const rawEnrollments = toRaw(enrollments.value);
    console.log('Raw Enrollments:', rawEnrollments); // Log the raw data of enrollments
    localStorage.setItem('enrollments', JSON.stringify(rawEnrollments));
    saveEnrollmentsToLocalStorage();
    updatePaginatedEnrollments();

    isMarkModalVisible.value = false;
    emit('markAdded', updatedEnrollment);
    closeAddMarkModal();
  } catch (error) {
    console.error('Error adding mark:', error);
    if (error.response) {
      console.error('Error Status:', error.response.status);
      console.error('API Response Error:', error.response.data);
    }
    alert('Gagal menambahkan mark. Silakan coba lagi.');
  }
};

onMounted(async () => {
  // Ambil data enrollments dari localStorage
  const storedEnrollments = localStorage.getItem('enrollments');
  if (storedEnrollments) {
    console.log('Data dari localStorage ditemukan:', storedEnrollments);

    // Parse data dari localStorage dan set ke reaktif property
    enrollments.value = JSON.parse(storedEnrollments);
    console.log('Reloaded Enrollments from LocalStorage:', enrollments.value);

    // Perbarui data yang ditampilkan jika menggunakan paginasi
    loadEnrollmentsFromLocalStorage();
    updatePaginatedEnrollments();
  } else {
    console.log('Tidak ada data yang tersimpan di localStorage');
  }

  isLoading.value = true;

  try {
    // Panggil fungsi untuk fetch data dari API
    await fetchData();
    await fetchStudents();
    await fetchCourses();
    await fetchEnrollments(); // Opsional, jika ingin memperbarui data dari server
  } catch (error) {
    console.error('Error fetching data:', error);
  }

  // Pastikan Flowbite diinisialisasi setelah semua data siap
  initFlowbite();
});

const logChange = (fieldName, value) => {
  console.log(
    `[Tambah/Perbarui Data Kognitif & Keterampilan] ${fieldName}:`,
    value
  );
};
</script>

<style scoped>
/* Add any custom styles for your table here */
table {
  width: 100%;
  border-collapse: collapse;
}

td,
th {
  padding: 12px 16px;
  text-align: left;
}

button {
  cursor: pointer;
  transition: all 0.2s ease;
}

button:hover {
  opacity: 0.8;
}

.grid div p {
  font-family: 'Poppins', sans-serif;
  letter-spacing: 1px;
  line-height: 1.4;
}

.grid div p span:first-child {
  font-size: 1.25rem; /* Ukuran untuk judul */
  color: black; /* Hijau untuk "Pendaftaran Aktif" */
}

.grid div p span:last-child {
  font-size: 2rem; /* Ukuran untuk angka */
  font-weight: 700;
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
                  {{ $page.props.auth.user.name }}:
                  <!-- {{ student_name }}-->
                </span>
                <span
                  class="block text-sm text-gray-900 truncate dark:text-white"
                >
                  <!--{{ form.role_type }}-->
                </span>
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
    <!-- Main content -->
    <main class="p-4 sm:p-6 lg:p-8 md:ml-64 h-screen pt-10 mb-20">
      <Head title="Membuat Enrollment" />
      <h2
        class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 mt-10 mb-6 text-center"
      >
        List Enrollment Siswa
      </h2>

      <div v-if="isLoading" class="spinner"></div>

      <div
        class="max-w-screen-md mx-auto px-2 py-4 bg-gray-100 rounded-lg shadow-md mb-12"
      >
        <div
          class="flex flex-col sm:flex-row sm:flex-wrap gap-2 justify-between items-center mb-4"
        >
          <!-- Search filter for Student Name -->
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Nama Kelas"
            class="w-full sm:w-1/4 text-sm px-3 py-1.5 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
          <!-- Search filter for Teacher Name -->
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Nama Guru"
            class="w-full sm:w-1/4 text-sm px-3 py-1.5 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
          <!-- Search filter for Subject -->
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Mata Pelajaran"
            class="w-full sm:w-1/4 text-sm px-3 py-1.5 border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-blue-500"
          />
          <button
            class="bg-blue-500 hover:bg-blue-600 text-white text-sm font-semibold py-1.5 px-3 rounded focus:outline-none focus:ring-1 focus:ring-blue-300 flex items-center"
            @click="showAddModal"
          >
            <i class="fa fa-plus mr-2"></i> Tambah
          </button>
        </div>
      </div>

      <!--      <pre>{{ JSON.stringify(students, null, 2) }}</pre>
-->
      <!-- Modal tambah enrollment -->
      <div
        v-if="isEnrollmentModalVisible"
        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center"
      >
        <div class="bg-white p-6 rounded-lg shadow-lg w-96">
          <h3 class="text-lg font-bold text-center">Tambah Enrollment Baru</h3>
          <form @submit.prevent="addEnrollment">
            <!-- Pilih Guru -->
            <div class="mb-4">
              <label
                for="teacherName"
                class="block text-sm font-medium text-gray-700"
              >
                Guru
              </label>
              <input
                type="text"
                id="teacherName"
                :value="$page.props.auth.user.name"
                class="w-full px-4 py-2 border rounded-md bg-gray-100 text-gray-800"
              />
            </div>

            <!-- Pilih Siswa -->
            <div class="mb-4">
              <label
                for="student"
                class="block text-sm font-medium text-gray-700"
                >Nama Siswa</label
              >
              <select
                v-model="newEnrollment.studentId"
                id="student"
                required
                class="w-full px-4 py-2 border rounded-md"
              >
                <option
                  v-for="student in students"
                  :key="student.id"
                  :value="student.id"
                >
                  {{ student.name }}
                </option>
              </select>
            </div>

            <!-- Pilih Mata Pelajaran -->
            <div class="mb-4">
              <label
                for="courseId"
                class="block text-sm font-medium text-gray-700"
              >
                Mata Pelajaran
              </label>
              <select
                v-model="newEnrollment.courseId"
                id="courseId"
                required
                @change="
                  console.log('Selected courseId:', newEnrollment.courseId)
                "
                class="w-full px-4 py-2 border rounded-md"
                v-if="courses.length > 0"
              >
                <option
                  v-for="course in courses"
                  :key="course.id"
                  :value="course.id"
                >
                  {{ course.mapel }}
                </option>
              </select>
            </div>

            <!-- Tanggal Enrollment -->
            <div class="mb-4">
              <label
                for="enrollmentDate"
                class="block text-sm font-medium text-gray-700"
                >Tanggal Enrollment</label
              >
              <input
                type="date"
                v-model="newEnrollment.enrollment_date"
                id="enrollmentDate"
                required
                class="w-full px-4 py-2 border rounded-md"
              />
            </div>

            <!-- Status -->
            <div class="mb-4">
              <label
                for="status"
                class="block text-sm font-medium text-gray-700"
                >Status</label
              >
              <select
                v-model="newEnrollment.status"
                id="status"
                required
                class="w-full px-4 py-2 border rounded-md"
              >
                <option value="active">Aktif</option>
                <option value="inactive">Tidak Aktif</option>
              </select>
            </div>

            <!-- Tombol Submit -->
            <div class="flex justify-end">
              <button
                type="button"
                @click="hideAddModal"
                class="btn btn-secondary mr-3"
              >
                Batal
              </button>
              <button type="submit" class="btn btn-primary mr-2">Simpan</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Modal Add Mark -->
      <div
        v-if="isMarkModalVisible"
        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center"
      >
        2 >

        <div
          class="bg-white p-4 sm:p-6 rounded-lg shadow-lg w-full max-w-lg max-h-[90vh] overflow-y-auto"
        >
          <!-- Tombol Close -->
          <button
            @click="hideMarkModal"
            class="absolute top-3 right-3 text-xl sm:text-2xl font-bold text-gray-700"
          >
            X
          </button>

          <!-- Judul Modal -->
          <h3 class="text-lg sm:text-xl font-bold text-center mb-4 sm:mb-6">
            Tambah/Perbarui Data Kognitif & Keterampilan
          </h3>

          <!-- Form -->
          <form @submit.prevent="saveMark">
            <!-- Enrollment ID -->
            <div class="mb-3">
              <label
                for="enrollmentId"
                class="block text-sm font-medium text-gray-700"
              >
                Enrollment ID
              </label>
              <input
                v-model="selectedEnrollmentId"
                id="enrollmentId"
                type="text"
                class="w-full px-3 py-2 border rounded-md bg-gray-100 text-gray-700"
                readonly
              />
            </div>

            <!-- Nama Siswa -->
            <div class="mb-3">
              <label
                for="studentName"
                class="block text-sm font-medium text-gray-700"
              >
                Nama Siswa
              </label>
              <input
                :value="
                  students.find(
                    (student) => student.id === Number(newMark.studentId)
                  )?.name || 'Siswa tidak ditemukan'
                "
                id="studentName"
                type="text"
                class="w-full px-3 py-2 border rounded-md bg-gray-100 text-gray-700"
                readonly
              />
            </div>

            <!-- Mata Pelajaran -->
            <div class="mb-3">
              <label
                for="mapelId"
                class="block text-sm font-medium text-gray-700"
              >
                Mata Pelajaran
              </label>
              <input
                :value="
                  courses.find((course) => course.id === newMark.mapelId)
                    ?.mapel || 'Mata pelajaran tidak ditemukan'
                "
                id="mapelId"
                type="text"
                class="w-full px-3 py-2 border rounded-md bg-gray-100 text-gray-700"
                readonly
              />
            </div>

            <!-- Tanggal Enrollment -->
            <div class="mb-3">
              <label
                for="enrollmentDate"
                class="block text-sm font-medium text-gray-700"
                >Tanggal Enrollment</label
              >
              <input
                type="date"
                v-model="newEnrollment.enrollment_date"
                id="enrollmentDate"
                required
                class="w-full px-4 py-2 border rounded-md"
              />
            </div>

            <!-- No. KD -->
            <div class="mb-3">
              <label
                for="enrollmentDate"
                class="block text-sm font-medium text-gray-700"
              >
                No. KD
              </label>
              <textarea
                v-model="newMark.noKd"
                @input="logChange('Keterampilan Dasar', $event.target.value)"
                id="description"
                class="mt-1 block w-full border-gray-300 rounded-md mb-2"
                placeholder="Masukkan Nomor Kompetensi Dasar"
              ></textarea>
            </div>

            <!-- Status -->
            <div class="mb-3">
              <label
                for="status"
                class="block text-sm font-medium text-gray-700"
              >
                Status
              </label>
              <select
                v-model="newMark.status"
                id="status"
                class="w-full px-3 py-2 border rounded-md"
              >
                <option value="active">Aktif</option>
                <option value="inactive">Tidak Aktif</option>
              </select>
            </div>

            <!-- Nilai Kognitif -->
            <h4 class="text-lg font-semibold mt-4 mb-4">Kognitif</h4>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label
                  for="cognitive1"
                  class="block text-sm font-medium text-gray-700"
                >
                  TEK
                </label>
                <input
                  v-model="newMark.cognitive1"
                  @input="logChange('Kognitif 1 (TEK)', $event.target.value)"
                  type="number"
                  id="cognitive1"
                  class="w-full px-3 py-2 border rounded-md"
                />
              </div>
              <div>
                <label
                  for="cognitive2"
                  class="block text-sm font-medium text-gray-700"
                >
                  NIL
                </label>
                <input
                  v-model="newMark.cognitive2"
                  @input="logChange('Kognitif 2 (NIL)', $event.target.value)"
                  type="number"
                  id="cognitive2"
                  class="w-full px-3 py-2 border rounded-md"
                />
              </div>
              <div>
                <label
                  for="cognitivePAS"
                  class="block text-sm font-medium text-gray-700"
                >
                  PAS
                </label>
                <input
                  v-model="newMark.cognitivePAS"
                  @input="logChange('Kognitif 3 (PAS)', $event.target.value)"
                  type="number"
                  id="cognitivePAS"
                  class="w-full px-3 py-2 border rounded-md"
                />
              </div>
              <div>
                <label
                  for="cognitiveAverage"
                  class="block text-sm font-medium text-gray-700"
                >
                  Rerata
                </label>
                <input
                  v-model="newMark.cognitiveAverage"
                  @input="logChange('Kognitif (AVG)', $event.target.value)"
                  type="number"
                  id="cognitiveAverage"
                  class="w-full px-3 py-2 border rounded-md"
                />
              </div>
            </div>

            <!-- Nilai Keterampilan -->
            <h4 class="text-lg font-semibold mt-4 mb-4">Keterampilan</h4>
            <div class="grid grid-cols-2 gap-3">
              <div>
                <label
                  for="skill1"
                  class="block text-sm font-medium text-gray-700"
                >
                  TEK
                </label>
                <input
                  v-model="newMark.skill1"
                  @input="
                    logChange('Keterampilan 2 (Skill1)', $event.target.value)
                  "
                  type="number"
                  id="skill1"
                  class="w-full px-3 py-2 border rounded-md"
                />
              </div>
              <div>
                <label
                  for="skill2"
                  class="block text-sm font-medium text-gray-700"
                >
                  NIL
                </label>
                <input
                  v-model="newMark.skill2"
                  @input="
                    logChange('Keterampilan 2 (Nil1)', $event.target.value)
                  "
                  type="number"
                  id="skill2"
                  class="w-full px-3 py-2 border rounded-md"
                />
              </div>
              <div>
                <label
                  for="skillPAS"
                  class="block text-sm font-medium text-gray-700"
                >
                  PAS
                </label>
                <input
                  v-model="newMark.skillPAS"
                  @input="
                    logChange('Keterampilan 3 (pas1)', $event.target.value)
                  "
                  type="number"
                  id="skillPAS"
                  class="w-full px-3 py-2 border rounded-md"
                />
              </div>
              <div>
                <label
                  for="skillAverage"
                  class="block text-sm font-medium text-gray-700"
                >
                  Rerata
                </label>
                <input
                  v-model="newMark.skillAverage"
                  @input="
                    logChange('Keterampilan 2 (avg)', $event.target.value)
                  "
                  type="number"
                  id="skillAverage"
                  class="w-full px-3 py-2 border rounded-md"
                />
              </div>
            </div>

            <!-- Nilai Akhir -->
            <div class="mt-4">
              <label
                for="finalMark"
                class="block text-sm font-medium text-gray-700"
              >
                Nilai Akhir
              </label>
              <input
                v-model="newMark.finalMark"
                @input="
                  logChange('Keterampilan 2 (NilaiAkhir)', $event.target.value)
                "
                type="number"
                id="finalMark"
                class="w-full px-3 py-2 border rounded-md"
              />
            </div>

            <!-- Tombol -->
            <div class="flex justify-end mt-4">
              <button
                type="button"
                @click="hideMarkModal"
                class="bg-gray-300 text-gray-700 py-2 px-3 rounded-lg mr-2"
              >
                Batal
              </button>
              <button
                type="submit"
                class="bg-blue-500 text-white py-2 px-3 rounded-lg"
              >
                Simpan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 mb-4">
        <div class="bg-white p-4 sm:p-5 rounded-md shadow-md text-center">
          <p class="text-lg sm:text-xl lg:text-2xl font-semibold text-blue-600">
            <span
              class="block text-base sm:text-lg lg:text-xl text-green-600 mb-1 font-semibold"
            >
              TOTAL PENDAFTARAN
            </span>
            <span class="flex items-center justify-center gap-2">
              <i class="fas fa-users"></i>{{ totalEnrollments }}
            </span>
          </p>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-md shadow-md text-center">
          <p
            class="text-lg sm:text-xl lg:text-2xl font-semibold text-green-600"
          >
            <span
              class="block text-base sm:text-lg lg:text-xl text-green-600 mb-1 font-semibold"
            >
              PENDAFTARAN AKTIF
            </span>
            <span class="flex items-center justify-center gap-2">
              <i class="fas fa-user-check"></i>{{ activeEnrollments }}
            </span>
          </p>
        </div>

        <div class="bg-white p-4 sm:p-5 rounded-md shadow-md text-center">
          <p class="text-lg sm:text-xl lg:text-2xl font-semibold text-red-600">
            <span
              class="block text-base sm:text-lg lg:text-xl text-green-600 mb-1 font-semibold"
            >
              PENDAFTARAN TIDAK AKTIF
            </span>
            <span class="flex items-center justify-center gap-2">
              <i class="fas fa-user-times"></i>{{ inactiveEnrollments }}
            </span>
          </p>
        </div>
      </div>

      <!-- Enrollments Table -->
      <div class="overflow-x-auto bg-white rounded-lg shadow-md mb-6">
        <table class="min-w-full table-auto border border-gray-300">
          <thead>
            <!-- Baris pertama header -->
            <tr class="bg-gray-100 border-b border-gray-300">
              <th
                rowspan="3"
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-300 w-12"
              >
                ID
              </th>
              <th
                rowspan="3"
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-300 w-48"
              >
                Nama Siswa
              </th>
              <th
                rowspan="3"
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-300 w-48"
              >
                Mata Pelajaran
              </th>
              <th
                rowspan="3"
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-300 w-48"
              >
                Nama Guru
              </th>

              <th
                rowspan="3"
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-300 w-36"
              >
                Tanggal Enrollment
              </th>
              <th
                rowspan="3"
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-300 w-24"
              >
                Status
              </th>
              <th
                rowspan="3"
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-300 w-24"
              >
                No. KD
              </th>
              <th
                colspan="4"
                class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-300"
              >
                Kognitif
              </th>
              <th
                rowspan="3"
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-300 w-24"
              >
                No. KD
              </th>
              <th
                colspan="4"
                class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-r border-gray-300"
              >
                Keterampilan
              </th>
              <th
                rowspan="3"
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700 border-r border-gray-300 w-12"
              >
                Nilai Akhir
              </th>
              <th
                rowspan="3"
                class="px-4 py-3 text-center text-sm font-semibold text-gray-700 border-l border-gray-300 w-36"
              >
                Actions
              </th>
            </tr>
            <!-- Baris kedua header -->
            <tr class="bg-gray-100 border-b border-gray-300">
              <th
                colspan="2"
                class="text-center text-sm font-semibold text-gray-700 border-l border-r border-gray-300"
              >
                Nilai 1
              </th>
              <th
                colspan="2"
                class="text-center text-sm font-semibold text-gray-700 border-r border-gray-300"
              >
                Nilai 2
              </th>
              <th
                colspan="2"
                class="text-center text-sm font-semibold text-gray-700 border-l border-r border-gray-300"
              >
                Nilai 1
              </th>
              <th
                colspan="2"
                class="text-center text-sm font-semibold text-gray-700 border-r border-gray-300"
              >
                Nilai 2
              </th>
            </tr>
            <!-- Baris ketiga header -->
            <tr class="bg-gray-100 border-b border-gray-300">
              <th
                class="text-center text-sm font-semibold text-gray-700 border-l border-r border-gray-300"
              >
                TEK
              </th>
              <th
                class="text-center text-sm font-semibold text-gray-700 border-r border-gray-300"
              >
                NIL
              </th>
              <th
                class="text-center text-sm font-semibold text-gray-700 border-l border-r border-gray-300"
              >
                TEK
              </th>
              <th
                class="text-center text-sm font-semibold text-gray-700 border-r border-gray-300"
              >
                NIL
              </th>
              <th
                class="text-center text-sm font-semibold text-gray-700 border-l border-r border-gray-300"
              >
                TEK
              </th>
              <th
                class="text-center text-sm font-semibold text-gray-700 border-r border-gray-300"
              >
                NIL
              </th>
              <th
                class="text-center text-sm font-semibold text-gray-700 border-l border-r border-gray-300"
              >
                TEK
              </th>
              <th
                class="text-center text-sm font-semibold text-gray-700 border-r border-gray-300"
              >
                NIL
              </th>
            </tr>
          </thead>
          <!--          <pre>{{ JSON.stringify(paginatedEnrollments, null, 2) }}</pre>
-->
          <tbody>
            <tr v-if="isLoading">
              <td colspan="16" class="text-center text-sm text-gray-700">
                Loading...
              </td>
            </tr>

            <tr
              v-for="enrollment in paginatedEnrollments"
              :key="enrollment.id"
              class="border-b border-gray-300"
            >
              <td
                class="px-4 py-3 text-sm text-gray-800 border-r border-gray-300"
              >
                {{ enrollment.id || 'ID tidak tersedia' }}
              </td>
              <td
                class="px-4 py-3 text-sm text-gray-800 border-r border-gray-300"
              >
                {{
                  enrollment.student && enrollment.student.name
                    ? enrollment.student.name
                    : 'Nama tidak tersedia'
                }}
              </td>
              <td
                class="px-4 py-3 text-sm text-gray-800 border-r border-gray-300"
              >
                {{
                  enrollment.mapel
                    ? enrollment.mapel.mapel
                    : 'Mapel tidak tersedia'
                }}
              </td>
              <td
                class="px-4 py-3 text-sm text-gray-800 border-r border-gray-300"
              >
                {{
                  enrollment.teacher && enrollment.teacher.name
                    ? enrollment.teacher.name
                    : 'Guru tidak tersedia'
                }}
              </td>
              <td
                class="px-4 py-3 text-sm text-gray-800 border-r border-gray-300"
              >
                {{
                  formatDate(enrollment.enrollment_date) ||
                  'Tanggal tidak tersedia'
                }}
              </td>
              <td
                class="px-4 py-3 text-sm text-gray-800 border-l border-gray-300 border-r border-gray-300"
              >
                <span
                  :class="{
                    'text-green-500': enrollment.status === 'active',
                    'text-red-500': enrollment.status === 'inactive',
                  }"
                >
                  {{ enrollment.status || 'Status tidak tersedia' }}
                </span>
              </td>
              <td class="text-center border-r border-gray-300">
                {{ enrollment.no_kd || '-' }}
              </td>
              <td class="text-center border-r border-gray-300">
                {{ enrollment.cognitive_1 || '-' }}
              </td>
              <td class="text-center border-r border-gray-300">
                {{ enrollment.cognitive_2 || '-' }}
              </td>
              <td class="text-center border-r border-gray-300">
                {{ enrollment.cognitive_pas || '-' }}
              </td>
              <td class="text-center border-r border-gray-300">
                {{ enrollment.cognitive_average || '-' }}
              </td>
              <td class="text-center border-r border-gray-300">
                {{ enrollment.no_kd || '-' }}
              </td>
              <td class="text-center border-r border-gray-300">
                {{ enrollment.skill_1 || '-' }}
              </td>
              <td class="text-center border-r border-gray-300">
                {{ enrollment.skill_2 || '-' }}
              </td>
              <td class="text-center border-r border-gray-300">
                {{ enrollment.skill_pas || '-' }}
              </td>
              <td class="text-center border-r border-gray-300">
                {{ enrollment.skill_average || '-' }}
              </td>
              <td class="text-center border-r border-gray-300">
                {{ enrollment.final_mark || '-' }}
              </td>
              <td
                class="px-4 py-3 text-sm text-gray-800 border-l border-gray-300"
              >
                <div class="flex space-x-2">
                  <button
                    @click="editEnrollment(enrollment.id)"
                    class="bg-blue-500 text-white py-1 px-4 rounded-lg hover:bg-blue-700"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteEnrollment(enrollment.id)"
                    class="bg-red-500 text-white py-1 px-4 rounded-lg hover:bg-red-700"
                  >
                    Delete
                  </button>
                  <button
                    @click="
                      markEnrollment(
                        enrollment.id,
                        enrollment.student_id,
                        enrollment.course_id
                      )
                    "
                    class="bg-green-500 text-white py-1 px-4 rounded-lg hover:bg-green-700"
                  >
                    Mark
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
      <!-- Pagination -->
      <div class="flex justify-between items-center">
        <!-- Tombol Previous -->
        <button
          @click="goToPage(currentPage - 1)"
          :disabled="currentPage === 1"
          class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400"
        >
          Previous
        </button>

        <!-- Informasi Halaman -->
        <span class="text-gray-700">
          Page {{ currentPage }} of {{ totalPages }}
        </span>

        <!-- Tombol Next -->
        <button
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage === totalPages"
          class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400"
        >
          Next
        </button>
      </div>

      <div class="row mt-3 me-3 mb-20">
        <div class="col-12">
          <p class="fw-bold">Keterangan:</p>
          <div class="d-flex">
            <div class="me-3">
              <span class="badge bg-info text-black fw-bold"
                >Kompetensi Dasar (KD)</span
              >
            </div>
            <div class="me-3">
              <span class="badge bg-primary text-black fw-bold"
                >Teknis\Teknik (TEK)</span
              >
            </div>
            <div class="me-3">
              <span class="badge bg-danger text-black fw-bold"
                >Nilai (NIL)</span
              >
            </div>
            <div class="me-3">
              <span class="badge bg-warning text-black fw-bold"
                >Penilaian Akhir Semester (PAS)</span
              >
            </div>
          </div>
        </div>
      </div>
      <!--<pre>{{ paginatedEnrollments }}</pre> -->
    </main>

    <!-- Sidebar -->
    <SidebarTeacher />
  </div>
</template>
