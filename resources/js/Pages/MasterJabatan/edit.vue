<script setup>
import { initFlowbite } from 'flowbite';
import { Link, useForm, router } from '@inertiajs/vue3';
import Swal from 'sweetalert2';
import { ref, onMounted, watch } from 'vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { usePage } from '@inertiajs/vue3';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';

const props = defineProps({
  jabatan: Object,
});

console.log('Props diterima di edit.vue:', props.jabatan);

const emit = defineEmits(['close']); // ✅ penting!

const form = useForm({
  nama_jabatan: props.jabatan.data?.nama_jabatan || '',
  deskripsi: props.jabatan.data?.deskripsi || '',
});

const submit = () => {
  form.put(route('master-jabatan.update', props.jabatan.data?.id), {
    onSuccess: () => {
      Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: 'Data jabatan berhasil diperbarui.',
        confirmButtonText: 'OK',
      }).then(() => {
        emit('close'); // ✅ tutup modal kalau perlu
        router.visit(route('master-jabatan.index'));
      });
    },
    onError: (errors) => {
      Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: 'Periksa kembali inputan Anda.',
      });
      console.error('❌ Error saat update:', errors);
    },
  });
};
</script>

<template>
  <div class="antialiased bg-gray-50 dark:bg-gray-900">
    <!-- NAVBAR -->
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
            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h6a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z"
                clip-rule="evenodd"
              ></path>
            </svg>
            <span class="sr-only">Toggle sidebar</span>
          </button>
          <a href="#" class="flex items-center justify-between mr-4">
            <img src="/images/barunawati.jpeg" class="mr-3 h-8" alt="Logo" />
            <span class="self-center text-2xl font-semibold dark:text-white">
              SMA BARUNAWATI SURABAYA
            </span>
          </a>
        </div>
        <div class="flex items-center lg:order-2">
          <button
            type="button"
            class="flex mx-3 text-sm bg-gray-800 rounded-full md:mr-0 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
            id="user-menu-button"
            data-dropdown-toggle="dropdown"
          >
            <span class="sr-only">Open user menu</span>
            <svg
              baseProfile="tiny"
              height="24px"
              viewBox="0 0 24 24"
              width="24px"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path
                d="M12,3c0,0-6.186,5.34-9.643,8.232C2.154,11.416,2,11.684,2,12c0,0.553,0.447,1,1,1h2v7c0,0.553,0.447,1,1,1h3  c0.553,0,1-0.448,1-1v-4h4v4c0,0.552,0.447,1,1,1h3c0.553,0,1-0.447,1-1v-7h2c0.553,0,1-0.447,1-1c0-0.316-0.154-0.584-0.383-0.768  C18.184,8.34,12,3,12,3z"
                fill="black"
              />
            </svg>
          </button>

          <!-- DROPDOWN -->
          <div
            class="hidden z-50 my-4 w-56 text-base list-none bg-white rounded divide-y divide-gray-100 shadow dark:bg-gray-700 dark:divide-gray-600 rounded-xl"
            id="dropdown"
          >
            <div class="py-3 px-3">
              <div>
                <span
                  class="block text-sm font-semibold text-gray-900 dark:text-white"
                >
                  {{ $page.props.auth.user.email }}
                </span>
                <span
                  class="block text-sm text-gray-900 truncate dark:text-white"
                >
                  {{ $page.props.auth.user.name }}
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

    <!-- MAIN CONTENT -->
    <main class="p-4 md:ml-64 h-auto pt-20">
      <div class="max-w-full mx-auto py-6 sm:px-6 lg:px-8 mt-10">
        <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
          <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-12">
            <form @submit.prevent="submit">
              <div class="shadow sm:rounded-md sm:overflow-hidden">
                <div class="bg-white py-6 px-4 space-y-6 sm:p-6">
                  <div>
                    <h3 class="text-lg leading-6 font-medium text-gray-900">
                      Edit Jabatan
                    </h3>
                    <p class="mt-1 text-sm text-gray-500">
                      Gunakan Form ini untuk memperbarui data jabatan
                    </p>
                  </div>

                  <div class="grid grid-cols-6 gap-6">
                    <!-- Nama Jabatan -->
                    <div class="col-span-6 sm:col-span-3">
                      <label
                        for="nama_jabatan"
                        class="block text-sm font-medium text-gray-700"
                      >
                        Nama Jabatan
                      </label>
                      <input
                        v-model="form.nama_jabatan"
                        id="nama_jabatan"
                        type="text"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm"
                        :class="
                          form.errors.nama_jabatan ? 'border-red-500' : ''
                        "
                      />
                      <div
                        v-if="form.errors.nama_jabatan"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ form.errors.nama_jabatan }}
                      </div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="col-span-6 sm:col-span-3">
                      <label
                        for="deskripsi"
                        class="block text-sm font-medium text-gray-700"
                      >
                        Deskripsi
                      </label>
                      <textarea
                        v-model="form.deskripsi"
                        id="deskripsi"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 sm:text-sm"
                        :class="form.errors.deskripsi ? 'border-red-500' : ''"
                      ></textarea>
                      <div
                        v-if="form.errors.deskripsi"
                        class="text-sm text-red-600 mt-1"
                      >
                        {{ form.errors.deskripsi }}
                      </div>
                    </div>
                  </div>
                </div>

                <!-- ACTION BUTTONS -->
                <div
                  class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex justify-end"
                >
                  <div class="flex items-center space-x-4">
                    <Link
                      :href="route('master-jabatan.index')"
                      class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                    >
                      Batal
                    </Link>
                    <button
                      type="submit"
                      class="btn btn-primary modal-title border border-transparent rounded-md shadow-sm py-2 px-4 inline-flex justify-center text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                      :disabled="form.processing"
                    >
                      {{ form.processing ? 'Memperbarui...' : 'Perbarui' }}
                    </button>
                  </div>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>

    <!-- SIDEBAR -->
    <SidebarAdmin />
  </div>
</template>
