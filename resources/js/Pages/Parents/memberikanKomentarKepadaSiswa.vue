<script setup>
import { ref, onMounted, nextTick, watch, computed } from 'vue';
import { initFlowbite } from 'flowbite';
import Swal from 'sweetalert2';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

import { Link, useForm, usePage, Head } from '@inertiajs/vue3';
import axios from 'axios';
const userName = ref('');
const { props } = usePage();
const showAddModal = ref(false);
const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

const students = ref(props.students || []);

const kelasFilter = ref('');
console.log('Students:', students.value);
const siswaFilter = ref('');
const kelasList = ref(props.kelasList || []);

const filteredStudentsByKelas = computed(() => {
  if (!form.selected_kelas) return [];
  return students.value.filter(
    (s) => s.class && s.class.name === form.selected_kelas
  );
});

const filteredStudents = computed(() => {
  let result = students.value;
  if (kelasFilter.value) {
    result = result.filter(
      (s) => kelasList.value[s.class_id - 1] === kelasFilter.value
    );
  }
  if (siswaFilter.value) {
    result = result.filter((s) =>
      s.name.toLowerCase().includes(siswaFilter.value.toLowerCase())
    );
  }
  return result;
});
const submitKomentar = async () => {
  if (!form.student_id || !form.komentar) {
    Swal.fire('Peringatan', 'Student dan komentar harus diisi!', 'warning');
    return;
  }
  try {
    await axios.post('/api/komentar-siswa', {
      student_id: form.student_id,
      komentar: form.komentar,
    });
    await Swal.fire({
      icon: 'success',
      title: 'Berhasil!',
      text: 'Komentar berhasil disimpan!',
      timer: 1500,
      showConfirmButton: false,
    });
    form.komentar = '';
    form.student_id = '';
    showAddModal.value = false;
  } catch (e) {
    Swal.fire('Gagal', 'Gagal menyimpan komentar', 'error');
    console.error(e.response?.data || e.message);
  }
};

onMounted(() => {
  initFlowbite();
});

watch(
  kelasList,
  (val) => {
    console.log('kelasList:', val);
  },
  { immediate: true }
);
</script>

<style scoped>
@import url('https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css');
.bg-primary1 {
  background-color: #0e70cc;
}

.bg-success {
  background-color: #28a745;
}

.bg-warning {
  background-color: #ffc107;
}

.bg-cyan {
  background-color: #10b0cc;
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

    <main class="p-7 md:ml-64 h-screen pt-20">
      <Head title="Komentar Siswa" />
      <div
        class="mb-6 bg-blue-50 px-4 py-3 rounded-lg shadow-sm w-full max-w-5xl mx-auto"
      >
        <div class="grid grid-cols-1 md:grid-cols-6 gap-3 items-center">
          <label
            class="font-semibold text-blue-700 flex items-center gap-1 min-w-max md:col-span-1"
          >
            <svg
              class="w-5 h-5 text-blue-500"
              fill="none"
              stroke="currentColor"
              stroke-width="2"
              viewBox="0 0 24 24"
            >
              <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            Filter Kelas:
          </label>
          <div class="min-w-0 md:col-span-1">
            <select
              v-model="kelasFilter"
              class="w-full border border-blue-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition text-blue-700 bg-white shadow-sm"
            >
              <option value="">Semua Kelas</option>
              <option v-for="kelas in kelasList" :key="kelas" :value="kelas">
                {{ kelas }}
              </option>
            </select>
          </div>
          <div class="min-w-0 md:col-span-2">
            <input
              v-model="siswaFilter"
              type="text"
              placeholder="Cari nama siswa..."
              class="w-full border border-blue-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 transition text-blue-700 bg-white shadow-sm"
            />
          </div>
          <div class="min-w-0 md:col-span-1 flex">
            <button
              v-if="kelasFilter || siswaFilter"
              @click="
                kelasFilter = '';
                siswaFilter = '';
              "
              class="w-full px-3 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs transition min-w-max"
              title="Reset filter"
            >
              Reset
            </button>
          </div>
          <div class="min-w-0 md:col-span-1 flex">
            <button
              @click="showAddModal = true"
              class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-blue-500 to-blue-700 hover:from-blue-600 hover:to-blue-800 text-white px-5 py-2 rounded shadow transition-all duration-200"
            >
              <svg
                class="w-5 h-5 flex-shrink-0"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
              >
                <path d="M12 4v16m8-8H4" />
              </svg>
              Tambah Komentar
            </button>
          </div>
        </div>
      </div>
      <div
        class="max-w-6xl mx-auto mt-10 bg-white rounded-xl shadow p-6 space-y-6"
      >
        <h2
          class="text-2xl font-bold mb-2 text-blue-700 text-center tracking-wide"
        >
          Input Komentar untuk Siswa
        </h2>
        <hr class="mb-4 border-blue-200" />
        <div class="overflow-x-auto rounded-lg shadow">
          <table
            class="w-full border border-gray-200 rounded-lg overflow-hidden text-sm"
          >
            <thead class="bg-blue-100 text-blue-900 uppercase text-xs">
              <tr>
                <th class="p-3 border-b border-gray-200 text-left">
                  Nama Siswa
                </th>
                <th class="p-3 border-b border-gray-200 text-left">Kelas</th>
                <th class="p-3 border-b border-gray-200 text-left">Komentar</th>
                <th class="p-3 border-b border-gray-200 text-left">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="student in filteredStudents"
                :key="student.id"
                class="even:bg-blue-50 odd:bg-white hover:bg-blue-200/40 transition-colors duration-150 group"
              >
                <td
                  class="p-3 border-b border-gray-100 font-medium group-hover:text-blue-800"
                >
                  {{ student.name }}
                </td>
                <td class="p-3 border-b border-gray-100">
                  <span
                    class="inline-block px-2 py-0.5 rounded bg-blue-200 text-blue-800 text-xs font-semibold"
                  >
                    {{ kelasList[student.class_id - 1] || '-' }}
                  </span>
                </td>
                <td class="p-3 border-b border-gray-100">
                  <span
                    v-if="
                      student.komentar_siswas && student.komentar_siswas.length
                    "
                  >
                    <span
                      v-for="komentar in student.komentar_siswas"
                      :key="komentar.id"
                      class="block px-2 py-1 rounded bg-blue-50 text-gray-700 mb-1"
                    >
                      {{ komentar.komentar }}
                    </span>
                  </span>
                  <span v-else class="text-gray-400">Belum ada komentar</span>
                </td>
                <td class="p-3 border-b border-gray-100 flex gap-2">
                  <button
                    @click="editKomentar(student)"
                    class="flex items-center gap-1 bg-yellow-400 hover:bg-yellow-500 text-white px-3 py-1 rounded shadow transition"
                    title="Edit"
                  >
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      viewBox="0 0 24 24"
                    >
                      <path
                        d="M15.232 5.232l3.536 3.536M9 13l6-6 3 3-6 6H9v-3z"
                      />
                    </svg>
                    Edit
                  </button>
                  <button
                    @click="hapusKomentar(student)"
                    class="flex items-center gap-1 bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded shadow transition"
                    title="Hapus"
                  >
                    <svg
                      class="w-4 h-4"
                      fill="none"
                      stroke="currentColor"
                      stroke-width="2"
                      viewBox="0 0 24 24"
                    >
                      <path d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    Hapus
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Modal -->
        <transition name="fade">
          <div
            v-if="showAddModal"
            class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
          >
            <div
              class="bg-white rounded-xl shadow-2xl p-6 w-full max-w-md animate-fadeIn relative"
            >
              <!-- Modal Title -->
              <div class="flex items-center gap-2 mb-4">
                <svg
                  class="w-7 h-7 text-blue-600"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path
                    d="M17 8h2a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2"
                  ></path>
                  <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <h3 class="text-xl text-center font-bold text-blue-700 flex-1">
                  Tambah Komentar Siswa
                </h3>
                <button
                  @click="showAddModal = false"
                  class="text-gray-400 hover:text-red-500 transition"
                  title="Tutup"
                >
                  <svg
                    class="w-6 h-6"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                  >
                    <path d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
              <hr class="mb-4 border-blue-100" />
              <form @submit.prevent="submitKomentar">
                <!-- Dropdown Kelas -->
                <div class="mb-3">
                  <label class="block mb-1 font-medium">Kelas</label>
                  <select
                    v-model="form.selected_kelas"
                    class="w-full border rounded px-2 py-1 focus:ring-2 focus:ring-blue-400"
                  >
                    <option value="" disabled>Pilih kelas</option>
                    <option
                      v-for="kelas in kelasList"
                      :key="kelas"
                      :value="kelas"
                    >
                      {{ kelas }}
                    </option>
                  </select>
                </div>
                <!-- Dropdown Siswa -->
                <div class="mb-3">
                  <label class="block mb-1 font-medium">Siswa</label>
                  <select
                    v-model="form.student_id"
                    class="w-full border rounded px-2 py-1 focus:ring-2 focus:ring-blue-400"
                    :disabled="!form.selected_kelas"
                  >
                    <option value="" disabled>Pilih siswa</option>
                    <option
                      v-for="student in students.filter(
                        (s) => kelasList[s.class_id - 1] === form.selected_kelas
                      )"
                      :key="student.id"
                      :value="student.id"
                    >
                      {{ student.name }}
                    </option>
                  </select>
                </div>
                <div class="mb-3">
                  <label class="block mb-1 font-medium">Komentar</label>
                  <input
                    v-model="form.komentar"
                    type="text"
                    class="w-full border rounded px-2 py-1 focus:ring-2 focus:ring-blue-400"
                    placeholder="Tulis komentar..."
                  />
                </div>
                <div class="flex gap-2 justify-end mt-4">
                  <button
                    type="button"
                    @click="showAddModal = false"
                    class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300"
                  >
                    Batal
                  </button>
                  <button
                    type="submit"
                    class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white"
                  >
                    Simpan
                  </button>
                </div>
              </form>
            </div>
          </div>
        </transition>
      </div>
    </main>

    <!-- Sidebar -->
    <aside
      class="fixed top-0 left-0 z-40 w-60 h-screen pt-4 transition-transform -translate-x-full bg-white border-r border-gray-200 md:translate-x-0 dark:bg-gray-800 dark:border-gray-900"
      aria-label="Sidenav"
      id="drawer-navigation"
      style=""
    >
      <div class="overflow-y-auto py-5 px-3 h-full bg-white dark:bg-gray-800">
        <ul class="space-y-2">
          <li>
            <a
              href="/parent-dashboard"
              class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
            >
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
                />
              </svg>
              <span class="ml-3">Beranda</span>
            </a>
          </li>
          <li>
            <a
              href="#"
              class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
            >
              <svg
                viewBox="0 0 256 256"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
              >
                <rect fill="none" height="256" width="256" />
                <path
                  d="M226.5,56.4l-96-32a8.5,8.5,0,0,0-5,0l-95.9,32h-.2l-1,.5h-.1l-1,.6c0,.1-.1.1-.2.2l-.8.7h0l-.7.8c0,.1-.1.1-.1.2l-.6.9c0,.1,0,.1-.1.2l-.4.9h0l-.3,1.1v.3A3.7,3.7,0,0,0,24,64v80a8,8,0,0,0,16,0V75.1L73.6,86.3A63.2,63.2,0,0,0,64,120a64,64,0,0,0,30,54.2,96.1,96.1,0,0,0-46.5,37.4,8.1,8.1,0,0,0,2.4,11.1,7.9,7.9,0,0,0,11-2.3,80,80,0,0,1,134.2,0,8,8,0,0,0,6.7,3.6,7.5,7.5,0,0,0,4.3-1.3,8.1,8.1,0,0,0,2.4-11.1A96.1,96.1,0,0,0,162,174.2,64,64,0,0,0,192,120a63.2,63.2,0,0,0-9.6-33.7l44.1-14.7a8,8,0,0,0,0-15.2ZM128,168a48,48,0,0,1-48-48,48.6,48.6,0,0,1,9.3-28.5l36.2,12.1a8,8,0,0,0,5,0l36.2-12.1A48.6,48.6,0,0,1,176,120,48,48,0,0,1,128,168Z"
                />
              </svg>
              <span class="ml-3">Memeriksa Tugas Submit</span>
            </a>
          </li>
          <li>
            <a
              href="membuat-enrollment"
              class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
            >
              <svg
                viewBox="0 0 512 512"
                width="24"
                height="24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <g id="E-learning_notification">
                  <path
                    d="M243.0771,299.7515V251.3271a12.5756,12.5756,0,0,0-12.5615-12.5615H212.44a5,5,0,0,0,0,10h18.0752a2.5646,2.5646,0,0,1,2.5615,2.5615v48.4244a2.5645,2.5645,0,0,1-2.5615,2.5615H102.127a2.5645,2.5645,0,0,1-2.5616-2.5615V251.3271a2.5646,2.5646,0,0,1,2.5616-2.5615h83.8183a5,5,0,1,0,0-10H102.127a12.5757,12.5757,0,0,0-12.5616,12.5615v48.4244A12.5757,12.5757,0,0,0,102.127,312.313H230.5156A12.5756,12.5756,0,0,0,243.0771,299.7515Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M305.1309,238.7656H270.8574a10.4457,10.4457,0,0,0-10.4336,10.4336v52.68a10.4458,10.4458,0,0,0,10.4336,10.4341h34.2735a10.4458,10.4458,0,0,0,10.4336-10.4341v-52.68A10.4457,10.4457,0,0,0,305.1309,238.7656Zm.4336,63.1133a.4343.4343,0,0,1-.4336.4341H270.8574a.4343.4343,0,0,1-.4336-.4341v-52.68a.4339.4339,0,0,1,.4336-.4336h34.2735a.4339.4339,0,0,1,.4336.4336Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M309.1992,360.7461H215.2568a5,5,0,1,0,0,10h93.9424a5,5,0,0,0,0-10Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M309.1992,335.2017H215.2568a5,5,0,1,0,0,10h93.9424a5,5,0,0,0,0-10Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M467.8184,122.0205a109.7113,109.7113,0,0,0-219.3941-2.4991H145.1484V119.15a12.48,12.48,0,0,0-12.4658-12.4658H102.0312A12.48,12.48,0,0,0,89.5654,119.15v1.07a54.0392,54.0392,0,0,0-45.3833,53.2611V459.8264l0,.0193a39.83,39.83,0,0,0,39.8467,39.8467H355.6045a5.0406,5.0406,0,0,0,5-5.0474V459.8457a5,5,0,0,0-10,0v29.0819a29.8445,29.8445,0,0,1,5.24-58.8871,5.01,5.01,0,0,0,4.3484-3.0642c.0051-.0115.0122-.0215.0171-.0329a5.0159,5.0159,0,0,0,.2688-.8653c.0059-.0254.0168-.0483.022-.0738A5.0241,5.0241,0,0,0,360.6045,425l-.0022-.0217V231.7017A109.84,109.84,0,0,0,467.8184,122.0205ZM358.1055,22.3076a99.7129,99.7129,0,1,1-99.7129,99.7129A99.8261,99.8261,0,0,1,358.1055,22.3076ZM99.5654,119.15a2.4687,2.4687,0,0,1,2.4658-2.4658h30.6514a2.4686,2.4686,0,0,1,2.4658,2.4658l-.0019,61.2065-6.9883-9.3046a12.7468,12.7468,0,0,0-10.0557-5.1226c-.0693-.0015-.1386-.002-.2089-.002a12.7429,12.7429,0,0,0-10.003,4.8038L99.5654,181.11Zm217.91,340.6958a39.6352,39.6352,0,0,0,11.6631,28.1777l.0039.0035q.8613.8621,1.7695,1.6655H84.0283A29.8521,29.8521,0,0,1,55.084,467.1733H238.0771a5,5,0,1,0,0-10H54.3074A29.8832,29.8832,0,0,1,84.0283,430H330.8867A39.77,39.77,0,0,0,317.4756,459.8457ZM84.0283,420a39.7524,39.7524,0,0,0-29.8476,13.4894l.0014-.0276v-259.98A44.0226,44.0226,0,0,1,89.5654,130.37v51.1742a9.7374,9.7374,0,0,0,6.583,9.2915,10.007,10.007,0,0,0,3.3233.5733,9.7314,9.7314,0,0,0,7.624-3.7036l8.5957-10.7188a2.827,2.827,0,0,1,2.2529-1.0591,2.7824,2.7824,0,0,1,2.2178,1.13l7.2647,9.6709a9.8479,9.8479,0,0,0,17.7216-5.915V129.5214H248.6541a109.8717,109.8717,0,0,0,101.9482,101.95V420Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M309.2578,171.2344h21.2686a25.572,25.572,0,0,0,50.65-5,5,5,0,0,0-5-5h-66.919a4.84,4.84,0,0,1-4.8349-4.835V145.6577a23.5977,23.5977,0,0,0,18.5556-21.7641c1.1221-19.8521,8.1309-43.5943,35.127-44.0254,26.9961.4311,34.0049,24.1733,35.1269,44.0254a23.6,23.6,0,0,0,18.5557,21.7641v10.7417a4.84,4.84,0,0,1-4.834,4.835h-9.5557a5,5,0,0,0,0,10h9.5557a14.8511,14.8511,0,0,0,14.834-14.835V141.1709a5,5,0,0,0-5-5h-.1192a13.5457,13.5457,0,0,1-13.4521-12.8418c-1.4985-26.5106-11.5112-43.8823-28.6445-50.4795V65.6013a16.4352,16.4352,0,0,0-16.41-16.42h-1.04a16.4439,16.4439,0,0,0-16.42,16.42v7.6323c-16.5585,6.8655-26.237,24.0745-27.708,50.096a13.5441,13.5441,0,0,1-13.4511,12.8413h-.12a5,5,0,0,0-5,5v15.2285A14.8518,14.8518,0,0,0,309.2578,171.2344Zm46.3467,10.5718a15.5882,15.5882,0,0,1-14.7337-10.5718h29.4673A15.588,15.588,0,0,1,355.6045,181.8062ZM350.7021,65.6013a6.4274,6.4274,0,0,1,6.42-6.42h1.04a6.4188,6.4188,0,0,1,6.41,6.42v4.7431a53.7425,53.7425,0,0,0-6.3946-.4762c-.0488-.001-.0957-.001-.1445,0a53.2611,53.2611,0,0,0-7.3311.597Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M300.7432,101.8721c.122.0088.2431.0127.3632.0127a5.0007,5.0007,0,0,0,4.9825-4.6416c1.1465-15.9453,11.3965-21.0577,11.9209-21.3086A5,5,0,0,0,313.874,66.83c-.6592.2959-16.164,7.5039-17.76,29.6963A5,5,0,0,0,300.7432,101.8721Z"
                    stroke="black"
                    stroke-width="4"
                  />
                  <path
                    d="M397.9336,75.9316c.4394.2085,10.7773,5.295,11.9277,21.3116a5.0007,5.0007,0,0,0,4.9825,4.6416c.12,0,.2412-.0039.3632-.0127a5.0012,5.0012,0,0,0,4.6289-5.3457c-1.5947-22.1924-17.1-29.4-17.76-29.6963a5,5,0,1,0-4.1426,9.1015Z"
                    stroke="black"
                    stroke-width="4"
                  />
                </g>
              </svg>
              <span class="ml-3">Melihat Nilai Siswa</span>
            </a>
          </li>
          <li>
            <a
              href="membuatTugasSiswa"
              class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
            >
              <svg
                viewBox="0 0 256 256"
                xmlns="http://www.w3.org/2000/svg"
                width="24"
                height="24"
              >
                <rect fill="none" height="256" width="256" />
                <path
                  d="M226.5,56.4l-96-32a8.5,8.5,0,0,0-5,0l-95.9,32h-.2l-1,.5h-.1l-1,.6c0,.1-.1.1-.2.2l-.8.7h0l-.7.8c0,.1-.1.1-.1.2l-.6.9c0,.1,0,.1-.1.2l-.4.9h0l-.3,1.1v.3A3.7,3.7,0,0,0,24,64v80a8,8,0,0,0,16,0V75.1L73.6,86.3A63.2,63.2,0,0,0,64,120a64,64,0,0,0,30,54.2,96.1,96.1,0,0,0-46.5,37.4,8.1,8.1,0,0,0,2.4,11.1,7.9,7.9,0,0,0,11-2.3,80,80,0,0,1,134.2,0,8,8,0,0,0,6.7,3.6,7.5,7.5,0,0,0,4.3-1.3,8.1,8.1,0,0,0,2.4-11.1A96.1,96.1,0,0,0,162,174.2,64,64,0,0,0,192,120a63.2,63.2,0,0,0-9.6-33.7l44.1-14.7a8,8,0,0,0,0-15.2ZM128,168a48,48,0,0,1-48-48,48.6,48.6,0,0,1,9.3-28.5l36.2,12.1a8,8,0,0,0,5,0l36.2-12.1A48.6,48.6,0,0,1,176,120,48,48,0,0,1,128,168Z"
                />
              </svg>
              <span class="ml-3">Memberikan Komentar Kepada Siswa</span>
            </a>
          </li>

          <li>
            <a
              href="bukuPenghubung"
              class="flex items-center p-2 text-base font-medium text-gray-900 rounded-lg dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 group"
            >
              <svg
                viewBox="0 0 576 512"
                class="w-6 h-6"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M144.3 32.04C106.9 31.29 63.7 41.44 18.6 61.29c-11.42 5.026-18.6 16.67-18.6 29.15l0 357.6c0 11.55 11.99 19.55 22.45 14.65c126.3-59.14 219.8 11 223.8 14.01C249.1 478.9 252.5 480 256 480c12.4 0 16-11.38 16-15.98V80.04c0-5.203-2.531-10.08-6.781-13.08C263.3 65.58 216.7 33.35 144.3 32.04zM557.4 61.29c-45.11-19.79-88.48-29.61-125.7-29.26c-72.44 1.312-118.1 33.55-120.9 34.92C306.5 69.96 304 74.83 304 80.04v383.1C304 468.4 307.5 480 320 480c3.484 0 6.938-1.125 9.781-3.328c3.925-3.018 97.44-73.16 223.8-14c10.46 4.896 22.45-3.105 22.45-14.65l.0001-357.6C575.1 77.97 568.8 66.31 557.4 61.29z"
                />
              </svg>
              <span class="ml-3">Melihat Presensi Siswa</span>
            </a>
          </li>
        </ul>
      </div>
    </aside>
  </div>
</template>

<script>
export default {
  setup() {},
};
</script>
