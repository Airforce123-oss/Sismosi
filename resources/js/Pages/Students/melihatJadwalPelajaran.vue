<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import { usePage, useForm, Head } from '@inertiajs/vue3';
import { initFlowbite } from 'flowbite';
import SidebarStudent from '@/Components/SidebarStudent.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';

const { props } = usePage();

const schedule = ref(props.schedule ?? []);
const kelas = ref(props.kelas ?? '-');
const waliKelas = ref(props.wali_kelas ?? '-');

// Hari-hari dalam seminggu
const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];
const filterHari = ref('');
const filterBulan = ref('');
const filterTahun = ref('');
const studentName = ref(props.student_name ?? '-');

watch([filterHari, filterBulan, filterTahun], () => {
  axios
    .get('/melihatJadwalPelajaran', {
      params: {
        hari: filterHari.value,
        bulan: filterBulan.value,
        tahun: filterTahun.value,
      },
    })
    .then((res) => {
      schedule.value = res.data.schedule;
    })
    .catch((err) => {
      console.error('Gagal memuat jadwal:', err);
    });
});

// Fungsi bantu: ambil jadwal per hari
const filteredScheduleByDay = (day) => {
  return schedule.value
    .filter((slot) => !!slot.jadwal?.[day])
    .map((slot) => ({
      jam_ke: slot.jam_ke,
      jam: slot.jam,
      jadwal: { [day]: slot.jadwal[day] },
    }));
};

// Data user dari session (opsional)
const userName = ref('');
const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

const fetchSessionData = async () => {
  try {
    const response = await axios.get('/api/session-name');
    userName.value = response.data.name;
  } catch (error) {
    console.error('Gagal ambil data session:', error);
  }
};

onMounted(() => {
  initFlowbite();
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
    <main class="p-6 md:ml-64 min-h-screen pt-20 bg-gray-50">
      <Head title="Jadwal Pelajaran" />

      <div class="max-w-5xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-bold text-center text-blue-700 mb-1">
          Jadwal Pelajaran Kelas {{ kelas }}
        </h2>
        <p class="text-center text-gray-500 mb-6">
          Nama Siswa: {{ studentName }}
        </p>

        <!-- Filter -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Hari</label
            >
            <select
              v-model="filterHari"
              class="w-full rounded border-gray-300 focus:ring focus:ring-blue-300"
            >
              <option value="">Pilih Semua</option>
              <option v-for="day in days" :key="day" :value="day">
                {{ day }}
              </option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Bulan</label
            >
            <select
              v-model="filterBulan"
              class="w-full rounded border-gray-300 focus:ring focus:ring-blue-300"
            >
              <option value="">Pilih Semua</option>
              <option v-for="b in 12" :key="b" :value="b">{{ b }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1"
              >Tahun Ajaran</label
            >
            <input
              v-model="filterTahun"
              type="text"
              class="w-full rounded border-gray-300 focus:ring focus:ring-blue-300"
              placeholder="cth: 2025/2026"
            />
          </div>
        </div>

        <!-- Tabel Jadwal -->
        <div
          v-for="day in days"
          :key="day"
          class="mb-8 rounded-lg overflow-hidden shadow border"
        >
          <div
            class="bg-blue-600 text-white text-center py-2 font-semibold capitalize"
          >
            {{ day }}
          </div>
          <table class="w-full text-sm text-left">
            <thead class="bg-gray-100">
              <tr>
                <th class="p-3 border">Jam Ke</th>
                <th class="p-3 border">Waktu</th>
                <th class="p-3 border">Mata Pelajaran</th>
                <th class="p-3 border">Kelas</th>
                <th class="p-3 border">Wali Kelas</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="day === 'sabtu' || day === 'minggu'">
                <td
                  colspan="5"
                  class="p-3 text-center text-red-500 font-semibold border"
                >
                  Libur
                </td>
              </tr>
              <template v-else>
                <template v-if="filteredScheduleByDay(day).length">
                  <tr
                    v-for="slot in filteredScheduleByDay(day)"
                    :key="`${day}-${slot.jam_ke}`"
                    :class="{ 'bg-gray-50': slot.jam_ke % 2 === 0 }"
                  >
                    <td class="p-3 border">{{ slot.jam_ke }}</td>
                    <td class="p-3 border">{{ slot.jam }}</td>
                    <td class="p-3 border">
                      {{ slot.jadwal[day]?.mapel || '✖' }}
                    </td>
                    <td class="p-3 border">
                      {{ slot.jadwal[day]?.kelas || '-' }}
                    </td>
                    <td class="p-3 border">
                      {{ slot.jadwal[day]?.wali_kelas || '-' }}
                    </td>
                  </tr>
                </template>
                <tr v-else>
                  <td
                    colspan="5"
                    class="p-3 text-center text-gray-500 italic border"
                  >
                    Tidak ada jadwal
                  </td>
                </tr>
              </template>
            </tbody>
          </table>
        </div>
      </div>
    </main>

    <!-- Sidebar -->
    <SidebarStudent
      :student_id="props.student_id"
      :student_name="props.student_name"
    />
  </div>
</template>
