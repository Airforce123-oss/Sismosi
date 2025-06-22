<script setup>
import { ref, onMounted, computed, watch, watchEffect } from 'vue';
import { initFlowbite } from 'flowbite';
import { usePage, Head, router } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import SidebarParent from '@/Components/SidebarParent.vue';
import Pagination from '@/Components/Pagination10.vue';

// Ambil props dari Inertia page
const { props } = usePage();
const data = props.data;
console.log('✅ Komponen Pagination9 diimport:', Pagination);

const kelasList = ref(props.kelasList);
const selectedClassId = ref(null);
const selectedClassName = ref(props.selectedClassName);

// Debug: tampilkan props utama untuk cek data
console.log('Props:', props);
console.log('Tugas (raw):', props.tugas);
console.log('Siswa (raw):', props.student);

// Form user (untuk update profile atau keperluan lain)
const form = ref({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

// Data tugas yang diterima dari backend
const tugas = computed(() => usePage().props.tugas);

console.log(tugas.data); // menampilkan Proxy(Array) {0: {…}, 1: {…}, 2: {…}, 3: {…}, 4: {…}}
console.log(tugas.meta); // menampilkan undefined
console.log(tugas.links); // menampilkan Proxy(Array) {0: {…}, 1: {…}, 2: {…}, 3: {…}}
console.log(usePage().props);

const tugasList = computed(() => tugas.value?.data ?? []);
console.log(
  'selectedClassId (typeof):',
  selectedClassId.value,
  typeof selectedClassId.value
);

// Metadata pagination tugas
const meta = computed(() => tugas.value?.meta ?? {});
console.log('Pagination Meta:', meta.value);

// Link pagination tugas
const links = computed(() => tugas.value?.links ?? []);
console.log('Pagination Links:', links.value);

// Data siswa
const student = ref(props.student || {});
console.log('Student:', student.value);

// ❗Perubahan penting: gunakan student.class?.name, bukan student.kelas?.name
console.log('Nama Kelas dari Relasi:', student.value.class?.name);

// Fungsi ketika tombol "Kerjakan" ditekan
function kerjakanTugas(task) {
  alert(`Mulai kerjakan tugas: ${task.id} - ${task.mapel?.mapel ?? ''}`);
}

const onChangeClass = () => {
  const classId = selectedClassId.value;

  router.get(
    '/memeriksa-tugas',
    { class_id: classId },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onSuccess: (page) => {},
    }
  );
};

const filteredTugasList = computed(() => {
  return tugasList.value.filter((task) => {
    const kelasId = task.kelas?.id;
    return selectedClassId.value ? kelasId === selectedClassId.value : true;
  });
});

function fetchTugasPage(page = 1) {
  const classIdToSend =
    selectedClassId.value !== null
      ? selectedClassId.value
      : student.value.kelas_id;

  console.log('📦 class_id yang dikirim ke pagination:', classIdToSend);

  router.get(
    '/memeriksa-tugas',
    {
      class_id: selectedClassId.value ?? student.value.kelas_id,
      page,
    },
    {
      preserveState: true,
      preserveScroll: true,
      replace: true,
      onSuccess: () => {
        console.log('📦 Berhasil fetch tugas halaman:', page);
      },
    }
  );
}

watch(
  () => props.data,
  (val) => {
    if (val && val.meta) {
      console.log('🧪 tugas meta:', val.meta);
    } else {
      console.log('❌ tugas belum tersedia:', val);
    }
  },
  { immediate: true }
);

onMounted(() => {
  console.log('👀 tugas di parent:', tugas);
  initFlowbite();
});

watch(
  () => props.selectedClassId,
  (val) => {
    selectedClassId.value = val != null && val !== 'null' ? Number(val) : null;
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
      <Head title="Memeriksa Tugas Siswa" />
      <div
        class="p-6 bg-gradient-to-br from-white via-indigo-50 to-indigo-100 rounded-xl shadow-xl border border-indigo-200"
      >
        <div
          class="flex flex-col md:flex-row items-center justify-between mb-8 border-b border-indigo-300 pb-4"
        >
          <h2
            class="text-3xl font-extrabold text-indigo-900 drop-shadow-sm mb-4 md:mb-0"
          >
            Tugas untuk Kelas
            <span
              class="text-indigo-600 ml-2 underline decoration-indigo-400 decoration-2 rounded-sm px-1"
            >
              {{ selectedClassName || student.class?.name || student.kelas_id }}
            </span>
          </h2>

          <!-- Filter Kelas -->
          <select
            v-model.number="selectedClassId"
            @change="onChangeClass"
            class="border border-indigo-400 rounded-md px-4 py-2 text-indigo-800 font-semibold focus:outline-none focus:ring-4 focus:ring-indigo-300 bg-white shadow-lg hover:shadow-indigo-300 transition duration-300"
          >
            <option :value="null" :selected="selectedClassId === null">
              Semua Kelas
            </option>

            <option
              v-for="kelas in kelasList"
              :key="kelas.id"
              :value="Number(kelas.id)"
            >
              {{ kelas.name }}
            </option>
          </select>
        </div>

        <div
          class="overflow-x-auto rounded-lg shadow-lg ring-1 ring-indigo-200"
        >
          <table
            class="min-w-full table-auto border-collapse bg-white rounded-lg shadow-md"
          >
            <thead class="bg-indigo-200 sticky top-0 z-30">
              <tr>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-indigo-900 uppercase tracking-wider"
                >
                  ID
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-indigo-900 uppercase tracking-wider"
                >
                  Mata Pelajaran
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-indigo-900 uppercase tracking-wider"
                >
                  Deskripsi
                </th>
                <th
                  class="px-6 py-3 text-left text-sm font-semibold text-indigo-900 uppercase tracking-wider"
                >
                  Kelas
                </th>
              </tr>
            </thead>
            <tbody class="text-indigo-900">
              <tr
                v-for="(task, index) in filteredTugasList"
                :key="task.id"
                class="border-b border-indigo-300 last:border-b-0 hover:bg-indigo-100 transition duration-300"
              >
                <td class="px-6 py-4 whitespace-nowrap font-semibold">
                  {{ (tugas.meta.from ?? 0) + index }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap font-medium">
                  {{ task.mapel?.mapel ?? '—' }}
                </td>
                <td class="px-6 py-4 whitespace-pre-wrap max-w-xl">
                  {{ task.description }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  {{ task.kelas?.name ?? '—' }}
                </td>
              </tr>

              <tr v-if="filteredTugasList.length === 0">
                <td colspan="5" class="text-center py-8 text-indigo-400 italic">
                  Tidak ada tugas untuk kelasmu saat ini.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <Pagination
          v-if="tugas && tugas.data && tugas.links"
          :data="tugas"
          :updatedPageNumber="fetchTugasPage"
        />
      </div>
    </main>

    <!-- Sidebar -->
    <SidebarParent />
  </div>
</template>
