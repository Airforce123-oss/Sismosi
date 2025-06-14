<script setup>
import { ref, onMounted, computed, watch, toRaw } from 'vue';
import { initFlowbite } from 'flowbite';
import SidebarTeacher from '@/Components/SidebarTeacher.vue';
import Pagination from '../../../Components/Pagination7.vue';
import { useForm, usePage, Head, router, Link } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import axios from 'axios';

// Amb
const { props } = usePage();
const students = ref(props.students || []);
const filteredCourses = ref([]);
const teachers = ref(props.teachers || []);
const mapels = ref(props.mapels || []);
const courses = ref(props.courses || []);
const tugas = ref(props.tugas || { data: [], meta: {}, links: {} });
const classesForStudent = ref(props.classes_for_student || []);

const meta = ref(props.meta || {});
const links = ref(props.links || {});

//const totalCourses = computed(() => tugas.value?.data?.length ?? 0);
const totalCourses = computed(() => props.tugas?.meta?.total ?? 0);

//console.log('🔍 Props.students:', props.students);
//console.log('Props.teachers toRaw:', toRaw(props.teachers));
//console.log('🔎 Contoh teacher id 1:', toRaw(teachers.value)[1]);
for (let i = 0; i < props.teachers.length; i++) {
  const teacher = props.teachers[i];
  const masterMapel = toRaw(teacher.masterMapel);

  //console.log(`--- Guru ke-${i} ---`);
  //console.log(`Nama Guru: ${teacher.name}`);
  //console.log(`NIP: ${teacher.nip}`);

  if (Array.isArray(masterMapel)) {
    if (masterMapel.length === 0) {
      //console.log(`⚠️ masterMapel kosong untuk guru ke-${i}`);
    } else {
      masterMapel.forEach((mapel, index) => {
        //console.log(`  Mapel ${index}:`, mapel);

        if (mapel.id && mapel.nama_mapel) {
          //console.log(`    → ID: ${mapel.id}, Nama: ${mapel.nama_mapel}`);
        } else {
          console.log(`    ⚠️ Mapel tidak lengkap:`, mapel);
        }
      });
    }
  } else {
    console.log(`❌ masterMapel bukan array di guru ke-${i}:`, masterMapel);
  }
}

const found = toRaw(teachers.value).find((t) => t.id === 2);
console.log('Guru dengan ID 2:', found);

// Form Data for User Authentication
const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

console.log('Form data:', form);

const enrollments = ref([]);

// Modal Control
const isTaskModalOpen = ref(false);
const isEditModalOpen = ref(false);

// Task Form Data
const taskForm = ref({
  mapel_id: '',
  description: '',
  teacher_id: props.auth.user?.id || null,
  student_id: '',
  class_id: '',
});

const editForm = ref({
  id: null,
  mapel_id: '',
  description: '',
  teacher_id: props.auth.user?.id || null,
  student_id: '',
  class_id: '',
});

// Pagination & Search Data
const totalPages = ref(1);
const currentPage = ref(1);

console.log('taskForm yang dikirim:', taskForm.value);

const searchQuery = ref('');

// Modal Handling Functions
const showAddModal = () => {
  isTaskModalOpen.value = true;
};

const selectedId = ref(null);
const selectedMapelTeachers = ref([]);

watch(
  [() => props.auth.user.id, () => teachers.value],
  ([userId, teachersList]) => {
    const numericUserId = Number(userId);
    if (!numericUserId || !teachersList.length) return;

    console.log('User ID login:', numericUserId);
    console.log('Daftar teachers:', toRaw(teachersList));

    const currentTeacher = toRaw(teachersList).find(
      (t) => Number(t.id) === numericUserId
    );

    if (!currentTeacher) {
      console.warn(`Guru dengan ID ${numericUserId} tidak ditemukan`);
      return;
    }

    // Ganti sesuai field dari server
    const mapels = Array.isArray(currentTeacher.masterMapel)
      ? currentTeacher.masterMapel
      : [];

    console.log('Mapel milik currentTeacher:', mapels);

    if (!mapels.length) {
      console.warn(`Guru ${currentTeacher.name} tidak punya mapel`);
    }

    filteredCourses.value = mapels.map((mapel) => ({
      id: mapel.id,
      nama_mapel: mapel.nama_mapel || '[Tanpa Nama]',
    }));

    console.log(
      `Mapel untuk guru ${currentTeacher.name}:`,
      toRaw(filteredCourses.value)
    );
  },
  { immediate: true }
);

const saveEnrollmentsToLocalStorage = () => {
  localStorage.setItem('enrollments', JSON.stringify(enrollments.value));
};

watch(enrollments, saveEnrollmentsToLocalStorage, { deep: true });

//const isModalOpen = ref(false);
const closeTaskModal = () => {
  console.log('Closing modal - before', isTaskModalOpen.value);
  isTaskModalOpen.value = false;
  console.log('Closing modal - after', isTaskModalOpen.value);
};

const fetchTugas = (page = 1) => {
  //console.log('fetchTugas dipanggil dengan page:', page);
  router.visit(route('membuatTugasSiswa'), {
    method: 'get',
    data: { page },
    preserveScroll: true,
    preserveState: true,
    only: ['tugas'],
    onSuccess: () => {
      //console.log('Navigasi ke halaman tugas berhasil');
    },
    onError: (errors) => {
      console.error('Gagal fetch tugas (Inertia):', errors);
    },
  });
};

// Save Task Function
const saveTask = async () => {
  try {
    // Set default class_id
    if (
      (!taskForm.value.class_id || taskForm.value.class_id === null) &&
      classesForStudent.value?.length > 0
    ) {
      const rawClasses = toRaw(classesForStudent.value);
      taskForm.value.class_id = rawClasses[0]?.id || null;
      console.log('Class ID setelah diatur:', taskForm.value.class_id);
    }

    // Set default mapel_id dari courses yang berisi pivot + mapel + guru
    if (
      (!taskForm.value.mapel_id || taskForm.value.mapel_id === null) &&
      filteredCourses.value?.length > 0
    ) {
      console.log(
        'filteredCourses.value sebelum set mapel_id:',
        toRaw(filteredCourses.value)
      );
      taskForm.value.mapel_id = toRaw(filteredCourses.value)[0]?.id || null;
      console.log('Mapel ID setelah diatur:', taskForm.value.mapel_id);
    }

    // Set default student_id
    if (
      (!taskForm.value.student_id || taskForm.value.student_id === null) &&
      students.value?.length > 0
    ) {
      taskForm.value.student_id = toRaw(students.value)[0]?.id || null;
      console.log('Student ID setelah diatur:', taskForm.value.student_id);
    }

    // Validasi id numerik
    const teacherId = parseInt(taskForm.value.teacher_id);
    const studentId = parseInt(taskForm.value.student_id);
    const classId = parseInt(taskForm.value.class_id);
    const mapelId = parseInt(taskForm.value.mapel_id);

    if (isNaN(teacherId)) {
      console.error('Teacher ID belum dipilih atau tidak valid.');
      return;
    }
    if (isNaN(studentId)) {
      console.error('Student ID belum dipilih atau tidak valid.');
      return;
    }
    if (isNaN(classId)) {
      console.error('Class ID belum dipilih atau tidak valid.');
      return;
    }
    if (isNaN(mapelId)) {
      console.error('Mapel ID belum dipilih atau tidak valid.');
      return;
    }

    // Assign kembali nilai numerik ke taskForm
    taskForm.value.teacher_id = teacherId;
    taskForm.value.student_id = studentId;
    taskForm.value.class_id = classId;
    taskForm.value.mapel_id = mapelId;

    // Setup axios & csrf
    const token = document.head.querySelector(
      'meta[name="csrf-token"]'
    ).content;
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    axios.defaults.withCredentials = true;

    console.log('Data yang akan dikirim ke backend:', {
      mapel_id: taskForm.value.mapel_id,
      description: taskForm.value.description,
      teacher_id: taskForm.value.teacher_id,
      student_id: taskForm.value.student_id,
      class_id: taskForm.value.class_id,
    });

    // Kirim data ke backend API
    const response = await axios.post('/api/tugas', {
      mapel_id: taskForm.value.mapel_id,
      description: taskForm.value.description,
      teacher_id: taskForm.value.teacher_id,
      student_id: taskForm.value.student_id,
      class_id: taskForm.value.class_id,
    });

    console.log('Response dari API:', response.data);

    if (response.data?.id || response.data?.data?.id) {
      console.log('Tugas berhasil disimpan.');
      closeTaskModal();
    } else {
      console.warn('Gagal menyimpan tugas, response:', response.data);
    }
  } catch (error) {
    console.error(
      'Error saat menyimpan tugas:',
      error.response?.data || error.message || 'Unknown error occurred'
    );
  }
};

const updateTask = async () => {
  try {
    console.log('Data sebelum update:', taskForm.value);

    // Konversi teacher_id ke integer
    editForm.value.teacher_id = parseInt(editForm.value.teacher_id);

    const response = await axios.put(
      '/api/enrollments/' + taskForm.value.id,
      editForm.value
    );

    console.log('Respons API setelah update:', response.data);

    if (response.data && response.data.enrollment) {
      const updatedEnrollment = response.data.enrollment;

      const index = enrollments.value.findIndex(
        (enrollment) => enrollment.id === updatedEnrollment.id
      );

      if (index !== -1) {
        enrollments.value[index] = { ...updatedEnrollment };
        console.log('Enrollments setelah update:', toRaw(enrollments.value));
      }

      localStorage.setItem('editForm', JSON.stringify(editForm.value));
      closeEditModal();
    } else {
      console.log('Gagal mengupdate enrollment:', response.data);
    }
  } catch (error) {
    console.error(
      'Error updating task:',
      error.response?.data || error.message || 'Unknown error occurred'
    );
  }
};

const handleTask = () => {
  if (taskForm.value.id) {
    // Jika ID ada, berarti ini adalah update
    updateTask();
  } else {
    // Jika ID tidak ada, berarti ini adalah add (tambah)
    saveTask();
  }
};

const pageNumber = ref(1);

watch(pageNumber, (newPage) => {
  console.log('Watcher: pageNumber berubah jadi', newPage);
  fetchTugas(newPage);
});

const updatedPageNumber = (page) => {
  if (!page || isNaN(page)) {
    console.warn('Halaman tidak valid:', page);
    return;
  }

  console.log('Berpindah ke halaman:', page);
  pageNumber.value = Number(page); // Akan memicu fetch ulang jika ada watch()
};

// On Mounted Hook to Fetch Initial Data
onMounted(async () => {
  try {
    // Ambil data taskForm dari localStorage
    const savedTaskForm = localStorage.getItem('taskForm');
    if (savedTaskForm) {
      taskForm.value = JSON.parse(savedTaskForm);
    }

    // Ambil enrollments dari localStorage atau fetch jika tidak ada
    const savedEnrollments = localStorage.getItem('enrollments');
    if (savedEnrollments) {
      enrollments.value = JSON.parse(savedEnrollments);
    } else {
      const enrollmentsResponse = await axios.get('/api/enrollments', {
        params: { page: currentPage.value },
      });
      enrollments.value = enrollmentsResponse.data.data;
      totalPages.value = enrollmentsResponse.data.last_page;
      localStorage.setItem('enrollments', JSON.stringify(enrollments.value));
    }

    // Courses
    const savedCourses = localStorage.getItem('courses');
    if (savedCourses) {
      courses.value = JSON.parse(savedCourses);
    } else {
      const { data } = await axios.get('/api/courses');
      courses.value = data.data;
      localStorage.setItem('courses', JSON.stringify(courses.value));
    }

    // Students
    const savedStudents = localStorage.getItem('students');
    if (savedStudents) {
      students.value = JSON.parse(savedStudents);
    } else {
      const { data } = await axios.get('/api/students');
      students.value = data.data;
      localStorage.setItem('students', JSON.stringify(students.value));
    }

    // Set default mapel_id dan student_id jika belum diisi
    if (!taskForm.value.mapel_id && courses.value.length) {
      taskForm.value.mapel_id = courses.value[0].id;
    }

    if (!taskForm.value.student_id && students.value.length) {
      taskForm.value.student_id = students.value[0].id;
    }

    // Fetch teachers
    const teachersResponse = await axios.get('/api/teachers/all');
    if (teachersResponse.data?.data) {
      console.warn(
        '🎓 Teachers fetched from API:',
        teachersResponse.data.data.length
      );
      console.log(
        '🆔 IDs:',
        teachersResponse.data.data.map((t) => t.id)
      );
      teachers.value = teachersResponse.data.data;
    }
  } catch (error) {
    console.error('Error fetching data:', error);
  }

  initFlowbite();
});

const saveTaskFormToLocalStorage = () => {
  localStorage.setItem('taskForm', JSON.stringify(taskForm.value));
};

watch(taskForm, saveTaskFormToLocalStorage, { deep: true });

watch(
  () => props.classes_for_student,
  (newVal) => {
    classesForStudent.value = newVal || [];
    // Set default class_id juga di sini
    if (classesForStudent.value.length > 0) {
      taskForm.value.class_id = toRaw(classesForStudent.value)[0].id || null;
    }
  },
  { immediate: true }
);

watch(
  pageNumber,
  (newPage) => {
    if (!newPage || isNaN(newPage)) return;

    // Update URL tanpa reload halaman
    const baseUrl = window.location.origin + window.location.pathname;
    const newUrl = `${baseUrl}?page=${newPage}`;
    window.history.pushState({}, '', newUrl);

    // Panggil ulang data untuk halaman baru
    fetchTugas(newPage);
  },
  { immediate: true }
);

watch(
  () => props.mapels,
  (newVal) => {
    mapels.value = newVal || [];
  }
);

// Optional: Watch untuk teacher_id hanya jika kamu ingin logging atau validasi tambahan
watch(
  () => taskForm.teacher_id,
  (newTeacherId) => {
    const selectedTeacher = selectedMapelTeachers.value.find(
      (teacher) => teacher.id === parseInt(newTeacherId)
    );

    console.log('Selected Teacher:', selectedTeacher);
  }
);
// Edit and Delete Enrollment Functions
function editEnrollment(enrollment) {
  console.log('Editing enrollment:', enrollment);
  editForm.value = { ...enrollment };
  isEditModalOpen.value = true;
  console.log('Modal state after edit:', isEditModalOpen.value);
}

function closeEditModal() {
  isEditModalOpen.value = false;
}

const deleteEnrollment = (id) => {
  console.log(`Delete enrollment dengan ID: ${id}`);
};

const submitEdit = async () => {
  try {
    editForm.value.teacher_id = String(editForm.value.teacher_id);
    // Log data sebelum pengiriman untuk memastikan data yang dikirim sudah benar
    console.log('Data yang akan diperbarui:', editForm.value);

    // Set CSRF Token di header
    const token = document.head.querySelector(
      'meta[name="csrf-token"]'
    ).content;
    axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
    axios.defaults.withCredentials = true;

    // Periksa apakah editForm memiliki ID yang valid
    if (!editForm.value.id) {
      console.error('ID form tidak valid.');
      return; // Hentikan eksekusi jika ID tidak valid
    }

    // Validasi jika teacher_id belum dipilih
    if (!editForm.value.teacher_id) {
      console.error('Teacher ID belum dipilih.');
      return; // Tampilkan pesan kesalahan jika `teacher_id` belum dipilih
    }

    // Kirim data update ke API
    const response = await axios.put(
      '/api/enrollments/' + editForm.value.id,
      editForm.value // Kirim seluruh data dari form untuk update
    );

    // Log respons API setelah update
    console.log('Respons API setelah update:', response.data);

    // Periksa apakah update berhasil
    if (response.data && response.data.enrollment) {
      // Jika berhasil, perbarui data di sisi client
      const updatedEnrollment = response.data.enrollment;

      // Cari index enrollment yang perlu diperbarui
      const index = enrollments.value.findIndex(
        (enrollment) => enrollment.id === updatedEnrollment.id
      );

      if (index !== -1) {
        // Update data di enrollments array
        enrollments.value[index] = updatedEnrollment;

        // Verifikasi pembaruan
        console.log('Enrollments setelah update:', toRaw(enrollments.value));
      }

      // Menyimpan perubahan di localStorage (optional)
      localStorage.setItem('editForm', JSON.stringify(editForm.value));

      // Menutup modal setelah berhasil menyimpan
      closeEditModal();
    } else {
      console.log('Gagal mengupdate enrollment:', response.data);
    }
  } catch (error) {
    console.error(
      'Error updating enrollment:',
      error.response?.data || error.message || 'Unknown error occurred'
    );
  }
};
</script>

<style scoped>
@import url('https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');

/* Responsif untuk tabel */
.table {
  width: 100%;
  margin-bottom: 1rem;
  border-collapse: collapse;
}

/* Responsif untuk pagination */
.pagination {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  margin-top: 1rem;
}

.pagination button {
  padding: 8px 12px;
  margin: 5px;
  cursor: pointer;
}

.pagination button:disabled {
  cursor: not-allowed;
  background-color: #f0f0f0;
}

/* Responsif untuk card */
.card {
  background-color: #fff;
  padding: 1rem;
  border-radius: 0.5rem;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
  text-align: center;
  transition: transform 0.3s ease;
}

.card:hover {
  transform: scale(1.05);
}

.card h3 {
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
}
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.modal-content {
  background-color: white;
  border-radius: 8px;
  padding: 20px;
  width: 90%;
  max-width: 500px;
  z-index: 10000;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.close-btn {
  font-size: 20px;
  background: none;
  border: none;
  cursor: pointer;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
}

.btn-save {
  background-color: #4caf50;
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
}

.btn-cancel {
background-color: #f44336;
  color: white;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
}

.btn-save:hover {
  background-color: #45a049;
}

.btn-cancel:hover {
  background-color: #e53935;
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
            style=""
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

    <!-- Main -->

    <main class="p-7 md:ml-64 h-screen">
      <Head title="Membuat Tugas Siswa" />
      <h2
        class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 mt-20 mb-6 text-center"
      >
        Tugas Siswa
      </h2>

      <!-- Kontrol -->
      <div class="container mx-auto px-4 py-6">
        <div
          class="flex flex-wrap sm:flex-nowrap justify-between items-center space-y-4 sm:space-y-0"
        >
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari Tugas..."
            class="w-full sm:w-auto px-4 py-2 border rounded-md"
          />
          <button
            class="btn btn-primary modal-title fs-5 w-full sm:w-auto"
            @click="showAddModal"
          >
            <i class="fa fa-plus mr-2"></i> Tambah Tugas
          </button>
        </div>

        <div
          v-if="isTaskModalOpen"
          class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center"
        >
          <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
            <!-- Add Judul Modal -->
            <h3 class="text-xl font-bold text-center mb-6">
              Tambah Tugas Siswa
            </h3>
            <!-- Form -->
            <form @submit.prevent="handleTask">
              <!-- Input Guru -->
              <!-- Nama Guru (Auto-selected & Disabled) -->
              <div class="mb-4">
                <label
                  for="teacher_id"
                  class="block text-sm font-medium text-gray-700"
                >
                  Nama Guru
                </label>
                <select
                  v-model="taskForm.teacher_id"
                  id="teacher_id"
                  class="w-full px-4 py-2 border rounded-md bg-gray-100 cursor-not-allowed"
                  disabled
                >
                  <option
                    v-if="props.auth && props.auth.user"
                    :value="props.auth.user.id"
                  >
                    {{ props.auth.user.name }}
                  </option>
                  <option v-else disabled>Guru tidak tersedia</option>
                </select>
              </div>

              <!-- Nama Mata Pelajaran -->
              <div class="mb-4">
                <label
                  for="course_id"
                  class="block text-sm font-medium text-gray-700"
                >
                  Nama Mata Pelajaran
                </label>
                <select
                  v-model="taskForm.mapel_id"
                  id="course_id"
                  class="w-full px-4 py-2 border rounded-md"
                >
                  <option value="" disabled selected>
                    Pilih Mata Pelajaran
                  </option>
                  <option
                    v-for="course in filteredCourses"
                    :key="course.id"
                    :value="course.id"
                  >
                    {{ course.nama_mapel }}
                  </option>
                </select>
              </div>

              <!-- Input Deskripsi Tugas -->
              <div class="mb-4">
                <label
                  for="description"
                  class="block text-sm font-medium text-gray-700"
                  >Deskripsi</label
                >
                <textarea
                  v-model="taskForm.description"
                  id="description"
                  class="mt-1 block w-full border-gray-300 rounded-md"
                  placeholder="Masukkan deskripsi"
                ></textarea>
              </div>

              <!-- Input Kelas -->
              <div class="mb-4">
                <label
                  for="class_id"
                  class="block text-sm font-medium text-gray-700"
                >
                  Kelas
                </label>
                <select
                  v-model="taskForm.class_id"
                  class="w-full px-4 py-2 border rounded-md"
                >
                  <option value="" disabled>Pilih Kelas</option>
                  <option
                    v-for="kelas in props.classes_for_student"
                    :key="kelas.id"
                    :value="kelas.id"
                  >
                    {{ kelas.name }}
                  </option>
                </select>
              </div>

              <div class="flex justify-end space-x-4">
                <button
                  type="button"
                  @click="closeTaskModal"
                  class="btn btn-secondary mr-3"
                >
                  Batal
                </button>
                <button type="submit" class="btn btn-primary mr-2">
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
        <div
          v-if="isEditModalOpen"
          class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center"
        >
          <div class="bg-white p-6 rounded-lg w-96 z-50">
            <h2 class="text-xl text-center font-bold mb-4">Edit Enrollment</h2>
            <form @submit.prevent="submitEdit">
              <div class="mb-4">
                <label
                  for="mapel_id"
                  class="block text-sm font-medium text-gray-700"
                  >Mapel</label
                >
                <input
                  v-model="editForm.mapel_id"
                  type="text"
                  id="mapel_id"
                  class="mt-1 block w-full border-gray-300 rounded-md"
                  readonly
                />
              </div>
              <div class="mb-4">
                <label
                  for="description"
                  class="block text-sm font-medium text-gray-700"
                  >Description</label
                >
                <textarea
                  v-model="editForm.description"
                  id="description"
                  class="mt-1 block w-full border-gray-300 rounded-md"
                  placeholder="Masukkan deskripsi"
                ></textarea>
              </div>
              <div class="mb-4">
                <label
                  for="teacher_id"
                  class="block text-sm font-medium text-gray-700"
                  >Teacher ID</label
                >
                <select
                  v-model="editForm.teacher_id"
                  id="teacher"
                  class="w-full px-4 py-2 border rounded-md"
                >
                  <option value="" disabled selected>Pilih Guru</option>
                  <option
                    v-for="teacher in teachers"
                    :key="teacher.id"
                    :value="teacher.id"
                  >
                    {{ teacher.name }}
                  </option>
                </select>
              </div>
              <div class="mb-4">
                <label
                  for="teacher_id"
                  class="block text-sm font-medium text-gray-700"
                  >Siswa</label
                >
                <input
                  v-model="editForm.student_id"
                  type="text"
                  id="mapel_id"
                  class="mt-1 block w-full border-gray-300 rounded-md"
                  readonly
                />
              </div>

              <div class="flex justify-end">
                <button @click="closeEditModal" class="btn btn-primary mr-2">
                  Batal
                </button>
                <button type="submit" class="btn btn-secondary mr-3">
                  Simpan
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div
        class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 mb-6 text-center"
      >
        <div class="bg-white p-6 rounded-lg shadow-lg text-center">
          <p class="text-xl sm:text-2xl lg:text-3xl font-bold text-blue-600">
            Total Tugas: {{ totalCourses }}
          </p>
        </div>
      </div>

      <!-- Tabel -->
      <div
        class="w-full overflow-x-auto overflow-y-auto max-h-[80vh] bg-white rounded-xl shadow-lg mb-8"
      >
        <table class="min-w-full table-auto border-collapse">
          <thead class="bg-gray-100 sticky top-0 z-10">
            <tr>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                ID
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                Mata Pelajaran
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                Deskripsi
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                Guru
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                Kelas
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="text-gray-700 text-sm md:text-base">
            <tr
              v-for="task in tugas.data"
              :key="task.id"
              class="border-b hover:bg-gray-50 transition duration-150"
            >
              <td class="px-4 py-3 whitespace-nowrap">{{ task.id }}</td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ task.mapel?.mapel ?? '—' }}
              </td>
              <td class="px-4 py-3 whitespace-pre-wrap">
                {{ task.description }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ task.teacher?.name ?? '—' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ task.kelas?.name ?? '—' }}
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-center space-x-2">
                  <button
                    @click="editTask(task)"
                    class="inline-flex items-center gap-2 bg-blue-500 text-white h-9 px-4 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300 transition"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="w-4 h-4"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15.232 5.232l3.536 3.536M9 11l6-6m2 2L11 15H9v-2l6-6z"
                      />
                    </svg>
                    Edit
                  </button>

                  <button
                    @click="deleteTask(task.id)"
                    class="inline-flex items-center gap-2 bg-red-500 text-white h-9 px-4 rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 transition"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="w-4 h-4"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4a1 1 0 011 1v1H9V4a1 1 0 011-1z"
                      />
                    </svg>
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <Pagination :data="props.tugas" :updatedPageNumber="updatedPageNumber" />
    </main>
    <!-- Sidebar -->
    <SidebarTeacher />
  </div>
</template>
