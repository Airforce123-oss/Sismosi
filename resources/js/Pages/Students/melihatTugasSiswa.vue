<script setup>
import { onMounted, ref, computed, watch, toRaw } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { initFlowbite } from 'flowbite';
import SidebarStudent from '@/Components/SidebarStudent.vue';
import VueApexCharts from 'vue3-apexcharts';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import $ from 'jquery';
import '@assets/plugins/simple-calendar/simple-calendar.css';

const userName = ref('');
const { props } = usePage();
const students = ref(props.students || []);
const filteredCourses = ref([]);
const teachers = ref(props.teachers || []);
const mapels = ref(props.mapels || []);
const courses = ref(props.courses || []);
const tugas = ref(props.tugas || { data: [], meta: {}, links: {} });
const classesForStudent = ref(props.classes_for_student || []);
const totalCourses = computed(() => props.tugas?.meta?.total ?? 0);

for (let i = 0; i < props.teachers.length; i++) {
  const teacher = props.teachers[i];
  const masterMapel = toRaw(teacher.masterMapel);

  //console.log(`--- Guru ke-${i} ---`);
  //console.log(`Nama Guru: ${teacher.name}`);
  //console.log(`NIP: ${teacher.nip}`);

  if (Array.isArray(masterMapel)) {
    if (masterMapel.length === 0) {
      //console.log(`⚠️ masterMapel kosong untuk guru ke-${i}`);
    } else {
      masterMapel.forEach((mapel, index) => {
        //console.log(`  Mapel ${index}:`, mapel);

        if (mapel.id && mapel.nama_mapel) {
          //console.log(`    → ID: ${mapel.id}, Nama: ${mapel.nama_mapel}`);
        } else {
          console.log(`    ⚠️ Mapel tidak lengkap:`, mapel);
        }
      });
    }
  } else {
    console.log(`❌ masterMapel bukan array di guru ke-${i}:`, masterMapel);
  }
}

const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

// Fungsi untuk mengambil data session
const fetchSessionData = async () => {
  try {
    const response = await axios.get('/api/session-name');
    userName.value = response.data.name;
  } catch (error) {
    console.error('There was an error fetching the session data:', error);
  }
};

// Inisialisasi kalender
// Memanggil Flowbite dan SimpleCalendar setelah komponen dimuat
onMounted(() => {
  initFlowbite();

  // Fetch session data
  fetchSessionData();
});
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
          <!--
                                        <button
                        type="button"
                        data-drawer-toggle="drawer-navigation"
                        aria-controls="drawer-navigation"
                        class="p-2 mr-1 text-gray-500 rounded-lg md:hidden hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-gray-700 focus:ring-4 focus:ring-gray-300 dark:focus:ring-gray-600"
                    >
                        <span class="sr-only">Toggle search</span>
                        <svg
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
                    -->
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

    <main class="p-7 md:ml-64 h-screen pt-5">
      <Head title="Dashboard" />
      <h2
        class="text-xl sm:text-2xl lg:text-3xl font-bold text-gray-800 mt-20 mb-6 text-center"
      >
        Melihat Tugas Siswa
      </h2>

      <!-- Tabel -->
      <div
        class="w-full overflow-x-auto overflow-y-auto max-h-[80vh] bg-white rounded-xl shadow-lg mb-8"
      >
        <table class="min-w-full table-auto border-collapse">
          <thead class="bg-gray-100 sticky top-0 z-10">
            <tr>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                ID
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                Mata Pelajaran
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                Deskripsi
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                Guru
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                Kelas
              </th>
              <th
                class="px-4 py-3 text-left text-sm font-semibold text-gray-700"
              >
                Aksi
              </th>
            </tr>
          </thead>
          <tbody class="text-gray-700 text-sm md:text-base">
            <tr
              v-for="task in tugas.data"
              :key="task.id"
              class="border-b hover:bg-gray-50 transition duration-150"
            >
              <td class="px-4 py-3 whitespace-nowrap">{{ task.id }}</td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ task.mapel?.mapel ?? '—' }}
              </td>
              <td class="px-4 py-3 whitespace-pre-wrap">
                {{ task.description }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ task.teacher?.name ?? '—' }}
              </td>
              <td class="px-4 py-3 whitespace-nowrap">
                {{ task.kelas?.name ?? '—' }}
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center justify-center space-x-2">
                  <button
                    @click="editTask(task)"
                    class="inline-flex items-center gap-2 bg-blue-500 text-white h-9 px-4 rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-300 transition"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="w-4 h-4"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M15.232 5.232l3.536 3.536M9 11l6-6m2 2L11 15H9v-2l6-6z"
                      />
                    </svg>
                    Edit
                  </button>

                  <button
                    @click="deleteTask(task.id)"
                    class="inline-flex items-center gap-2 bg-red-500 text-white h-9 px-4 rounded-lg hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-300 transition"
                  >
                    <svg
                      xmlns="http://www.w3.org/2000/svg"
                      class="w-4 h-4"
                      fill="none"
                      viewBox="0 0 24 24"
                      stroke="currentColor"
                    >
                      <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5-4h4a1 1 0 011 1v1H9V4a1 1 0 011-1z"
                      />
                    </svg>
                    Hapus
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </main>

    <!-- Sidebar -->
    <SidebarStudent :student_id="student_id" :student_name="student_name" />
  </div>
</template>
