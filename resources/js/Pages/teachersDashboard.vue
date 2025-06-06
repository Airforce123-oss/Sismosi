<script setup>
import { onMounted, ref } from 'vue';
import { initFlowbite } from 'flowbite';
import SidebarTeacher from '@/Components/SidebarTeacher.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import VueApexCharts from 'vue3-apexcharts';
//import ApexCharts from "apexcharts";
import { Link, useForm, usePage, Head } from '@inertiajs/vue3';
import $ from 'jquery';
//import "@/assets/plugins/jquery.simple-calendar.js";
//import "@assets/js/bootstrap-datetimepicker.min.js";
//import "@assets/plugins/simple-calendar/jquery.simple-calendar.js";
import '@assets/plugins/simple-calendar/jquery.simple-calendar.js';
import '@assets/plugins/simple-calendar/simple-calendar.css';

const userName = ref('');
const { props } = usePage();
const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

defineProps({
  total: Number, // Pastikan tipe data sesuai dengan yang dikirimkan dari Laravel
});

onMounted(() => {
  initFlowbite();

  // calendar trial
  const daysContainer = document.getElementById('days');
  const monthYearDisplay = document.getElementById('monthYear');
  /*
    const prevButton = document.getElementById("prev");
    const nextButton = document.getElementById("next");
    */

  const months = [
    'January',
    'February',
    'March',
    'April',
    'May',
    'June',
    'July',
    'August',
    'September',
    'October',
    'November',
    'December',
  ];
  let currentDate = new Date();

  function renderCalendar() {
    daysContainer.innerHTML = '';
    monthYearDisplay.textContent = `${
      months[currentDate.getMonth()]
    } ${currentDate.getFullYear()}`;

    const firstDayOfMonth = new Date(
      currentDate.getFullYear(),
      currentDate.getMonth(),
      1
    ).getDay();
    const daysInMonth = new Date(
      currentDate.getFullYear(),
      currentDate.getMonth() + 1,
      0
    ).getDate();

    for (let i = 0; i < firstDayOfMonth; i++) {
      daysContainer.appendChild(document.createElement('div'));
    }

    for (let day = 1; day <= daysInMonth; day++) {
      const dayElement = document.createElement('div');
      dayElement.textContent = day;
      dayElement.classList.add(
        'flex',
        'items-center',
        'justify-center',
        'w-12',
        'h-12'
      );

      if (
        day === currentDate.getDate() &&
        currentDate.getMonth() === new Date().getMonth() &&
        currentDate.getFullYear() === new Date().getFullYear()
      ) {
        dayElement.classList.add('bg-blue-500', 'text-white', 'rounded-full');
      }

      daysContainer.appendChild(dayElement);
    }
  }
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
      <Head title="Dashboard Guru" />
      <div class="text-2xl col-sm-12 mb-10">
        <div class="page-sub-header">
          <div>
            <h3 class="page-title">
              Selamat Datang {{ $page.props.auth.user.name }}!
            </h3>
          </div>
        </div>
      </div>

      <div class="container mx-auto py-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
          <!-- Data Siswa Card -->
          <div class="bg-primary1 text-white p-4 rounded shadow-md">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-4xl font-bold text-white">{{ total }} 125</h3>
                <p class="font-bold">Data Siswa</p>
              </div>
              <i class="ion ion-person-stalker text-4xl"></i>
            </div>
            <a href="#" class="block mt-4 text-sm text-white hover:underline">
              Lihat detail
              <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>

          <!-- Absensi Card -->
          <div class="bg-success text-white p-4 rounded shadow-md">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-4xl font-bold text-white">16</h3>
                <p class="font-bold">Total Kelas</p>
              </div>
              <i class="ion ion-person text-4xl"></i>
            </div>
            <a href="#" class="block mt-4 text-sm text-white hover:underline">
              Lihat detail
              <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>

          <!-- Input Card -->
          <div class="bg-warning text-white p-4 rounded shadow-md">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-4xl font-bold text-white">8</h3>
                <p class="font-bold">Total Mata Pelajaran</p>
              </div>
              <i class="ion ion-person text-4xl"></i>
            </div>
            <a href="#" class="block mt-4 text-sm text-white hover:underline">
              Lihat detail
              <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>
    </main>

    <!-- Sidebar -->
    <SidebarTeacher />
  </div>
</template>
