<script setup>
import { ref, onMounted, nextTick, watch } from 'vue';
import { initFlowbite } from 'flowbite';
import SidebarAdmin from '@/Components/SidebarAdmin.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
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

console.log('props:', props);
const totalMaleStudentsPerMonth = props.totalMaleStudentsPerMonth;
const totalFemaleStudentsPerMonth = props.totalFemaleStudentsPerMonth;


console.log('Laki-laki per bulan:', totalMaleStudentsPerMonth);
console.log('Perempuan per bulan:', totalFemaleStudentsPerMonth);

// Chart options, watchers, observers... (tidak diubah)
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

    if (!newChartOptions || !newSeries) {
      console.warn('Data belum lengkap, menunggu update...');
      return;
    }

    await nextTick();

    if (
      !document.querySelector('#apexcharts-area') ||
      !document.querySelector('#bar')
    ) {
      console.error('Chart elements not found in DOM.');
      return;
    }

    checkChartElements();
  } catch (error) {
    console.error('Error during watch execution:', error);
  }
});

const initializeChart = (selector, options) => {
  const element = document.querySelector(selector);
  if (!element) {
    console.error(`Element with selector '${selector}' not found.`);
    return null;
  }

  const chart = new ApexCharts(element, options);
  chart.render();
  return chart;
};

onMounted(async () => {
  nextTick(() => {
    checkChartElements();
    initializeChart('#apexcharts-area', lineChartOptions);
    initializeChart('#bar', barChartOptions);
  });

  initFlowbite();
});

const createChart = (selector, options) => {
  const chartElement = document.querySelector(selector);
  if (!chartElement) {
    console.error(`Element with selector '${selector}' not found.`);
    return null;
  }

  const chart = new ApexCharts(chartElement, options);
  chart.render();
  return chart;
};

const lineChartOptions = {
  chart: { height: 300, type: 'line', toolbar: { show: false } },
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth' },
  series: [
    {
      name: 'Guru',
      color: '#3D5EE1',
      data: [props.totalTeachers || 0],
    },
    {
      name: 'Siswa',
      color: '#70C4CF',
      data: [props.total || 0],
    },
  ],
  xaxis: { categories: ['Total'] },
};

const barChartOptions = {
  chart: { type: 'bar', height: 300, toolbar: { show: false } },
  dataLabels: { enabled: false },
  plotOptions: { bar: { columnWidth: '55%', endingShape: 'rounded' } },
  stroke: { show: true, width: 2, colors: ['transparent'] },
  series: [
    {
      name: 'Laki-laki',
      color: '#70C4CF',
      data: totalMaleStudentsPerMonth,
    },
    {
      name: 'Perempuan',
      color: '#3D5EE1',
      data: totalFemaleStudentsPerMonth,
    },
  ],
  xaxis: {
    categories: [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'Mei',
      'Jun',
      'Jul',
      'Agu',
      'Sep',
      'Okt',
      'Nov',
      'Des',
    ],
  },
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

      <div class="container mx-auto px-4 py-6">
        <div
          class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
        >
          <!-- Data Siswa -->
          <div
            class="bg-gradient-to-r from-blue-600 to-blue-800 text-white rounded-2xl shadow-lg p-6 transition hover:shadow-xl min-h-[180px] flex flex-col justify-between"
          >
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-4xl text-white font-extrabold">
                  {{ props.total }}
                </h3>
                <p class="mt-1 text-base font-semibold">Total Siswa</p>
                <p class="mt-1 text-xs text-white/80 italic">
                  Data seluruh siswa dalam sistem
                </p>
              </div>
              <div
                class="flex items-center justify-center w-16 h-16 bg-white/10 rounded-full"
              >
                <i class="ion ion-person-stalker text-4xl"></i>
              </div>
            </div>
            <div class="mt-4 flex justify-between items-center text-sm">
              <a href="/students" class="flex items-center hover:underline">
                Lihat detail <i class="fas fa-arrow-circle-right ml-2"></i>
              </a>
              <span class="bg-white/10 px-2 py-1 rounded text-xs font-medium"
                >Updated</span
              >
            </div>
          </div>

          <!-- Data Guru -->
          <div
            class="bg-gradient-to-r from-green-500 to-green-700 text-white rounded-2xl shadow-lg p-6 transition hover:shadow-xl min-h-[180px] flex flex-col justify-between"
          >
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-4xl text-white font-extrabold">
                  {{ props.totalTeachers }}
                </h3>
                <p class="mt-1 text-base font-semibold">Total Guru</p>
                <p class="mt-1 text-xs text-white/80 italic">
                  Guru aktif dalam sistem
                </p>
              </div>
              <div
                class="flex items-center justify-center w-16 h-16 bg-white/10 rounded-full"
              >
                <i class="ion ion-ios-people text-4xl"></i>
              </div>
            </div>
            <div class="mt-4 flex justify-between items-center text-sm">
              <a href="/teachers" class="flex items-center hover:underline">
                Lihat detail <i class="fas fa-arrow-circle-right ml-2"></i>
              </a>
              <span class="bg-white/10 px-2 py-1 rounded text-xs font-medium"
                >Aktif</span
              >
            </div>
          </div>

          <!-- Data Kelas -->
          <div
            class="bg-gradient-to-r from-purple-500 to-purple-700 text-white rounded-2xl shadow-lg p-6 transition hover:shadow-xl min-h-[180px] flex flex-col justify-between"
          >
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-4xl text-white font-extrabold">
                  {{ props.totalClasses }}
                </h3>
                <p class="mt-1 text-base font-semibold">Total Kelas</p>
                <p class="mt-1 text-xs text-white/80 italic">
                  Kelas terdaftar tahun ini
                </p>
              </div>
              <div
                class="flex items-center justify-center w-16 h-16 bg-white/10 rounded-full"
              >
                <i class="ion ion-university text-4xl"></i>
              </div>
            </div>
            <div class="mt-4 flex justify-between items-center text-sm">
              <a href="/kelas" class="flex items-center hover:underline">
                Lihat detail <i class="fas fa-arrow-circle-right ml-2"></i>
              </a>
              <span class="bg-white/10 px-2 py-1 rounded text-xs font-medium"
                >Tahun Ajaran</span
              >
            </div>
          </div>

          <!-- Data Mapel -->
          <div
            class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-2xl shadow-lg p-6 transition hover:shadow-xl min-h-[180px] flex flex-col justify-between"
          >
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-4xl text-white font-extrabold">
                  {{ props.totalMapel }}
                </h3>
                <p class="mt-1 text-base font-semibold">Total Mata Pelajaran</p>
                <p class="mt-1 text-xs text-white/80 italic">
                  Mapel aktif di sistem
                </p>
              </div>
              <div
                class="flex items-center justify-center w-16 h-16 bg-white/10 rounded-full"
              >
                <i class="ion ion-ios-book text-4xl"></i>
              </div>
            </div>
            <div class="mt-4 flex justify-between items-center text-sm">
              <a
                href="/mataPelajaran"
                class="flex items-center hover:underline"
              >
                Lihat detail <i class="fas fa-arrow-circle-right ml-2"></i>
              </a>
              <span class="bg-white/10 px-2 py-1 rounded text-xs font-medium"
                >Semester ini</span
              >
            </div>
          </div>

          <!-- Data Jabatan -->
          <div
            class="bg-gradient-to-r from-cyan-500 to-cyan-700 text-white rounded-2xl shadow-lg p-6 transition hover:shadow-xl min-h-[180px] flex flex-col justify-between"
          >
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-4xl text-white font-extrabold">
                  {{ props.totalJabatan }}
                </h3>
                <p class="mt-1 text-base font-semibold">Total Jabatan</p>
                <p class="mt-1 text-xs text-white/80 italic">
                  Jabatan guru dan staf lainnya
                </p>
              </div>
              <div
                class="flex items-center justify-center w-16 h-16 bg-white/10 rounded-full"
              >
                <i class="ion ion-briefcase text-4xl"></i>
              </div>
            </div>
            <div class="mt-4 flex justify-between items-center text-sm">
              <a
                href="/indexMasterJabatan"
                class="flex items-center hover:underline"
              >
                Lihat detail <i class="fas fa-arrow-circle-right ml-2"></i>
              </a>
              <span class="bg-white/10 px-2 py-1 rounded text-xs font-medium"
                >Struktur aktif</span
              >
            </div>
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
