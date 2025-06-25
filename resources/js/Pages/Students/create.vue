<script setup>
import { Link, useForm, router } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { watch, ref, onMounted } from 'vue';
import { initFlowbite } from 'flowbite';
import axios from 'axios';
import InputError from '@/Components/InputError.vue';
import Swal from 'sweetalert2';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';

// Props dari controller Laravel (via Inertia)
const props = defineProps({
  classes: Object,
  genders: { type: Object, default: () => ({ data: [] }) },
  religions: { type: Object, default: () => ({ data: [] }) },
  nomor_induk: { type: String, default: '' }, // ← nomor_induk (string)
  no_induk_id: { type: Number, default: null }, // ← id relasi dari tabel no_induks
});

// Data dropdown
const classes = ref(props.classes.data || []);
const genders = ref(props.genders.data || []);
const religions = ref(props.religions.data || []);
const sections = ref({}); // kalau nanti kamu pakai section_id

// Inertia Form setup
const form = useForm({
  name: '',
  no_induk: '',
  student_id: props.nomor_induk || '', // ← tetap string NIS, bukan ID siswa
  no_induk_id: props.no_induk_id ?? null, // ← tetap pastikan null jika tidak ada
  gender_id: '',
  class_id: '',
  religion_id: '',
});

// Debugging (bisa dihapus di produksi)
console.log('props.no_induk_id:', props.no_induk_id);
console.log('form.no_induk_id:', form.no_induk_id);

// Ambil section (opsional)
watch(
  () => form.class_id,
  (newValue) => {
    if (newValue) getSections(newValue);
  }
);

const getSections = async (class_id) => {
  try {
    const response = await axios.get(`/api/sections?class_id=${class_id}`);
    sections.value = response.data;
  } catch (error) {
    console.error('Error fetching sections:', error);
  }
};

// Kirim form ke Laravel
const submit = () => {
  form.post(route('students.store'), {
    onSuccess: () => {
      Swal.fire({
        title: 'Berhasil!',
        text: 'Data siswa berhasil disimpan.',
        icon: 'success',
        confirmButtonText: 'OK',
      }).then(() => {
        router.visit(route('students.index'), { replace: true });
      });
    },
    onError: () => {
      const errorMessages = Object.values(form.errors).join('\n');
      Swal.fire({
        title: 'Gagal!',
        text: errorMessages || 'Terjadi kesalahan saat menyimpan data siswa.',
        icon: 'error',
        confirmButtonText: 'OK',
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
      <div class="sm:flex sm:items-center">
        <div class="max-w-full mx-auto py-6 sm:px-6 lg:px-8 mt-20">
          <div class="lg:grid lg:grid-cols-12 lg:gap-x-5">
            <div class="space-y-6 sm:px-6 lg:px-0 lg:col-span-12">
              <!-- Formulir Tambah Siswa -->
              <form @submit.prevent="submit">
                <div class="shadow sm:rounded-md sm:overflow-hidden">
                  <div class="bg-white py-6 px-4 space-y-6 sm:p-6">
                    <div>
                      <h3 class="text-lg leading-6 font-medium text-gray-900">
                        Informasi Siswa
                      </h3>
                      <p class="mt-1 text-sm text-gray-500">
                        Gunakan Form ini untuk mengisi data siswa
                      </p>
                    </div>

                    <div class="grid grid-cols-6 gap-6">
                      <!-- Nomor Induk Baru -->
                      <div class="col-span-6 sm:col-span-3">
                        <label
                          for="no_induk"
                          class="block text-sm font-medium text-gray-700"
                        >
                          Nomor Induk
                        </label>
                        <input
                          v-model="form.no_induk"
                          type="text"
                          id="no_induk"
                          placeholder="Masukkan Nomor Induk Baru"
                          class="mt-1 block w-full border border-gray-300 text-gray-900 rounded-md shadow-sm py-2 px-3 focus:outline-none sm:text-sm"
                          :class="{
                            'text-red-900 border-red-300': form.errors.no_induk,
                          }"
                        />
                        <InputError
                          class="mt-2"
                          :message="form.errors.no_induk"
                        />
                      </div>

                      <!-- Nama -->
                      <div class="col-span-6 sm:col-span-3">
                        <label
                          for="name"
                          class="block text-sm font-medium text-gray-700"
                          >Nama</label
                        >
                        <input
                          v-model="form.name"
                          type="text"
                          id="name"
                          placeholder="Masukkan Nama"
                          class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none sm:text-sm"
                          :class="{
                            'text-red-900 border-red-300': form.errors.name,
                          }"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                      </div>

                      <!-- Jenis Kelamin -->
                      <div class="col-span-6 sm:col-span-3">
                        <label
                          for="gender_id"
                          class="block text-sm font-medium text-gray-700"
                          >Jenis Kelamin</label
                        >
                        <select
                          v-model="form.gender_id"
                          id="gender_id"
                          class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none sm:text-sm"
                          :class="{
                            'text-red-900 border-red-300':
                              form.errors.gender_id,
                          }"
                        >
                          <option value="">Pilih Jenis Kelamin</option>
                          <option
                            v-for="item in genders"
                            :key="item.id"
                            :value="item.id"
                          >
                            {{ item.name }}
                          </option>
                        </select>
                        <InputError
                          class="mt-2"
                          :message="form.errors.gender_id"
                        />
                      </div>

                      <!-- Kelas -->
                      <div class="col-span-6 sm:col-span-3">
                        <label
                          for="class_id"
                          class="block text-sm font-medium text-gray-700"
                          >Kelas</label
                        >
                        <select
                          v-model="form.class_id"
                          id="class_id"
                          class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none sm:text-sm"
                          :class="{
                            'text-red-900 border-red-300': form.errors.class_id,
                          }"
                        >
                          <option value="">Pilih Kelas</option>
                          <option
                            v-for="item in classes"
                            :key="item.id"
                            :value="item.id"
                          >
                            {{ item.name }}
                          </option>
                        </select>
                        <InputError
                          class="mt-2"
                          :message="form.errors.class_id"
                        />
                      </div>

                      <!-- Agama -->
                      <div class="col-span-6 sm:col-span-3">
                        <label
                          for="religion_id"
                          class="block text-sm font-medium text-gray-700"
                          >Agama</label
                        >
                        <select
                          v-model="form.religion_id"
                          id="religion_id"
                          class="mt-1 block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none sm:text-sm"
                          :class="{
                            'text-red-900 border-red-300':
                              form.errors.religion_id,
                          }"
                        >
                          <option value="">Pilih Agama</option>
                          <option
                            v-for="item in religions"
                            :key="item.id"
                            :value="item.id"
                          >
                            {{ item.name }}
                          </option>
                        </select>
                        <InputError
                          class="mt-2"
                          :message="form.errors.religion_id"
                        />
                      </div>
                    </div>
                  </div>

                  <!-- Tombol -->
                  <div
                    class="px-4 py-3 bg-gray-50 text-right sm:px-6 flex justify-end"
                  >
                    <div class="flex items-center space-x-4">
                      <Link
                        :href="route('students.index')"
                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md bg-indigo-100 focus:outline-none"
                      >
                        Batal
                      </Link>
                      <button
                        type="submit"
                        class="btn btn-primary modal-title border border-transparent rounded-md shadow-sm py-2 px-4 text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none"
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
      </div>
    </main>

    <!-- end1-->

    <!-- Sidebar -->
    <SidebarAdmin />
  </div>
</template>
