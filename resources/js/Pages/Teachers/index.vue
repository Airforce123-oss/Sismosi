<script setup>
import { initFlowbite } from 'flowbite';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';
import Pagination from '../../Components/Pagination2.vue';
import { Link, Head, useForm, usePage, router } from '@inertiajs/vue3';
import { ref, watch, computed, onMounted } from 'vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import Edit from './edit.vue';
import MagnifyingGlass from '@/Components/Icons/MagnifyingGlass.vue';
//const { props } = usePage();
import Swal from 'sweetalert2';
const props = defineProps({
  classes: {
    type: Object,
    default: () => ({ data: [] }), // Set default ke objek kosong jika tidak ada
  },
  mapel: {
    type: Array,
    default: () => [], // Default ke array kosong jika tidak ada data mapel
  },
  wali_kelas: {
    type: Object,
    default: () => ({ data: [] }), // Set default ke objek kosong dengan data array kosong
  },
});
const page = usePage();
const classes = props.classes?.data || [];
const classes_for_student = page.props.classes_for_student || { data: [] };
const mapel = page.props.mapel || [];
const teacher = page.props.teacher || null;
if (!props.classes) {
  console.log('props.classes:', props.classes); // Cek apakah benar-benar null atau tidak dikirim
  console.warn('No classes prop provided');
} else if (!props.classes.data || props.classes.data.length === 0) {
}

const showCreate = ref(false);

const editTeacher = (id) => {
  showCreate.value = true;
  router.visit(route('teachers.index', { teacher_id: id }), {
    preserveScroll: true,
    preserveState: true,
    replace: true,
  });
};

//console.log('Classes:', classes);

const waliKelas = ref(props.wali_kelas || { data: [] });
const mapels = ref(props.mapel || []);
//console.log('Mapel yang diterima:', props.mapel);
let searchTerm = ref(props.search ?? '');

// Inisialisasi form dengan menambahkan properti role_type
const form = useForm({
  name: '',
  no_induk_id: '',
  gender_id: '',
  class_id: '',
  religion_id: '',
  role_type: props.auth?.user?.role_type || '',
  mapel: [],
});

const getTeacherMapel = (teacher) => {
  // 1. Pastikan teacher ada
  if (!teacher) {
    return 'Data guru tidak tersedia';
  }

  // 2. Periksa apakah teacher.master_mapel ada dan merupakan array
  if (!teacher.master_mapel || !Array.isArray(teacher.master_mapel)) {
    console.log(
      'master_mapel tidak valid atau tidak ada untuk teacher id:',
      teacher.id
    );
    return 'Belum ada mata pelajaran';
  }

  // 3. Pastikan teacher.master_mapel tidak kosong
  if (teacher.master_mapel.length === 0) {
    console.log('Tidak ada mata pelajaran untuk teacher id:', teacher.id);
    return 'Belum ada mata pelajaran';
  }

  // 4. Jika ada mata pelajaran, lanjutkan proses pencarian
  // Map dan gabungkan nama mata pelajaran
  return teacher.master_mapel
    .map((mapel) => {
      const mapelDetail = mapels.value.find((m) => m.id === mapel.id); // Ganti mapel.mapel_id dengan mapel.id
      if (mapelDetail) {
        return mapelDetail.mapel; // Kembalikan nama mata pelajaran
      } else {
        return 'Tidak diketahui'; // Jika tidak ada kecocokan, tampilkan 'Tidak diketahui'
      }
    })
    .join(', '); // Gabungkan hasil menjadi string
};

const pageNumber = ref(1);
//console.log('isi pagenumber:', pageNumber.value);
const itemsPerPage = ref(10);
const currentPage = ref(1);
const perPage = ref(5);
pageNumber.value = 1;
console.log(pageNumber.value); // Akses dengan .value

const teachersUrl = computed(() => {
  const url = new URL(route('teachers.index'));
  url.searchParams.set('page', pageNumber.value); // pastikan pageNumber ada
  if (searchTerm.value) {
    url.searchParams.set('search', searchTerm.value);
  }
  return url;
});

const updatedPageNumber = (link) => {
  //console.log('updatedPageNumber dipanggil dengan link:', link);

  if (!link.url) {
    console.warn('Link tidak memiliki properti url:', link);
    return;
  }

  try {
    const urlObj = new URL(link.url, window.location.origin);
    const page = urlObj.searchParams.get('page');
    if (page === null) {
      console.warn('Parameter page tidak ditemukan di URL:', urlObj.toString());
      return;
    }
    pageNumber.value = Number(page);
  } catch (error) {
    console.error('Error parsing URL:', error);
  }
};

onMounted(() => {
  initFlowbite();
});

watch(
  () => teachersUrl.value,
  (updatedTeachersUrl) => {
    router.visit(updatedTeachersUrl.toString(), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
  }
);

watch(
  () => props.mapel,
  (newVal) => {},
  { immediate: true }
);

const deleteForm = useForm({});

const deleteTeacher = (id) => {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Data Guru ini akan dihapus dan tidak dapat dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
  }).then((result) => {
    if (result.isConfirmed) {
      deleteForm.delete(route('teachers.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
          pageNumber.value = 1;
          router.visit(teachersUrl.value.toString(), {
            replace: true,
            preserveState: true,
            preserveScroll: true,
          });
        },
      });

      Swal.fire('Terhapus!', 'Data guru telah berhasil dihapus.', 'success');
    }
  });
};

watch(
  () => page.props.wali_kelas,
  (newVal) => {
    if (newVal && newVal.data) {
      waliKelas.value = newVal;
    }
  },
  { immediate: true }
);
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

    <main class="p-4 md:ml-64 h-auto pt-20">
      <Head title="Teachers" />
      <div class="text-2xl col-sm-12 mb-10"></div>
      <div class="mx-auto max-w-7xl sm:items-center">
        <div class="px-4 py-4 sm:px-6 lg:px-8 -ml-10">
          <div class="sm:flex sm:items-center">
            <div class="sm:flex-auto">
              <h1 class="text-3xl font-semibold text-gray-900">Guru</h1>
              <p class="mt-2 text-sm text-gray-700">Daftar Semua Guru</p>
            </div>

            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
              <!-- Link untuk tambah guru -->
              <Link
                :href="route('teachers.create')"
                class="btn btn-primary modal-title fs-5 inline-flex items-center gap-x-2 py-2 px-4 text-sm font-medium text-white border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <i class="fa fa-plus mr-2"></i> Tambah Guru
              </Link>
            </div>
          </div>

          <div class="flex flex-col justify-between sm:flex-row mt-6">
            <div class="relative text-sm text-gray-800 col-span-3">
              <div
                class="absolute pl-2 left-0 top-0 bottom-0 flex items-center pointer-events-none text-gray-500"
              >
                <MagnifyingGlass />
              </div>

              <input
                type="text"
                v-model="searchTerm"
                placeholder="Cari Data Guru.."
                id="search"
                class="block rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
              />
            </div>
          </div>

          <!-- Hanya render jika classes sudah valid -->
          <div v-if="classes?.data?.length > 0">
            <Index
              :classes="classes"
              errors="{}"
              auth="{ user: { role: 'teacher' } }"
              wali_kelas="{ data: [], links: {}, meta: {} }"
            />
          </div>

          <div class="mt-8 flex flex-col mr-20">
            <div class="w-full overflow-x-auto">
              <div
                class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8"
              >
                <div
                  class="overflow-x-auto shadow ring-1 ring-black ring-opacity-5 md:rounded-lg"
                >
                  <table class="min-w-full bg-white text-sm">
                    <thead class="bg-gray-50">
                      <tr>
                        <th
                          class="w-12 py-3.5 pl-4 pr-3 text-left font-semibold text-gray-900 sm:pl-6"
                        >
                          ID
                        </th>
                        <th
                          class="w-40 py-3.5 px-3 text-left font-semibold text-gray-900"
                        >
                          Nama
                        </th>
                        <th
                          class="w-36 py-3.5 px-3 text-left font-semibold text-gray-900"
                        >
                          NIP
                        </th>
                        <th
                          class="w-40 py-3.5 px-3 text-left font-semibold text-gray-900"
                        >
                          Mapel
                        </th>
                        <th
                          class="w-40 py-3.5 px-3 text-left font-semibold text-gray-900"
                        >
                          Jabatan
                        </th>
                        <th
                          class="w-32 py-3.5 px-3 text-right font-semibold text-gray-900"
                        >
                          Aksi
                        </th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                      <tr
                        v-for="(teacher, index) in waliKelas.data"
                        :key="teacher.id"
                        class="hover:bg-gray-50"
                      >
                        <td
                          class="whitespace-nowrap py-4 pl-4 pr-3 text-gray-900 sm:pl-6"
                        >
                          <span v-if="pageNumber && perPage">
                            {{
                              (Number(pageNumber) - 1) * Number(perPage) +
                              index +
                              1
                            }}
                          </span>
                          <span v-else>Loading...</span>
                        </td>
                        <td class="whitespace-nowrap py-4 px-3 text-gray-900">
                          {{ teacher.name }}
                        </td>
                        <td class="whitespace-nowrap py-4 px-3 text-gray-900">
                          {{ teacher.nip ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap py-4 px-3 text-gray-900">
                          {{ getTeacherMapel(teacher) }}
                        </td>
                        <td class="whitespace-nowrap py-4 px-3 text-gray-900">
                          {{ teacher.jabatan?.nama_jabatan ?? '-' }}
                        </td>
                        <td class="whitespace-nowrap py-4 px-3 text-right">
                          <Link
                            :href="route('teachers.edit', teacher.id)"
                            class="text-indigo-600 hover:underline"
                          >
                            Edit
                          </Link>

                          <button
                            @click="deleteTeacher(teacher.id)"
                            class="ml-2 text-red-600 hover:underline"
                          >
                            Hapus
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                </div>

                <Pagination
                  v-if="wali_kelas && wali_kelas.meta"
                  :data="{ meta: wali_kelas.meta, items: wali_kelas.data }"
                  :updatedPageNumber="updatedPageNumber"
                />
              </div>
            </div>
          </div>
        </div>
      </div>
      <edit
        v-if="showCreate && mapel.length > 0"
        :mapel="mapel"
        :classesForStudent="classes_for_student"
        :teacher="teacher"
      />
    </main>

    <!-- end1-->

    <!-- Sidebar -->
    <SidebarAdmin />
  </div>
</template>
