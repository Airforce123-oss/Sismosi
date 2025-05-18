<script setup>
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { usePage, useForm, Head } from '@inertiajs/vue3';
import { initFlowbite } from 'flowbite';

// === Props ===
const { props } = usePage();
const schedule = ref(props.schedule ?? []); // jadwal dari backend
const kelasList = ref(props.kelasList ?? []); // daftar kelas
const waliKelas = ref(props.wali_kelas ?? []); // daftar wali kelas)

// === Hari-hari dalam seminggu ===
const days = ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu'];

// === Kelas yang sedang dipilih ===
const selectedKelas = ref(''); // default: semua kelas

// === Fungsi bantu: cek apakah ada jadwal di hari tertentu ===
const hasJadwalForDay = (day) => {
  return schedule.value.some((slot) => !!slot?.jadwal?.[day]);
};

// === Filter jadwal sesuai hari dan kelas yang dipilih ===
const filteredScheduleByDay = (day) => {
  return schedule.value
    .filter((slot) => {
      const entry = slot.jadwal?.[day];
      if (!entry) return false;
      if (!selectedKelas.value) return true;
      return entry.kelas_id === selectedKelas.value;
    })
    .map((slot) => ({
      jam_ke: slot.jam_ke,
      jam: slot.jam,
      jadwal: { [day]: slot.jadwal[day] },
      wali_kelas: slot.wali_kelas,
    }));
};

// === Data user dari session ===
const userName = ref('');
const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

// === Ambil data user dari API session ===
const fetchSessionData = async () => {
  try {
    const response = await axios.get('/api/session-name');
    userName.value = response.data.name;
  } catch (error) {
    console.error('Gagal ambil data session:', error);
  }
};

// === Inisialisasi plugin & ambil data saat komponen dimount ===
onMounted(() => {
  initFlowbite();
  fetchSessionData();
  console.log('Daftar kelas:', kelasList.value);
  console.log('Daftar wali kelas', waliKelas.value);
});

// === Log perubahan kelas terpilih ===
watch(selectedKelas, (newVal) => {
  console.log('Kelas yang dipilih:', newVal);
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

    <main class="p-7 md:ml-64 h-screen pt-20">
      <Head title="Melihat Jadwal Pelajaran" />
      <div class="text-2xl col-sm-12 mb-10">
        <div class="page-sub-header"></div>
      </div>
      <div class="mt-10 max-w-4xl mx-auto">
        <!-- Dropdown Pilih Kelas -->
        <div class="mb-6">
          <label for="kelas" class="block text-gray-700 font-semibold mb-2">
            Pilih Kelas
          </label>
          <div class="relative">
            <select
              id="kelas"
              v-model="selectedKelas"
              class="block w-full appearance-none bg-white border border-gray-300 text-gray-700 py-2 px-4 pr-10 rounded-xl shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition duration-200"
            >
              <option value="">Semua Kelas</option>
              <option
                v-for="(name, id) in kelasList"
                :key="id"
                :value="Number(id)"
              >
                {{ name }}
              </option>
            </select>

            <!-- Icon Dropdown -->
            <div
              class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-400"
            >
              <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                viewBox="0 0 24 24"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  d="M19 9l-7 7-7-7"
                ></path>
              </svg>
            </div>
          </div>
        </div>

        <!--   <p>Selected Kelas ID: {{ selectedKelas }}</p>-->

        <h2 class="text-xl text-center font-bold text-gray-800 mb-4">
          Laporan Jadwal Mingguan
        </h2>

        <div
          v-for="day in days"
          :key="day"
          class="mb-8 border border-gray-300 rounded-lg overflow-hidden"
        >
          <div
            class="bg-blue-600 px-4 py-2 text-lg text-center text-white font-semibold capitalize border-b"
          >
            {{ day }}
          </div>

          <table class="w-full text-sm text-left">
            <thead class="bg-gray-200">
              <tr>
                <th class="p-3 border">Jam Ke</th>
                <th class="p-3 border">Waktu</th>
                <th class="p-3 border">Mata Pelajaran</th>
                <th class="p-3 border">Kelas</th>
                <th class="p-3 border">Wali Kelas</th>
              </tr>
            </thead>
            <tbody>
              <!-- Hari libur (Sabtu & Minggu) -->
              <tr v-if="day === 'sabtu' || day === 'minggu'">
                <td
                  colspan="4"
                  class="p-3 border text-center text-red-500 font-semibold"
                >
                  Libur
                </td>
              </tr>

              <!-- Hari kerja -->
              <template v-else>
                <template v-if="filteredScheduleByDay(day).length > 0">
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

                <!-- Jika tidak ada jadwal -->
                <tr v-else>
                  <td
                    colspan="4"
                    class="p-3 border text-center text-gray-500 italic"
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
              href="student-dashboard"
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
              href="melihatTugas"
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
              <span class="ml-3">Melihat Tugas</span>
            </a>
          </li>
          <li>
            <a
              href="melihatDataAbsensiSiswa"
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
              <span class="ml-3">Melihat Absensi</span>
            </a>
          </li>
          <li>
            <a
              href="melihatJadwalPelajaran"
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
              <span class="ml-3">Melihat Jadwal Pelajaran</span>
            </a>
          </li>
        </ul>
      </div>
    </aside>
  </div>
</template>
