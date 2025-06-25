<script setup>
import { onMounted, ref, computed, watch } from 'vue';
import { initFlowbite } from 'flowbite';
import axios from 'axios';
import SidebarTeacher from '@/Components/SidebarTeacher.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

// State for student profile
const studentProfile = ref({});
const paginatedBooks = ref([]);
const searchQuery = ref('');

// Modal state
const showModalAdd = ref(false);
const showModalEdit = ref(false);

// Function to open the modal
const openAddModal = () => {
  showModalAdd.value = true;
  showModalEdit.value = false;
};

const editStudent = (student) => {
  // Set form fields with the current student data
  console.log('Student received in editStudent:', student);
  form.name = student.name;
  form.student_id = student.student_id;
  form.gender = student.gender;
  form.class_id = student.class_id;
  form.parentName = student.parent_name;
  form.address = student.address;

  // Open the modal
  showModalEdit.value = true;
};

// Function to close the modal
const closeModal = () => {
  showModalAdd.value = false;
  showModalEdit.value = false;
};

const students = ref([]);

const props = defineProps({
  auth: { type: Object },
  classes_for_student: {
    type: Object,
    required: true,
  },
  teachers: { type: Object, default: () => ({ data: [] }) },
  genders: {
    type: Array,
    default: () => [], // Pastikan `genders` adalah array meskipun kosong
  },
});

console.log('Classes for student:', props.classes_for_student);

const classesForStudent = props.classes || {};
console.log(classesForStudent);

const detailStudentsState = ref([]);
console.log(detailStudentsState.value);

const getClassName = (classId) => {
  const classItem = props.classes_for_student.find(
    (item) => item.id === classId
  );
  return classItem ? classItem.name : '-';
};

const genders = [...props.genders];

const getGenderName = (id) => {
  const genderItem = props.genders.find((item) => item.id === id); // Asumsi item memiliki `id`
  return genderItem ? genderItem.name : '-';
};

watch(
  () => props.classes_for_student,
  (newValue) => {
    console.log('Data classes_for_student:', newValue);
  }
);

const form = useForm({
  name: '',
  student_id: '',
  gender: '',
  class_id: '',
  parentName: '',
  address: '',
});

console.log('Form Data yang Dikirim:', {
  name: form.name,
  student_id: form.student_id,
  class_id: form.class_id,
  gender: form.gender,
  parent_name: form.parentName,
  address: form.address,
});

console.log('parentName:', form.parentName);

const studentData = ref(null);

const addStudentData = (newStudent) => {
  if (newStudent) {
    students.value.push(newStudent); // Menambahkan data siswa baru
  }
};

// Example entries
const entries = ref([
  {
    date: '11 Okt 2023',
    parentName: 'Nia Gustiani',
    studentName: 'Rangea Pratama',
    gender: 'L',
    class: 'X IPS 3',
    issue: 'Ketahuan membawa rokok ke sekolah',
    action:
      'Siswa diberi sanksi oleh BK dan orang tua diminta untuk bekerjasama memantau anak di rumah',
    note: '',
  },
  {
    date: '16 Nov 2023',
    parentName: 'Dede Purwati',
    studentName: 'Syifaus Saadah',
    gender: 'P',
    class: 'X IPS 3',
    issue: 'Konsultasi beasiswa siswa berprestasi',
    action:
      'Siswa diarahkan mengikuti seleksi yang berkaitan dengan penambahan pengetahuan akademik',
    note: '',
  },
  {
    date: '20 Nov 2023',
    parentName: 'Popon Rohayati',
    studentName: 'Zakey Muhammad Husni',
    gender: 'L',
    class: 'X IPS 3',
    issue: 'Konsultasi perihal pembelajaran di sekolah',
    action:
      'Siswa diberikan stimulus agar bisa lebih konsentrasi dan semangat mengikuti pembelajaran di sekolah',
    note: '',
  },
]);

const filter = ref({
  class: '',
  gender: '',
});

const uniqueClasses = computed(() => {
  const classes = entries.value.map((entry) => entry.class);
  return [...new Set(classes)];
});

const filteredEntries = computed(() => {
  return entries.value.filter((entry) => {
    const matchClass = filter.value.class
      ? entry.class === filter.value.class
      : true;
    const matchGender = filter.value.gender
      ? entry.gender === filter.value.gender
      : true;
    return matchClass && matchGender;
  });
});

const books = ref({ data: [], total: 0, per_page: 5 });
const currentPage = ref(1);
const itemsPerPage = 5;
const totalPages = ref(0);

const fetchBooks = async () => {
  const response = await axios.get(
    `/api/buku_penghubungs?page=${currentPage.value}`
  );
  books.value = response.data;
  totalPages.value = Math.ceil(books.value.total / itemsPerPage);
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
    fetchBooks();
  }
};

const prevPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
    fetchBooks();
  }
};

// Fetch student data from API
const fetchStudents = async () => {
  let page = 1;
  let allStudents = [];
  try {
    let response;
    do {
      response = await axios.get(
        `http://127.0.0.1:8000/api/students?page=${page}`
      );
      console.log(`Fetched Page ${page}:`, response.data.data);
      allStudents = [...allStudents, ...response.data.data];
      page++;
    } while (response.data.next_page_url);

    students.value = allStudents;
    console.log('All Students Fetched:', students.value);
  } catch (error) {
    console.error('Error fetching students:', error);
  }
};

const fetchDetailStudents = async () => {
  let page = 1;
  let allDetails = []; // Array untuk menampung semua data
  try {
    let response;
    do {
      // Fetch data dari API detailstudents dengan parameter page
      response = await axios.get(
        `http://127.0.0.1:8000/api/detailstudents?page=${page}`
      );
      console.log(`Fetched Page ${page}:`, response.data.data);

      // Gabungkan data dari halaman saat ini ke array allDetails
      allDetails = [...allDetails, ...response.data.data];
      page++;
    } while (response.data.next_page_url); // Lanjutkan jika masih ada halaman berikutnya

    console.log('All Detail Students Fetched:', allDetails);

    // Simpan data ke state atau variabel
    detailStudentsState.value = allDetails; // Misal, menggunakan Vue ref
  } catch (error) {
    console.error('Error fetching detail students:', error);
  }
};

// Function to handle form submission
const submitForm = async () => {
  console.log('Form Data Setelah Validasi:', {
    name: form.name,
    student_id: form.student_id,
    gender: form.gender,
    class_id: form.class_id,
    parentName: form.parentName,
    address: form.address,
  });

  try {
    // Mengirim data ke API yang benar (detailstudents)
    const response = await axios.post('/api/students', {
      name: form.name,
      student_id: form.student_id, // pastikan menggunakan student_id sesuai dengan field di tabel
      class_id: form.class_id, // sesuai dengan field di tabel
      gender: form.gender,
      parent_name: form.parentName ?? null, // Send null if no parent name
      address: form.address ?? null, // Send null if no address
    });

    console.log('API Response:', response.data);

    // Cek apakah statusnya 200 atau 201 dan pastikan pesan berisi "Siswa berhasil ditambahkan!"
    if (
      (response.status === 200 || response.status === 201) &&
      response.data.message &&
      response.data.message.includes('Siswa berhasil ditambahkan!')
    ) {
      // Menambahkan data siswa baru ke dalam array students
      addStudentData(response.data.student);

      // Reset form atau tindak lanjut lainnya
      form.name = '';
      form.student_id = '';
      form.gender = '';
      form.class_id = '';
      form.parentName = '';
      form.address = '';

      alert('Data siswa berhasil ditambahkan!');
    } else {
      alert(
        'Terjadi kesalahan saat menambah siswa. Pesan: ' + response.data.message
      );
    }
  } catch (error) {
    console.error('Error during form submission:', error);
    alert(
      'Terjadi kesalahan saat mengirimkan data. Pesan kesalahan: ' +
        error.message
    );
  }
};

const updateStudent = async () => {
  try {
    console.log('Student ID:', form.student_id); // Pastikan student_id ada

    let url = null;

    if (form.student_id) {
      url = `/api/detailstudents/${form.student_id}/detail`;
      console.log('Generated URL:', url);
    } else {
      console.error(
        'student_id tidak tersedia di form. Pastikan untuk mengisi student_id.'
      );
      return; // Hentikan eksekusi jika student_id tidak ada
    }

    console.log('Request URL:', url);

    console.log('Payload data:', {
      name: form.name,
      student_id: form.student_id,
      gender: form.gender,
      class_id: form.class_id,
      parent_name: form.parentName,
      address: form.address,
    });

    console.log('Form student_id:', form.student_id); // Pastikan student_id sudah ada

    // Gunakan PUT untuk update
    const response = await axios.put(url, {
      name: form.name,
      student_id: form.student_id,
      gender: form.gender,
      class_id: form.class_id,
      parent_name: form.parentName,
      address: form.address,
    });

    if (response.status === 200) {
      alert('Data siswa berhasil diperbarui!');
      showModalEdit.value = false;
      fetchStudents();
    }
  } catch (error) {
    console.error(
      'Error updating student:',
      error.response ? error.response.data : error.message
    );
    alert('Terjadi kesalahan saat memperbarui data siswa.');
  }
};

// Call the fetch function when the component is mounted
onMounted(() => {
  console.log('Component Mounted');
  getGenderName();
  updateStudent();
  fetchDetailStudents();
  fetchStudents();
  initFlowbite();
});
</script>

<style>
table {
  width: 100%;
  border-collapse: collapse;
}

th {
  background-color: #f4f4f4;
}

table th,
table td {
  text-align: left;
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
      <h2
        class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 mt-20 mb-6 text-center"
      >
        Identitas Siswa
      </h2>
      <div
        class="flex flex-wrap sm:flex-nowrap justify-between items-center space-y-4 sm:space-y-0 mb-10"
      >
        <input
          v-model="searchQuery"
          type="text"
          placeholder="Cari Identitas Siswa..."
          class="w-full sm:w-auto px-4 py-2 border rounded-md"
        />
        <button
          class="btn btn-primary modal-title fs-5 w-full sm:w-auto"
          @click="openAddModal"
        >
          <i class="fa fa-plus mr-2"></i> Tambah Identitas Siswa
        </button>
      </div>
      <!-- Identitas Siswa -->
      <div
        class="w-full overflow-x-auto overflow-y-auto max-h-[80vh] bg-white rounded-xl shadow-lg mb-8"
      >
        <table
          class="min-w-full table-auto border-collapse shadow-lg rounded-xl overflow-hidden"
        >
          <thead
            class="bg-gradient-to-r from-indigo-500 to-blue-500 text-white text-sm font-semibold"
          >
            <tr class="bg-gray-100">
              <th
                class="px-4 py-2 text-left text-sm font-semibold text-gray-700"
              >
                ID
              </th>
              <th
                class="px-4 py-2 text-left text-sm font-semibold text-gray-700"
              >
                Namaa
              </th>
              <th
                class="px-4 py-2 text-left text-sm font-semibold text-gray-700"
              >
                NIS
              </th>
              <th
                class="px-4 py-2 text-left text-sm font-semibold text-gray-700"
              >
                Kelas
              </th>
              <th
                class="px-4 py-2 text-left text-sm font-semibold text-gray-700"
              >
                Jenis Kelamin
              </th>
              <th
                class="px-4 py-2 text-left text-sm font-semibold text-gray-700"
              >
                Nama Orang Tua
              </th>
              <th
                class="px-4 py-2 text-left text-sm font-semibold text-gray-700"
              >
                Alamat
              </th>
              <th
                class="px-4 py-2 text-left text-sm font-semibold text-gray-700 text-right"
              >
                Aksi
              </th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="(student, index) in detailStudentsState"
              :key="student.id"
            >
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ index + 1 }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ student.name || '-' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ student.student_id || '-' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ getClassName(student.class_id) || '-' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ student.gender }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ student.parent_name || 'No Parent Name' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ student.address || '-' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700 text-right">
                <div class="flex justify-end gap-2">
                  <button
                    @click="editStudent(student.id)"
                    class="bg-blue-500 text-white py-1 px-4 rounded-lg hover:bg-blue-700"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteStudent(student.id)"
                    class="bg-red-500 text-white py-1 px-4 rounded-lg hover:bg-red-700"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Button to open the modal -->

      <!-- Modal tambah  -->
      <div
        v-if="showModalAdd"
        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center"
      >
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
          <h3 class="text-xl font-bold text-center mb-6">
            Tambah Identitas Siswa
          </h3>
          <form @submit.prevent="submitForm">
            <div class="mb-4">
              <label for="name" class="block mb-2">Nama Siswa</label>
              <select
                v-model="form.name"
                id="name"
                class="w-full p-2 border rounded"
                required
              >
                <option value="">Pilih Nama Siswa</option>
                <!-- Menampilkan opsi nama siswa dari API -->
                <option
                  v-for="student in students"
                  :key="student.id"
                  :value="student.name"
                >
                  {{ student.name }}
                </option>
              </select>
            </div>

            <div class="mb-4">
              <label for="studentId" class="block mb-2"
                >Nomor Induk Siswa</label
              >
              <input
                type="text"
                placeholder="Nomor Induk Siswa"
                v-model="form.student_id"
                id="studentId"
                class="w-full p-2 border rounded"
                @input="validateNumber"
                required
              />
            </div>
            <div class="mb-4">
              <label for="gender" class="block mb-2">Jenis Kelamin</label>
              <select
                v-model="form.gender"
                id="gender"
                class="w-full p-2 border rounded"
                required
              >
                <option value="">Pilih Jenis Kelamin</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>

            <div class="mb-4">
              <label for="class" class="block mb-2">Kelas</label>
              <select
                v-model="form.class_id"
                id="class_id"
                class="w-full p-2 border rounded"
                required
              >
                <option value="">Pilih Kelas</option>
                <!-- Check if classes data is available before looping -->
                <option
                  v-for="classItem in props.classes_for_student || []"
                  :key="classItem.id"
                  :value="classItem.id"
                >
                  {{ classItem.name }}
                </option>
                <!-- Fallback message if no data is available -->
                <option v-if="props.classes_for_student?.length === 0" disabled>
                  No classes available
                </option>
              </select>
            </div>

            <div class="mb-4">
              <label for="parentName" class="block mb-2">Nama Orang Tua</label>
              <input
                type="text"
                v-model="form.parentName"
                id="parentName"
                placeholder="Nama Orang Tua"
                class="w-full p-2 border rounded"
                required
              />
            </div>
            <div class="mb-4">
              <label for="address" class="block mb-2">Alamat</label>
              <textarea
                v-model="form.address"
                placeholder="Alamat Siswa"
                id="address"
                class="w-full p-2 border rounded"
                required
              ></textarea>
            </div>
            <div class="flex justify-between">
              <button
                type="button"
                @click="closeModal"
                class="bg-gray-500 text-white px-4 py-2 rounded-lg"
              >
                Batal
              </button>
              <button
                type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg"
              >
                Simpan
              </button>
            </div>
          </form>
        </div>
      </div>

      <!--Modal Edit -->
      <div
        v-if="showModalEdit"
        class="fixed inset-0 z-50 bg-black bg-opacity-50 flex justify-center items-center"
      >
        <div class="bg-white p-6 rounded-lg shadow-lg w-full max-w-md">
          <h3 class="text-xl font-bold text-center mb-6">
            Edit Identitas Siswa
          </h3>
          <form @submit.prevent="updateStudent">
            <div class="mb-4">
              <label for="name" class="block mb-2">Nama Siswa</label>
              <select
                v-model="form.name"
                id="name"
                class="w-full p-2 border rounded"
                required
              >
                <option value="">Pilih Nama Siswa</option>
                <!-- Menampilkan opsi nama siswa dari API -->
                <option
                  v-for="student in students"
                  :key="student.id"
                  :value="student.name"
                >
                  {{ student.name }}
                </option>
              </select>
            </div>

            <div class="mb-4">
              <label for="studentId" class="block mb-2"
                >Nomor Induk Siswa</label
              >
              <input
                type="text"
                placeholder="Nomor Induk Siswa"
                v-model="form.student_id"
                id="studentId"
                class="w-full p-2 border rounded"
                @input="validateNumber"
                required
              />
            </div>
            <div class="mb-4">
              <label for="gender" class="block mb-2">Jenis Kelamin</label>
              <select
                v-model="form.gender"
                id="gender"
                class="w-full p-2 border rounded"
                required
              >
                <option value="">Pilih Jenis Kelamin</option>
                <option value="L">Laki-laki</option>
                <option value="P">Perempuan</option>
              </select>
            </div>

            <div class="mb-4">
              <label for="class" class="block mb-2">Kelas</label>
              <select
                v-model="form.class_id"
                id="class_id"
                class="w-full p-2 border rounded"
                required
              >
                <option value="">Pilih Kelas</option>
                <!-- Check if classes data is available before looping -->
                <option
                  v-for="classItem in props.classes_for_student || []"
                  :key="classItem.id"
                  :value="classItem.id"
                >
                  {{ classItem.name }}
                </option>
                <!-- Fallback message if no data is available -->
                <option v-if="props.classes_for_student?.length === 0" disabled>
                  No classes available
                </option>
              </select>
            </div>

            <div class="mb-4">
              <label for="parentName" class="block mb-2">Nama Orang Tua</label>
              <input
                type="text"
                v-model="form.parentName"
                id="parentName"
                placeholder="Nama Orang Tua"
                class="w-full p-2 border rounded"
                required
              />
            </div>
            <div class="mb-4">
              <label for="address" class="block mb-2">Alamat</label>
              <textarea
                v-model="form.address"
                placeholder="Alamat Siswa"
                id="address"
                class="w-full p-2 border rounded"
                required
              ></textarea>
            </div>
            <div class="flex justify-between">
              <button
                type="button"
                @click="closeModal"
                class="bg-gray-500 text-white px-4 py-2 rounded-lg"
              >
                Batal
              </button>
              <button
                type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded-lg"
              >
                Simpan
              </button>
            </div>
          </form>
        </div>
      </div>
    </main>
    <!-- Sidebar -->
    <SidebarTeacher />
  </div>
</template>
