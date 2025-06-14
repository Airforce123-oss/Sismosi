<script setup>
import { onMounted, ref, watchEffect, computed, watch } from 'vue';
import axios from 'axios';
import { Head, usePage, useForm, router } from '@inertiajs/vue3';
import SidebarStudent from '@/Components/SidebarStudent.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import VueApexCharts from 'vue3-apexcharts';
import VSelect from 'vue-select';
import 'vue-select/dist/vue-select.css';
import { initFlowbite } from 'flowbite';
import { useRoute } from 'vue-router';
import $ from 'jquery';
import '@assets/plugins/simple-calendar/simple-calendar.css';
import Login from './Auth/Login.vue';

// Ambil props dari Inertia
const userName = ref('');

// Ambil props yang sudah dikirimkan melalui Inertia
const { student_id, student_name, auth } = usePage().props;

console.log('✅ Student Name dari props:', student_name);
console.log('✅ Student ID dari props:', student_id);
console.log('✅ User:', auth?.user);

// Fungsi untuk mengambil data session
const fetchSessionData = async () => {
  try {
    const response = await axios.get('/api/session-name');
    userName.value = response.data.name;
  } catch (error) {
    console.error('There was an error fetching the session data:', error);
  }
};

const fetchLoggedInStudent = async () => {
  try {
    const response = await axios.get('/api/logged-in-student', {
      headers: { Authorization: `Bearer ${token}` },
    });

    console.log('✅ Fetched logged-in student:', response.data);

    if (response.data && response.data.id) {
      // Perbarui student_id hanya jika berbeda dari yang ada di props
      if (student_id.value !== response.data.id) {
        student_id.value = response.data.id;
        console.log('✅ Updated studentId:', student_id.value);
      }

      // Perbarui nama user pada auth.user jika ada perubahan
      if (response.data.name && auth.user) {
        auth.user.name = response.data.name;
        console.log('✅ Updated auth.user.name:', auth.user.name);
      }
    } else {
      console.error('❗ Invalid student data');
    }
  } catch (error) {
    //console.error('❌ Error fetching logged-in student:', error);
  }
};

// Inisialisasi kalender dan ambil session data saat komponen dimount
onMounted(() => {
  fetchLoggedInStudent();
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
                  {{ student_name }}
                </span>
                <span
                  class="block text-sm text-gray-900 truncate dark:text-white"
                >
                  <!--{{ form.role_type }}-->
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
    <!-- Main -->

    <main class="p-7 md:ml-64 h-screen pt-20">
      <Head title="Dashboard Siswa" />
      <div class="text-2xl col-sm-12 mb-10">
        <div>
          <h3 class="page-title">Selamat Datang {{ student_name }}</h3>
          <p class="text-gray-600">
            <!--            ID Siswa Anda: <strong>{{ student_id }}</strong>
-->
          </p>
        </div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
          <div
            class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6"
          >
            <!-- Card 1 -->
            <div
              class="bg-primary1 text-white p-4 rounded-xl shadow-md transition-transform hover:scale-105"
            >
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-3xl md:text-4xl font-bold text-white">8</h3>
                  <p class="font-semibold text-sm md:text-base">
                    Absensi Kehadiran
                  </p>
                </div>
                <i class="ion ion-person-stalker text-3xl md:text-4xl"></i>
              </div>
              <a
                href="#"
                class="block mt-4 text-sm hover:underline flex items-center gap-1"
              >
                Lihat detail <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>

            <!-- Card 2 -->
            <div
              class="bg-success text-white p-4 rounded-xl shadow-md transition-transform hover:scale-105"
            >
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-3xl md:text-4xl font-bold text-white">8</h3>
                  <p class="font-semibold text-sm md:text-base">Tugas</p>
                </div>
                <i class="ion ion-person-stalker text-3xl md:text-4xl"></i>
              </div>
              <a
                href="#"
                class="block mt-4 text-sm hover:underline flex items-center gap-1"
              >
                Lihat detail <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>

            <!-- Card 3 -->
            <div
              class="bg-cyan text-white p-4 rounded-xl shadow-md transition-transform hover:scale-105"
            >
              <div class="flex items-center justify-between">
                <div>
                  <h3 class="text-3xl md:text-4xl font-bold text-white">8</h3>
                  <p class="font-semibold text-sm md:text-base">
                    Data Mata Pelajaran
                  </p>
                </div>
                <i class="ion ion-log-in text-3xl md:text-4xl"></i>
              </div>
              <a
                href="/guru/profil"
                class="block mt-4 text-sm hover:underline flex items-center gap-1"
              >
                Lihat detail <i class="fas fa-arrow-circle-right"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
      <div class="row">
        <div class="col-12 col-lg-16 col-xl-6"></div>
      </div>
    </main>

    <!-- Sidebar -->
    <SidebarStudent :student_id="student_id" :student_name="student_name" />
  </div>
</template>
