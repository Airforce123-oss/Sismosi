<script setup>
import { onMounted, ref, computed, watch, isRef } from 'vue';
import { initFlowbite } from 'flowbite';
import axios from 'axios';
import Swal from 'sweetalert2';
import Pagination from '@/Components/Pagination15.vue';

import SidebarTeacher from '@/Components/SidebarTeacher.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

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
  console.log('Student received in editStudent:', student);

  form.id = student.id;
  form.name = student.name || '';
  form.student_id = student.student?.no_induk?.no_induk || '';
  form.student_db_id = student.student?.id || '';

  form.gender =
    student.gender === '1' ? 'L' : student.gender === '2' ? 'P' : '';

  form.class_id = student.class_id || '';
  form.parentName = student.parent_name || '';
  form.address = student.address || '';

  showModalEdit.value = true;
};

// Function to close the modal
const closeModal = () => {
  showModalAdd.value = false;
  showModalEdit.value = false;
};

const props = defineProps({
  auth: { type: Object },
  students: {
    type: Object,
    required: true,
  },
  classes_for_student: {
    type: Object,
    required: true,
  },
  teachers: { type: Object, default: () => ({ data: [] }) },
  genders: {
    type: Array,
    default: () => [], // Pastikan `genders` adalah array meskipun kosong
  },
  detailStudents: {
    type: Object,
    required: true,
  },
  selectedClassId: {
    type: Number,
    required: true,
  },
});

const students = computed(() => {
  return detailStudentsState.value.filter(
    (student) => student.class_id === props.selectedClassId
  );
});

const paginationData = ref({});

const fetchDetailStudents = async (
  classId = selectedClassId.value,
  page = 1
) => {
  try {
    const response = await axios.get('/api/detailstudents', {
      params: {
        class_id: classId,
        page: page,
      },
    });

    console.log('📦 Full response:', response.data);

    const responseData = response.data?.data;

    if (responseData && Array.isArray(responseData.data)) {
      // 🟢 Berhasil ambil data siswa
      detailStudentsState.value = responseData.data;

      // 🟢 Simpan metadata pagination seperti current_page, total, dll
      paginationData.value = responseData;

      console.log(
        '✅ detailStudentsState setelah fetch:',
        detailStudentsState.value
      );
      console.log('✅ paginationData:', paginationData.value);
    } else {
      console.warn('❗️Data siswa tidak ditemukan atau tidak berbentuk array.');
      detailStudentsState.value = [];
      paginationData.value = null;
    }
  } catch (error) {
    console.error('❌ Gagal memuat data detail siswa:', error);
    detailStudentsState.value = [];
    paginationData.value = null;
  }
};

const updatedPageNumber = async (page) => {
  currentPage.value = page;
  await fetchDetailStudents(selectedClassId.value, page);
};

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

watch(
  () => props.classes_for_student,
  (newValue) => {
    console.log('Data classes_for_student:', newValue);
  }
);

const form = useForm({
  id: '',
  selectedStudent: '',
  name: '',
  student_id: '',
  student_db_id: '',
  gender: '',
  class_id: '',
  parentName: '',
  address: '',
});

const onStudentChange = () => {
  if (form.selectedStudent) {
    form.name = form.selectedStudent.name;
    form.student_id = form.selectedStudent.noInduk?.no_induk ?? '';
    form.gender = form.selectedStudent.gender ?? '';
    form.class_id = form.selectedStudent.class?.id ?? '';
  } else {
    form.name = '';
    form.student_id = '';
    form.gender = '';
    form.class_id = '';
  }
};

console.log('Form Data yang Dikirim:', {
  name: form.name,
  student_id: form.student_id,
  class_id: form.class_id,
  gender: form.gender_id,
  parent_name: form.parentName,
  address: form.address,
});

const addStudentData = (newStudent) => {
  if (newStudent) {
    students.value.push(newStudent); // Menambahkan data siswa baru
  }
};

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

// Function to handle form submission
const submitForm = async () => {
  console.log('Form Data Setelah Validasi:', {
    name: form.name,
    student_id: form.student_id,
    gender_id: typeof form.gender === 'object' ? form.gender.id : form.gender,
    class_id: form.class_id,
    parentName: form.parentName,
    address: form.address,
  });

  try {
    const response = await axios.post('/api/detailstudents', {
      name: form.name,
      student_id: form.student_id,
      gender_id: typeof form.gender === 'object' ? form.gender.id : form.gender,
      class_id: form.class_id,
      parent_name: form.parentName,
      address: form.address,
    });

    console.log('API Response:', response.data);

    if (response.status === 200 || response.status === 201) {
      addStudentData(response.data.student);

      await fetchDetailStudents();

      showModalAdd.value = false;

      // Reset form
      form.name = '';
      form.student_id = '';
      form.gender = '';
      form.class_id = '';
      form.parentName = '';
      form.address = '';

      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data siswa berhasil ditambahkan.',
        timer: 2000,
        showConfirmButton: false,
      });
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text:
          'Terjadi kesalahan saat menambah siswa. Pesan: ' +
          response.data.message,
      });
    }
  } catch (error) {
    if (error.response && error.response.status === 422) {
      console.error('Validation errors:', error.response.data.errors);

      Swal.fire({
        icon: 'warning',
        title: 'Validasi Gagal!',
        text: 'Mohon periksa kembali data yang Anda masukkan.',
      });
    } else {
      console.error('Error during form submission:', error);

      Swal.fire({
        icon: 'error',
        title: 'Kesalahan!',
        text:
          'Terjadi kesalahan saat mengirimkan data. Pesan: ' + error.message,
      });
    }
  }
};

const updateStudent = async () => {
  console.log('form.id di updateStudent:', form.id);

  try {
    if (!form.id) {
      await Swal.fire({
        icon: 'error',
        title: 'ID Tidak Ditemukan',
        text: 'ID siswa tidak ditemukan.',
      });
      return;
    }

    const url = `/api/detailstudents/${form.id}/detail`;

    console.log('Request URL:', url);
    console.log('Payload data:', {
      name: form.name,
      student_id: form.student_id,
      student_db_id: form.student_db_id,
      gender: form.gender,
      class_id: form.class_id,
      parent_name: form.parentName,
      address: form.address,
    });
    console.log('form.student_db_id:', form.student_db_id);

    const response = await axios.put(url, {
      name: form.name,
      student_id: form.student_id,
      student_db_id: form.student_db_id,
      gender: form.gender,
      class_id: form.class_id,
      parent_name: form.parentName,
      address: form.address,
    });

    if (response.status === 200) {
      await Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data siswa berhasil diperbarui.',
      });

      showModalEdit.value = false;

      await fetchDetailStudents(selectedClassId.value);
    }
  } catch (error) {
    console.error(
      'Error updating student:',
      error.response ? error.response.data : error.message
    );

    await Swal.fire({
      icon: 'error',
      title: 'Gagal Memperbarui',
      text: 'Terjadi kesalahan saat memperbarui data siswa.',
    });
  }
};

const deleteStudent = async (id) => {
  try {
    const result = await Swal.fire({
      title: 'Yakin ingin menghapus siswa ini?',
      text: 'Tindakan ini tidak dapat dibatalkan!',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal',
    });

    if (result.isConfirmed) {
      const url = `/api/detailstudents/${id}/detail`; // tetap sama

      await axios.delete(url); // metode DELETE tetap

      await Swal.fire({
        icon: 'success',
        title: 'Terhapus!',
        text: 'Data siswa berhasil dihapus.',
      });

      await fetchDetailStudents(selectedClassId.value); // refresh data
    }
  } catch (error) {
    console.error(
      '❌ Gagal menghapus siswa:',
      error.response?.data || error.message
    );
    await Swal.fire({
      icon: 'error',
      title: 'Gagal!',
      text: 'Terjadi kesalahan saat menghapus siswa.',
    });
  }
};

const selectedClassId = ref(props.selectedClassId || null);

watch(selectedClassId, (newVal) => {
  console.log('selectedClassId berubah:', newVal);
  fetchDetailStudents(newVal);
});

onMounted(() => {
  console.log('Component Mounted');
  fetchDetailStudents(selectedClassId.value);
  console.log('Classes dari wali login:', props.classes_for_student);
  initFlowbite();
});

watch(selectedClassId, (val) => {
  fetchDetailStudents(val);
});

watch(
  () => form.selectedStudent,
  (newStudent) => {
    if (newStudent) {
      form.student_id = newStudent.noInduk?.no_induk ?? '';
      form.gender = newStudent.gender ?? '';
      form.class_id = newStudent.class?.id ?? '';
    } else {
      form.student_id = '';
      form.gender = '';
      form.class_id = '';
    }
  }
);
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
                Nama
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
              <td class="px-4 py-2 text-sm text-gray-700">{{ index + 1 }}</td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ student.name || '-' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ student.student?.no_induk?.no_induk || '-' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{ getClassName(student.class_id) || '-' }}
              </td>
              <td class="px-4 py-2 text-sm text-gray-700">
                {{
                  student.gender === '1'
                    ? 'Laki-laki'
                    : student.gender === '2'
                    ? 'Perempuan'
                    : '-'
                }}
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
                    @click="editStudent(student)"
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
        <Pagination
          v-if="
            Array.isArray(paginationData.links) && paginationData.links.length
          "
          :data="paginationData"
          :updatedPageNumber="updatedPageNumber"
        />
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
                v-model="form.selectedStudent"
                @change="onStudentChange"
                id="name"
                class="w-full p-2 border rounded"
                required
              >
                <option value="">Pilih Nama Siswa</option>
                <option
                  v-for="student in props.students.data"
                  :key="student.id"
                  :value="student"
                >
                  {{ student.name }}
                  <!-- -
                  {{ student.noInduk?.no_induk ?? 'Tanpa No Induk' }} -
                  {{ student.class?.name ?? 'Tanpa Kelas' }} -
                  {{ student.gender?.name ?? 'Tanpa Gender' }} 
                   -->
                </option>
              </select>
            </div>

            <div class="mb-4">
              <label for="studentId" class="block mb-2"
                >Nomor Induk Siswa</label
              >
              <input
                type="text"
                id="studentId"
                v-model="form.student_id"
                class="w-full p-2 border rounded bg-gray-100 cursor-not-allowed"
                readonly
              />
            </div>

            <div class="mb-4">
              <label for="gender" class="block mb-2">Jenis Kelamin</label>
              <input
                type="text"
                id="gender"
                class="w-full p-2 border rounded bg-gray-100"
                :value="form.gender?.name ?? 'Tidak diketahui'"
                readonly
              />
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
