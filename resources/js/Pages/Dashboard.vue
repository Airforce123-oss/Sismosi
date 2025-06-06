<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { initFlowbite } from 'flowbite';
import  SidebarAdmin  from '@/Components/SidebarAdmin.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Link, useForm, usePage, Head } from '@inertiajs/vue3';
import ApexCharts from 'apexcharts';

import axios from 'axios';

const userName = ref('');
const { props } = usePage();
const form = useForm({
  name: props.auth.user.name,
  email: props.auth.user.email,
  role_type: props.auth.user.role_type,
});

const apexChartElement = ref(null);
const barChartElement = ref(null);
const mainChartElement = ref(null);

defineProps({ total: Number });

// Options untuk Chart Utama
const chartOptions = ref(null);
const series = ref(null);

const checkChartElements = () => {
  try {
    const apexChartElement = document.querySelector('#apexcharts-area');
    const barChartElement = document.querySelector('#bar');

    if (apexChartElement && barChartElement) {
      console.log('Chart elements found:', apexChartElement, barChartElement);
    } else {
      console.warn('Chart elements not found, waiting for data rendering...');
    }
  } catch (error) {
    console.error('Error in checkChartElements:', error);
  }
};

watch([chartOptions, series], async ([newChartOptions, newSeries]) => {
  try {
    console.log('chartOptions or series updated:', {
      newChartOptions,
      newSeries,
    });

    // Pastikan data sudah benar sebelum lanjut
    if (!newChartOptions || !newSeries) {
      console.warn('Data belum lengkap, menunggu update...');
      return;
    }

    await nextTick(); // Tunggu DOM untuk memperbarui
    if (
      !document.querySelector('#apexcharts-area') ||
      !document.querySelector('#bar')
    ) {
      console.error('Chart elements not found in DOM.');
      return;
    }
    checkChartElements();
    checkChartElements(); // Periksa apakah elemen sudah dirender
  } catch (error) {
    console.error('Error during watch execution:', error);
  }
});

const initializeChart = (selector, options) => {
  console.log(`Attempting to initialize chart with selector: '${selector}'`);

  const element = document.querySelector(selector);
  if (!element) {
    console.error(`Element with selector '${selector}' not found.`);
    return null;
  }

  const chart = new ApexCharts(element, options);
  chart.render();
  console.log(`Chart initialized:`, chart);
  return chart;
};

onMounted(async () => {
  // Tunggu hingga DOM diperbarui sepenuhnya
  nextTick(() => {
    checkChartElements();
    const apexChart = initializeChart('#apexcharts-area', lineChartOptions);
    const barChart = initializeChart('#bar', barChartOptions);

    if (apexChart && barChart) {
      console.log('Charts successfully initialized.');
    } else {
      console.error('Failed to initialize charts.');
    }
    const apexChartElement = document.querySelector('#apexcharts-area');
    const barChartElement = document.querySelector('#bar');
    if (apexChartElement && barChartElement) {
      console.log('Chart elements found:', apexChartElement, barChartElement);
    } else {
      console.log('Waiting for chart elements...');
    }
  });
  console.log('Rendering Chart...', apexChartElement.value);
  initFlowbite();
});

const observer = new MutationObserver((mutationsList) => {
  mutationsList.forEach((mutation) => {
    const apexChartElement = document.querySelector('#apexcharts-area');
    const barChartElement = document.querySelector('#bar');
    const mainChartElement = document.querySelector('#chart');

    if (
      apexChartElement.value.length > 0 &&
      barChartElement.value.length > 0 &&
      mainChartElement.value.length > 0
    ) {
      setupCharts();
    } else {
      console.error('Chart elements still not found.');
    }
  });
});

// Setup Charts setelah elemen DOM ditemukan
function setupCharts() {
  if (
    apexChartElement.value.length > 0 &&
    barChartElement.value.length > 0 &&
    mainChartElement.value.length > 0
  ) {
    createChart(apexChartElement.value[0], lineChartOptions);
    createChart(barChartElement.value[0], barChartOptions);
    createChart(mainChartElement.value[0], chartOptions);
  } else {
    console.error('Required chart elements still not found in DOM.');
  }
}

const createChart = (selector, options) => {
  console.log('Trying to initialize chart with selector:', selector);

  const chartElement = document.querySelector(selector);

  if (!chartElement) {
    console.error(`Element with selector '${selector}' not found.`);
    return null;
  }

  const chart = new ApexCharts(chartElement, options);
  chart.render(); // Metode render
  console.log('Chart initialized:', chart);
  return chart;
};

const lineChartOptions = {
  chart: { height: 300, type: 'line', toolbar: { show: false } },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth' },
  series: [
    { name: 'Guru', color: '#3D5EE1', data: [45, 60, 75, 51, 42, 42, 30] },
  ],
  xaxis: { categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'] },
};

// Options untuk Bar Chart
const barChartOptions = {
  chart: { type: 'bar', height: 300, toolbar: { show: false } },
  dataLabels: { enabled: false },
  plotOptions: { bar: { columnWidth: '55%', endingShape: 'rounded' } },
  stroke: { show: true, width: 2, colors: ['transparent'] },
  series: [
    {
      name: 'Laki-laki',
      color: '#70C4CF',
      data: [420, 532, 516, 575, 519, 517, 454],
    },
    {
      name: 'Perempuan',
      color: '#3D5EE1',
      data: [336, 612, 344, 647, 345, 563, 256],
    },
  ],
  xaxis: { categories: [2009, 2010, 2011, 2012, 2013, 2014, 2015] },
};
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
      <Head title="Dashboard Admin" />

      <div class="text-2xl col-sm-12 mb-10">
        <div class="page-sub-header">
          <h3 class="page-title">
            Selamat Datang {{ $page.props.auth.user.name }}!
          </h3>
        </div>
      </div>

      <div class="container mx-auto py-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="bg-primary1 text-white p-4 rounded shadow-md">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-4xl font-bold text-white">58</h3>
                <p class="font-bold">Data Siswa</p>
              </div>
              <i class="ion ion-person-stalker text-4xl"></i>
            </div>
            <a href="#" class="block mt-4 text-sm text-white hover:underline">
              Lihat detail
              <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>

          <div class="bg-success text-white p-4 rounded shadow-md">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-4xl font-bold text-white">16</h3>
                <p class="font-bold">Data Guru</p>
              </div>
              <i class="ion ion-person-stalker text-4xl"></i>
            </div>
            <a href="#" class="block mt-4 text-sm text-white hover:underline">
              Lihat detail
              <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>

          <div class="bg-warning text-white p-4 rounded shadow-md">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-4xl font-bold text-white">16</h3>
                <p class="font-bold">Data Kelas</p>
              </div>
              <i class="ion ion-stats-bars text-4xl"></i>
            </div>
            <a href="#" class="block mt-4 text-sm text-white hover:underline">
              Lihat detail
              <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>

          <!-- Profil Card -->
          <div class="bg-cyan text-white p-4 rounded shadow-md">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-4xl font-bold text-white">8</h3>
                <p class="font-bold">Data Mata Pelajaran</p>
              </div>
              <i class="ion ion-log-in text-4xl"></i>
            </div>
            <a
              href="/guru/profil"
              class="block mt-4 text-sm text-white hover:underline"
            >
              Lihat detail
              <i class="fas fa-arrow-circle-right"></i>
            </a>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-12 col-lg-6">
          <div class="card card-chart">
            <div class="card-header">
              <div class="row align-items-center">
                <div class="col-6">
                  <h5 class="card-title">Overview</h5>
                </div>
                <div class="col-6">
                  <ul class="chart-list-out">
                    <li><span class="circle-blue"></span>Guru</li>
                    <li><span class="circle-green"></span>Siswa</li>
                    <li class="star-menus">
                      <a href="javascript:;"
                        ><i class="fas fa-ellipsis-v"></i
                      ></a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div id="apexcharts-area"></div>
            </div>
          </div>
        </div>

        <div class="col-md-12 col-lg-6">
          <div class="card card-chart">
            <div class="card-header">
              <div class="row align-items-center">
                <div class="col-6">
                  <h5 class="card-title">Jumlah Siswa</h5>
                </div>
                <div class="col-6">
                  <ul class="chart-list-out">
                    <li><span class="circle-blue"></span>Perempuan</li>
                    <li><span class="circle-green"></span>Laki-laki</li>
                    <li class="star-menus">
                      <a href="javascript:;"
                        ><i class="fas fa-ellipsis-v"></i
                      ></a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
            <div class="card-body">
              <div id="bar"></div>
            </div>
          </div>
        </div>
      </div>
      <!--
             <p>
                Catatan: Melihat data siswa, mengelola data siswa, melihat data
                guru, mengelola data guru, melihat presensi siswa, mengelola
                presensi siswa, melihat presensi guru mengelola presensi guru,
                melihat mata pelajaran, mengelola mata pelajaran,
            </p>
            -->
    </main>

    <!-- Sidebar -->
    <SidebarAdmin />
  </div>
</template>

<script>
export default {
  setup() {},
};
</script>
