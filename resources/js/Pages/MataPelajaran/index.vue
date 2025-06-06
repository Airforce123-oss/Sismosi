<script setup>
import { initFlowbite } from 'flowbite';
import Pagination from '../../Components/Pagination6.vue';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';
import MagnifyingGlass from '../../Components/Icons/MagnifyingGlass.vue';
import { Link, Head, useForm, router } from '@inertiajs/vue3';
import { onMounted, ref, watch, computed } from 'vue';
import Swal from 'sweetalert2';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
const props = defineProps({
  auth: { type: Object },
  master_mapel: {
    type: Object,
    required: true,
  },
});

// akses lewat props.master_mapel
console.log('isi master_mapel', props.master_mapel);
const form = useForm({
  name: props.auth?.user?.name || '',
  email: props.auth?.user?.email || '',
  role_type: props.auth?.user?.role_type || '',
});

const currentPage = ref(1); // Gunakan ini sebagai pengganti pageNumber
const searchTerm = ref('');

const kelasUrl = computed(() => {
  const url = new URL(route('matapelajaran.index'));
  url.searchParams.set('page', currentPage.value); // Gunakan currentPage
  if (searchTerm.value) {
    url.searchParams.set('search', searchTerm.value);
  }
  return url;
});

const deleteForm = useForm({}); // Deklarasi deleteForm

const deleteMapel = (mapel) => {
  Swal.fire({
    title: 'Apakah Anda yakin?',
    text: 'Data Mata Pelajaran ini akan dihapus dan tidak dapat dikembalikan!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#3085d6',
    cancelButtonColor: '#d33',
    confirmButtonText: 'Ya, hapus!',
    cancelButtonText: 'Batal',
  }).then((result) => {
    if (result.isConfirmed) {
      // Gunakan deleteForm untuk penghapusan
      deleteForm.delete(route('matapelajaran.destroy', { id: mapel.id }), {
        preserveScroll: true,
        onSuccess: () => {
          Swal.fire(
            'Terhapus!',
            'Data Mata Pelajaran telah berhasil dihapus.',
            'success'
          );
          currentPage.value = 1; // Reset halaman setelah penghapusan
          router.visit(kelasUrl.value.toString(), {
            replace: true,
            preserveState: true,
            preserveScroll: true,
          });
        },
        onError: (errors) => {
          console.error('Error deleting Mata Pelajaran:', errors);
          Swal.fire(
            'Gagal!',
            'Terjadi kesalahan saat menghapus data Mata Pelajaran.',
            'error'
          );
        },
      });
    }
  });
};

const updatedPageNumber = (link) => {
  if (!link.url || link.active) return;

  const url = new URL(link.url);
  const pageNumber = url.searchParams.get('page');
  if (pageNumber) {
    currentPage.value = parseInt(pageNumber, 10);
  }
};

onMounted(() => {
  initFlowbite();
});

const paginationLinks = computed(() => {
  const linksObj = props.master_mapel?.links || {};
  return [
    { url: linksObj.prev, label: 'Prev', active: false },
    { url: linksObj.first, label: '1', active: false },
    { url: linksObj.next, label: 'Next', active: false },
    { url: linksObj.last, label: 'Last', active: false },
  ].filter((link) => link.url !== null);
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
    <!-- start1 -->

    <main class="p-4 md:ml-64 h-auto pt-20">
      <Head title="Mata Pelajaran" />
      <div class="container mx-auto p-4">
        <div class="px-4 py-4 sm:px-6 lg:px-8">
          <div class="sm:flex sm:items-center mb-10">
            <div class="sm:flex-auto">
              <h1 class="text-3xl font-semibold text-gray-900">
                Mata Pelajaran
              </h1>
              <p class="mt-2 text-sm text-gray-700">Daftar Mata Pelajaran</p>
            </div>

            <div class="mt-4 sm:mt-0 sm:ml-16 sm:flex-none">
              <Link
                :href="route('matapelajaran.create')"
                class="btn btn-primary modal-title fs-5 inline-flex items-center gap-x-2 py-2 px-4 text-sm font-medium text-white border border-transparent rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                <i class="fa fa-plus"></i>
                <span>Tambah Mata Pelajaran</span>
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
                placeholder="Cari Data Mata Pelajaran.."
                id="search"
                class="block rounded-lg border-0 py-2 pl-10 text-gray-900 ring-1 ring-inset ring-gray-200 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6 w-96"
              />
            </div>
          </div>
          <div class="mt-8 flex flex-col mr-20">
            <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
              <div
                class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8"
              >
                <div
                  class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg relative"
                >
                  <table class="min-w-full bg-white">
                    <thead class="bg-gray-50">
                      <tr>
                        <th
                          scope="col"
                          class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                        >
                          ID
                        </th>
                        <th
                          scope="col"
                          class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6"
                        >
                          Kode Mata Pelajaran
                        </th>
                        <th
                          scope="col"
                          class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                        >
                          Nama Mata Pelajaran
                        </th>
                        <th
                          scope="col"
                          class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                        >
                          Hari
                        </th>
                        <th
                          scope="col"
                          class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900"
                        >
                          Jam Ke
                        </th>
                        <th
                          scope="col"
                          class="relative whitespace-nowrap py-3.5 pl-3 pr-4 text-right text-sm font-semibold text-gray-900 sm:pr-6"
                        >
                          Action
                        </th>
                      </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                      <tr
                        v-for="mapel in props.master_mapel.data"
                        :key="mapel.id"
                      >
                        <td
                          class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                        >
                          {{ mapel.id }}
                        </td>
                        <td
                          class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6"
                        >
                          {{ mapel.kode_mapel }}
                        </td>
                        <td
                          class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                        >
                          {{ mapel.mapel }}
                        </td>
                        <td
                          class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                        >
                          {{
                            mapel.hari !== null && mapel.hari !== ''
                              ? mapel.hari
                              : 'Tidak Ditetapkan'
                          }}
                        </td>
                        <td
                          class="whitespace-nowrap px-3 py-4 text-sm text-gray-500"
                        >
                          {{
                            mapel.jam_ke !== null && mapel.jam_ke !== ''
                              ? mapel.jam_ke
                              : 'Tidak Ditetapkan'
                          }}
                        </td>
                        <td
                          class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6"
                        >
                          <Link
                            :href="
                              route('matapelajaran.edit', { mapel: mapel.id })
                            "
                            class="text-indigo-600 hover:text-indigo-900"
                          >
                            Edit
                          </Link>
                          <button
                            @click="deleteMapel(mapel)"
                            class="ml-2 text-indigo-600 hover:text-indigo-900"
                          >
                            Hapus
                          </button>
                        </td>
                      </tr>
                    </tbody>
                  </table>
                  <Pagination
                    :data="master_mapel"
                    :links="paginationLinks"
                    :updatedPageNumber="updatedPageNumber"
                    :onChangePage="updatedPageNumber"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- end1-->

    <!-- Sidebar -->
    <SidebarAdmin />
  </div>
</template>
