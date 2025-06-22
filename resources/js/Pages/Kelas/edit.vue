<script setup>
import { initFlowbite } from 'flowbite';
import { Link, useForm, router } from '@inertiajs/vue3';
import { onMounted } from 'vue';
import Swal from 'sweetalert2';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

import InputError from '@/Components/InputError.vue';

const props = defineProps({
  classForStudent: {
    type: Object,
    required: true,
  },
});

// Inisialisasi form dengan data dari props
const formData = useForm({
  name: props.classForStudent.name || '',
  kode_kelas: props.classForStudent.kode_kelas || '',
});

const submit = () => {
  console.log('🔵 Submitting data:', formData);

  formData.put(route('kelas.update', props.classForStudent.id), {
    onSuccess: () => {
      console.log(
        `✅ Data kelas berhasil diperbarui:\n🆔 ID: ${props.classForStudent.id}\n📛 Nama Kelas: ${formData.name}\n🏷️ Kode Kelas: ${formData.kode_kelas}`
      );

      Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: 'Data kelas berhasil diperbarui!',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK',
      }).then(() => {
        router.visit(route('kelas.index'));
      });
    },
    onError: (errors) => {
      console.error('❌ Error saat memperbarui data kelas:', errors);

      Swal.fire({
        icon: 'error',
        title: 'Gagal Memperbarui',
        text: 'Periksa kembali data yang diinput.',
        confirmButtonColor: '#d33',
        confirmButtonText: 'Tutup',
      });
    },
  });
};

onMounted(() => {
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
        <!--max-w-7xl mx-auto py-6 sm:px-6 lg:px-8 -->
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
          <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-12">
            <form @submit.prevent="submit">
              <div class="shadow sm:rounded-md sm:overflow-hidden">
                <div class="bg-white py-6 px-4 space-y-6 sm:p-6">
                  <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                      Informasi Kelas
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                      Gunakan Form ini untuk memperbarui data Kelas
                    </p>
                  </div>
                  <div class="grid grid-cols-6 gap-6">
                    <!-- Nama -->
                    <div class="col-span-6 sm:col-span-3">
                      <label
                        for="name"
                        class="block text-sm font-medium text-gray-700"
                      >
                        Nama Kelas
                      </label>
                      <input
                        v-model="formData.name"
                        type="text"
                        id="name"
                        placeholder="Kelas XII-4"
                        class="mt-1 block w-full ..."
                        :class="{
                          'text-red-900 focus:ring-red-500 focus:border-red-500 border-red-300':
                            formData.errors.name,
                        }"
                      />
                      <InputError
                        class="mt-2"
                        :message="formData.errors.name"
                      />
                    </div>

                    <!-- Kelas -->
                    <div class="col-span-6 sm:col-span-3">
                      <label
                        for="kode"
                        class="block text-sm font-medium text-gray-700"
                        >Kode Kelas</label
                      >
                      <input
                        v-model="formData.kode_kelas"
                        type="text"
                        id="kode-kelas"
                        placeholder="MK-XXXXXXXXX"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                        :class="{
                          'text-red-900 focus:ring-red-500 focus:border-red-500 border-red-300':
                            formData.errors.kode_kelas,
                        }"
                      />
                      <InputError
                        class="mt-2"
                        :message="formData.errors.kode_kelas"
                      />

                      <InputError
                        class="mt-2"
                        :message="formData.errors.kode"
                      />
                    </div>
                  </div>
                </div>
                <div
                  class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex justify-end"
                >
                  <div class="flex items-center space-x-4">
                    <Link
                      :href="route('kelas.index')"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md bg-indigo-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                      Batal
                    </Link>

                    <button
                      type="submit"
                      class="btn btn-primary modal-title border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                      Perbarui
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
