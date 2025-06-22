<script setup>
import { initFlowbite } from 'flowbite';
import { Link, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, onMounted, watch } from 'vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';

const props = defineProps({
  jabatans: Array,
  mapel: Array,
  teacher: Object,
});

// Logging untuk debugging
console.log('✅ Props received in edit.vue:', props);

// Reactive refs
const jabatans = ref(props.jabatans || []);
const mapels = ref(props.mapel || []);

// Gunakan useForm untuk binding form
const formData = useForm({
  name: '',
  nip: '',
  email: '',
  jabatan_id: '',
  mapel_id: '',
});

// Isi data awal saat mount
onMounted(() => {
  if (props.teacher) {
    formData.name = props.teacher.name ?? '';
    formData.nip = props.teacher.nip ?? '';
    formData.email = props.teacher.email ?? '';
    formData.jabatan_id = props.teacher.jabatan_id ?? '';
    formData.mapel_id = props.teacher.mapel_id ?? '';
  }
  

  initFlowbite();
});

// Update mapels jika berubah
watch(
  () => props.mapel,
  (newMapels) => {
    if (Array.isArray(newMapels) && newMapels.length > 0) {
      mapels.value = newMapels;
    } else {
      console.warn('❗ mapel kosong atau tidak valid');
    }
  },
  { immediate: true }
);

// Fungsi submit
const submit = () => {
  console.log('🔁 Submitting update:', formData);

  formData.put(route('teachers.update', props.teacher.id), {
    onSuccess: () => {
      Swal.fire({
        title: 'Sukses!',
        text: 'Data guru berhasil diperbarui.',
        icon: 'success',
        confirmButtonText: 'OK',
      }).then(() => {
        router.visit(route('teachers.index'), { replace: true });
      });
    },
    onError: (errors) => {
      console.error('❌ Validation Errors:', errors);
      Swal.fire({
        title: 'Gagal!',
        text: 'Terjadi kesalahan saat memperbarui data.',
        icon: 'error',
        confirmButtonText: 'Tutup',
      });
    },
  });
};
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
            class="flex mx-3 text-sm bg-gray-800 rounded-full md:mr-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
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

    <!-- start1 -->

    <main class="p-4 md:ml-64 h-auto pt-20">
      <div class="max-w-full mx-auto py-6 sm:px-6 lg:px-8 mt-10">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
          <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-12">
            <form @submit.prevent="submit">
              <div class="shadow sm:rounded-md sm:overflow-hidden">
                <div class="bg-white py-6 px-4 space-y-6 sm:p-6">
                  <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                      Informasi Guru
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                      Gunakan Form ini untuk mengisi data guru
                    </p>
                  </div>

                  <div class="grid grid-cols-6 gap-6">
                    <!-- Nama -->
                    <div class="col-span-6 sm:col-span-3">
                      <label
                        for="name"
                        class="block text-sm font-medium text-gray-700"
                        >Nama</label
                      >
                      <input
                        v-model="formData.name"
                        type="text"
                        id="name"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm"
                        :class="formData.errors.name ? 'border-red-500' : ''"
                      />
                      <div
                        v-if="formData.errors.name"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ formData.errors.name }}
                      </div>
                    </div>

                    <!-- NIP -->
                    <div class="col-span-6 sm:col-span-3">
                      <label
                        for="nip"
                        class="block text-sm font-medium text-gray-700"
                        >NIP</label
                      >
                      <input
                        v-model="formData.nip"
                        type="text"
                        id="nip"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm"
                        :class="formData.errors.nip ? 'border-red-500' : ''"
                      />
                      <div
                        v-if="formData.errors.nip"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ formData.errors.nip }}
                      </div>
                    </div>

                    <!-- Dropdown Jabatan -->
                    <div class="col-span-6 sm:col-span-3">
                      <label
                        for="jabatan_id"
                        class="block text-sm font-medium text-gray-700"
                      >
                        Jabatan
                      </label>
                      <select
                        v-model="formData.jabatan_id"
                        id="jabatan_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm"
                        :class="
                          formData.errors.jabatan_id ? 'border-red-500' : ''
                        "
                      >
                        <option value="">Pilih Jabatan</option>
                        <option
                          v-for="jab in jabatans"
                          :key="jab.id"
                          :value="jab.id"
                        >
                          {{ jab.nama_jabatan }}
                        </option>
                      </select>
                      <div
                        v-if="formData.errors.jabatan_id"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ formData.errors.jabatan_id }}
                      </div>
                    </div>

                    <!-- Mapel -->
                    <div class="col-span-6 sm:col-span-3">
                      <label
                        for="mapel_id"
                        class="block text-sm font-medium text-gray-700"
                      >
                        Mata Pelajaran
                      </label>
                      <select
                        id="mapel_id"
                        v-model="formData.mapel_id"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm"
                        :class="
                          formData.errors.mapel_id ? 'border-red-500' : ''
                        "
                      >
                        <option value="">Pilih Mapel</option>
                        <option
                          v-for="mapel in mapels"
                          :key="mapel.id"
                          :value="mapel.id"
                        >
                          {{ mapel.mapel }} ({{ mapel.hari }}, Jam
                          {{ mapel.jam_ke }})
                        </option>
                      </select>
                      <div
                        v-if="formData.errors.mapel_id"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ formData.errors.mapel_id }}
                      </div>
                    </div>
                  </div>
                </div>

                <div
                  class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex justify-end"
                >
                  <div class="flex items-center space-x-4">
                    <Link
                      :href="route('teachers.index')"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                      Batal
                    </Link>
                    <button
                      type="submit"
                      class="btn btn-primary modal-title border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                      Simpan
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>

    <!-- end1-->

    <!-- Sidebar -->
    <SidebarAdmin />
  </div>
</template>
