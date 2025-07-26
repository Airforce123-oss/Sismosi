<script setup>
import { initFlowbite } from 'flowbite';
import Pagination from '../../Components/Pagination.vue';
import { Link, Head, useForm, usePage, router } from '@inertiajs/vue3';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';
import Swal from 'sweetalert2';
import Edit from './edit.vue';
import { ref, watch, computed, onMounted } from 'vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import MagnifyingGlass from '@/Components/Icons/MagnifyingGlass.vue';
const { props } = usePage();
const pageNumber = ref(1);
const perPage = ref(5);

console.log('pageNumber:', pageNumber.value);
console.log('perPage:', perPage.value);

console.log('pageNumber:', pageNumber.value);
console.log('perPage:', perPage.value);
//console.log("index:", index);

const showStudentEdit = ref(false);
const student = ref(null);

defineProps({
  students: {
    type: Object,
    required: true,
  },
  classes: {
    type: Array, 
    required: true,
  },
  genders: {
    type: Array, 
    required: true,
  },
  no_induks: {
    type: Array, 
    required: true,
  },
  religions: {
    type: Array, 
    required: true,
  },
});

const selectedStudent = ref(null);

const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

let searchTerm = ref(props.search ?? '');

let selectedClass = ref('');

const updatedPageNumber = (link) => {
  const page = new URL(link.url).searchParams.get('page'); // Ambil nilai page dari URL
  pageNumber.value = page;
  router.visit(`/students?page=${pageNumber.value}`, {
    preserveScroll: true,
  });
};

const studentsUrl = computed(() => {
  const url = new URL(route('students.index'));
  url.searchParams.set('page', pageNumber.value);
  if (searchTerm.value) {
    url.searchParams.set('search', searchTerm.value);
  }
  if (selectedClass.value) {
    url.searchParams.set('class', selectedClass.value);
  }
  return url;
});

const handleTambahSiswa = () => {
  console.log('Selected Student:', selectedStudent.value);

  if (selectedStudent.value) {
    // Gunakan no_induk_id, bukan student_id
    router.visit(
      route('students.create', {
        no_induk_id: selectedStudent.value.no_induk_id,
      })
    );
  } else {
    router.visit(route('students.create'));
  }
};

watch(
  () => studentsUrl.value,
  (updatedStudentsUrl) => {
    console.log('Navigating to URL:', updatedStudentsUrl.toString());
    router.visit(updatedStudentsUrl.toString(), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
  }
);

const deleteForm = useForm({});

const deleteStudent = (id) => {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Data siswa ini akan dihapus dan tidak dapat dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
  }).then((result) => {
    if (result.isConfirmed) {
      deleteForm.delete(route('students.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
          pageNumber.value = 1;
          router.visit(studentsUrl.value.toString(), {
            replace: true,
            preserveState: true,
            preserveScroll: true,
          });
        },
      });

      Swal.fire('Terhapus!', 'Data siswa telah berhasil dihapus.', 'success');
    }
  });
};

pageNumber.value = 1;
console.log(pageNumber.value); // Akses dengan .value

onMounted(() => {
  console.log('students data:', props.students);
  initFlowbite();
});
</script>

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
            class="hidden z-50 my-4 w-56 text-base list-none bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 rounded-xl"
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

    <!-- start1 -->

    <main class="p-7 md:ml-64 h-auto pt-20">
      <Head title="Students" />
      <div class="text-2xl col-sm-12 mb-10">
        <div class="sm:flex sm:items-center">
          <div class="sm:flex-auto">
            <h1 class="text-3xl font-semibold text-gray-900">Siswa</h1>
            <p class="mt-2 text-sm text-gray-700">Daftar Semua Siswa</p>

            <div class="mt-6 flex flex-col gap-4 sm:flex-row sm:items-center">
              <!-- Input Search -->
              <div class="relative w-full sm:max-w-xs text-sm text-gray-800">
                <div
                  class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400"
                >
                  <MagnifyingGlass class="w-4 h-4" />
                </div>

                <input
                  type="text"
                  v-model="searchTerm"
                  placeholder="Cari Data Siswa.."
                  id="search"
                  class="block w-full rounded-lg border-0 py-2 pl-10 pr-4 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                />
              </div>

              <!-- Select Filter Kelas -->
              <div class="relative w-full sm:max-w-xs text-sm text-gray-800">
                <div
                  class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    class="h-4 w-4"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                  >
                    <path
                      stroke-linecap="round"
                      stroke-linejoin="round"
                      stroke-width="2"
                      d="M4 6h16M4 12h16M4 18h16"
                    />
                  </svg>
                </div>

                <select
                  v-model="selectedClass"
                  class="block w-full rounded-lg border-0 py-2 pl-10 pr-10 text-gray-900 ring-1 ring-inset ring-gray-200 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm"
                >
                  <option value="">Semua Kelas</option>
                  <option
                    v-for="kelas in props.classes"
                    :key="kelas.id"
                    :value="kelas.id"
                  >
                    {{ kelas.name }}
                  </option>
                </select>
              </div>
            </div>
          </div>

          <div class="mt-4 sm:ml-16 sm:flex-none">
            <button
              @click.prevent="handleTambahSiswa"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded hover:bg-indigo-700"
            >
              Tambah Siswa
              <template v-if="selectedStudent">
                : {{ selectedStudent.name }}
              </template>
            </button>
          </div>
        </div>
      </div>

      <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="mt-8">
          <div
            class="overflow-x-auto bg-white shadow ring-1 ring-black ring-opacity-5 rounded-lg"
          >
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th
                    class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900"
                  >
                    ID
                  </th>
                  <th
                    class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900"
                  >
                    Nomor Induk Siswa
                  </th>
                  <th
                    class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900"
                  >
                    Nama
                  </th>
                  <th
                    class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900"
                  >
                    Jenis Kelamin
                  </th>
                  <th
                    class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900"
                  >
                    Kelas
                  </th>
                  <th
                    class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900"
                  >
                    Agama
                  </th>
                  <th
                    class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900"
                  >
                    Dibuat Pada
                  </th>
                  <th
                    class="py-3.5 px-4 text-left text-sm font-semibold text-gray-900"
                  >
                    Aksi
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr v-for="(student, index) in students.data" :key="student.id">
                  <td
                    class="whitespace-nowrap py-4 px-4 text-sm font-medium text-gray-900"
                  >
                    <span v-if="pageNumber && perPage">
                      {{
                        (Number(pageNumber) - 1) * Number(perPage) +
                        Number(index) +
                        1
                      }}
                    </span>
                    <span v-else>Loading...</span>
                  </td>
                  <td
                    class="whitespace-nowrap py-4 px-4 text-sm font-medium text-gray-900"
                  >
                    {{ student.noInduk.no_induk }}
                  </td>
                  <td
                    class="whitespace-nowrap py-4 px-4 text-sm font-medium text-gray-900"
                  >
                    {{ student.name }}
                  </td>
                  <td class="whitespace-nowrap py-4 px-4 text-sm text-gray-500">
                    {{ student.gender.name }}
                  </td>
                  <td class="whitespace-nowrap py-4 px-4 text-sm text-gray-500">
                    {{ student.class.name }}
                  </td>
                  <td class="whitespace-nowrap py-4 px-4 text-sm text-gray-500">
                    {{ student.religion.name }}
                  </td>
                  <td class="whitespace-nowrap py-4 px-4 text-sm text-gray-500">
                    {{ student.created_at }}
                  </td>
                  <td class="whitespace-nowrap py-4 px-4 text-sm font-medium">
                    <Link
                      :href="route('students.edit', student.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                      >Edit</Link
                    >
                    <button
                      @click="deleteStudent(student.id)"
                      class="ml-2 text-indigo-600 hover:text-indigo-900"
                    >
                      Hapus
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-6">
            <Pagination
              :data="students"
              :updatedPageNumber="updatedPageNumber"
            />
          </div>
        </div>
      </div>

      <edit
        v-if="showStudentEdit && student"
        :student="student"
        :classes="classes"
        :genders="genders"
        :religions="religions"
        :no-induks="no_induks"
      />
    </main>

    <!-- end1-->

    <!-- Sidebar -->
    <SidebarAdmin />
  </div>
</template>
