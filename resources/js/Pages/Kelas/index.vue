<script setup>
import { initFlowbite } from 'flowbite';
import Pagination from '../../Components/Pagination1.vue';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';
import { Link, useForm, usePage, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { onMounted, ref, computed, watch, defineProps } from 'vue';
import { Head } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import MagnifyingGlass from '../../Components/Icons/MagnifyingGlass.vue';
// Define props passed to the component
const props = defineProps({
  classes_for_student: {
    type: Object,
    required: true,
  },
});

// Mengambil data dari props
console.log('Props:', props);
console.log('Classes for Student:', props.classes_for_student);
if (!props.classes_for_student.meta) {
  console.warn('Meta tidak tersedia, pastikan data dikirim dengan benar.');
}

const pageNumber = ref(1); // Inisialisasi dengan nilai default
const perPage = ref(5); // Inisialisasi dengan nilai default

console.log('Page Number:', pageNumber.value);
console.log('Per Page:', perPage.value);

// Mengambil meta dengan validasi
const meta = computed(() => {
  return props.classes_for_student ? props.classes_for_student.meta : null;
});
console.log(
  'Meta:',
  props.classes_for_student
    ? props.classes_for_student.meta
    : 'Meta tidak tersedia'
);
console.log('Classes for Student:', props.classes_for_student);

const logWaliKelas = () => {
  console.log(
    'Data Kelas:',
    JSON.stringify(props.classes_for_student, null, 2)
  );

  props.classes_for_student.data.forEach((classForStudent) => {
    // Pastikan untuk memeriksa apakah wali_kelas ada
    if (classForStudent.wali_kelas) {
      console.log('Wali Kelas:', classForStudent.wali_kelas.name); // Akses nama wali kelas
    } else {
      console.log('Wali Kelas: Tidak ada wali kelas untuk kelas ini');
    }
  });
};

// Define form and page-related variables
const form = useForm({
  id_kelas: '',
  kode_kelas: '',
  classes: '',
});

// Memanggil fetchClasses dengan currentPage

console.log('pageNumber:', pageNumber.value);

// URL for mapels (subjects) with pagination and search parameters
const mapelsUrl = computed(() => {
  const url = new URL(route('kelas.index'));
  url.searchParams.set('page', pageNumber.value); // Ensure pageNumber is included
  return url;
});

// Form for deletion
const deleteForm = useForm({});

const deleteClass = (id) => {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Data Kelas ini akan dihapus dan tidak dapat dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
  }).then((result) => {
    if (result.isConfirmed) {
      deleteForm.delete(route('kelas.destroy', id), {
        preserveScroll: true,
        onSuccess: () => {
          pageNumber.value = 1;
          router.visit(mapelsUrl.value.toString(), {
            replace: true,
            preserveState: true,
            preserveScroll: true,
          });
        },
      });

      Swal.fire('Terhapus!', 'Data kelas telah berhasil dihapus.', 'success');
    }
  });
};

pageNumber.value = 1;
console.log(`Current page number is: ${pageNumber.value}`); // Akses dengan .value
let searchTerm = ref(props.search ?? '');

const updatedPageNumber = (link) => {
  console.log('Received link:', link);

  if (!link || !link.url) {
    console.warn('Link or link.url is undefined, skipping navigation.');
    return; // Exit function if the link is invalid
  }

  const page = new URL(link.url).searchParams.get('page');
  if (page) {
    pageNumber.value = page;
    router.visit(`/kelas?page=${pageNumber.value}`, {
      preserveScroll: true,
    });
  } else {
    console.error('Page number not found in URL');
  }
};

const classesUrl = computed(() => {
  const url = new URL(route('kelas.index')); // Ganti dengan rute yang sesuai
  url.searchParams.set('page', pageNumber.value); // Pastikan pageNumber ada
  if (searchTerm.value) {
    url.searchParams.set('search', searchTerm.value); // Jika ada pencarian
  }
  return url;
});

watch(
  () => classesUrl.value,
  (updatedClassesUrl) => {
    console.log('Navigating to URL:', updatedClassesUrl.toString());
    router.visit(updatedClassesUrl.toString(), {
      preserveScroll: true,
    });
  }
);

const kelasUrl = computed(() => {
  const url = new URL(route('kelas.index'));
  url.searchParams.set('page', pageNumber.value); // pastikan pageNumber ada
  if (searchTerm.value) {
    url.searchParams.set('search', searchTerm.value);
  }
  return url;
});

watch(
  () => kelasUrl.value,
  (updatedKelasUrl) => {
    console.log('Navigating to URL:', updatedKelasUrl.toString());
    router.visit(updatedKelasUrl.toString(), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
  }
);

const loadDataForPage = (page) => {
  const url = new URL(route('kelas.index')); // Ganti dengan rute yang sesuai
  url.searchParams.set('page', page); // Set parameter halaman

  // Navigasi ke URL baru
  router.visit(url.toString(), {
    preserveScroll: true,
    preserveState: true,
  });
};
console.log(props.classes_for_student);

// On mounted, initialize Flowbite
onMounted(() => {
  initFlowbite();
  logWaliKelas();
});

// Example of using Ziggy to generate the route for editing a class
const editClassRoute = (classId) => {
  // Pastikan classId diberikan sebagai parameter untuk route kelas.edit
  return route('kelas.edit', { classId });
};

// Watch for changes in mapelsUrl and trigger page navigation
watch(
  () => mapelsUrl.value,
  (updatedMapelsUrl) => {
    router.visit(updatedMapelsUrl.toString(), {
      preserveState: true,
      preserveScroll: true,
      replace: true,
    });
  }
);

watch(
  () => props.classes_for_student.data,
  (newClasses) => {
    console.log('Data kelas yang diterima:', newClasses);
  },
  { immediate: true } // Jalankan segera saat komponen dimuat
);

watch([pageNumber, perPage], ([newPageNumber, newPerPage]) => {
  console.log('Updated Page Number:', newPageNumber);
  console.log('Updated Per Page:', newPerPage);
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
                ></span>
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

    <main class="p-4 md:ml-64 h-auto pt-20">
      <Head title="Teachers" />

      <div class="flex-1 p-6">
        <div class="mx-auto max-w-7xl sm:items-center">
          <div class="px-4 py-4 sm:px-6 lg:px-8 -ml-10">
            <div class="sm:flex sm:items-center">
              <div class="sm:flex-auto">
                <h1 class="text-3xl font-semibold text-gray-900">Kelas</h1>
                <p class="mt-2 text-sm text-gray-700">Daftar Semua Kelas</p>
              </div>

              <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
                <Link
                  :href="route('kelas.create')"
                  class="btn btn-primary modal-title fs-5 inline-flex items-center gap-x-2 py-2 px-4 text-sm font-medium text-white border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  <i class="fa fa-plus mr-2"></i> Tambah Kelas
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
                  placeholder="Cari Data Kelas.."
                  id="search"
                  class="block rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                />
              </div>
            </div>

            <div class="mt-8 flex flex-col mr-20">
              <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
                <div
                  class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8"
                >
                  <div
                    class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 sm:rounded-lg relative"
                  >
                    <table class="min-w-full bg-white">
                      <thead class="divide-y divide-gray-200 bg-gray-50">
                        <tr>
                          <th
                            scope="col"
                            class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-1/4"
                          >
                            Kode Kelas
                          </th>
                          <th
                            scope="col"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-1/3"
                          >
                            Nama Kelas
                          </th>
                          <!--                   <th
                            scope="col"
                            class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 w-1/4"
                          >
                            Wali Kelas
                          </th>-->
                          <th
                            scope="col"
                            class="relative whitespace-nowrap py-3.5 pl-3 pr-4 text-right text-sm font-semibold text-gray-900 sm:pr-6 w-1/4"
                          >
                            Action
                          </th>
                        </tr>
                      </thead>
                      <tbody class="divide-y divide-gray-200 bg-white">
                        <tr
                          v-for="(classForStudent, index) in props
                            .classes_for_student.data"
                          :key="classForStudent.id"
                        >
                          <td
                            class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
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
                            class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                          >
                            {{ classForStudent.name }}
                          </td>
                          <td
                            class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6"
                          >
                            <div class="flex justify-end space-x-4">
                              <Link
                                :href="editClassRoute(classForStudent.id)"
                                class="text-indigo-600 hover:text-indigo-900"
                              >
                                Edit
                              </Link>
                              <button
                                @click="deleteClass(classForStudent.id)"
                                class="text-indigo-600 hover:text-indigo-900"
                              >
                                Hapus
                              </button>
                            </div>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                  <!-- Pagination -->
                  <Pagination
                    v-if="classes_for_student && classes_for_student.meta"
                    :data="{
                      meta: classes_for_student.meta,
                      items: classes_for_student.data,
                    }"
                    :updatedPageNumber="updatedPageNumber"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Sidebar -->
    <SidebarAdmin />
  </div>

  <!-- -----  -->
</template>
